<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Models\ContactPerson;
use App\Models\ContactPersonEmail;
use App\Models\ContactPersonPhone;
use Illuminate\Support\Facades\Config;
use PDO;

class ContactPersonController extends Controller
{
   
    public function getDataForLeads(Request $request)
    {
   $dbname = $request->header('X-Tenant');
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
        // $result = ContactPerson::select('id','name','type')->get();
        $result = ContactPerson::select('id','name','type')->get();
        // $result = array();

            $this->response["status"] = true;
            $this->response["message"] = __('strings.get_all_success');
            $this->response["data"] = $result;
            return response()->json($this->response);

    }
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"contactPeople"},
     *     path="/contact-people",
     *     operationId="getContactPeople",
     *     summary="Contact People",
     *     description="Contact People",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fetched all data successfully"),
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
     *                         property="name",
     *                         type="string",
     *                         example="Contact Person Name"
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
     *              @OA\Property(property="message", type="string", example="Something went wrong!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
     *          )
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $dbname = $request->header('X-Tenant');
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
        // $result = ContactPerson::select('id','name','type')->get();
        $id = ContactPerson::select('id','name','type')->with('audits')->get();
        // $result = array();
                  foreach($id as $key => $val){

            $email = ContactPersonEmail::where(['contact_person_id' => $val->id])->select('id','email')->get();
            $phone = ContactPersonPhone::where(['contact_person_id' => $val->id])->select('phone')->get();


            $result[$key]=[
                 "data" => $val,
                'email'=>$email ?? [],
                'phone'=>$phone ?? []
            ];


        }
            $this->response["status"] = true;
            $this->response["message"] = __('strings.get_all_success');
            $this->response["data"] = $result;
            return response()->json($this->response);

    }

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"contactPeople"},
     *     path="/contact-people",
     *     operationId="postContactPerson",
     *     summary="Create Contact Person",
     *     description="Create Contact Person",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Contact Person", description=""),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Created successfully"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                      @OA\Property(
     *                  property="name",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected name is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        // $user = $request->user();
        // $request->validation($request,$this->rules);
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64|unique:App\Models\ContactPerson,name',
            // 'email' => 'required|array|max:64',
            // 'phone' => 'required|digits:10|max:64',

        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // $data =array();
        // $phoness = array();
        // for($i=0;$i<count($request->email);$i++){
        //     $data[] = [
        //         "email" => $request->email[$i],
        //     ];
        // }
        // for($i=0;$i<count($request->phone);$i++){
        //     $phoness[] = [
        //         "phone" => $request->phone[$i],
        //     ];
        // }

        // return $d = [$data,$phoness];
        // return $request->all();
        $contactPerson = new ContactPerson();
        $contactPerson->name = $request->name;
        $contactPerson->save();
        // $id = DB::getPdo()->lastInsertId();;
        $id = $contactPerson->id;

        if(count($request->email) > 1){

        for($i=0;$i<count($request->email);$i++){
        $contactPersonEmail = new ContactPersonEmail();

            $contactPersonEmail->contact_person_id = $id;
            $contactPersonEmail->email = $request->email[$i];
            // return $contactPersonEmail
            $contactPersonEmail->save();
            }
        }else{
        $contactPersonEmail = new ContactPersonEmail();

            $contactPersonEmail->contact_person_id = $id;
            $contactPersonEmail->email = $request->email[0];
            $contactPersonEmail->save();
        }
        if(count($request->phone) > 1){
            for($i=0;$i<count($request->phone);$i++){
        $contactPersonPhone = new ContactPersonPhone();

                $contactPersonPhone->contact_person_id = $id;
                $contactPersonPhone->phone = $request->phone[$i];
                $contactPersonPhone->save();
            }
        }else{
        $contactPersonPhone = new ContactPersonPhone();

            $contactPersonPhone->contact_person_id = $id;
                $contactPersonPhone->phone = $request->phone[0];
                $contactPersonPhone->save();
        }
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"contactPeople"},
     *     path="/contact-people/{contactPersonID}",
     *     operationId="getContactPerson",
     *     summary="Show Contact Person",
     *     description="Show Contact Person",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="contactPersonID", in="path", required=true, description="Contact Person ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fethced data successfully!"),
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
     *                         property="name",
     *                         type="string",
     *                         example="Contact Person Name"
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
     *              @OA\Property(property="message", type="string", example="Something went wrong!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
     *          )
     *     ),
     * )
     */
    public function show($contactPersonID)
    {
        $validator = Validator::make(['contact_person_id' => $contactPersonID], [
            'contact_person_id' => 'required|exists:App\Models\ContactPerson,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $contactPerson = ContactPerson::select('id', 'name')->find($contactPersonID);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $contactPerson;
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"contactPeople"},
     *     path="/contact-people/{contactPersonID}",
     *     operationId="putContactPerson",
     *     summary="Update Contact Person",
     *     description="Update Contact Person",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="contactPersonID", in="path", required=true, description="Contact Person ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Contact Person name", description=""),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Updated successfully"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
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
     *              @OA\Property(property="message", type="string", example="Something went wrong!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                      @OA\Property(
     *                  property="contact_person_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected contact_person_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function update(Request $request, $contactPersonID)
    {
        $validator = Validator::make(['contact_person_id' => $contactPersonID] + $request->all(), [
            'name' => 'required|max:64',
            'contact_person_id' => 'required|exists:App\Models\ContactPerson,id',
            // 'contact_email_id' => 'required|exists:App\Models\ContactPersonEmail,id',
            // 'contact_phone_id' => 'required|exists:App\Models\ContactPersonPhone,id',


        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // if(count($request->emails) > )
        $emails = ContactPersonEmail::where('contact_person_id', $request->contact_person_id)->get();
        $count_of_emails = count($emails);
        $phones = ContactPersonPhone::where('contact_person_id', $request->contact_person_id)->get();
        $count_of_phones = count($phones);

        $email_values = [];
        foreach($emails as $newUser){

            $email_values[] =  $newUser->email;
        }

        $phone_values = [];
        foreach($phones as $newUser){

            $phone_values[] =  $newUser->phone;
        }


        if(count($request->emails) === $count_of_emails){
            // update emails if count of records is same but email is different      
            for($i = 0; $i<count($request->emails); $i++){
                // return $key;
                if(!in_array($request->emails[$i], $email_values)){           
                    return $request->emails[$i];
                    $update_emails = ContactPersonEmail::where(['contact_person_id' => $contactPersonID, 'email' => $request->emails[$i]])->update([
                        'email' => $request->emails[$i],
                    ]);
                }
            }
        }
    
        if(count($request->emails) === $count_of_emails){
             // update phones if count of records is same but email is different      
             for($i = 0; $i<count($request->phones); $i++){
                // return $key;
                if(!in_array($request->phones[$i], $phone_values)){           
                    return $request->phones[$i];
                    $update_phones = ContactPersonPhone::where(['contact_person_id' => $contactPersonID, 'phone' => $request->phones[$i]])->update([
                        'phone' => $request->phones[$i],
                    ]);
                }
            }
        }
        // return $phone_values;
        if(count($request->emails) > $count_of_emails){
           
            // add new email records

            // $newRecords = [];
            for($i = 0; $i<count($request->emails); $i++){
                // return $key;
                if(!in_array($request->emails[$i], $email_values)){
                    
                    // return $request->emails[1];

                    // return $request->emails[$i];
                    $add_email = new ContactPersonEmail();
                    $add_email->contact_person_id = $request->contact_person_id;
                    $add_email->email = $request->emails[$i];
                    $add_email->save();

                }
            }
            // return $newRecords;     
        }
        if(count($request->phones) > $count_of_phones){
           
            // add new phones records

            // $newRecords = [];
            for($i = 0; $i<count($request->phones); $i++){
                // return $key;
                if(!in_array($request->phones[$i], $phone_values)){
                    
                    // return $request->emails[1];

                    // return $request->phones[$i];
                    $add_phones = new ContactPersonPhone();
                    $add_phones->contact_person_id = $contactPersonID;
                    $add_phones->phone = $request->phones[$i];
                    $add_phones->save();
                }
            }
            // return $newRecords;
        }

     
        // delete  the email values if there are not present in the request
        if($count_of_emails > count($request->emails)){

            for($i = 0; $i<count($email_values); $i++){
                // return $key;
                if(!in_array($email_values[$i], $request->emails)){           
                    // return $email_values[$i];
                    ContactPersonEmail::where(['contact_person_id' => $contactPersonID, 'email' => $email_values[$i]])->forceDelete();
                }
            }
        }
        if($count_of_phones > count($request->phones)){

            // delete  the phone values if there are not present in the request
            
        for($i = 0; $i<count($phone_values); $i++){
            // return $key;
            if(!in_array($phone_values[$i], $request->phones)){           
                // return $phone_values[$i];
                ContactPersonPhone::where(['contact_person_id' => $contactPersonID, 'phone' => $phone_values[$i]])->forceDelete();

            }
        }
    }

        $contactPerson = ContactPerson::select('id', 'name')->find($contactPersonID);

        if(!$contactPerson){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $contactPerson->fill($request->only(['name']));
        $contactPerson->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"contactPeople"},
     *     path="/contact-people/{contactPersonID}",
     *     operationId="deleteContactPerson",
     *     summary="Delete Contact Person",
     *     description="Delete Contact Person",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="contactPersonID", in="path", required=true, description="Contact Person ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Deleted successfully"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
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
     *              @OA\Property(property="message", type="string", example="Something went wrong!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                      @OA\Property(
     *                  property="contact_person_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected contact_person_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function destroy($contactPersonID)
    {
        $validator = Validator::make(['contact_person_id' => $contactPersonID], [
            'contact_person_id' => 'required|exists:App\Models\ContactPerson,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $contactPerson = ContactPerson::select('id', 'name')->find($contactPersonID);

        if(!$contactPerson){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($contactPerson->forceDelete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
