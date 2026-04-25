<?php
/*
|--------------------------------------------------------------------------
| FILE INI: app/Console/Commands/TestResendMail.php
|--------------------------------------------------------------------------
| Jalankan: php artisan mail:test-resend --to=emailkamu@gmail.com
|--------------------------------------------------------------------------
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestResendMail extends Command
{
    protected $signature   = 'mail:test-resend {--to= : Alamat email tujuan test}';
    protected $description = 'Kirim email test via Resend untuk verifikasi konfigurasi';

    public function handle(): int
    {
        /* ── 1. Cek konfigurasi ── */
        $this->info('');
        $this->line('┌─────────────────────────────────────────┐');
        $this->line('│  MediaTools — Resend Mail Debugger       │');
        $this->line('└─────────────────────────────────────────┘');
        $this->info('');

        $mailer   = config('mail.default');
        $fromAddr = config('mail.from.address');
        $fromName = config('mail.from.name');
        $apiKey   = config('resend.api_key') ?: env('RESEND_API_KEY');

        $this->table(['Config Key', 'Value'], [
            ['MAIL_MAILER',        $mailer],
            ['MAIL_FROM_ADDRESS',  $fromAddr],
            ['MAIL_FROM_NAME',     $fromName],
            ['RESEND_API_KEY',     $apiKey ? substr($apiKey, 0, 8) . '...' . substr($apiKey, -4) : '❌ TIDAK ADA'],
        ]);

        /* ── 2. Validasi ── */
        if (! $apiKey) {
            $this->error('RESEND_API_KEY tidak ditemukan di .env!');
            return 1;
        }
        if ($mailer !== 'resend') {
            $this->warn("MAIL_MAILER = '{$mailer}', bukan 'resend'. Periksa .env Anda.");
        }

        /* ── 3. Tentukan tujuan ── */
        $to = $this->option('to') ?: $this->ask('Kirim test ke email apa?');
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Email '{$to}' tidak valid.");
            return 1;
        }

        /* ── 4. Kirim email test sederhana ── */
        $this->info("Mengirim test email ke: {$to} ...");

        try {
            Mail::raw(
                "✅ Test email dari MediaTools berhasil!\n\n"
                . "Waktu  : " . now()->format('d M Y H:i:s') . " WIB\n"
                . "Mailer : {$mailer}\n"
                . "From   : {$fromAddr}\n\n"
                . "Jika Anda menerima email ini, konfigurasi Resend berfungsi dengan baik.",
                function (Message $msg) use ($to, $fromAddr, $fromName) {
                    $msg->to($to)
                        ->from($fromAddr, $fromName)
                        ->subject('[MediaTools] Test Email — ' . now()->format('H:i:s'));
                }
            );

            $this->info('');
            $this->line('  <fg=green>✓ Email berhasil dikirim via Resend!</>');
            $this->line("  Cek inbox/spam di: {$to}");
            $this->info('');

            /* ── 5. Petunjuk lanjutan ── */
            $this->line('Langkah selanjutnya:');
            $this->line('  1. Cek email masuk di <' . $to . '>');
            $this->line('  2. Cek Resend Dashboard → https://resend.com/emails');
            $this->line('  3. Pastikan MX record <' . parse_url(config('app.url'), PHP_URL_HOST) . '> sudah setup');

            return 0;

        } catch (\Exception $e) {
            $this->error('GAGAL: ' . $e->getMessage());
            $this->info('');
            $this->line('Kemungkinan penyebab:');
            $this->line('  • RESEND_API_KEY salah atau expired');
            $this->line('  • Domain pengirim belum diverifikasi di Resend');
            $this->line('  • Package resend/resend-laravel belum di-install');
            $this->line('  • Jalankan: composer require resend/resend-laravel');
            return 1;
        }
    }
}