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
        return view('tools.pasfoto.index');
    }
}
