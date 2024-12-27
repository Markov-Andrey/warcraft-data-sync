<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class W3xController extends Controller
{
    public function index()
    {
        $config = config('w3x');

        $files = $config['files'] ?? [];
        $directories = $config['directories'] ?? [];

        return view('project', compact('files', 'directories'));
    }
}
