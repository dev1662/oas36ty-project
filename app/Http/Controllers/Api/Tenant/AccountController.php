<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tenant;
use App\Models\CentralUser;
use App\Models\User;

use App\Http\Resources\TenantResource;
use App\Http\Resources\OrganizationResource;

class AccountController extends Controller
{
    /**
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"auth"},
     *     path="/logout",
     *     operationId="postLogout",
     *     summary="Logout",
     *     description="Logout",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success Message!"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized Error Message!")
     *          )
     *     ),
     * )
     */

    public function logout(Request $request){ 

        $user = $request->user();

        // Revoke Token
        $user->token()->revoke();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.logged_out');
        return response()->json($this->response);
    }
}
