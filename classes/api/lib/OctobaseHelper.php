<?php namespace Dilexus\Octobase\Classes\Api\Lib;
      use RainLab\User\Facades\Auth;




class OctobaseHelper {
    static function getAuthUser($username, $password){
        try{
            $user = Auth::authenticate([
                'login' => $username,
                'password' => $password
            ]);
            return $user;
        }catch(\Exception $e){
            return response()->json(['error' => 'Username or password is incorrect'], 400);
        }
    }

}
