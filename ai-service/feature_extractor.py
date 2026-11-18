import cv2
import torch
import numpy as np
from PIL import Image
from torchvision.models import resnet50, ResNet50_Weights

class FeatureExtractor:

    def __init__(self):
        self.device = "cuda" if torch.cuda.is_available() else "cpu"

        weights = ResNet50_Weights.DEFAULT
        self.transform = weights.transforms()

        self.model = resnet50(weights=weights)
        self.model.fc = torch.nn.Identity()
        self.model.to(self.device)
        self.model.eval()

    @torch.inference_mode()
    def extract(self, crop):
        if crop is None or crop.size == 0:
            return None

        rgb = cv2.cvtColor(crop, cv2.COLOR_BGR2RGB)
        image = Image.fromarray(rgb)

        tensor = self.transform(image).unsqueeze(0).to(self.device)

        embedding = self.model(tensor)
        embedding = embedding.cpu().numpy().flatten()

        return embedding / max(np.linalg.norm(embedding), 1e-8)