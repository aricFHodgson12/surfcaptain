<?php

namespace App\Http\Controllers;

use App\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class FaqController extends Controller
{
    /**
     * Show the faq view
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function __invoke()
    {
        $faqs = Faq::where('active',1)->orderBy('order','asc')->get();
        return view('faq', ['faqs' => $faqs]);
    }
}
