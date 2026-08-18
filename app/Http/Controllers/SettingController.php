<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the Settings hub index.
     */
    public function index(): View
    {
        return view('settings.index');
    }
}
