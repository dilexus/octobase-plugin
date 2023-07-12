<?php namespace Dilexus\Octobase\Classes\Api\Lib;
      use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SetLocale {
    public function handle(Request $request, Closure $next) {
        app()->setLocale($request->segment(1));
        URL::defaults(['locale' => $request->segment(1)]);
        return $next($request);
    }
}
