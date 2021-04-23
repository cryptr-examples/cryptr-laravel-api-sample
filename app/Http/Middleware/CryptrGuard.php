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
