<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PluginController extends Controller
{
    public function index(): View
    {
        return view('admin.plugins');
    }
}
