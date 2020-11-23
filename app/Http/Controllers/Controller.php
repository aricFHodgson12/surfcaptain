<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Canvas\UserMeta;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Build a global JavaScript object for the Vue app.
     *
     * @return array
     */
    protected function scriptVariables(): array
    {
        $user = auth()->user();

        if ($user) {
            $metaData = UserMeta::where('user_id', $user->id)->first();
            $avatar = !empty(optional($metaData)->avatar) ? $metaData->avatar : $this->generateDefaultGravatar($user->email);
        }

        return [
            'avatar' => $avatar ?? null,
            'canvasPath' => config('canvas.path'),
            'path' => config('studio.path'),
            'identifier' => config('studio.identifier'),
            'timezone' => config('app.timezone'),
            'user' => $user,
        ];
    }    
}
