<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

use Closure;
use Response;

class OctobaseAuthAdmin {
    public function handle($request, Closure $next){
        return Response::make( "Access Denied" , 403);
        //return $next($request);
    }
}
