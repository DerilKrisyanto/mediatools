#!/usr/bin/env python3
"""
MediaTools — Metadata & Privacy Sanitizer Engine
=================================================
Strips all metadata (EXIF, GPS, XMP, ICC, Author, DocInfo, etc.)
from image files (JPEG, PNG, WebP) and PDF documents.

Usage:
    python3 sanitize_metadata.py <input_path> <output_path>

Stdout (on success):
    JSON: {"ok": true, "removed": ["GPS", "EXIF", ...], "file": "name.jpg"}

Exit codes:
    0 — success
    1 — missing / bad arguments
    2 — unsupported file type
    3 — processing error

Dependencies:
    pip install Pillow pikepdf
"""

import sys
import os
import io
import json
import traceback

# ──────────────────────────────────────────────────────────────────────────────


def main() -> int:
    if len(sys.argv) < 3:
        _err("Usage: sanitize_metadata.py <input_path> <output_path>")
        return 1

    input_path  = sys.argv[1]
    output_path = sys.argv[2]

    if not os.path.isfile(input_path):
        _err(f"Input file not found: {input_path}")
        return 3

    os.makedirs(os.path.dirname(os.path.abspath(output_path)), exist_ok=True)

    ext = os.path.splitext(input_path)[1].lower()

    try:
        if ext in ('.jpg', '.jpeg'):
            removed = sanitize_jpeg(input_path, output_path)
        elif ext == '.png':
            removed = sanitize_png(input_path, output_path)
        elif ext == '.webp':
            removed = sanitize_webp(input_path, output_path)
        elif ext == '.pdf':
            removed = sanitize_pdf(input_path, output_path)
        else:
            _err(f"Unsupported file type: {ext}")
            return 2
    except ImportError as exc:
        _err(f"Missing dependency: {exc}. Install with: pip install Pillow pikepdf")
        return 3
    except Exception as exc:
        _err(f"Processing error: {exc}")
        traceback.print_exc(file=sys.stderr)
        return 3

    # Emit structured JSON on stdout for the controller to parse if needed
    print(json.dumps({
        "ok":      True,
        "file":    os.path.basename(output_path),
        "removed": removed,
    }))
    return 0


# ──────────────────────────────────────────────────────────────────────────────
# JPEG
# ──────────────────────────────────────────────────────────────────────────────

def sanitize_jpeg(input_path: str, output_path: str) -> list[str]:
    from PIL import Image

    removed = []

    with Image.open(input_path) as img:
        info = img.info or {}

        # Track what we're removing
        if img.getexif():
            removed.append("EXIF")
        if _has_gps(img):
            removed.append("GPS Location")
        if info.get('icc_profile'):
            removed.append("ICC Profile")
        if info.get('comment'):
            removed.append("Comment")
        if info.get('photoshop'):
            removed.append("Photoshop IRB")
        if info.get('xmp'):
            removed.append("XMP")

        # Normalize mode
        if img.mode not in ('RGB', 'L'):
            img = img.convert('RGB')

        # First save to clean buffer (drops all ancillary data)
        buf = io.BytesIO()
        img.save(buf, format='JPEG', quality=95, optimize=True, exif=b'')
        buf.seek(0)

        # Second open + save ensures no metadata leaks through Pillow's internals
        with Image.open(buf) as clean:
            clean.save(output_path, format='JPEG', quality=95, optimize=True, exif=b'')

    if not removed:
        removed.append("(already clean)")

    return removed


# ──────────────────────────────────────────────────────────────────────────────
# PNG
# ──────────────────────────────────────────────────────────────────────────────

def sanitize_png(input_path: str, output_path: str) -> list[str]:
    from PIL import Image

    removed = []
    chunk_keys = ('comment', 'Software', 'Artist', 'Copyright', 'Description',
                  'Creation Time', 'Author', 'Title', 'Warning', 'exif', 'xmp',
                  'icc_profile')

    with Image.open(input_path) as img:
        info = img.info or {}
        for key in chunk_keys:
            if key in info:
                removed.append(key)
        if img.getexif():
            removed.append("EXIF")

        # Preserve transparency
        if img.mode == 'RGBA':
            mode = 'RGBA'
        elif img.mode == 'P':
            mode = 'RGBA' if 'transparency' in info else 'RGB'
            img = img.convert(mode)
        else:
            mode = 'RGB'
            img = img.convert(mode)

        # Save through a clean buffer
        buf = io.BytesIO()
        img.save(buf, format='PNG')
        buf.seek(0)

        with Image.open(buf) as clean:
            clean.save(output_path, format='PNG', optimize=True)

    if not removed:
        removed.append("(already clean)")

    return removed


