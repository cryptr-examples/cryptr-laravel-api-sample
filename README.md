# Cryptr with Laravel API

## 03 - Valide access tokens

### Install dependencies

🛠 Update composer setup by adding `PHP-JWT` to encode and decode `JSON Web Tokens (JWT)` in PHP:

```bash
composer require firebase/php-jwt
```

🛠 Open up `composer.json` and add `Cryptr\\": "cryptr/` in autoloader psr-4:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Cryptr\\": "cryptr/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
},
```

With the autoloader, we can define a namespace prefix and the directory mapped to that prefix. Everything in the cryptr folder is a namespace. Anything the composer asks for that has a namespace starting with "Cryptr" can be found in the "cryptr" directory.

🛠 Don’t forget to run `composer dump-autoload`

### Create sample resource model

🛠 First, create a basic model in order to have a data structure:

```bash
php artisan make:model Course
```

🛠 Next, add `protected $fillable = ['title', 'date', 'desc', 'img'];` before use `HasFactory` in `app/Models/Course.php`:

```php
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class Course extends Model
{
   // Add protected $fillable here
   protected $fillable = ['title', 'date', 'desc', 'img'];
 
   use HasFactory;
}
```

### Create sample resource controller

🛠 First, create an API resource controller which does not include the edit creation methods by adding `--api` and add `--model` to use a model instance:

```bash
php artisan make:controller CourseController --api --model=Course
```

Note: __The purpose of the controller is to receive a request (which has already been selected by a route) and to define the appropriate response.__

🛠 Next, create an Api folder and move generated file in it with this command:

```bash
mkdir app/Http/Controllers/Api && mv app/Http/Controllers/CourseController.php "$_"
```

🛠️️ Open up `app/Http/Controllers/Api/CourseController.php` and replace the contents with this:

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
   public function index()
   {
       return Course::all()->toJson();
   }
 
   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
   {
       $course = Course::create($request->all());
 
       if ($course) {
           return response() -> json([
               'data' => $course
           ], 200);
       } else {
           return response() -> json([
               'error' => 'unprocessable course'
           ], 422);
       }
   }
 
   /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function show($id)
   {
       if (Course::where('id', $id)->exists()) {
 
           return Course::find($id)->toJson();
       } else {
           return response()->json([
               "error" => "course not found"
           ], 404);
       }
   }
 
   /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function update(Request $request, $id)
   {
       if (Course::where('id', $id)->exists()) {
           $course = Course::find($id);
 
           $course->date = is_null($request->date) ? $course->date : $request->date;
           $course->desc = is_null($request->desc) ? $course->desc : $request->desc;
           $course->img = is_null($request->img) ? $course->img : $request->img;
           $course->title = is_null($request->title) ? $course->title : $request->title;
           $course->save();
 
           return response()->json([
               'data' => $course
           ], 200);
         } else {
           return response()->json([
             "error" => "course not found"
           ], 404);
         }
   }
 
   /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function destroy($id)
   {
       if(Course::where('id', $id)->exists()) {
           $course = Course::find($id);
           $course->delete();
 
           return response()->json([
             "message" => "records deleted"
           ], 202);
         } else {
           return response()->json([
             "error" => "course not found"
           ], 404);
         }
   }
}
```

### Link the route and the controller

🛠️️ Open up `routes/api.php` and add `use app\Http\Controllers\Api\CourseController;`

```php
<?php
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// 1. Add this line:
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
 
Route::middleware('auth:api')->get('/user', function (Request $request) {
   return $request->user();
});
```

🛠️️ Next, remove the base route and add this code instead: `Route::apiResource('courses', CourseController::class);`

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
 
// 2. Modify the route:
Route::apiResource('courses', CourseController::class);
```

🛠️️ Next, add `/v1` to api in `app/Providers/RouteServiceProvider.php`

```php
// ...
 
public function boot()
{
    $this->configureRateLimiting();

    $this->routes(function () {
        // Add /v1 to api
        Route::prefix('api/v1')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    });
}

// ...
```

🛠️️ Next, update course controller in `app/Http/Controllers/Api/CourseController.php`:

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
   public function index()
   {
       // Update here:
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
```

🛠️️ Run the server with command `php artisan serve` and open **insomnia** or **postman** to make a `GET` request should end with `200`

### JWT validation

🛠️️ Create cryptr folder and go to the folder:

```bash
mkdir cryptr && cd cryptr
```

🛠️️ Create `CryptrJwtVerifier.php` with command `touch CryptrJwtVerifier.php` and copy paste this code:

