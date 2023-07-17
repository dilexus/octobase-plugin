<?php
use Illuminate\Http\Request;
use RainLab\User\Facades\Auth;

Route::prefix('octobase')->group(function () {

    Route::post('login', function (Request $request)  {
        try{
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $credentials = explode(':', base64_decode($token));
            $username = $credentials[0];
            $password = $credentials[1];

            $user = getAuthUser($username, $password);
            return response()->json([ 'first_name' => $user['name'],
            'last_name' => $user['surname'],
            'email' => $user['email'],
            'username' => $user['username'],
            'token' => hash('sha256',$user['persist_code'])]);

        }catch(\Exception $e){
            return response()->json(['error' => 'Incorrect credentials'], 400);
        }

    });

    function findGroupByCode($groups, $code){
        foreach ( $groups as $element ) {
            if ( $code == $element->code ) {
                return $element;
            }
        }
    }

    function getAuthUser($username, $password){
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


});
