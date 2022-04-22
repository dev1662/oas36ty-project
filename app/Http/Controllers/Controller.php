<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Server(
 *  url="http://localhost:8000/api",
 *  description="Localhost"
 * )
 * @OA\Info(
 *    title="API Documentation",
 *    version="1.0.0",
 * )
 * @OA\SecurityScheme(
 *  description="Execute Login or Signup APIs to get the authentication token",
 *  name="Token Based Authentication",
 *  securityScheme="bearerAuth",
 *  type="http",
 *  scheme="bearer"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $response = array("status" => false, "message" => "Something went wrong!");
}