```php
<?php
namespace Cryptr;
 
use \Firebase\JWT\JWK;
use \Firebase\JWT\JWT;
use \Illuminate\Support\Facades\Http;
use DateTime;
use Exception;
use Cryptr\JwtClaimsValidation;
 
class CryptrJwtVerifier
{
 public function __construct($cryptrBaseUrl, $domain, $allowedOrigins, $allowedCLientIds)
 
 {
   $this->wellknownEndpoint = "$cryptrBaseUrl/t/$domain/.well-known";
 
   # JWT
   $this->jwks = NULL;
 
   # Issuer
   $this->tenantDomain = $domain;
   $this->issuer = "$cryptrBaseUrl/t/$domain";
 
   # Audiences
   $this->allowedOrigins = $allowedOrigins;
   $this->allowedClientIds = $allowedCLientIds;
 }
 
 public function getJwks()
 {
   try {
     $keys = Http::get($this->wellknownEndpoint)['keys'];
     $jwks = ['keys' => $keys];
     $this->jwks = $jwks;
 
     return $jwks;
   } catch (Exception $e) {
       echo 'Can not fetch JWKS : ',  $e->getMessage(), "\n";
       return [];
   }
 }
 
 public function decode($jwt, $jwks)
 {
  $publicKeys = JWK::parseKeySet($jwks);
   return JWT::decode($jwt, $publicKeys, array('RS256'));
 }
 
 public function validate($jwt, $jwks = NULL)
 {
   // 0. Prepare public keys
   $jwks = $jwks ? $jwks : $this->jwks;
   $publicKeys = JWK::parseKeySet($jwks);
   // 1. Decode token
   $decodedJwt = $this->decode($jwt, $jwks);
 
   // 2. Assert claims
   $jwtClaims = new JwtClaimsValidation($this->tenantDomain, $this->issuer, $this->allowedOrigins, $this->allowedClientIds);
   $jwtClaims->isValid($decodedJwt);
 
   return $decodedJwt;
 }
}
```

🛠️️ Create `JwtClaimsValidation.php` with command `touch JwtClaimsValidation.php` and copy paste this code:

```php
<?php
namespace Cryptr;
 
use DateTime;
use Exception;
 
 
class JwtClaimsValidation
{
 public function __construct($tenantDomain, $issuer, $allowedOrigins, $allowedClientIds)
 {
   # Issuer
   $this->tenantDomain = $tenantDomain;
   $this->issuer = $issuer;
 
   # Audiences
   $this->allowedOrigins = $allowedOrigins;
   $this->allowedClientIds = $allowedClientIds;
 }
 
 public function validateResourceOwner($decodedToken, $userId)
 {
   if ($decodedToken->sub != $userId) {
     throw new Exception('The resource owner identifier (cryptr user id) of the JWT claim (sub) is not compliant');
   }
   return true;
 }
 
 public function validateScopes($decodedToken, $authorizedScopes)
 {
   if (array_intersect($decodedToken->scp, $authorizedScopes) != $decodedToken->scp){
     throw new Exception('The scopes of the JWT claim (scp) resource are not compliants');
   };
   return true;
 }
 
 private function currentTime() {
   return new DateTime();
 }
 
 public function validateExpiration($decodedToken) {
   $expiration = DateTime::createFromFormat( 'U', $decodedToken->exp );
 
   if ($expiration < $this->currentTime()){
     throw new Exception('The expiration of the JWT claim (exp) should be greater than current time');
   }
 
   return true;
 }
 
 public function validateIssuedAt($decodedToken) {
   $issuedAt = DateTime::createFromFormat( 'U', $decodedToken->iat );
 
   if ($this->currentTime() < $issuedAt){
     throw new Exception('The issuedAt of the JWT claim (iat) should be lower than current time');
   };
 
   return true;
 }
 
 public function validateNotBefore($decodedToken) {
   $notBefore = DateTime::createFromFormat( 'U', $decodedToken->nbf );
 
   if ($this->currentTime() < $notBefore){
     throw new Exception('The notBefore of the JWT claim (iat) should be lower than current time');
   };
 
   return true;
 }
 
 public function validateIssuer($decodedToken) {
   if ($decodedToken->iss != $this->issuer){
     throw new Exception('The JWT (iss) claim issuer must conform to issuer from config');
   };
 
   return true;
 }
 
 public function validateAudience($decodedToken) {
   if (!in_array($decodedToken->aud, $this->allowedOrigins)){
     throw new Exception('The JWT (aud) claim audience must conform to audience from config');
   };
 
   return true;
 }
 
 public function isValid($decodedToken)
 {
   // exp (Expiration Time)
   return $this->validateExpiration($decodedToken) &&
     // iat (Issued At)
     $this->validateIssuedAt($decodedToken) &&
     // nbf (Not before)
   //   $this->validateNotBefore($decodedToken)&&
     // iss (Issuer)
     $this->validateIssuer($decodedToken) &&
     // aud (Audience)
     $this->validateAudience($decodedToken);
 }
}
```

These are tools that will make it possible to retrieve the token (token of the user session) before retrieving the response to the request, and verify the token thanks to the validations claims.

[Next](https://github.com/cryptr-examples/cryptr-laravel-api-sample/tree/04-protect-api-endpoints)
