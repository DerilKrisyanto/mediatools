#!/bin/bash
# ============================================================
# MediaTools — Buat placeholder OG Images (1200×630 px)
# Jalankan dari root project Laravel: bash create-og-images.sh
# Butuh: ImageMagick (convert command)
# Install: sudo apt install imagemagick  /  brew install imagemagick
# ============================================================

OUTDIR="public/images/og"
mkdir -p "$OUTDIR"

TOOLS=(
  "home:MediaTools — 10+ Tools Digital Gratis:Tools Digital untuk UMKM & Creator Indonesia"
  "bgremover:Background Remover Gratis:Hapus background foto otomatis dengan AI BiRefNet"
  "fileconverter:Konversi File Online Gratis:PDF Word Excel JPG — 5 file sekaligus"
  "imageconverter:Image Converter Gratis:Resize Kompres Konversi Gambar di Browser"
  "invoice:Invoice Generator Gratis:Buat tagihan profesional — download PDF A4"
  "linktree:LinkTree Builder Gratis:Satu halaman untuk semua link sosmed kamu"
  "mediadownloader:Media Downloader Gratis:Download YouTube TikTok Instagram tanpa watermark"
  "passwordgenerator:Password Generator Gratis:Buat kata sandi kuat — zero server privasi 100%"
  "pdfutilities:PDF Tools Gratis:Merge Split Compress PDF tanpa upload server"
  "qr:QR Code Generator Gratis:Buat QR Code custom bisnis — download PNG HD"
  "signature:Email Signature Generator:Tanda tangan email profesional Gmail Outlook"
)

for entry in "${TOOLS[@]}"; do
  IFS=':' read -r name title subtitle <<< "$entry"
  FILE="$OUTDIR/${name}.png"

  convert \
    -size 1200x630 \
    gradient:'#071a1a-#0b2323' \
    -fill '#a3e635' \
    -font "DejaVu-Sans-Bold" \
    -pointsize 52 \
    -gravity Center \
    -annotate +0-60 "$title" \
    -fill '#9ca3af' \
    -pointsize 28 \
    -gravity Center \
    -annotate +0+40 "$subtitle" \
    -fill '#a3e635' \
    -pointsize 22 \
    -gravity SouthEast \
    -annotate -40+30 "mediatools.cloud" \
    "$FILE"

  echo "✅ Created: $FILE"
done

echo ""
echo "🎉 Semua OG images berhasil dibuat di $OUTDIR/"
echo "   Setiap file 1200×630px siap untuk Open Graph & Twitter Card."
