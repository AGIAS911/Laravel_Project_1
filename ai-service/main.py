from fastapi import FastAPI, WebSocket, WebSocketDisconnect
from starlette.concurrency import run_in_threadpool
from ultralytics import YOLO
import cv2
import numpy as np

app = FastAPI()

@app.get("/")
def health_check():
    return {"status": "running", "model": "YOLO26s"}

@app.websocket("/ws/detect")
async def detect(websocket: WebSocket):
    await websocket.accept()
    model = YOLO("yolo26s.pt")

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
                conf=0.5,
                tracker="bytetrack.yaml",
                verbose=False
            )

            detections = []

            for box in results[0].boxes:
                detections.append({
                    "track_id": int(box.id.item()) if box.id is not None else None,
                    "label": model.names[int(box.cls.item())],
                    "confidence": float(box.conf.item()),
                    "bbox": box.xyxy[0].tolist()
                })

            await websocket.send_json({
                "detections": detections
            })

    except WebSocketDisconnect:
        print("Client disconnected")