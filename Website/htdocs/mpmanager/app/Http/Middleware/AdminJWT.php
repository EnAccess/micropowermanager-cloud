<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Claims\Collection;

class AdminJWT
{
    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response
         */
        return  $next($request);
    }

}
