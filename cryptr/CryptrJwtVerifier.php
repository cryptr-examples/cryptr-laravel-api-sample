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
