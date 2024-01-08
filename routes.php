<?php
//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Illuminate\Http\Request;
use RainLab\User\Facades\Auth;
use October\Rain\Auth\Models\User;
use Dilexus\Octobase\Models\Settings;

Route::prefix('octobase')->group(function () {

    Route::post('login', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $credentials = explode(':', base64_decode($token));
            $username = $credentials[0];
            $password = $credentials[1];

            $user = Auth::authenticate([
                'login' => $username,
                'password' => $password,
            ]);
            if (!$user) {
                return response()->json(['error' => 'No user exists for authentication purposes'], 401);
            }
            return response()->json(['first_name' => $user['name'],
                'last_name' => $user['surname'],
                'email' => $user['email'],
                'username' => $user['username'],
                'groups' => $user['groups']->lists('code'),
                'token' => hash('sha256', $user['persist_code']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('logout', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $user = User::whereRaw('SHA2(persist_code, 256) = ?', [$token])->first();
            if (!$user) {
                return response()->json(['error' => 'Unauthroized acceess'], 401);
            }
            Auth::setUser($user);
            Auth::logout();
            return response()->json(['success' => 'Signout Success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('register', function (Request $request) {
        try {
            $registration_disabled = Settings::get('registration_disabled');
            $require_activation = Settings::get('require_activation');
            Settings::get('registration_disabled');
            if ($registration_disabled) {
                return response()->json(['error' => 'User registration is disabled'], 403);
            } else {
                $payload = [
                    'name' => $request->input('first_name'),
                    'surname' => $request->input('last_name'),
                    'email' => $request->input('email'),
                    'username' => $request->input('username'),
                    'password' => $request->input('password'),
                    'password_confirmation' => $request->input('password_confirmation'),
                ];
                $authUser = Auth::register($payload, $require_activation);
                Auth::setUser($authUser);
                Auth::login($authUser, true);
                $authUser->groups()->attach(2);
                $avatar = $authUser['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json(['first_name' => $authUser['name'],
                    'last_name' => $authUser['surname'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'is_new' => true,
                    'token' => hash('sha256', $authUser['persist_code'])]
                );
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::get('user', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $user = User::whereRaw('SHA2(persist_code, 256) = ?', [$token])->first();

            if (!$user) {
                return response()->json(['error' => 'Unauthroized acceess'], 401);
            }

            $authUser = Auth::findUserById($user->id);

            if ($authUser) {
                $avatar = $authUser['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json(['first_name' => $user['name'],
                    'last_name' => $authUser['surname'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'token' => $token]
                );

            } else {
                return response()->json(['error' => 'User not Found for the given token'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('refresh', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $user = User::whereRaw('SHA2(persist_code, 256) = ?', [$token])->first();

            if (!$user) {
                return response()->json(['error' => 'Unauthroized acceess'], 401);
            }

            Auth::setUser($user);
            Auth::logout();
            Auth::login($user, true);
            $authUser = Auth::findUserById($user->id);

            if ($authUser) {
                $avatar = $authUser['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json(['first_name' => $authUser['name'],
                    'last_name' => $authUser['surname'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'token' => hash('sha256', $authUser['persist_code'])]
                );

            } else {
                return response()->json(['error' => 'User Not Found for the given token'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('login/social', function (Request $request) {
        try {
            $idTokenString = $request->input('token');
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount('config/firebase_credentials.json');
            $auth = $factory->createAuth();
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $auth->getUser($uid);

            $authUser = Auth::findUserByEmail($user->email);
            if (!$authUser) {
                $require_activation = Settings::get('require_activation');
                $randomPass = randomPassword();
                list($first_name, $last_name) = explode(" ", $user->displayName, 2);

                $payload = [
                    'name' => $first_name,
                    'surname' => $last_name,
                    'email' => $user->email,
                    'username' => $user->uid,
                    'password' => $randomPass,
                    'password_confirmation' => $randomPass,
                ];
                $authUser = Auth::register($payload, $require_activation);
                Auth::setUser($authUser);
                Auth::login($authUser, true);
                $authUser->groups()->attach(2);
                $avatar = $authUser['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json(['first_name' => $authUser['name'],
                    'last_name' => $authUser['surname'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'is_new' => true,
                    'token' => hash('sha256', $authUser['persist_code'])]
                );
            } else {
                Auth::setUser($authUser);
                Auth::login($authUser, true);
                if ($authUser) {
                    $avatar = $authUser['avatar'];
                    if ($avatar) {
                        $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                    }
                    return response()->json(['first_name' => $authUser['name'],
                        'last_name' => $authUser['surname'],
                        'email' => $authUser['email'],
                        'username' => $authUser['username'],
                        'is_activated' => $authUser['is_activated'],
                        'groups' => $authUser['groups']->lists('code'),
                        'avatar' => $avatar,
                        'token' => hash('sha256', $authUser['persist_code'])]
                    );

                } else {
                    return response()->json(['error' => 'User Not Found for the given token'], 400);
                }
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

});

function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
