<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;

class SystemSecurityController extends Controller
{
    public function validateFileContent($content) {
        $patterns = [
            // === PHP webshell patterns ===
            '/<\?php/i',
            '/eval\s*\(/i',
            '/base64_decode\s*\(/i',
            '/shell_exec\s*\(/i',
            '/gzinflate\s*\(/i',
            '/gzuncompress\s*\(/i',
            '/str_rot13\s*\(/i',
            '/rawurldecode\s*\(/i',
            '/urldecode\s*\(/i',
            '/function\s*\(/i',
            '/b64decode\s*\(/i',
            '/convert_uudecode\s*\(/i',
            '/system\s*\(/i',
            '/exec\s*\(/i',
            '/passthru\s*\(/i',
            '/popen\s*\(/i',
            '/proc_open\s*\(/i',
            '/\$_GET\s*\[/i',
            '/\$_POST\s*\[/i',
            '/\$_REQUEST\s*\[/i',
            '/\$_COOKIE\s*\[/i',
            '/\$_FILES\s*\[/i',
            '/file_put_contents\s*\(/i',
            '/fopen\s*\(/i',
            '/fwrite\s*\(/i',
            '/unlink\s*\(/i',
            '/move_uploaded_file\s*\(/i',
            '/copy\s*\(/i',
            '/preg_replace\s*\(.*\/e/i',
            '/create_function\s*\(/i',
            '/assert\s*\(/i',
            '/@eval/i',
            '/@system/i',
            '/@include/i',
            '/@require/i',
            '/strrev\s*\(/i',
            '/b374k/i',
            '/\.html/i',
            '/phpspy/i',
            '/if\s*\(isset\s*\(\$_(GET|POST|REQUEST)\[.*\]\)\)/i',
            '/if\s*\(\$_(GET|POST|REQUEST)\[.*\]\s*==\s*[\'"].*[\'"].*\)/i',

            // === Python malicious patterns ===
            '/import\s+os/i',
            '/import\s+subprocess/i',
            '/import\s+socket/i',
            '/import\s+pymysql/i',
            '/import\s+psycopg2/i',
            '/import\s+sqlite3/i',
            '/os\.system\s*\(/i',
            '/subprocess\.(Popen|call|run)\s*\(/i',
            '/socket\.socket\s*\(/i',
            '/open\s*\(.*(passwd|shadow)/i',
            '/requests\.get\s*\(/i',
            '/requests\.post\s*\(/i',
            '/base64\.b64decode\s*\(/i',
            '/marshal\.loads\s*\(/i',
            '/exec\s*\(/i',
            '/eval\s*\(/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}