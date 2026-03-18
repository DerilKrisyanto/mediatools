<?php
namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BgRemoverService;
use Illuminate\Support\Facades\Storage;

class BgRemoverController extends Controller
{
    private $service;

    public function __construct(BgRemoverService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('tools.bgremover.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'files.*' => 'required|image|mimes:jpg,jpeg,png|max:5120', // Max 5MB
        ]);
        // Validasi ukuran file untuk mencegah timeout di shared hosting
        if (!$request->hasFile('files')) {
            return response()->json(['error'=>'No file uploaded'],400);
        }

        $files = $request->file('files');

        if (!$files) {
            return response()->json(['error' => 'No files uploaded'], 400);
        }

        try {
            [$sessionId, $results] = $this->service->processImages($files);

            return response()->json([
                'session_id' => $sessionId,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function download($sessionId, $filename)
    {
        $filename = basename($filename);

        $path = storage_path("app/public/results/$sessionId/$filename");

        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path, "Bg_Remover_" . $filename . "_by_mediaTools.png");
    }

    public function cleanup(Request $request)
    {
        $sessionId = $request->input('session_id');

        if ($sessionId) {
            $this->service->cleanup($sessionId);
        }

        return response()->json(['status' => 'cleaned']);
    }
}