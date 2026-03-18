<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;

class PasswordGeneratorController extends Controller
{
    public function index()
    {
        return view('tools.passwordgenerator.index');
    }
}