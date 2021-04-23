# Cryptr with Laravel API

## 04 - Protect API Endpoints

### CryptrGuard middleware

🛠️️ Return in root folder and create `CryptrGuard` middleware:

```bash
php artisan make:middleware CryptrGuard
```

Note: __Middleware is responsible for filtering HTTP requests that arrive in the application, as well as those that leave it. In our case, it is verifying the authentication of a user so they can access certain resources.__

🛠️️ Open up `app/Http/Middleware/CryptrGuard.php` and replace it with the following code:

```php
<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Cryptr\CryptrJwtVerifier;
use Illuminate\Support\Facades\Log;
 
class CryptrGuard
{
   // To handle Public Keys in cache to avoid HTTP requests :
   //
   // const JWKS_KEY = "cryptr_jwks";
   // const JWKS_TTL_SECONDS = 900;
 
   public function handle(Request $request, Closure $next)
   {
       $requestOrigin = $request->headers->get('origin');
       // 0. Config
       $cryptrBaseUrl = env('CRYPTR_BASE_URL');
       $domain = env('TENANT_DOMAIN');
       $allowedOrigins = explode(',',env('DEFAULT_REDIRECT_URI'));
       $allowedClientIds = explode(',',env('CLIENT_ID'));
 
       // 1. Handle CORS
       $allowOriginHeader = in_array($requestOrigin, $allowedOrigins) ? $requestOrigin : array_values($allowedOrigins)[0];
 
       $headers = [
           'Access-Control-Allow-Origin' => $allowOriginHeader,
           'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
           'Access-Control-Allow-Headers'=> 'Content-Type, Authorization, Origin'
       ];
 
       if($request->getMethod() == "OPTIONS") {
           // The client-side application can set only headers allowed in Access-Control-Allow-Headers
           return Response::make('OK', 200, $headers);
       }
 
       // 2. JWT Validation
       $cryptrJwtVerifier = new CryptrJwtVerifier($cryptrBaseUrl, $domain, $allowedOrigins, $allowedClientIds);
 
       try {
           // 2.1 Fetch public keys set to validate token
           //
           // To handle Public Keys in cache to avoid HTTP requests, uncomment the following lines :
           //
           // $jwks = Cache::get(JWKS_KEY);
           //
           // if (!$jwks) {
           //     $jwks = $cryptrJwtVerifier->getJwks();
           //     Cache::add(JWKS_KEY, $jwks, JWKS_TTL_SECONDS);
           // }
           // ...
 
           $jwks = $cryptrJwtVerifier->getJwks();
           // 2.2 Validate token with
           $decoded = $cryptrJwtVerifier->validate($request->bearerToken(), $jwks);
 
           // 1.bis Add headers to response
           $response = $next($request);
           foreach($headers as $key => $value){
               $response->header($key, $value);
           }
           return $response;
 
           // ...
 
       } catch (\Exception $exception) {
           $errMsg = $exception->getMessage();
           $errLine = $exception->getLine();
           error_log("Can not handle: $errMsg:$errLine");
           return response('Unauthorized', 401)->header('Content-Type', 'text/plain');
       }
   }
}
```

In this middleware:
- Check the header, if it does not contain `http://localhost:4000` the request is blocked  
- Retrieve the client's key to validate the token of the request  
- Accept request and process it, otherwise return `401`  

### Configure middleware

🛠️️ Open up `app/Http/Kernel.php` and register cryptr-guard middleware `'cryptr-guard' => [\App\Http\Middleware\CryptrGuard::class]` to Kernel.php in `$middlewareGroups`

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
           'throttle:api',
           \Illuminate\Routing\Middleware\SubstituteBindings::class,
       ],
       // Add cryptr guard:
       'cryptr-guard' => [\App\Http\Middleware\CryptrGuard::class]
   ];
 
   // ...
```

🛠️️ Open up `routes/api.php` to protect individual API endpoints by applying the `jwt` middleware, wrap route with `Route::prefix('/')->middleware('cryptr-guard')->group(function () {`

```php
<?php
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
 
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
 
// Apply cryptr-guard middleware:
Route::prefix('/')->middleware('cryptr-guard')->group(function () {
   Route::apiResource('courses', CourseController::class);
});
```

For all actions, the routes are secured by respecting the criteria of Cryptr

It is now time to try this on an application. For this purpose, we have an example app on Vue.

🛠️️  Clone our `cryptr-vue-sample`:

```bash
git clone --branch 07-backend-courses-api https://github.com/cryptr-examples/cryptr-vue2-sample.git
```

🛠 Install the Vue project dependencies with `yarn`

🛠️️  Add `.env.local` file in the Vue project with your variables:

```javascript
VUE_APP_AUDIENCE=http://localhost:8080
VUE_APP_CLIENT_ID=YOUR_CLIENT_ID
VUE_APP_CRYPTR_BASE_URL=YOUR_BASE_URL
VUE_APP_DEFAULT_LOCALE=fr
VUE_APP_DEFAULT_REDIRECT_URI=http://localhost:8080
VUE_APP_TENANT_DOMAIN=YOUR_DOMAIN
VUE_APP_CRYPTR_TELEMETRY=FALSE
```

🛠️️  Run vue server with `yarn serve` and try to connect. Your Vue application redirects you to your sign form page, where you can sign in or sign up with an email.

Note: __You can log in with a sandbox email and we send you a magic link which should directly arrive in your personal inbox.__

Once you're connected, click on "Protected route". You can now view the list of the courses.

[Next](https://github.com/cryptr-examples/cryptr-laravel-api-sample/tree/05-bonus)
