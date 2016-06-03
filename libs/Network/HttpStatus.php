<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/27
 * Time: 12:13
 */

namespace Network;


class HttpStatus
{
    const GO_ON = 100;

    const SUCCESS = 200;

    const MOVED = 301;

    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const FORBIDDEN = 403;
    const UNAUTHORIZED = 401;

    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
}