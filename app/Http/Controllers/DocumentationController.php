<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Display the documentation index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('documentation.index');
    }
}
