<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Acepta el/los proxies (Render) para leer X-Forwarded-Proto=https
     * Usa '*' si estás detrás de un proxy que no controlas.
     */
    protected $proxies = '*';

    /**
     * Headers a confiar (todos los X-Forwarded-*)
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
