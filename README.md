
 # Octobase

## Introduction
An awesome  plugin for OctoberCMS is available to expose REST API services for data access from anywhere. This plugin enables the creation of CRUD operations for REST services and allows the creation of custom REST services with middleware support for API authorization. Additionally, the plugin provides an API for OctoberCMS frontend users to perform actions such as login, registration, token refresh, and retrieval of user information. Bearer API tokens are utilized for API authorization within this plugin.

##  How To
If you want to expose a REST webservice from your OctoberCMS plugin, please ensure that the following plugins have been installed.
1. October CMS Frontend Users Plugin
2. Octobase Plugin

## Create your Routes and APIs
1. Create routes.php in your plugin
2. As an example if you want to enable CRUD services. Let's say your plugin is School and you have a Model for Student
   
```
  Route::prefix('api/school/v1')->group(function () {

    (new Octobase)->crud('Dilexus\School\Models\Student',
        ['obPublic'],
        ['obPublic'],
        ['obPublic'],
        ['obPublic'],
        ['obPublic']
    );
});

 ```

Here ObPublic is a middlewere that expose your CRUD APIs to public.

## Operations
The above will expose the following APIs to the public

GET     /api/school/v1/students       - List all students

GET     /api/school/v1/students/1     - List single student by Id

POST    /api/school/v1/students      - Create a new student

POST    /api/school/v1/students/1    - updates a student by Id

DELETE  /api/school/v1/students/1  - delete a student by Id



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
