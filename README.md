# Cryptr with Laravel API

## 02 - Add your Cryptr credentials

🛠 Complete the .env file with the variables that you get when creating your application at the end of Cryptr Onboarding or on your Cryptr application. Don't forget to replace `YOUR_CLIENT_ID` & `YOUR_DOMAIN`

```javascript
CRYPTR_BASE_URL=https://auth.cryptr.eu
TENANT_DOMAIN=YOUR_DOMAIN
DEFAULT_REDIRECT_URI=http://localhost:8081
CLIENT_ID=YOUR_CLIENT_ID
```

Note: __If you are from the EU, you must add `https://auth.cryptr.eu/` in the `CRYPTR_BASE_URL` variable, and if you are from the US, you must add `https://auth.cryptr.us/` in the same variable.__

[Next](https://github.com/cryptr-examples/cryptr-laravel-api-sample/tree/03-validate-access-tokens)
