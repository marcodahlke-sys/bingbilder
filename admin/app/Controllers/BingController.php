<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class BingController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $this->view('bing/index', [
            'title' => 'Bing-Download',
        ]);
    }
}