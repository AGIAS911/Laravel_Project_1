from collections import Counter, deque

import numpy as np


class ObjectIdentityManager:
    def __init__(
        self, similarity_threshold=0.85, ambiguity_margin=0.04,
        max_embeddings=6, refresh_every=12, track_timeout=10,
        pending_refresh_every=5,
    ):
        self.objects = {}
        self.track_mapping = {}
        self.track_last_seen = {}
        self.embedding_attempts = {}
        self.pending_tracks = set()
        self.next_id = 1
        self.frame_number = 0
        self.similarity_threshold = similarity_threshold
        self.ambiguity_margin = ambiguity_margin
        self.max_embeddings = max_embeddings
        self.refresh_every = refresh_every
        self.pending_refresh_every = pending_refresh_every
        self.track_timeout = track_timeout
        self.active_objects = set()

    def start_frame(self):
        self.frame_number += 1
        self.active_objects.clear()
        for track_id, last_seen in list(self.track_last_seen.items()):
            if self.frame_number - last_seen > self.track_timeout:
                self.track_mapping.pop(track_id, None)
                self.embedding_attempts.pop(track_id, None)
                self.pending_tracks.discard(track_id)
                del self.track_last_seen[track_id]

    def get_mapped_id(self, track_id, label):
        object_id = self.track_mapping.get(track_id)
        if object_id is None or self.objects[object_id]["label"] != label:
            return None
        return object_id

    def needs_embedding(self, track_id, label):
        object_id = self.get_mapped_id(track_id, label)
        if object_id in self.active_objects:
            return True
        last_attempt, last_label = self.embedding_attempts.get(track_id, (0, None))
        interval = self.pending_refresh_every if track_id in self.pending_tracks else self.refresh_every
        return last_label != label or self.frame_number - last_attempt >= interval

    def record_embedding_attempt(self, track_id, label):
        self.embedding_attempts[track_id] = (self.frame_number, label)

    def _remember_embedding(self, object_id, embedding):
        if embedding is None:
            return
        gallery = self.objects[object_id]["embeddings"]
        if all(float(np.dot(embedding, saved)) < 0.99 for saved in gallery):
            gallery.append(embedding)

    def identify(self, track_id, label, embedding):
        self.track_last_seen[track_id] = self.frame_number
        object_id = self.get_mapped_id(track_id, label)
        if object_id is not None and object_id not in self.active_objects:
            self._remember_embedding(object_id, embedding)
            self.active_objects.add(object_id)
            return object_id

        same_class = [
            (candidate_id, obj["embeddings"])
            for candidate_id, obj in self.objects.items()
            if obj["label"] == label
        ]
        candidates = [
            (candidate_id, gallery)
            for candidate_id, gallery in same_class
            if candidate_id not in self.active_objects
        ]
        scores = []
        if embedding is not None:
            for candidate_id, gallery in candidates:
                if gallery:
                    score = max(float(np.dot(embedding, saved)) for saved in gallery)
                    scores.append((score, candidate_id))
        scores.sort(reverse=True)

        if not candidates:
            decision = "all_same_class_active" if same_class else "no_same_class_identity"
        elif embedding is None:
            decision = "no_embedding"
        elif not scores:
            decision = "no_saved_embeddings"
        elif scores[0][0] < self.similarity_threshold:
            decision = "below_threshold"
        elif len(scores) > 1 and scores[0][0] - scores[1][0] < self.ambiguity_margin:
            decision = "ambiguous_scores"
        else:
            decision = "reconnected"

        if (
            not candidates or track_id not in self.pending_tracks
            or self.embedding_attempts.get(track_id) == (self.frame_number, label)
        ):
            print(
                f"IDENTITY DEBUG | track={track_id} | label={label} | "
                f"all_same_class={[candidate_id for candidate_id, _ in same_class]} | "
                f"active={sorted(self.active_objects)} | "
                f"has_embedding={embedding is not None} | "
                f"candidates={[(candidate_id, round(score, 3)) for score, candidate_id in scores]} | "
                f"reason={decision}",
                flush=True,
            )

        if not candidates:
            object_id = self.next_id
            self.next_id += 1
            self.objects[object_id] = {
                "label": label, "embeddings": deque(maxlen=self.max_embeddings),
            }
        elif decision == "reconnected":
            object_id = scores[0][1]
            print(f"IDENTITY: Track {track_id} reconnected to #{object_id}", flush=True)
        else:
            if track_id not in self.pending_tracks:
                print(f"IDENTITY: Track {track_id} ({label}) needs review", flush=True)
            self.track_mapping.pop(track_id, None)
            self.pending_tracks.add(track_id)
            return None

        self.pending_tracks.discard(track_id)
        self.track_mapping[track_id] = object_id
        self._remember_embedding(object_id, embedding)
        self.active_objects.add(object_id)
        return object_id

    def get_inventory(self):
        return dict(Counter(obj["label"] for obj in self.objects.values()))

    def get_total(self):
        return len(self.objects)
