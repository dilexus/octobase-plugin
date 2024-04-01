<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Closure;
use RainLab\User\Facades\Auth;
use October\Rain\Auth\Models\User;
use Dilexus\Octobase\Models\Settings;

class OctobaseAuthRegistered
{
    public function handle($request, Closure $next, $own = 'false')
    {

        if (Settings::get('octobase_debug_on')) {
            return $next($request);
        }

        $authroization = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authroization);
        $user = User::whereRaw('SHA2(persist_code, 256) = ?', [$token])->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized Access'], 401);
        }
        $authUser = Auth::findUserById($user->id);
        $groups = $authUser['groups']->lists('code');
        if (in_array('registered', $groups)) {
            $request->attributes->add(['userId' => $user['id']]);
            $request->attributes->add(['own' => $own]);
            return $next($request);
        } else {
            return response()->json(['error' => 'Forbidden Access, Registered Users Only'], 403);
        }

    }
}
