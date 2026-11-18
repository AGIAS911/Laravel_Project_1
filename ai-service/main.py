from fastapi import FastAPI, WebSocket, WebSocketDisconnect
from starlette.concurrency import run_in_threadpool
from ultralytics import YOLO
from collections import Counter
from pathlib import Path

import cv2
import numpy as np

from feature_extractor import FeatureExtractor
from identity_manager import ObjectIdentityManager

app = FastAPI()
SERVICE_DIR = Path(__file__).resolve().parent

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

@app.websocket("/ws/detect")
async def detect(websocket: WebSocket):
    await websocket.accept()

    model = await run_in_threadpool(YOLO, str(SERVICE_DIR / "yolo26s.pt"))
    extractor = await run_in_threadpool(FeatureExtractor)
    identity_manager = ObjectIdentityManager()
    track_hits = Counter()

    try:
        while True:
            data = await websocket.receive_bytes()

            frame = cv2.imdecode(
                np.frombuffer(data, np.uint8),
                cv2.IMREAD_COLOR
            )

            if frame is None:
                await websocket.send_json({"error": "Invalid frame"})
                continue

            results = await run_in_threadpool(
                model.track,
                frame,
                persist=True,
                conf=0.3,
                iou=0.4,
                tracker=str(SERVICE_DIR / "custom_tracker.yaml"),
                verbose=False
            )

            for box in results[0].boxes:
                print(model.names[int(box.cls.item())], float(box.conf.item()), flush=True)

            detections = []
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
                track_hits[track_id] += 1

                if track_hits[track_id] < 3:
                    continue

                bbox = box.xyxy[0].tolist()
                embedding = None
                mapped_id = identity_manager.track_mapping.get(track_id)
                if mapped_id is None or mapped_id in identity_manager.active_objects:
                    x1, y1, x2, y2 = np.clip(bbox, 0, [width, height, width, height]).astype(int)
                    embedding = await run_in_threadpool(extractor.extract, frame[y1:y2, x1:x2])

                object_id = identity_manager.identify(track_id, label, embedding)
                detections.append({
                    "track_id": track_id,
                    "object_id": object_id,
                    "label": label,
                    "confidence": float(box.conf.item()),
                    "bbox": bbox
                })

            await websocket.send_json({
                "detections": detections,
                "inventory": identity_manager.get_inventory(),
                "total_items": identity_manager.get_total()
            })

    except WebSocketDisconnect:
        print("Client disconnected")
