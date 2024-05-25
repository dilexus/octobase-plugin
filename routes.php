<?php
//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Dilexus\Octobase\Classes\Api\Lib\Utils;
use Dilexus\Octobase\Models\Settings;
use Illuminate\Http\Request;
use RainLab\User\Models\User;

Route::prefix('octobase')->group(function () {

    Route::post('login', function (Request $request) {
        try {
            $attempt = Auth::attempt([
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ], true);

            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'No user exists for authentication purposes'], 401);
            }

            return response()->json([
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'username' => $user['username'],
                'groups' => $user['groups']->lists('code'),
                'token' => $user->getRememberToken(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('logout', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(['error' => 'Unauthroized acceess'], 401);
            }
            Auth::login($user, true);
            Auth::logout();
            return response()->json(['success' => 'Signout Success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('check', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(['error' => 'Token Expired'], 404);
            }
            return response()->json(['success' => 'Token Exists'], 200);
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
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
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
                return response()->json([
                    'id' => $authUser['id'],
                    'first_name' => $authUser['first_name'],
                    'last_name' => $authUser['last_name'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'is_new' => true,
                    'token' => $authUser->getRememberToken()], 201
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
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(['error' => 'Unauthroized acceess'], 401);
            }

            $authUser = Auth::findUserById($user->id);

            if ($authUser) {
                $avatar = $authUser['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json([
                    'id' => $authUser['id'],
                    'first_name' => $authUser['first_name'],
                    'last_name' => $authUser['last_name'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'token' => $token]
                );

            } else {
                return response()->json(['error' => 'User not Found for the given token'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('refresh', function (Request $request) {
        try {
            $authroization = $request->header('Authorization');
            $token = str_replace('Bearer ', '', $authroization);

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(['error' => 'Unauthroized acceess'], 401);
            }
            Auth::login($user, true);
            Auth::logout();
            Auth::login($user, true);

            if ($user) {
                $avatar = $user['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json([
                    'id' => $user['id'],
                    'first_name' => $user['name'],
                    'last_name' => $user['surname'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'is_activated' => $user['is_activated'],
                    'groups' => $user['groups']->lists('code'),
                    'avatar' => $avatar,
                    'token' => $user->getRememberToken()]
                );

            } else {
                return response()->json(['error' => 'User Not Found for the given token'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

    Route::post('login/firebase', function (Request $request) {
        try {
            $idTokenString = $request->input('token');
            $credentialsArray = json_decode(Settings::get('firebase_credentials'), true);
            $firebase_credentials = Settings::get('firebase_credentials');
            if (!empty($firebase_credentials)) {
                $factory = (new \Kreait\Firebase\Factory)->withServiceAccount($firebase_credentials);
            } else {
                $factory = (new \Kreait\Firebase\Factory)->withServiceAccount('config/firebase_credentials.json');
            }

            $auth = $factory->createAuth();
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $auth->getUser($uid);

            $authUser = User::where('email', $user->email)->first();
            if (!$authUser) {
                $require_activation = Settings::get('require_activation');
                $randomPass = Utils::randomPassword();

                $payload = [
                    'first_name' => $user->displayName,
                    'last_name' => $user->uid,
                    'email' => $user->email,
                    'username' => $user->uid,
                    'password' => $randomPass,
                    'password_confirmation' => $randomPass,
                ];
                $authUser = Auth::register($payload, $require_activation);
                Auth::setUser($authUser);
                Auth::login($authUser, true);
                $groups = Settings::get('default_groups');
                if ($groups) {
                    $groups = array_map('intval', explode(',', $groups));
                } else {
                    $groups = [2];
                }
                $authUser->groups()->attach($groups);
                $avatar = $authUser['avatar'];
                if ($avatar) {
                    $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                }
                return response()->json([
                    'id' => $authUser['id'],
                    'first_name' => $authUser['first_name'],
                    'last_name' => $authUser['last_name'],
                    'email' => $authUser['email'],
                    'username' => $authUser['username'],
                    'is_activated' => $authUser['is_activated'],
                    'groups' => $authUser['groups']->lists('code'),
                    'avatar' => $avatar,
                    'is_new' => true,
                    'token' => $authUser->getRememberToken()], 201
                );
            } else {
                Auth::setUser($authUser);
                Auth::login($authUser, true);
                if ($authUser) {
                    $avatar = $authUser['avatar'];
                    if ($avatar) {
                        $avatar = ['path' => $avatar['path'], 'extenstion' => $avatar['extension']];
                    }
                    return response()->json([
                        'id' => $authUser['id'],
                        'first_name' => $authUser['first_name'],
                        'last_name' => $authUser['last_name'],
                        'email' => $authUser['email'],
                        'username' => $authUser['username'],
                        'is_activated' => $authUser['is_activated'],
                        'groups' => $authUser['groups']->lists('code'),
                        'avatar' => $avatar,
                        'token' => $authUser->getRememberToken()]
                    );

                } else {
                    Auth::logout();
                    return response()->json(['error' => 'User Not Found for the given token'], 400);
                }
            }

        } catch (\Exception $e) {
            Auth::logout();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    });

});
