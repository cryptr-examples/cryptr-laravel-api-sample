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
