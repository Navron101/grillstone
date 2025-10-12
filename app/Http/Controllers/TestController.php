<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::all();

        return Inertia::render('Tests/Index', [
            'tests' => $tests
        ]);
    }
}
