<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Closure;
use Dilexus\Octobase\Models\Settings;
use Response;

class OctobaseAuthRestricted
{
    public function handle($request, Closure $next)
    {
        if (Settings::get('octobase_debug_on')) {
            return $next($request);
        }
        return Response::make('{"error" : "Forbidden Access to All"}', 403, ['Content-Type' => 'application/json']);
    }
}
