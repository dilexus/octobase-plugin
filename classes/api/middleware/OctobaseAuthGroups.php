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


class OctobaseAuthGroups
{
    public function handle($request, Closure $next, $groups, $own = 'false')
    {

        if (Settings::get('octobase_debug_on')) {
            $userId = Settings::get('octobase_debug_user_id');
            $request->attributes->add(['userId' => $userId]);
            $authUser = Auth::findUserById($userId);
            if (!$authUser) {
                return response()->json(['error' => 'User Not Found'], 401);
            }
            $regGroups = $authUser['groups']->lists('code');
            $request->attributes->add(['groups' => $regGroups]);
            $authGroups = explode(':', $groups);
            $request->attributes->add(['allowedGroups' => $authGroups]);
            $request->attributes->add(['own' => $own]);
            return $next($request);
        }

        $authGroups = explode(':', $groups);
        $authroization = $request->header('Authorization');
        try {
            $token = Crypt::decryptString(str_replace('Bearer ', '', $authroization));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid Token'], 401);
        }
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized Access, Token Expired'], 401);
        }
        $authUser = Auth::findUserById($user->id);
        $regGroups = $authUser['groups']->lists('code');
        $commonGroups = array_intersect($regGroups, $authGroups);

        if (empty($commonGroups)) {
            return response()->json(['error' => 'Fobidden Access, Specific Groups Only'], 403);
        }

        $request->attributes->add(['groups' => $regGroups]);
        $request->attributes->add(['allowedGroups' => $authGroups]);
        $request->merge(['userId' => $user['id']]);
        $request->attributes->add(['own' => $own]);
        return $next($request);

    }
}
