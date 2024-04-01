
 # Octobase : REST API Services Plugin for OctoberCMS

## Introduction
An awesome  plugin for OctoberCMS is available to expose REST API services for data access from anywhere. This plugin enables the creation of CRUD operations for REST services and allows the creation of custom REST services with middleware support for API authorization. Additionally, the plugin provides an API for OctoberCMS frontend users to perform actions such as login, registration, token refresh, and retrieval of user information. Bearer API tokens are utilized for API authorization within this plugin.

## Requirements
Tested in PHP 8.2 and above. You need to enable sodium extension in the server inorder to work with this plugin

## Flutter SDK
You can connect the APIs of your Octobase Easily with Flutter SDK here https://pub.dev/packages/octobase_flutter

## Manual Installation
php artisan plugin:install Dilexus.Octobase --from=https://github.com/chaturadilan/octobase-plugin.git

## Roadmap
This plugin requires additional features to be added in the future.

1. ~~Social Auth Login support~~ (Completed with Firebase Authentication)
2. Mobile and Web SDKs(Javascript, Flutter, Kotlin, Swift) support for the plugin

You can assist in accelerating the development of the plugin by contributing to the following features that are on the roadmap.

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/bcdperera)


##  How To
If you want to expose a REST webservice from your OctoberCMS plugin, please ensure that the following plugins have been installed.
1. October CMS Frontend Users Plugin
2. Octobase Plugin

## Create your Routes and APIs
1. Create routes.php in your plugin
2. As an example if you want to enable CRUD services. Let's say your plugin is School and you have a Model for Student

```
use Dilexus\Octobase\Classes\Api\Lib\Octobase;

...


  Route::prefix('api/school/v1')->group(function () {

    (new Octobase)->crud('Dilexus\School\Models\Student',
        ['obPublic'], // List All records
        ['obPublic'], // List single record
        ['obPublic'], // add single record
        ['obPublic'], // update single record
        ['obPublic']  // delete single record
    );

});

 ```

Here obPublic is a middlewere that expose your CRUD APIs to public.

More Examples

```
 (new Octobase)->crud('Dilexus\School\Models\Student',
        createM: ['obPublic'], // Enable only the add (create) API
    );
```

## Operations

The above will expose the following APIs to the public

### Main APIs

GET     /api/school/v1/students       - List all students (you can use page, limit query parametrs for pagination, with, where, order quary parameters to filter data)

GET     /api/school/v1/students/1     - List single student by Id (form parameters are required)

POST    /api/school/v1/students      - Create a new student (form parameters are required)

POST    /api/school/v1/students/1    - updates a student by Id

DELETE  /api/school/v1/students/1  - delete a student by Id

### File APIs

POST    /api/school/v1/students/1/files    - upload file to the student (form file parameters are required)

DELETE    /api/school/v1/students/1/files    - delete file of a student (file parameter name is required, use all to delete all files)

### Language
if you want to speicify a language to the api, either you can use Content-Language (Eg: Content-Language: en) header or locale (Eg: locale=en) query parameter. For this you need to have OctoberCMS Translation plugin

## More Examples

This will only allow list API to public, by default other APIs are restricted to anyone

```
  Route::prefix('api/school/v1')->group(function () {

    (new Octobase)->crud('Dilexus\School\Models\Category',
        ['obPublic'], // List All records
    );

});

 ```


## Middleware

#### obRestricted
Restrict Anyone from acccessing the APIs

#### obRegistered
Only registered users will be allowed to access the APIs. If you want to restrict registered users to only access their own data, add ":true" after the middleware name. For example, [obRegistered:true]. However, please ensure that the table has a user_id column if you choose to do so.

#### obAdmin
Only registered Admin will be allowed to access the APIs. Please make sure to create a Admin user group with the code 'admin' in the Groups section of the Users Plugin and user has been assigned to admin group. Admin has permission to do any operations on the APIs

#### obPublic
Anyone who calls the APIs has unrestricted access to them.

#### obGroups
Only the defined groups can access the APIs. For example, if you want to restrict access to the groups "admin" and "api", you can add them as follows using a colon: [obGroups:admin:api].

## Custom Functions
If you want to modify the output before it is sent to the client, you can use a custom function like below

```
 (new Octobase)->crud('Dilexus\School\Models\Student',
        ['obPublic'],
        function :function ($records, $method) {
            return $records->select('name');
        }
 );

```


## Authentication and Authrorization

### Authentication
You can authenticate the API using following API. You have to send login, password form parameters. login is the user's email

POST /octobase/login

#### Registration
You can register the user using following API. You need to send first_name, last_name, email, password, confirm_password as form parameters. You can disable this feature from Octobase plugin settings. Also You can enable the auto activation of the user from the plugin settings.

POST /octobase/register

#### Refresh
You can send the SHA256 token and get a new token from refresh API. You need to send the token in the Authtorization heder as a Bearer token

POST /octobase/refresh

#### User
You get the user information from this API. You need to send the token in the Authtorization header as a Bearer token

GET /octobase/user

#### Check
You get the status of the token whether it is exist or not. You need to send the token in the Authtorization header as a Bearer token

GET /octobase/check

#### Firebase Authentication
You can authenticate it with Firebase by sending ID token to following API. It required a form parameter called token (eg: token=[Firebase User ID Token]). It will create a record in Users if the user is not there or return the existing user with Octobase token. You can use Octobease token after that to call APIs. Firebase can be configured in Octobase Settings

POST /octobase/login/firebase

### Authrorization
To Authorize all your APIs you need to send the token in the Authtorization heder as a bearer token

### Debug mode Settings
If you are using APIs solely for testing purposes, you can enable the debug mode to test them without authentication and authorization.


## Create your own APIs with Middleware
You can attach Octobase middleware to your own APIs as well. As an example.

```
Route::prefix('api/school/v1')->group(function () {

    Route::get('student/{id}/getInfo', function (Request $request, $id)  {
       // Logic Here
    })->middleware(['obRegistered']);

});

```

##  License (MIT)

Copyright (c) 2023 Chatura Dilan Perera

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Developer
This plgin is developed by Chatura Dilan Perera

## Bugs and Comments
To report bugs or comments on this app or if you are looking to create an Flutter app based on this app please contact me 'Chatura Dilan Perera'
