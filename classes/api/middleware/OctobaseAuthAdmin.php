<?php namespace Dilexus\Octobase\Classes\Api\Middleware;

use Closure;
use October\Rain\Auth\Models\User;
use RainLab\User\Facades\Auth;

class OctobaseAuthAdmin {
    public function handle($request, Closure $next){
        $authroization = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authroization);
        $user = User::whereRaw('SHA2(persist_code, 256) = ?', [$token])->first();
        if(!$user){
            return response()->json(['error' => 'Unauthorized Accesss'], 401);
        }
        $authUser = Auth::findUserById($user->id);
        $groups = $authUser['groups']->lists('code');
        if(in_array('admin', $groups)){
            $request->merge(['userId' => $user['id']]);
            $request->attributes->add(['own' => 'false']);
            return $next($request);
        }else {
            return response()->json(['error' => 'Unauthorized Accesss'], 401);
        }

    }
}