# ──────────────────────────────────────────────────────────────────────────────
# WebP
# ──────────────────────────────────────────────────────────────────────────────

def sanitize_webp(input_path: str, output_path: str) -> list[str]:
    from PIL import Image

    removed = []

    with Image.open(input_path) as img:
        if img.getexif():
            removed.append("EXIF")
        if img.info.get('icc_profile'):
            removed.append("ICC Profile")
        if img.info.get('xmp'):
            removed.append("XMP")

        # Keep alpha if present
        if img.mode not in ('RGB', 'RGBA'):
            img = img.convert('RGBA' if 'A' in img.mode else 'RGB')

        buf = io.BytesIO()
        img.save(buf, format='WEBP', quality=90, exif=b'')
        buf.seek(0)

        with Image.open(buf) as clean:
            clean.save(output_path, format='WEBP', quality=90, exif=b'')

    if not removed:
        removed.append("(already clean)")

    return removed


# ──────────────────────────────────────────────────────────────────────────────
# PDF
# ──────────────────────────────────────────────────────────────────────────────

def sanitize_pdf(input_path: str, output_path: str) -> list[str]:
    import pikepdf

    SENSITIVE_INFO_KEYS = [
        '/Title', '/Author', '/Subject', '/Keywords',
        '/Creator', '/Producer', '/CreationDate', '/ModDate',
        '/Trapped', '/Company', '/SourceModified', '/DocumentID',
        '/Manager', '/Category', '/ContentStatus',
    ]

    removed = []

    with pikepdf.open(input_path, allow_overwriting_input=False) as pdf:

        # 1. Clear XMP metadata (covers Dublin Core, Adobe, etc.)
        with pdf.open_metadata() as meta:
            if meta.keys():
                removed.extend([str(k).split('}')[-1] for k in list(meta.keys())[:6]])
                removed.append("XMP Metadata")
            meta.clear()

        # 2. Clear legacy /Info dictionary
        try:
            if '/Info' in pdf.trailer:
                info_dict = pdf.trailer['/Info']
                for key in SENSITIVE_INFO_KEYS:
                    try:
                        if key in info_dict:
                            removed.append(key.lstrip('/'))
                            del info_dict[key]
                    except (KeyError, AttributeError):
                        pass
        except Exception:
            pass

        # 3. Strip /Metadata streams from pages and document catalog
        _strip_object_metadata(pdf.Root)
        for page in pdf.pages:
            _strip_object_metadata(page)

        # 4. Save — no linearize to avoid pikepdf re-injecting Producer
        pdf.save(
            output_path,
            linearize=False,
            object_stream_mode=pikepdf.ObjectStreamMode.generate,
        )

    return removed if removed else ["(already clean)"]


def _strip_object_metadata(obj) -> None:
    """Recursively remove /Metadata streams from PDF objects."""
    try:
        import pikepdf
        if isinstance(obj, pikepdf.Dictionary) and '/Metadata' in obj:
            del obj['/Metadata']
    except Exception:
        pass


# ──────────────────────────────────────────────────────────────────────────────
# Utilities
# ──────────────────────────────────────────────────────────────────────────────

def _has_gps(img) -> bool:
    """Check whether a Pillow image carries GPS EXIF tags."""
    try:
        exif = img.getexif()
        return bool(exif.get_ifd(0x8825))  # 0x8825 = GPSInfo IFD tag
    except Exception:
        return False


def _err(msg: str) -> None:
    print(f"ERROR: {msg}", file=sys.stderr)


# ──────────────────────────────────────────────────────────────────────────────

if __name__ == '__main__':
    sys.exit(main())