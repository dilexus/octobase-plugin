<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Closure;
use Response;

class OctobaseAuthRestricted {
    public function handle($request, Closure $next){

        return Response::make( '{"message" : "Access Denied"}' , 403, ['Content-Type' => 'application/json']);
    }
}
