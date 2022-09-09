<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use PDO;
use Illuminate\Support\Facades\Config;
/**
 * @OA\Server(
 *  url="https://api-office36ty.protracked.in/v1",
 *  description="Office36ty"
 * )
 * @OA\Server(
 *  url="http://192.168.1.10:8000/v1",
 *  description="Localhost"
 * )
 *  @OA\Server(
 *  url="http://127.0.0.1:8000/v1",
 *  description="Localhost"
 * )
 * @OA\Info(
 *    title="API Documentation",
 *    version="1.0.0",
 * )
 * 
 * @OA\SecurityScheme(
 *  description="Execute Login or Signup APIs to get the authentication token",
 *  name="Token Based Authentication",
 *  securityScheme="bearerAuth",
 *  type="http",
 *  scheme="bearer"
 * )
 * @OA\Parameter(
 *      parameter="tenant--header",
 *      in="header",
 *      name="X-Tenant",
 *      description="Tenant ID",
 *      @OA\Schema(
 *          type="string",
 *          default="oas36ty",
 *      )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];

    public $response = array("status" => false, "message" => "Something went wrong!");

    protected function makeJson() {
        return response()->json([
                    'error' => $this->error,
                    'status_code' => $this->status_code,
                    'message' => $this->message,
                    'result' => $this->result
        ]);
    }

    public function switchingDB($dbName)
    {
        Config::set("database.connections.mysql", [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME','root'),
            'password' => env('DB_PASSWORD',''),
            'unix_socket' => env('DB_SOCKET',''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
    }
}
