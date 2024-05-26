<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Closure;
use Dilexus\Octobase\Models\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use RainLab\User\Models\User;

class OctobaseAuthAdmin
{
    public function handle($request, Closure $next)
    {
        if (Settings::get('octobase_debug_on')) {
            return $next($request);
        }

        $authroization = $request->header('Authorization');
        try {
            $token = Crypt::decryptString(str_replace('Bearer ', '', $authroization));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid Token'], 401);
        }
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized Access'], 401);
        }
        $authUser = Auth::findUserById($user->id);
        $groups = $authUser['groups']->lists('code');
        if (in_array('admin', $groups)) {
            $request->merge(['userId' => $user['id']]);
            $request->attributes->add(['own' => 'false']);
            return $next($request);
        } else {
            return response()->json(['error' => 'Fobidden Access, Admins Only'], 403);
        }

    }
}
