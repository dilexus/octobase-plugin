<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

use Closure;
use Response;

class OctobaseAuthDenied {
    public function handle($request, Closure $next){

        return Response::make( '{"message" : "Access Denied"}' , 403, ['Content-Type' => 'application/json']);
    }
}
