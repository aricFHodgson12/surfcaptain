<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class HttpsProtocol
{
    public function handle($request, Closure $next)
    {
        Log::info('is secure: '.$request->secure());

        if (!$request->secure() && in_array(App::environment(), array('dev', 'production'))) {
            Log::info(secure_url($request->getRequestUri()));
            //return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
