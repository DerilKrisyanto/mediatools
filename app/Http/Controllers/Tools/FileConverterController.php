<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FileConverterController extends Controller
{
    private string $storageDir;

    public function __construct()
    {
        $this->storageDir = storage_path('app/file_converter');
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function index()
    {
        return view('tools.fileconverter.index');
    }

    /* =========================================================
       PROCESS
    ========================================================= */
    public function process(Request $request)
    {
        $request->validate([
            'file'            => 'required|file|max:10240',
            'conversion_type' => 'required|string|in:jpg_to_pdf,png_to_pdf,word_to_pdf,excel_to_pdf,ppt_to_pdf,pdf_to_jpg,pdf_to_png,pdf_to_word,pdf_to_excel,pdf_to_ppt,jpg_to_png,png_to_jpg,jpg_to_webp,png_to_webp,webp_to_jpg,webp_to_png',
        ]);

        $type      = $request->input('conversion_type');
        $file      = $request->file('file');
        $sessionId = Str::uuid()->toString();

        $this->lazyCleanup();

        try {
            $outputFiles = match ($type) {
                'jpg_to_pdf', 'png_to_pdf' => $this->imageToPdf($file, $sessionId),
                'word_to_pdf'  => $this->wordToPdf($file, $sessionId),
                'excel_to_pdf' => $this->excelToPdf($file, $sessionId),
                'ppt_to_pdf'   => $this->pptToPdf($file, $sessionId),
                'pdf_to_jpg'   => $this->pdfToImage($file, $sessionId, 'jpg'),
                'pdf_to_png'   => $this->pdfToImage($file, $sessionId, 'png'),
                'pdf_to_word'  => $this->pdfToWord($file, $sessionId),
                'pdf_to_excel' => $this->pdfToExcel($file, $sessionId),
                'pdf_to_ppt'   => $this->pdfToPpt($file, $sessionId),
                'jpg_to_png', 'png_to_jpg',
                'jpg_to_webp', 'png_to_webp',
                'webp_to_jpg', 'webp_to_png' => $this->convertImage($file, $sessionId, $type),
                default => throw new \Exception("Tipe konversi tidak didukung."),
            };

            return response()->json([
                'success' => true,
                'files'   => $outputFiles,
                'session' => $sessionId,
            ]);

        } catch (\Exception $e) {
            Log::error("FileConverter [{$type}]: " . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
       HELPERS
    ========================================================= */
    private function requireExtension(string $ext, string $hint = ''): void
    {
        if (!extension_loaded($ext)) {
            throw new \Exception(
                "Ekstensi PHP '{$ext}' tidak aktif." . ($hint ? " {$hint}" : '')
            );
        }
    }

    private function requireClass(string $class, string $package): void
    {
        if (!class_exists($class)) {
            throw new \Exception("Library belum diinstall. Jalankan: composer require {$package}");
        }
    }

    /* =========================================================
       1. IMAGE → PDF
    ========================================================= */
    private function imageToPdf($file, string $sessionId): array
    {
        $this->requireExtension('gd', "Aktifkan extension=gd di php.ini");
        $this->loadFpdf();

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $imgInfo = @getimagesize($tmpPath);
            if (!$imgInfo || !$imgInfo[0]) {
                throw new \Exception("File gambar tidak valid atau rusak.");
            }

            [$imgW, $imgH] = $imgInfo;
            $mime = $imgInfo['mime'];

            $gd = match (true) {
                str_contains($mime, 'jpeg') => imagecreatefromjpeg($tmpPath),
                str_contains($mime, 'png')  => imagecreatefrompng($tmpPath),
                str_contains($mime, 'webp') => imagecreatefromwebp($tmpPath),
                str_contains($mime, 'gif')  => imagecreatefromgif($tmpPath),
                str_contains($mime, 'bmp')  => imagecreatefrombmp($tmpPath),
                default => throw new \Exception("Format gambar tidak didukung: {$mime}"),
            };

            if (!$gd) throw new \Exception("Gagal membaca file gambar.");

            $canvas = imagecreatetruecolor($imgW, $imgH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $gd, 0, 0, 0, 0, $imgW, $imgH);
            imagedestroy($gd);

            $cleanPath = $this->storageDir . "/{$sessionId}_clean.jpg";
            imagejpeg($canvas, $cleanPath, 92);
            imagedestroy($canvas);

            // Ukuran A4: 210x297mm. Hitung skala agar fit
            $dpi    = 96;
            $mmW    = round($imgW * 25.4 / $dpi, 2);
            $mmH    = round($imgH * 25.4 / $dpi, 2);
            $margin = 10;
            $maxW   = 210 - $margin * 2;
            $maxH   = 297 - $margin * 2;

            if ($mmW > $maxW || $mmH > $maxH) {
                $scale = min($maxW / $mmW, $maxH / $mmH);
                $mmW   = round($mmW * $scale, 2);
                $mmH   = round($mmH * $scale, 2);
            }

            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->SetMargins($margin, $margin, $margin);
            $pdf->AddPage();
            $pdf->Image($cleanPath, $margin, $margin, $mmW, $mmH, 'JPEG');

            $outName = "{$sessionId}.pdf";
            $pdf->Output('F', $this->storageDir . "/{$outName}");

            @unlink($cleanPath);
            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       2. WORD → PDF (PhpWord → HTML → Dompdf)
          Kualitas terbaik yang bisa dicapai tanpa LibreOffice
    ========================================================= */
    private function wordToPdf($file, string $sessionId): array
    {
        $this->requireExtension('zip', "Aktifkan 'extension=zip' di php.ini. File DOCX adalah format ZIP.");
        $this->requireClass('\PhpOffice\PhpWord\PhpWord',   'phpoffice/phpword');
        $this->requireClass('\Dompdf\Dompdf',               'dompdf/dompdf');

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
            \PhpOffice\PhpWord\Settings::setCompatibility(true);

            $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpPath);

            // Export ke HTML
            $htmlPath = $this->storageDir . "/{$sessionId}_word.html";
            $writer   = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $writer->save($htmlPath);
            $html = file_get_contents($htmlPath);
            @unlink($htmlPath);

            if (empty(trim($html))) {
                throw new \Exception("Gagal membaca file Word. Pastikan file tidak rusak.");
            }

            // Inject CSS tambahan untuk memperbaiki rendering
            $extraCss = '
            <style>
                body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #000; margin: 0; padding: 20px 40px; }
                p { margin: 0 0 6pt 0; }
                table { border-collapse: collapse; width: 100%; margin-bottom: 10pt; }
                td, th { padding: 4pt 6pt; vertical-align: top; }
                h1 { font-size: 18pt; font-weight: bold; margin: 12pt 0 6pt; }
                h2 { font-size: 16pt; font-weight: bold; margin: 10pt 0 5pt; }
                h3 { font-size: 14pt; font-weight: bold; margin: 8pt 0 4pt; }
                h4, h5, h6 { font-size: 12pt; font-weight: bold; margin: 6pt 0 3pt; }
                ul, ol { margin: 6pt 0; padding-left: 20pt; }
                li { margin-bottom: 3pt; }
                .page-break { page-break-after: always; }
                img { max-width: 100%; height: auto; }
            </style>';

            $html = str_replace('</head>', $extraCss . '</head>', $html);
            if (!str_contains($html, '</head>')) {
                $html = $extraCss . $html;
            }

            $dompdf = $this->makeDompdf([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled'         => false,
                'enable_remote'        => false,
                'default_font'         => 'DejaVu Sans',
                'dpi'                  => 96,
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $outName = "{$sessionId}.pdf";
            file_put_contents($this->storageDir . "/{$outName}", $dompdf->output());

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       3. EXCEL → PDF (PhpSpreadsheet → styled HTML → Dompdf)
          Preserves cell colors, borders, font sizes sebisa mungkin
    ========================================================= */
    private function excelToPdf($file, string $sessionId): array
    {
        $this->requireExtension('zip', "Aktifkan 'extension=zip' di php.ini. File XLSX adalah format ZIP.");
        $this->requireClass('\PhpOffice\PhpSpreadsheet\Spreadsheet', 'phpoffice/phpspreadsheet');
        $this->requireClass('\Dompdf\Dompdf', 'dompdf/dompdf');

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);

            $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>';
            $html .= 'body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 9pt; margin: 0; padding: 10px; }';
            $html .= 'h3 { font-size: 11pt; margin: 12px 0 4px; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 4px; }';
            $html .= 'table { border-collapse: collapse; width: 100%; margin-bottom: 20px; table-layout: auto; }';
            $html .= 'td, th { border: 1px solid #ccc; padding: 3px 5px; vertical-align: middle; overflow: hidden; }';
            $html .= '</style></head><body>';

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $sheet   = $spreadsheet->getSheetByName($sheetName);
                $highRow = $sheet->getHighestRow();
                $highCol = $sheet->getHighestColumn();
                $highColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highCol);

                $html .= '<h3>' . htmlspecialchars($sheetName) . '</h3><table>';

                for ($row = 1; $row <= min($highRow, 1000); $row++) {
                    $html .= '<tr>';
                    for ($colIdx = 1; $colIdx <= $highColIdx; $colIdx++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                        $cell      = $sheet->getCell($colLetter . $row);
                        $val       = $cell->getFormattedValue();

                        // Ambil style sel
                        $style    = $sheet->getStyle($colLetter . $row);
                        $cssStyle = $this->excelStyleToCss($style);

                        // Merge cells
                        $colspan = 1;
                        $rowspan = 1;
                        foreach ($sheet->getMergeCells() as $mergeRange) {
                            [$mergeColLetter, $mergeRow] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString(
                                explode(':', $mergeRange)[0]
                            );
                            if ($mergeColLetter === $colLetter && (int)$mergeRow === $row) {
                                [$mergeStart, $mergeEnd] = explode(':', $mergeRange);
                                [$sc, $sr] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($mergeStart);
                                [$ec, $er] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($mergeEnd);
                                $colspan = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ec)
                                         - \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sc) + 1;
                                $rowspan = (int)$er - (int)$sr + 1;
                                break;
                            }
                        }

                        $colspanAttr = $colspan > 1 ? " colspan=\"{$colspan}\"" : '';
                        $rowspanAttr = $rowspan > 1 ? " rowspan=\"{$rowspan}\"" : '';

                        $html .= "<td{$colspanAttr}{$rowspanAttr} style=\"{$cssStyle}\">"
                               . htmlspecialchars((string)$val)
                               . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }
            $html .= '</body></html>';

            // Deteksi orientasi — jika ada kolom banyak, pakai landscape
            $maxCols = 0;
            foreach ($spreadsheet->getSheetNames() as $sn) {
                $s = $spreadsheet->getSheetByName($sn);
                $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($s->getHighestColumn());
                if ($c > $maxCols) $maxCols = $c;
            }
            $orientation = $maxCols > 8 ? 'landscape' : 'portrait';

            $dompdf = $this->makeDompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();

            $outName = "{$sessionId}.pdf";
            file_put_contents($this->storageDir . "/{$outName}", $dompdf->output());

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /**
     * Konversi PhpSpreadsheet style ke CSS inline
     */
    private function excelStyleToCss(\PhpOffice\PhpSpreadsheet\Style\Style $style): string
    {
        $css = '';

        // Background color
        $fill = $style->getFill();
        if ($fill->getFillType() !== \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE) {
            $bgColor = $fill->getStartColor()->getRGB();
            if ($bgColor && $bgColor !== '000000' && $bgColor !== 'FFFFFF' && strlen($bgColor) === 6) {
                $css .= "background-color:#{$bgColor};";
            }
        }

        // Font
        $font = $style->getFont();
        if ($font->getBold())   $css .= 'font-weight:bold;';
        if ($font->getItalic()) $css .= 'font-style:italic;';
        if ($font->getSize() && $font->getSize() > 0) {
            $css .= 'font-size:' . (int)$font->getSize() . 'pt;';
        }
        $fontColor = $font->getColor()->getRGB();
        if ($fontColor && $fontColor !== '000000' && strlen($fontColor) === 6) {
            $css .= "color:#{$fontColor};";
        }

        // Alignment
        $align = $style->getAlignment();
        $hAlign = $align->getHorizontal();
        if ($hAlign === 'center')  $css .= 'text-align:center;';
        elseif ($hAlign === 'right') $css .= 'text-align:right;';

        // Wrap text
        if ($align->getWrapText()) $css .= 'white-space:normal;';

        return $css;
    }

    /* =========================================================
       4. PPT → PDF
    ========================================================= */
    private function pptToPdf($file, string $sessionId): array
    {
        $this->requireExtension('zip', "Aktifkan 'extension=zip' di php.ini.");
        $this->requireClass('\PhpOffice\PhpPresentation\PhpPresentation', 'phpoffice/phppresentation');
        $this->requireClass('\Dompdf\Dompdf', 'dompdf/dompdf');

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $presentation = \PhpOffice\PhpPresentation\IOFactory::load($tmpPath);
            $slideCount   = $presentation->getSlideCount();

            $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>';
            $html .= 'body { font-family: "DejaVu Sans", Arial, sans-serif; margin: 0; padding: 0; }';
            $html .= '.slide { width: 100%; min-height: 380px; padding: 30px 40px; box-sizing: border-box; page-break-after: always; border: 1px solid #eee; margin-bottom: 0; }';
            $html .= '.slide-header { color: #999; font-size: 9pt; margin-bottom: 16px; border-bottom: 2px solid #ddd; padding-bottom: 6px; }';
            $html .= '.slide-title { font-size: 20pt; font-weight: bold; margin-bottom: 16px; color: #1a1a2e; }';
            $html .= '.slide-body p { font-size: 12pt; margin: 6px 0; line-height: 1.6; }';
            $html .= '</style></head><body>';

            for ($i = 0; $i < $slideCount; $i++) {
                $slide  = $presentation->getSlide($i);
                $isFirst = true;

                $html .= '<div class="slide">';
                $html .= '<div class="slide-header">Slide ' . ($i + 1) . " / {$slideCount}</div>";
                $html .= '<div class="slide-body">';

                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                        $shapeText = '';
                        foreach ($shape->getParagraphs() as $para) {
                            $lineText = '';
                            foreach ($para->getRichTextElements() as $el) {
                                $lineText .= $el->getText();
                            }
                            if (trim($lineText)) {
                                $shapeText .= '<p>' . htmlspecialchars(trim($lineText)) . '</p>';
                            }
                        }
                        if ($shapeText && $isFirst) {
                            $html    .= '<div class="slide-title">' . strip_tags($shapeText) . '</div>';
                            $isFirst  = false;
                        } else {
                            $html .= $shapeText;
                        }
                    }
                }

                $html .= '</div></div>';
            }
            $html .= '</body></html>';

            $dompdf = $this->makeDompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper([0, 0, 841.89, 595.28], 'landscape'); // A4 landscape exact
            $dompdf->render();

            $outName = "{$sessionId}.pdf";
            file_put_contents($this->storageDir . "/{$outName}", $dompdf->output());

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       5. PDF → IMAGE (Imagick)
    ========================================================= */
    private function pdfToImage($file, string $sessionId, string $format = 'jpg'): array
    {
        $this->requireExtension('imagick',
            "Ekstensi Imagick dibutuhkan untuk PDF → gambar. Hubungi hosting untuk mengaktifkannya.");

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($tmpPath . '[0-19]');

            $imgFormat   = $format === 'png' ? 'png' : 'jpeg';
            $outputFiles = [];

            foreach ($imagick as $i => $page) {
                $page->setImageFormat($imgFormat);
                $page->setImageBackgroundColor('white');
                $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                if ($imgFormat === 'jpeg') {
                    $page->setImageCompressionQuality(90);
                }
                $fname = "{$sessionId}_p" . ($i + 1) . ".{$format}";
                $page->writeImage($this->storageDir . "/{$fname}");
                $outputFiles[] = $fname;
            }

            $imagick->clear();
            $imagick->destroy();

            if (empty($outputFiles)) {
                throw new \Exception("Tidak ada halaman yang berhasil dikonversi.");
            }

            return $outputFiles;

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       6. PDF → WORD
          Menggunakan smalot/pdfparser dengan konfigurasi optimal
    ========================================================= */
    private function pdfToWord($file, string $sessionId): array
    {
        $this->requireExtension('zip', "Aktifkan 'extension=zip' di php.ini untuk membuat file DOCX.");
        $this->requireClass('\PhpOffice\PhpWord\PhpWord', 'phpoffice/phpword');

        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \Exception(
                "Library smalot/pdfparser diperlukan. Jalankan: composer require smalot/pdfparser"
            );
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $text = $this->extractTextFromPdf($tmpPath);

            Log::info("PDF → Word extraction result", [
                'length'  => strlen($text),
                'preview' => substr($text, 0, 200),
            ]);

            if (empty(trim($text))) {
                throw new \Exception(
                    "Tidak dapat mengekstrak teks dari PDF ini. " .
                    "PDF ini kemungkinan berbasis gambar/scan. " .
                    "Gunakan 'PDF → JPG' untuk mendapatkan gambar halaman."
                );
            }

            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->setDefaultFontName('Arial');
            $phpWord->setDefaultFontSize(11);
            $phpWord->getSettings()->setUpdateFields(true);

            $section = $phpWord->addSection([
                'paperSize'    => 'A4',
                'marginTop'    => 1440,
                'marginBottom' => 1440,
                'marginLeft'   => 1440,
                'marginRight'  => 1440,
            ]);

            // Styling untuk heading detection
            $headingStyle = ['bold' => true, 'size' => 14, 'color' => '1a1a2e'];
            $normalStyle  = ['size' => 11];
            $paraStyle    = ['spaceAfter' => 100, 'lineHeight' => 1.5];

            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    $section->addTextBreak(1);
                    continue;
                }

                // Heuristik heading: pendek, huruf besar semua, atau diawali #
                $isHeading = (strlen($line) < 80 && preg_match('/^[A-Z\s\.\:\/\-]{4,}$/', $line))
                          || preg_match('/^#{1,6}\s/', $line)
                          || (strlen($line) < 50 && strlen($line) > 3 && !preg_match('/[a-z]/', $line));

                $line = ltrim($line, '# ');

                if ($isHeading) {
                    $section->addText(htmlspecialchars($line), $headingStyle, $paraStyle);
                } else {
                    $section->addText(htmlspecialchars($line), $normalStyle, $paraStyle);
                }
            }

            $outName = "{$sessionId}.docx";
            $writer  = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($this->storageDir . "/{$outName}");

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       7. PDF → EXCEL
    ========================================================= */
    private function pdfToExcel($file, string $sessionId): array
    {
        $this->requireExtension('zip', "Aktifkan 'extension=zip' di php.ini untuk membuat XLSX.");
        $this->requireClass('\PhpOffice\PhpSpreadsheet\Spreadsheet', 'phpoffice/phpspreadsheet');

        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \Exception("Library smalot/pdfparser diperlukan. Jalankan: composer require smalot/pdfparser");
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $text = $this->extractTextFromPdf($tmpPath);

            if (empty(trim($text))) {
                throw new \Exception("Tidak dapat mengekstrak data dari PDF ini.");
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data dari PDF');

            // Style header
            $headerStyle = [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF2C3E50']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ];

            $lines = array_values(array_filter(
                explode("\n", $text),
                fn ($l) => trim($l) !== ''
            ));

            $maxCols = 1;
            $excelRow = 1;

            foreach ($lines as $line) {
                $cols = preg_split('/\t+|\s{3,}/', trim($line));
                $cols = array_filter($cols, fn ($c) => trim($c) !== '');
                $cols = array_values($cols);

                if (count($cols) > $maxCols) $maxCols = count($cols);

                foreach ($cols as $c => $cell) {
                    $sheet->setCellValueByColumnAndRow($c + 1, $excelRow, trim($cell));
                }
                $excelRow++;

                if ($excelRow > 10000) break;
            }

            // Style baris pertama sebagai header
            if ($excelRow > 1) {
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCols);
                $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray($headerStyle);
                $sheet->getStyle("A1:{$lastColLetter}" . ($excelRow - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFCCCCCC'],
                        ],
                    ],
                ]);

                // Auto-size semua kolom
                for ($c = 1; $c <= $maxCols; $c++) {
                    $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
                }
            }

            // Freeze pane pada baris pertama
            $sheet->freezePane('A2');

            $outName = "{$sessionId}.xlsx";
            $writer  = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($this->storageDir . "/{$outName}");

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       8. PDF → PPT
    ========================================================= */
    private function pdfToPpt($file, string $sessionId): array
    {
        $this->requireExtension('zip', "Aktifkan 'extension=zip' di php.ini.");
        $this->requireClass('\PhpOffice\PhpPresentation\PhpPresentation', 'phpoffice/phppresentation');

        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \Exception("Library smalot/pdfparser diperlukan. Jalankan: composer require smalot/pdfparser");
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $text = $this->extractTextFromPdf($tmpPath);

            if (empty(trim($text))) {
                throw new \Exception("Tidak dapat mengekstrak teks dari PDF ini.");
            }

            $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
            $presentation->removeSlide(0);

            $lines  = array_values(array_filter(
                explode("\n", $text), fn ($l) => trim($l) !== ''
            ));
            $chunks = array_chunk($lines, 8);

            foreach ($chunks as $chunk) {
                $slide = $presentation->createSlide();
                $shape = $slide->createRichTextShape()
                    ->setHeight(500)->setWidth(820)
                    ->setOffsetX(40)->setOffsetY(30);
                $shape->getActiveParagraph()->getFont()->setSize(14);

                foreach ($chunk as $idx => $line) {
                    $para = $shape->createParagraph();
                    $run  = $para->createTextRun(trim($line));
                    $run->getFont()->setSize($idx === 0 ? 22 : 13);
                    if ($idx === 0) $run->getFont()->setBold(true);
                    $run->getFont()->setColor(new \PhpOffice\PhpPresentation\Style\Color(
                        $idx === 0 ? 'FF1a1a2e' : 'FF333333'
                    ));
                }
            }

            $outName = "{$sessionId}.pptx";
            $writer  = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
            $writer->save($this->storageDir . "/{$outName}");

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       9. IMAGE → IMAGE (GD)
    ========================================================= */
    private function convertImage($file, string $sessionId, string $type): array
    {
        $this->requireExtension('gd', "Aktifkan 'extension=gd' di php.ini.");

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $outFmt = match (true) {
                str_ends_with($type, '_png')  => 'png',
                str_ends_with($type, '_webp') => 'webp',
                default                       => 'jpg',
            };

            $src = match ($ext) {
                'jpg', 'jpeg' => imagecreatefromjpeg($tmpPath),
                'png'         => imagecreatefrompng($tmpPath),
                'webp'        => imagecreatefromwebp($tmpPath),
                default       => throw new \Exception("Format gambar tidak didukung: {$ext}"),
            };

            if (!$src) throw new \Exception("Gagal membaca gambar sumber.");

            $w = imagesx($src);
            $h = imagesy($src);

            $canvas = imagecreatetruecolor($w, $h);

            if ($outFmt === 'png') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            } else {
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            }

            imagecopy($canvas, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);

            $outName = "{$sessionId}.{$outFmt}";
            $outPath = $this->storageDir . "/{$outName}";

            $ok = match ($outFmt) {
                'png'  => imagepng($canvas, $outPath, 6),
                'webp' => imagewebp($canvas, $outPath, 85),
                default=> imagejpeg($canvas, $outPath, 92),
            };

            imagedestroy($canvas);

            if (!$ok) throw new \Exception("Gagal menyimpan hasil konversi.");

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       HELPER: Buat instance Dompdf dengan konfigurasi optimal
    ========================================================= */
    private function makeDompdf(array $extraOptions = []): \Dompdf\Dompdf
    {
        $this->requireClass('\Dompdf\Dompdf', 'dompdf/dompdf');

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 96);
        $options->set('defaultPaperSize', 'A4');
        $options->set('isFontSubsettingEnabled', true);

        foreach ($extraOptions as $k => $v) {
            $options->set($k, $v);
        }

        return new \Dompdf\Dompdf($options);
    }

    /* =========================================================
       HELPER: Ekstrak teks dari PDF (smalot + fallback)
    ========================================================= */
    private function extractTextFromPdf(string $pdfPath): string
    {
        // Prioritas utama: smalot/pdfparser
        if (class_exists('\Smalot\PdfParser\Parser')) {
            try {
                $config = new \Smalot\PdfParser\Config();
                $config->setRetainImageContent(false);
                $config->setIgnoreEncryption(true);
                $config->setDecodeMemoryLimit(128 * 1024 * 1024); // 128MB

                $parser = new \Smalot\PdfParser\Parser([], $config);
                $pdf    = $parser->parseFile($pdfPath);

                // Coba per halaman dulu
                $text = '';
                foreach ($pdf->getPages() as $page) {
                    try {
                        $pageText = $page->getText();
                        if (!empty(trim($pageText))) {
                            $text .= trim($pageText) . "\n\n";
                        }
                    } catch (\Exception $pe) {
                        Log::warning("Page extraction failed: " . $pe->getMessage());
                    }
                }

                // Fallback: ambil semua teks sekaligus
                if (empty(trim($text))) {
                    $text = $pdf->getText();
                }

                if (!empty(trim($text))) {
                    return $this->cleanExtractedText($text);
                }

            } catch (\Exception $e) {
                Log::warning("smalot/pdfparser failed: " . $e->getMessage());
            }
        }

        // Fallback: stream decompression + BT/ET parsing
        $raw = @file_get_contents($pdfPath);
        if (!$raw) return '';

        $text = '';

        // Coba decode FlateDecode streams
        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $raw, $streams)) {
            foreach ($streams[1] as $stream) {
                $decompressed = @gzuncompress($stream);
                if ($decompressed !== false && strlen($decompressed) > 10) {
                    $extracted = $this->extractFromBtEt($decompressed);
                    if (!empty(trim($extracted))) {
                        $text .= $extracted . "\n";
                    }
                }
            }
        }

        if (empty(trim($text))) {
            $text = $this->extractFromBtEt($raw);
        }

        return $this->cleanExtractedText($text);
    }

    private function extractFromBtEt(string $content): string
    {
        $text = '';
        if (!preg_match_all('/BT(.+?)ET/s', $content, $blocks)) return '';

        foreach ($blocks[1] as $block) {
            if (preg_match_all('/\((.+?)\)\s*Tj/s', $block, $tj)) {
                foreach ($tj[1] as $t) $text .= $this->decodePdfString($t) . ' ';
            }
            if (preg_match_all('/\[(.+?)\]\s*TJ/s', $block, $TJ)) {
                foreach ($TJ[1] as $arr) {
                    preg_match_all('/\((.+?)\)/', $arr, $parts);
                    foreach ($parts[1] as $p) $text .= $this->decodePdfString($p);
                    $text .= ' ';
                }
            }
            if (preg_match('/T[dD*]/', $block)) $text .= "\n";
        }

        return $text;
    }

    private function decodePdfString(string $s): string
    {
        $s = str_replace(['\\n','\\r','\\t','\\(','\\)','\\\\'], ["\n","\r","\t",'(',')',"\\"], $s);
        return preg_replace_callback('/\\\\([0-7]{1,3})/', fn ($m) => chr(octdec($m[1])), $s);
    }

    private function cleanExtractedText(string $text): string
    {
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/u', ' ', $text);
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        $text = preg_replace('/(\r\n|\r)/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    /* =========================================================
       HELPER: Load FPDF
    ========================================================= */
    private function loadFpdf(): void
    {
        $paths = [
            app_path('Libraries/fpdf/fpdf.php'),
            base_path('vendor/setasign/fpdf/fpdf.php'),
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
        throw new \Exception("FPDF tidak ditemukan. Jalankan: composer require setasign/fpdf");
    }

    /* =========================================================
       DOWNLOAD
    ========================================================= */
    public function download(string $filename)
    {
        $filename = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) abort(403);

        $path = $this->storageDir . "/{$filename}";
        if (!file_exists($path)) abort(404, 'File tidak ditemukan atau sudah dihapus.');

        $ext     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ];

        return response()->download($path, $filename, [
            'Content-Type'        => $mimeMap[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /* =========================================================
       CLEANUP
    ========================================================= */
    private function lazyCleanup(): void
    {
        $limit = time() - (15 * 60);
        foreach (glob($this->storageDir . '/*') ?: [] as $f) {
            if (is_file($f) && filemtime($f) < $limit) @unlink($f);
        }
    }

    public function cleanup(Request $request)
    {
        $sessionId = $request->input('session_id');
        if ($sessionId && preg_match('/^[a-zA-Z0-9\-]+$/', $sessionId)) {
            foreach (glob($this->storageDir . "/{$sessionId}*") ?: [] as $f) @unlink($f);
        }
        return response()->json(['success' => true]);
    }
}