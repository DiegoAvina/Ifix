<?php

namespace App\Http\Controllers;

use App\Services\Storefront\HomeService;

class HomeController extends Controller
{
    public function index(HomeService $home)
    {
        return view('home', $home->data());
    }
}