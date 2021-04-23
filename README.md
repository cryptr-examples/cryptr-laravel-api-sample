# Cryptr with Laravel API

## 05 - Bonus

🛠️️ Open up `app/Http/Controllers/Api/CourseController.php` and add request:

```php
<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
 
class CourseController extends Controller
{
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   // 1 Add request:
   public function index(Request $request)
   {
       // 2 Check which user is it:
       error_log(serialize($request->session()->get('cryptr-user')));
       return response()->json(
           [array(
               "id" => 1,
               "user_id" =>
               "eba25511-afce-4c8e-8cab-f82822434648",
               "title" => "learn git",
               "tags" => ["colaborate", "git" ,"cli", "commit", "versionning"],
               "img" => "https://carlchenet.com/wp-content/uploads/2019/04/git-logo.png",
               "desc" => "Learn how to create, manage, fork, and collaborate on a project. Git stays a major part of all companies projects. Learning git is learning how to make your project better everyday",
               "date" => '5 Nov',
               "timestamp" => 1604577600000,
               "teacher" => array(
                   "name" => "Max",
                   "picture" => "https://images.unsplash.com/photo-1558531304-a4773b7e3a9c?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=634&q=80"
               )
           )], 200);
   }
 
   // ...
```

🛠️️ Open up `app/Http/Kernel.php` and add `\Illuminate\Session\Middleware\StartSession::class` in api

```php
// ...
 
   protected $middlewareGroups = [
       'web' => [
           \App\Http\Middleware\EncryptCookies::class,
           \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
           \Illuminate\Session\Middleware\StartSession::class,
           // \Illuminate\Session\Middleware\AuthenticateSession::class,
           \Illuminate\View\Middleware\ShareErrorsFromSession::class,
           \App\Http\Middleware\VerifyCsrfToken::class,
           \Illuminate\Routing\Middleware\SubstituteBindings::class,
       ],
 
       'api' => [
           \Illuminate\Session\Middleware\StartSession::class,
           'throttle:api',
           \Illuminate\Routing\Middleware\SubstituteBindings::class,
       ],
       'cryptr-guard' => [\App\Http\Middleware\CryptrGuard::class]
   ];
 
   // ...

```

🛠️️ Open up `app/Http/Middleware/CryptrGuard.php` and retrieve token informations with `$request->session()->put('cryptr-user', $decoded);`

```php
// ...
 
  $jwks = $cryptrJwtVerifier->getJwks();
  // 2.2 Validate token with
  $decoded = $cryptrJwtVerifier->validate($request->bearerToken(), $jwks);

  // store $decoded if you need to handle it from Controller action
  $request->session()->put('cryptr-user', $decoded);

  // 1.bis Add headers to response
  $response = $next($request);
  foreach($headers as $key => $value){
      $response->header($key, $value);
  }
  return $response;

  // ...
```

The metadata is stored in the `cryptr-user` variable, this variable is used to identify the user.

Congratulations if you made it to the end!

I hope this was helpful, and thanks for reading! 🙂
