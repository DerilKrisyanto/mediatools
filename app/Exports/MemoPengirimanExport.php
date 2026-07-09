<?php

namespace App\Exports;

use App\Models\MemoPengiriman;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MemoPengirimanExport
{
    protected Collection $memos;

    public function __construct(Collection $memos)
    {
        $this->memos = $memos;
    }

    /**
     * Bangun objek Spreadsheet lengkap (header, data, styling) dari koleksi memo.
     */
    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Memo Pengiriman');

        $headings = $this->headings();
        $lastColumnIndex  = count($headings);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);

        // Tulis header di baris 1
        $sheet->fromArray($headings, null, 'A1');

        // Tulis data mulai baris 2
        $row = 2;
        foreach ($this->memos as $memo) {
            $sheet->fromArray($this->mapRow($memo), null, 'A' . $row);
            $row++;
        }
        $lastRow = $row - 1;

        // Style header: background abu-abu + bold (meniru tampilan lama)
        $sheet->getStyle("A1:{$lastColumnLetter}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '1F2937'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Format angka untuk kolom Biaya Kirim (L) dan Biaya Instalasi (P)
        if ($lastRow >= 2) {
            $sheet->getStyle("L2:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("P2:P{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        // Auto-size semua kolom
        foreach (range(1, $lastColumnIndex) as $colIndex) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Freeze baris header saat scroll
        $sheet->freezePane('A2');

        return $spreadsheet;
    }

    protected function headings(): array
    {
        return [
            'No. Memo',
            'Diterima Dari',
            'No. Struk',
            'No Telepon (Dari)',
            'Berupa',
            'Contact Person Tujuan',
            'Alamat Tujuan',
            'Keterangan Lainnya',
            'No Telepon (Tujuan)',
            'Nama Customer Service',
            'Hari / Jam / Tgl Pengiriman',
            'Biaya Kirim (Rp)',
            'Instalasi',
            'No. Struk Instalasi',
            'Hari / Jam / Tgl Instalasi',
            'Biaya Instalasi (Rp)',
            'Dibuat Pada',
        ];
    }

    protected function mapRow(MemoPengiriman $m): array
    {
        return [
            $m->nomor_memo,
            $m->diterima_dari,
            $m->no_struk ?: '-',
            $m->telepon_dari ?: '-',
            $m->berupa_text ?: '-',
            $m->tujuan_contact_person,
            $m->tujuan_alamat,
            $m->keterangan_lainnya ?: '-',
            $m->tujuan_telepon ?: '-',
            $m->customer_service ?: '-',
            $m->pengiriman_hari_tanggal ?: '-',
            (float) ($m->biaya_kirim ?? 0),
            $m->instalasi ? 'Ya' : 'Tidak',
            $m->no_struk_instalasi ?: '-',
            $m->instalasi_hari_tanggal ?: '-',
            (float) ($m->biaya_instalasi ?? 0),
            Carbon::parse($m->tanggal_memo)->format('d-m-Y'),
        ];
    }
}