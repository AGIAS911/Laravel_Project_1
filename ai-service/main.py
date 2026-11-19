import json
import os
import re
import time
from urllib.error import URLError
from urllib.request import Request, urlopen

from fastapi import FastAPI, WebSocket, WebSocketDisconnect
from starlette.concurrency import run_in_threadpool
from ultralytics import YOLO
from pathlib import Path

import cv2
import numpy as np

from feature_extractor import FeatureExtractor
from identity_manager import ObjectIdentityManager

app = FastAPI()
SERVICE_DIR = Path(__file__).resolve().parent
VISIBLE_TRACK_HITS = 2
INVENTORY_TRACK_HITS = 3
SCANNER_API_URL = os.environ.get("SCANNER_API_URL", "http://127.0.0.1:8000/api/scanner").rstrip("/")

ALLOWED_CLASSES = {
    "chair",
    "couch",
    "bed",
    "dining table",
    "tv",
    "refrigerator",
    "microwave",
    "oven",
    "laptop",
    "potted plant",
}

@app.get("/")
def health_check():
    return {"status": "running", "model": "YOLO26s"}


def scanner_request(path, token, items=None):
    payload = None if items is None else json.dumps({"items": items}).encode()
    request = Request(
        f"{SCANNER_API_URL}/{path}",
        data=payload,
        headers={"Authorization": f"Bearer {token}", "Accept": "application/json",
                 "Content-Type": "application/json"},
        method="GET" if payload is None else "PUT",
    )
    try:
        with urlopen(request, timeout=3) as response:
            return response.status == 204
    except (URLError, TimeoutError):
        return False


@app.websocket("/ws/detect")
async def detect(websocket: WebSocket):
    protocols = websocket.scope.get("subprotocols", [])
    token = next((value[6:] for value in protocols if re.fullmatch(r"token\.[a-zA-Z0-9]{64}", value)), None)
    if "moveit.scan" not in protocols or token is None or not await run_in_threadpool(scanner_request, "device", token):
        await websocket.close(code=1008)
        return
    await websocket.accept(subprotocol="moveit.scan")

    model = await run_in_threadpool(YOLO, str(SERVICE_DIR / "yolo26s.pt"))
    allowed_ids = [
        class_id for class_id, name in model.names.items() if name in ALLOWED_CLASSES
    ]
    extractor = await run_in_threadpool(FeatureExtractor)
    identity_manager = ObjectIdentityManager()
    track_observations = {}
    accepted_labels = {}
    synced_inventory = {}

    try:
        while True:
            data = await websocket.receive_bytes()
            if not await run_in_threadpool(scanner_request, "device", token):
                await websocket.close(code=1008)
                return
            start_time = time.perf_counter()

            frame = cv2.imdecode(
                np.frombuffer(data, np.uint8),
                cv2.IMREAD_COLOR
            )

            if frame is None:
                await websocket.send_json({"error": "Invalid frame"})
                continue

            tracking_start = time.perf_counter()
            results = await run_in_threadpool(
                model.track,
                frame,
                persist=True,
                conf=0.35,
                iou=0.5,
                imgsz=512,
                classes=allowed_ids,
                tracker=str(SERVICE_DIR / "custom_tracker.yaml"),
                verbose=False
            )

            tracking_ms = round((time.perf_counter() - tracking_start) * 1000, 2)
            embedding_seconds = 0.0

            detections = []
            pending_detections = []
            inventory_candidates = []
            counted_ids = set()
            identity_manager.start_frame()
            height, width = frame.shape[:2]

            for box in results[0].boxes:
                label = model.names[int(box.cls.item())]

                if label not in ALLOWED_CLASSES:
                    continue

                if box.id is None:
                    continue

                track_id = int(box.id.item())

                if track_id in counted_ids:
                    continue

                counted_ids.add(track_id)
                frame_number = identity_manager.frame_number
                previous_frame, previous_label, previous_hits = track_observations.get(
                    track_id, (0, None, 0)
                )
                consecutive_hits = 1
                if previous_frame == frame_number - 1 and previous_label == label:
                    consecutive_hits += previous_hits
                track_observations[track_id] = (frame_number, label, consecutive_hits)
                accepted_label = accepted_labels.get(track_id)

                if accepted_label is None and consecutive_hits < VISIBLE_TRACK_HITS:
                    continue

                bbox = box.xyxy[0].tolist()
                detection = {
                    "track_id": track_id,
                    "label": label,
                    "confidence": float(box.conf.item()),
                    "bbox": bbox,
                }
                if (
                    (accepted_label is None and consecutive_hits < INVENTORY_TRACK_HITS)
                    or (accepted_label is not None and label != accepted_label)
                ):
                    pending_detections.append(detection)
                    continue

                inventory_candidates.append(detection)

            # Give existing tracks first claim on their identities in this frame.
            inventory_candidates.sort(key=lambda item: identity_manager.get_mapped_id(
                item["track_id"], item["label"]
            ) is None)
            for detection in inventory_candidates:
                track_id, label = detection["track_id"], detection["label"]
                embedding = None
                if identity_manager.needs_embedding(track_id, label):
                    bbox = detection["bbox"]
                    x1, y1, x2, y2 = np.clip(bbox, 0, [width, height, width, height]).astype(int)
                    crop = frame[y1:y2, x1:x2]
                    if crop.size > 0:
                        identity_manager.record_embedding_attempt(track_id, label)
                        embedding_start = time.perf_counter()
                        embedding = await run_in_threadpool(extractor.extract, crop)
                        embedding_seconds += time.perf_counter() - embedding_start

                object_id = identity_manager.identify(track_id, label, embedding)
                if object_id is None:
                    pending_detections.append(detection)
                    continue
                accepted_labels[track_id] = label
                detection["object_id"] = object_id
                detections.append(detection)

            response = {
                "detections": detections,
                "pending_detections": pending_detections,
                "inventory": identity_manager.get_inventory(),
                "total_items": identity_manager.get_total()
            }
            processing_ms = round((time.perf_counter() - start_time) * 1000, 2)
            if response["inventory"] != synced_inventory:
                items = [
                    {"item_type": "sofa" if label == "couch" else label, "quantity": quantity}
                    for label, quantity in response["inventory"].items()
                ]
                if not await run_in_threadpool(scanner_request, "inventory", token, items):
                    await websocket.close(code=1011)
                    return
                synced_inventory = response["inventory"]
            print(
                f"INVENTORY STATE | items={response['inventory']} | total={response['total_items']}",
                flush=True,
            )
            resnet_ms = round(embedding_seconds * 1000, 2)
            print(
                f"YOLO + BoT-SORT: {tracking_ms:.2f} ms | "
                f"ResNet50: {resnet_ms:.2f} ms | Total: {processing_ms:.2f} ms | "
                f"Detections: {len(detections)} confirmed, {len(pending_detections)} pending",
                flush=True,
            )
            response.update(
                tracking_ms=tracking_ms,
                resnet_ms=resnet_ms,
                processing_ms=processing_ms,
            )
            await websocket.send_json(response)

    except WebSocketDisconnect:
        print("Client disconnected")
