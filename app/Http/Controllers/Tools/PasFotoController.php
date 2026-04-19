<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasFotoController extends Controller
{
    /**
     * Display the PasFoto Online tool page.
     * All image processing is done client-side — no server upload needed.
     */
    public function index()
    {
        $seo = [
            'title'       => 'Buat Pas Foto Online Gratis – Ukuran 2x3, 3x4, 4x6 | PasFotoOnline',
            'description' => 'Buat pas foto digital untuk CPNS, lamaran kerja, KTP, dan dokumen resmi Indonesia secara gratis. Ubah ukuran 2x3, 3x4, 4x6, ganti background merah atau biru, kompres file, dan download JPG/PDF langsung di browser — tanpa daftar, tanpa upload ke server.',
            'keywords'    => 'pas foto online, pas foto 2x3, pas foto 3x4, pas foto 4x6, ganti background pas foto, pas foto CPNS, foto lamaran kerja, pas foto merah, pas foto biru, kompres pas foto, pas foto digital gratis, ukuran pas foto Indonesia, buat pas foto online gratis',
            'og_image'    => asset('assets/og-pasfoto.jpg'),
            'canonical'   => url('/pasfoto'),
            'schema' => [
                '@context'            => 'https://schema.org',
                '@type'               => 'WebApplication',
                'name'                => 'PasFotoOnline – MediaTools',
                'url'                 => url('/pasfoto'),
                'description'         => 'Buat pas foto digital untuk dokumen resmi Indonesia langsung di browser.',
                'applicationCategory' => 'UtilitiesApplication',
                'operatingSystem'     => 'Web Browser',
                'inLanguage'          => 'id-ID',
                'offers' => [
                    '@type'         => 'Offer',
                    'price'         => '0',
                    'priceCurrency' => 'IDR',
                ],
                'featureList' => [
                    'Ubah ukuran pas foto 2x3, 3x4, 4x6 cm',
                    'Ganti background merah atau biru',
                    'Kompres ukuran file di bawah 200KB atau 300KB',
                    'Export JPG dan PDF siap cetak',
                    'Crop otomatis terpusat',
                    'Tanpa registrasi dan tanpa upload ke server',
                ],
            ],
        ];

        return view('tools.pasfoto.index', compact('seo'));
    }
}
