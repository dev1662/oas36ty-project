<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Models\ClientContactPersonEmail;

class ContactPersonEmailController extends Controller
{
    /**
     * 
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonEmails"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/emails",
     *     operationId="getClientContactPersonEmails",
     *     summary="Client Contact Person Emails",
     *     description="Client Contact Person Emails",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success Message!"),
     *              @OA\Property(
     *                  property="data", 
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="Client Contact Person Email"
     *                      ),
     *                  ),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error Message!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized!")
     *          )
     *     ),
     * )
     */
    public function index($clientID, $clientContactPersonID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID], [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $client = Client::select('id', 'name')->find($clientID);
        $clientContactPerson = $client->contactPeople()->select('id', 'name')->find($clientContactPersonID);

        if(!$clientContactPerson){
            $this->response["message"] = __('strings.get_all_failed');
            return response()->json($this->response);
        }

        $result = $clientContactPerson->emails()->select('id', 'email')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $result;
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonEmails"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/emails",
     *     operationId="postClientContactPersonEmail",
     *     summary="Create Client Contact Person Email",
     *     description="Create Client Contact Person Email",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="Client Contact Person Email", description=""),
     *         )
     *     ),
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
     *              @OA\Property(property="message", type="string", example="Unauthorized!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error Message!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors", 
     *                  type="object",
     *                      @OA\Property(
     *                  property="email", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected email is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function store(Request $request, $clientID, $clientContactPersonID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID] + $request->all(), [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'email' => 'required|email|max:64',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $client = Client::select('id', 'name')->find($clientID);
        $clientContactPerson = $client->contactPeople()->select('id', 'name')->find($clientContactPersonID);

        if(!$clientContactPerson){
            $this->response["message"] = __('strings.store_failed');
            return response()->json($this->response);
        }

        $clientContactPersonEmail = new ClientContactPersonEmail($request->all());
        $clientContactPersonEmail->status = ClientContactPersonEmail::STATUS_ACTIVE;
        $clientContactPerson->emails()->save($clientContactPersonEmail);
        
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonEmails"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/emails/{clientContactPersonEmailID}",
     *     operationId="getClientContactPersonEmail",
     *     summary="Show Client Contact Person Email",
     *     description="Show Client Contact Person Email",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Parameter(name="clientContactPersonEmailID", in="path", required=true, description="Client Contact Person Email ID"),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success Message!"),
     *              @OA\Property(
     *                  property="data", 
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="Client Contact Person Email"
     *                      ),
     *                  ),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error Message!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized!")
     *          )
     *     ),
     * )
     */
    public function show($clientID, $clientContactPersonID, $clientContactPersonEmailID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID, 'client_contact_person_email_id' => $clientContactPersonEmailID], [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'client_contact_person_email_id' => 'required|exists:App\Models\ClientContactPersonEmail,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $client = Client::select('id', 'name')->find($clientID);
        $clientContactPerson = $client->contactPeople()->select('id', 'name')->find($clientContactPersonID);

        if(!$clientContactPerson){
            $this->response["message"] = __('strings.get_one_failed');
            return response()->json($this->response);
        }

        $clientContactPersonEmail = $clientContactPerson->emails()->select('id', 'email')->find($clientContactPersonEmailID);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $clientContactPersonEmail;
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonEmails"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/emails/{clientContactPersonEmailID}",
     *     operationId="putClientContactPersonEmail",
     *     summary="Update Client Contact Person Email",
     *     description="Update Client Contact Person Email",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Parameter(name="clientContactPersonEmailID", in="path", required=true, description="Client Contact Person Email ID"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="Client Contact Person Email", description=""),
     *         )
     *     ),
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
     *              @OA\Property(property="message", type="string", example="Unauthorized!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error Message!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors", 
     *                  type="object",
     *                      @OA\Property(
     *                  property="client_id", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected client_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function update(Request $request, $clientID, $clientContactPersonID, $clientContactPersonEmailID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID, 'client_contact_person_email_id' => $clientContactPersonEmailID] + $request->all(), [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'client_contact_person_email_id' => 'required|exists:App\Models\ClientContactPersonEmail,id',
            'email' => 'required|email|max:64',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $client = Client::select('id', 'name')->find($clientID);
        $clientContactPerson = $client->contactPeople()->select('id', 'name')->find($clientContactPersonID);

        if(!$clientContactPerson){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response);
        }

        $clientContactPersonEmail = $clientContactPerson->emails()->select('id', 'email')->find($clientContactPersonEmailID);

        if(!$clientContactPersonEmail){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $clientContactPersonEmail->fill($request->only(['email']));
        $clientContactPersonEmail->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonEmails"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/emails/{clientContactPersonEmailID}",
     *     operationId="deleteClientContactPersonEmail",
     *     summary="Delete Client Contact Person Email",
     *     description="Delete Client Contact Person Email",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Parameter(name="clientContactPersonEmailID", in="path", required=true, description="Client Contact Person Email ID"),
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
     *              @OA\Property(property="message", type="string", example="Unauthorized!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error Message!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors", 
     *                  type="object",
     *                      @OA\Property(
     *                  property="client_id", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected client_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function destroy($clientID, $clientContactPersonID, $clientContactPersonEmailID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID, 'client_contact_person_email_id' => $clientContactPersonEmailID], [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'client_contact_person_email_id' => 'required|exists:App\Models\ClientContactPersonEmail,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $client = Client::select('id', 'name')->find($clientID);
        $clientContactPerson = $client->contactPeople()->select('id', 'name')->find($clientContactPersonID);

        if(!$clientContactPerson){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response);
        }

        $clientContactPersonEmail = $clientContactPerson->emails()->select('id', 'email')->find($clientContactPersonEmailID);

        if(!$clientContactPersonEmail){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }
        
        if ($clientContactPersonEmail->delete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
