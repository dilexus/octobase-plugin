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
        $token = str_replace('Bearer ', '', $authroization);
        $user = User::whereRaw('SHA2(persist_code, 256) = ?', [$token])->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized Access'], 401);
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
