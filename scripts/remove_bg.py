#!/usr/bin/env python3
"""
scripts/remove_bg.py
Production-grade background removal — rivals remove.bg quality.

One-time setup:
    pip install rembg[gpu] Pillow

    # GPU acceleration (optional but recommended):
    pip install onnxruntime-gpu   # NVIDIA CUDA
    # or just: pip install rembg  # CPU-only fallback

Models (auto-downloaded on first use, cached in ~/.u2net/):
    birefnet-general      — best overall quality (~430 MB)
    birefnet-portrait     — best for human portraits/hair (~430 MB)
    isnet-general-use     — balanced quality/speed (~100 MB)
    u2net                 — fast, good quality (~170 MB)

Usage:
    python remove_bg.py <input_path> <output_path> <model> <alpha_matting>
"""

import sys
import os
import json


def main():
    if len(sys.argv) < 3:
        fail("Usage: remove_bg.py <input> <output> [model] [alpha_matting]")

    input_path   = sys.argv[1]
    output_path  = sys.argv[2]
    model_name   = sys.argv[3] if len(sys.argv) > 3 else "birefnet-general"
    use_matting  = (sys.argv[4].lower() == "true") if len(sys.argv) > 4 else True

    if not os.path.exists(input_path):
        fail(f"Input not found: {input_path}")

    try:
        from rembg import remove, new_session

        # Session is cached in memory; model weights cached on disk (~/.u2net/)
        session = new_session(model_name)

        with open(input_path, "rb") as fh:
            input_data = fh.read()

        # Alpha matting uses pymatting to recover fine hair strands.
        # It runs a second pass on the uncertain edge zone identified by the AI.
        # Thresholds tuned empirically for portraits & product photography.
        if use_matting:
            output_data = remove(
                input_data,
                session=session,
                alpha_matting=True,
                alpha_matting_foreground_threshold=240,
                alpha_matting_background_threshold=10,
                alpha_matting_erode_size=10,
                post_process_mask=True,      # morphological cleanup
            )
        else:
            output_data = remove(
                input_data,
                session=session,
                alpha_matting=False,
                post_process_mask=True,
            )

        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        with open(output_path, "wb") as fh:
            fh.write(output_data)

        print(json.dumps({"status": "success", "model": model_name}))
        sys.exit(0)

    except ImportError as exc:
        fail(f"Missing dependency — run: pip install rembg[gpu] Pillow\nDetail: {exc}")

    except Exception as exc:
        fail(str(exc))


def fail(msg: str) -> None:
    print(json.dumps({"status": "error", "message": msg}), file=sys.stderr)
    sys.exit(1)


if __name__ == "__main__":
    main()