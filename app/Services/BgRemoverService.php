<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BgRemoverService
{
    private $allowedExtensions = ['jpg','jpeg','png'];

    public function processImages($files)
    {
        $sessionId = Str::uuid()->toString();

        $uploadDir = storage_path("app/public/uploads/$sessionId");
        $resultDir = storage_path("app/public/results/$sessionId");

        File::makeDirectory($uploadDir, 0755, true, true);
        File::makeDirectory($resultDir, 0755, true, true);

        $results = [];

        // ===== BATASI JUMLAH FILE =====
        $maxFiles = 10;
        if (count($files) > $maxFiles) {
            throw new \Exception("Maximum $maxFiles images allowed per process.");
        }

        foreach ($files as $file) {

            // ===== BATASI UKURAN FILE =====
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file->getSize() > $maxSize) {
                throw new \Exception("File size must be less than 5MB.");
            }

            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, $this->allowedExtensions)) {
                throw new \Exception("Only JPG, JPEG, PNG allowed");
            }

            $uid = Str::uuid()->toString();

            $inputName = "$uid.$ext";
            $inputPath = "$uploadDir/$inputName";

            $file->move($uploadDir, $inputName);

            $resultName = "$uid.png";
            $resultPath = "$resultDir/$resultName";

            $this->callRemoveBgApi($inputPath, $resultPath);

            $results[] = [
                "uid" => $uid,
                "session_id" => $sessionId,
                "result_name" => $resultName,
                "result_url" => asset("storage/results/$sessionId/$resultName")
            ];
        }

        return [$sessionId, $results];
    }

    private function callRemoveBgApi($inputPath, $outputPath)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => env('REMOVE_BG_API_KEY'),
        ])
        ->attach(
            'image_file',
            file_get_contents($inputPath),
            basename($inputPath)
        )
        ->post('https://api.remove.bg/v1.0/removebg', [
            'size' => 'preview',
            'format' => 'png',
            'type' => 'auto'
        ]);

        if (!$response->successful()) {

            $body = json_decode($response->body(), true);

            if (isset($body['errors'][0]['code'])) {

                if ($body['errors'][0]['code'] === 'insufficient_credits') {
                    throw new \Exception("API remove.bg kehabisan credit.");
                }

                throw new \Exception($body['errors'][0]['title']);
            }

            throw new \Exception("Remove.bg error: " . $response->body());
        }

        File::put($outputPath, $response->body());
    }

    public function cleanup($sessionId)
    {
        File::deleteDirectory(storage_path("app/public/uploads/$sessionId"));
        File::deleteDirectory(storage_path("app/public/results/$sessionId"));
    }
}