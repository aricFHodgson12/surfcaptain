<?php

namespace App\Http\Controllers;

use App\Alert;
use App\CanvasPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        //if loading pwa, check to see if there is a last page, and redirect there.
        if ( $request->query('source') == 'pwa' and $lastPWAPage = $request->cookie('sc_lastpage'))
            return redirect($lastPWAPage);

        return view('spa', [
            'scripts' => $this->scriptVariables(),
        ]);

    }


    public function getBlogposts() {
        $blogPosts = CanvasPost::whereNull('deleted_at')
            ->select('title','summary','slug','featured_image','user_id')
            ->orderBy('published_at','DESC')
            ->limit(4)
            ->get();

        return $blogPosts;

    }

    public function getAlert()
    {
        $return = array(
            'error' => false,
            'alertTxt' => ''
        );

        if ($alert = Alert::where('active',1)->orderBy('created_at','DESC')->first())
            $return['alertTxt'] = $alert->alert_text;

        return $return;

    }
}
