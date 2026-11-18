from ultralytics import YOLO

model = YOLO("yolo26s.pt")

model.track(
    source=0,
    show=True,
    conf=0.5,
    tracker="bytetrack.yaml",
    persist=True
)