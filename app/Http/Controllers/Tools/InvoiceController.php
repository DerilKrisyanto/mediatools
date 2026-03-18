<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('tools.invoice.index');
    }
}