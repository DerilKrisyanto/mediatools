#!/usr/bin/env python3
import json
import sys

deps = {}
for name, mod in [
    ("pdfplumber", "pdfplumber"),
    ("python_docx", "docx"),
    ("openpyxl", "openpyxl"),
    ("pdf2docx", "pdf2docx"),
]:
    try:
        __import__(mod)
        deps[name] = True
    except ImportError:
        deps[name] = False

print(json.dumps({"success": True, "python": sys.version, "deps": deps}))