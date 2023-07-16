<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

use Closure;

class OctobaseAuthPublic {
    public function handle($request, Closure $next){
        return $next($request);
    }
}
