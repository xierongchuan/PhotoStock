<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function login(): View
    {
        return view('auth');
    }

    public function dashboard(): View
    {
        return view('dashboard');
    }
}
