<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Http\V_1_0_0\Authentications\Models\Authentications;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use App\Helpers\ResponseBuilder;

class JwtMiddleware
{
    public $validationErrStatus = 400;
    public $validationTokenErrStatus = 401;

    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->get('token');
        if(!$token) {
            // Unauthorized response if token not there
            return ResponseBuilder::responseResult($this->validationTokenErrStatus, 'Token not provided.');
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return ResponseBuilder::responseResult($this->validationErrStatus, 'Provided token is expired.');
        } catch(Exception $e) {
            return ResponseBuilder::responseResult($this->validationErrStatus, 'An error while decoding token.');
        }
        $user = Authentications::find($credentials->sub);
        if (!empty($user)) {
            if ($user->role <> 2) {
                return ResponseBuilder::responseResult($this->validationErrStatus, 'Invalid Token');
            }
            // Now let's put the user in the request class so that you can grab it from there
            $request->auth = $user;
            return $next($request);
        }
        return ResponseBuilder::responseResult($this->validationErrStatus, 'Invalid Token');
    }
}