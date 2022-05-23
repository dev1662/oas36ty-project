<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Models\ClientContactPersonPhone;

class ClientContactPersonPhoneController extends Controller
{
    /**
     * 
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonPhones"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/phones",
     *     operationId="getClientContactPersonPhones",
     *     summary="Client Contact Person Phones",
     *     description="Client Contact Person Phones",
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
     *                         property="phone",
     *                         type="string",
     *                         example="Client Contact Person Phone"
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

        $result = $clientContactPerson->phones()->select('id', 'phone')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $result;
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonPhones"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/phones",
     *     operationId="postClientContactPersonPhone",
     *     summary="Create Client Contact Person Phone",
     *     description="Create Client Contact Person Phone",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="phone", type="string", example="Client Contact Person Phone", description=""),
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
     *                  property="phone", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected phone is invalid."
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
            'phone' => 'required|digits:10|max:20',
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

        $clientContactPersonPhone = new ClientContactPersonPhone($request->all());
        $clientContactPersonPhone->status = ClientContactPersonPhone::STATUS_ACTIVE;
        $clientContactPerson->phones()->save($clientContactPersonPhone);
        
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonPhones"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/phones/{clientContactPersonPhoneID}",
     *     operationId="getClientContactPersonPhone",
     *     summary="Show Client Contact Person Phone",
     *     description="Show Client Contact Person Phone",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Parameter(name="clientContactPersonPhoneID", in="path", required=true, description="Client Contact Person Phone ID"),
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
     *                         property="phone",
     *                         type="string",
     *                         example="Client Contact Person Phone"
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
    public function show($clientID, $clientContactPersonID, $clientContactPersonPhoneID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID, 'client_contact_person_phone_id' => $clientContactPersonPhoneID], [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'client_contact_person_phone_id' => 'required|exists:App\Models\ClientContactPersonPhone,id',
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

        $clientContactPersonPhone = $clientContactPerson->phones()->select('id', 'phone')->find($clientContactPersonPhoneID);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $clientContactPersonPhone;
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonPhones"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/phones/{clientContactPersonPhoneID}",
     *     operationId="putClientContactPersonPhone",
     *     summary="Update Client Contact Person Phone",
     *     description="Update Client Contact Person Phone",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Parameter(name="clientContactPersonPhoneID", in="path", required=true, description="Client Contact Person Phone ID"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="phone", type="string", example="Client Contact Person Phone", description=""),
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
    public function update(Request $request, $clientID, $clientContactPersonID, $clientContactPersonPhoneID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID, 'client_contact_person_phone_id' => $clientContactPersonPhoneID] + $request->all(), [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'client_contact_person_phone_id' => 'required|exists:App\Models\ClientContactPersonPhone,id',
            'phone' => 'required|digits:10|max:20',
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

        $clientContactPersonPhone = $clientContactPerson->phones()->select('id', 'phone')->find($clientContactPersonPhoneID);

        if(!$clientContactPersonPhone){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $clientContactPersonPhone->fill($request->only(['phone']));
        $clientContactPersonPhone->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"clientContactPersonPhones"},
     *     path="/clients/{clientID}/contact-people/{clientContactPersonID}/phones/{clientContactPersonPhoneID}",
     *     operationId="deleteClientContactPersonPhone",
     *     summary="Delete Client Contact Person Phone",
     *     description="Delete Client Contact Person Phone",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="clientID", in="path", required=true, description="Client ID"),
     *     @OA\Parameter(name="clientContactPersonID", in="path", required=true, description="Client Contact Person ID"),
     *     @OA\Parameter(name="clientContactPersonPhoneID", in="path", required=true, description="Client Contact Person Phone ID"),
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
    public function destroy($clientID, $clientContactPersonID, $clientContactPersonPhoneID)
    {
        $validator = Validator::make(['client_id' => $clientID, 'client_contact_person_id' => $clientContactPersonID, 'client_contact_person_phone_id' => $clientContactPersonPhoneID], [
            'client_id' => 'required|exists:App\Models\Client,id',
            'client_contact_person_id' => 'required|exists:App\Models\ClientContactPerson,id',
            'client_contact_person_phone_id' => 'required|exists:App\Models\ClientContactPersonPhone,id',
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

        $clientContactPersonPhone = $clientContactPerson->phones()->select('id', 'phone')->find($clientContactPersonPhoneID);

        if(!$clientContactPersonPhone){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }
        
        if ($clientContactPersonPhone->delete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
