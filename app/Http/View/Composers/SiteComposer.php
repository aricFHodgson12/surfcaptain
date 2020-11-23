<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SiteComposer
{

    protected $user;

    public function __construct()
    {
        // Dependencies automatically resolved by service container...
        $this->user = Auth::user();
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        //only show site units on pages that have data to be converted
        $unitUrls = array('forecast','nearby');
        $showUnits = false;
        foreach ($unitUrls as $url) {
            if (strpos(request()->path(),$url) !== false)
                $showUnits = true;
        }
        $view->with('showUnits',$showUnits);

        $view->with('user', $this->user);

        if ($this->user) {
            $subscription = ($this->user->subscribed('pro')) ? 'Pro' : 'Basic';
            $view->with('subscription',$subscription);
        }
    }
}
