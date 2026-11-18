from pathlib import Path

import cv2
import numpy as np

from feature_extractor import FeatureExtractor


def main():
    image_path = Path(__file__).with_name("chair.jpg")
    image = cv2.imread(str(image_path))
    if image is None:
        raise SystemExit(f"Invalid image: {image_path}")

    features = FeatureExtractor().extract(image)
    if features is None or features.shape != (2048,):
        raise SystemExit("Expected a 2048-element feature vector")
    if not np.isclose(np.linalg.norm(features), 1.0):
        raise SystemExit("Expected a feature vector with unit L2 norm")

    print("Vector shape:", features.shape)
    print("Features:", features[:10])


if __name__ == "__main__":
    main()
