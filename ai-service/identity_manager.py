from collections import Counter

import numpy as np


class ObjectIdentityManager:
    def __init__(self, similarity_threshold=0.90):
        self.objects = {}
        self.track_mapping = {}
        self.next_id = 1
        self.similarity_threshold = similarity_threshold
        self.active_objects = set()

    def start_frame(self):
        self.active_objects.clear()

    def identify(self, track_id, label, embedding):
        if track_id in self.track_mapping:
            object_id = self.track_mapping[track_id]
            if object_id not in self.active_objects:
                self.active_objects.add(object_id)
                return object_id

        best_id = None
        best_similarity = self.similarity_threshold

        if embedding is not None:
            for object_id, obj in self.objects.items():
                if obj["label"] != label or object_id in self.active_objects:
                    continue
                if obj["embedding"] is None:
                    continue

                # FeatureExtractor returns L2-normalized embeddings.
                similarity = float(np.dot(embedding, obj["embedding"]))
                if similarity > best_similarity:
                    best_similarity = similarity
                    best_id = object_id

        if best_id is None:
            best_id = self.next_id
            self.next_id += 1
            self.objects[best_id] = {"label": label, "embedding": embedding}

        self.track_mapping[track_id] = best_id
        self.active_objects.add(best_id)
        return best_id

    def get_inventory(self):
        return dict(Counter(obj["label"] for obj in self.objects.values()))

    def get_total(self):
        return len(self.objects)
