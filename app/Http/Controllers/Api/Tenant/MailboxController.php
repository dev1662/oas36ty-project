<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\ForgotOrganization;
use App\Mail\MailBoxSendMail;
use App\Models\CentralUser;
use App\Models\EmailInbound;
use App\Models\EmailOutbound;
use App\Models\EmailsSetting;
use App\Models\Mailbox;
use App\Models\UserEmail;
use Exception;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MailboxController extends Controller
{
   

     /**
     *
     * @OA\post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Box"},
     *     path="/apps/email/emails",
     *     operationId="getMailboxEmails",
     *     summary="Fetch Emails",
     *     description="Fetch Emails",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="search", in="query", required=false, description="Search"),
     * *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *               @OA\Property(
     *                         property="currrent",
     *                         type="object",
     *                         @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                      ),
     *              
     *                      @OA\Property(
     *                         property="page",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="folder",
     *                         type="string",
     *                         example="INBOX"
     *                      ),
     *                      
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fetched all records successfully"),
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
     *                         property="attachments",
     *                         type="string",
     *                         example="0"
     *                      ),
     *                      @OA\Property(
     *                         property="avatar",
     *                         type="string",
     *                         example="avatar.jpeg"
     *                      ),
     *              
     *                       @OA\Property(
     *                         property="created_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *                       @OA\Property(
     *                         property="date",
     *                        type="string",
     *                         example="2022-10-17 10:53:57"
     *                      ),
     *                      @OA\Property(
     *                         property="folder",
     *                         type="string",
     *                         example="INBOX"
     *                      ),
     *                       @OA\Property(
     *                         property="from_email",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                       @OA\Property(
     *                         property="from_name",
     *                         type="string",
     *                         example="Oas36ty <example@gmail.com>"
     *                      ),
     *                       @OA\Property(
     *                         property="isStarred",
     *                         type="integer",
     *                         example="0"
     *                      ),
     *                       @OA\Property(
     *                         property="label",
     *                         type="string",
     *                         example="NULL"
     *                      ),
     *                       @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         example="<html>This is testing message</html>"
     *                      ),
     *                       @OA\Property(
     *                         property="message_id",
     *                         type="string",
     *                         example="2565747e7ab44e8a7a0717003e02074c@gitlab.protracked.in"
     *                      ),
     *                       @OA\Property(
     *                         property="subject",
     *                         type="string",
     *                         example="Oas36ty WebApp | Leads Search (#30)"
     *                      ),
     *                       @OA\Property(
     *                         property="to_email",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                       @OA\Property(
     *                         property="type",
     *                         type="string",
     *                         example="primary"
     *                      ),
     *                       @OA\Property(
     *                         property="u_date",
     *                         type="string",
     *                         example="1665921153"
     *                      ),
     *                       @OA\Property(
     *                         property="updated_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *      
     *                ),
     *          ),
     *      )
     * ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong!")
     *          )
     *     ),
     * 
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
     *          )
     *     ),
     * )
     */

    public function fetchEmails(Request $req)
    {



        
        
        $user_id = $req->currrent['id'];
        $emails = $req->currrent['email'];

        // return $req->page;

        $page = $req->page;
        if ($page > 1) {

            $offset = ($page - 1) * 20;
        } else {
            $offset = 0;
        }

        // return $user_id;
        $check_assigned_emails = UserEmail::where('user_id', $user_id)->whereNotNull('emails_setting_id')->with('EmailsSetting')->get();
        $inbound_array = [];
        // foreach loop to get inbound details
        foreach ($check_assigned_emails as $index => $emails) {
            $email_setting_id = $emails->emails_setting_id;
            $email_inbound = EmailInbound::where('id', $email_setting_id)->first();
            $inbound_array[] = $email_inbound;
        }
        // foreach loop to check inbound username
        $result = [];
        //  return $inbound_array;
        $total_count = [];
        foreach ($inbound_array as $index => $username) {
            // return $username;
            if($username != null){
            // return $username->mail_username;
            // $result[$index]= Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->paginate(20);
            if($req->folder == 'sent'){
                if($req->q){

                    $result[] = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('subject', 'LIKE', '%'.$req->q.'%')->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
                }
                if(!$req->q){
                    $result[] = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();

                }
            }
            if($req->folder == 'draft'){

                $result[] = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Drafts'])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
            }
            if($req->folder == 'spam'){

                $result[] = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Spam'])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
            }
            if($req->folder == 'trash'){
                $result[] = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Trash'])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
                // return $req->result;
            }
            if(!$req->folder){
                if($req->q){

                    $result[] = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'INBOX'])->where('subject', 'LIKE', '%'.$req->q.'%')->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
                }
                if(!$req->q){
                    $result[] = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'INBOX'])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();

                }
            }
            if($req->folder == 'starred'){
                $result[] = Mailbox::where(['to_email' => $username->mail_username, 'isStarred' => 1])->orderBy('u_date', 'desc')->offset($offset)->limit(10)->get();

            }
            // if($req->folder == 'starred'){
            //     $result[$index] = Mailbox::where(['to_email' => $username->mail_username, 'isStarred' => 1])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();

            // }
            // $total_count[$index] =  Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->get();

            $total_count =  ['count' => UserEmail::select('inbound_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
        }
    }
        //  return $total_count;
        //  return $result;
        // $result = Mailbox::all();
        if ($result) {
            $result = $result[0];
           $total_count = $total_count[0] ?? [];
        }
        if ($total_count) {
           
            $count_of_msg = $total_count['count']->inbound_msg_count;
        } else {
            $count_of_msg = 0;
        }

        // return $total_count;
        // $msg = [];

        // return $result;
        if ($page > 1) {
            $count_email = ($page - 1) * 20 + count($result);
        } else {
            $count_email =count($result);
        }
        $meta = [
            'emailsMeta' =>  $count_email,
            'email_count' => $count_of_msg,
        ];
        $this->response['status'] = true;
        $this->response['message'] = 'data fetched';
        $this->response['data'] = $result;
        $this->response['meta'] = $meta;
        return response()->json($this->response);
    }

     /**
     *
     * @OA\post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Box"},
     *     path="/apps/email/update-emails",
     *     operationId="isStarredMailboxEmails",
     *     summary="Update isStarred",
     *     description="Fetch Emails",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="search", in="query", required=false, description="Search"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                         property="dataToUpdate",
     *                         type="object",
     *                         @OA\Property(
     *                         property="isStarred",
     *                         type="boolean",
     *                         example="true"
     *                      ),
     *           
     *                      ),
     *                  @OA\Property(
     *                         property="emailIds",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *            )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Record updated successfully"),
     *           
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
  
    public function updateEmails(Request $req)
    {
        //  return $req->dataToUpdate['isStarred'];
        if($req->dataToUpdate['isStarred'] == true){

            $email_id = $req->emailIds;
         
            Mailbox::find($email_id)->update([
                'isStarred' => 1
            ]);
        }else if($req->dataToUpdate['isStarred'] == false){
            $email_id = $req->emailIds;
         
            Mailbox::find($email_id)->update([
                'isStarred' => 0
            ]);
        }
        
        
    }

   
     /**
     *
     * @OA\post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Box"},
     *     path="/sendEmail-outBound",
     *     operationId="composeEmail",
     *     summary="Compose",
     *     description="Send email",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                       property="data",
     *                         type="object",
     *                       @OA\Property(
     *                         property="bcc",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                       @OA\Property(
     *                         property="cc",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                  @OA\Property(
     *                         property="from",
     *                         type="object",
     *                       @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),   
     *                    ),
     *                   @OA\Property(
     *                         property="to",
     *                         type="array",
     *                          @OA\Items(
     *                         type ="object",
     *                          @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="example1@gmail.com"
     *                      ), 
     *                      ),
     *                    ),
     *                   @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         example="Message Description .........."
     *                      ), 
     *                   @OA\Property(
     *                         property="subject",
     *                         type="string",
     *                         example="subject Notes"
     *                      ),   
     *                ),
     *                   @OA\Property(
     *                   property="currrent",
     *                         type="object",
     *                         @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                      ),
     *            )
     *                 
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Email sent successfully"),
     *           
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
   
    public function sendEmail(Request $request)
    {
        // return $request->all();
        $user = $request->user();
        $bcc=  $request->data['bcc'] ?? '';
        $cc=  $request->data['cc'] ?? '';
        //  $attach = $request->data['attach'] ?? '';
        //  [
        //     public_path('/files/rbn.jpeg'),
        //     public_path('/files/frnt.txt'),
        // ];
        // return $attach;
        $attach = [];
        if($request->data['attach']){

            $base64String = $request->data['attach'];
            // $base64String= "base64 string";
            foreach($base64String as $file){

                // $image = $request->image; // the base64 image you want to upload
                $slug = time().$user->id; //name prefix
                $avatar = $this->getFileName($file, $slug);
                // $original_name = explode(' ', $avatar['name']);
                // return $original_name;
                Storage::disk('s3')->put('email-files/' . $avatar['name'] ,  base64_decode($avatar['file']), 'public');
                
                $url = Storage::disk('s3')->url('email-files/' . $avatar['name']);
                $attach[] = $url ?? '';
            }
        }

        // return $attach;

        $outbound_id= $request->data['from']['id'];
        $centralUser =  CentralUser::where('email', json_decode($request->header('currrent'))->email)->first();

        $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
        tenancy()->initialize($tenant);
      $user_setting  = UserEmail::where(['user_id'=> json_decode($request->header('currrent'))->id, 'emails_setting_id' => $outbound_id])->get();
    //   $details_outbound = [];
    //   foreach ($user_setting as $index => $user_emails) {
        if($user_setting){

            $details_outbound = EmailsSetting::where(['id'=> $outbound_id, 'outBound_status' => 'tick'])->first();
        
        // if($details_outbound){
        //     break;
        // }else{
        //     continue;
        // }
    //   }
      if($details_outbound){

          $mailsetting = EmailOutbound::where(['id'=>$details_outbound->id])->first();
        
        // echo"<pre>";
        // print_r($mailsetting);
        // die;
       // $mailboxsetting = EmailInbound::where(['status'=>'active'])->first();
       
        if($mailsetting){
            $data = [
                'driver'            => $mailsetting->mail_transport,
                'host'              => $mailsetting->mail_host,
                'port'              => $mailsetting->mail_port,
                'encryption'        => $mailsetting->mail_encryption,
                'username'          => $mailsetting->mail_username,
                'password'          => $mailsetting->mail_password,
                'from'              => [
                    // 'address'=>$mailsetting->mail_from,
                    'name'   => 'Oas36ty'
                ]
            ];
            Config::set('mail',$data);
            // return config('mail');
        }
    }
}
        // return Config::get('mail');
        $message =  $request->data['message'];
        $subject = $request->data['subject'];
        // return $subject;
        // $message $

        $status = [];
        foreach($request->data['to'] as $email){
            $data_arr= [
              'message' => $message, 'subject' => $subject, 'email' => $email ?? '', 'email_bcc' => $bcc, 'email_cc' => $cc, 'attach'=> $attach
            ];
            // return $data_arr;
            $status = $this->SendEmailDriven($data_arr);
        //   $status =  mailto:mail::to('devoas36ty@gmail.com')->send(new MailBoxSendMail($message, $subject));
        //      config(['mail.mailers.smtp.username' => 'mailto:robin@gmail.com']);
        //      config(['mail.mailers.smtp.password' => 'mailto:robin@gmail.com']);
        //      config(['mail.mailers.smtp.username' => 'mailto:robin@gmail.com']);

        //     //  return config('mail.mailers.smtp.username');
            
        }
        return $status;
    }



    public function send_email_sms($email_data = [], $sms_data = [])
    {
        ## sending email

        try {
            if (!empty($email_data) && array_key_exists('email', $email_data)) {
                $email = $email_data['email'];
                if ($email) {
                    $files = $email_data['attach'];
                    $data = [];
                    $email_template = array_key_exists('email_template', $email_data)  ? $email_data['email_template'] : '';
                    $data['email'] = $email;
                    $data['template_data'] = array_key_exists('template_data', $email_data)  ? $email_data['template_data'] : '';
                    $data['email_subject'] = array_key_exists('email_subject', $email_data)  ? $email_data['email_subject'] : 'EMail from Oas36ty';
                    $data['email_from'] = array_key_exists('email_from', $email_data) ? $email_data['email_from'] : 'robinoas36ty@gmail.com';

                    $data['email_from_name'] = array_key_exists('email_from_name', $email_data) ? $email_data['email_from_name'] : 'Oas36ty';
                    $data['email_cc'] = array_key_exists('email_cc', $email_data)  ? $email_data['email_cc'] : '';
                    $data['email_bcc'] = array_key_exists('email_bcc', $email_data)  ? $email_data['email_bcc'] : '';
                    $data['email_replyTo'] = array_key_exists('email_replyTo', $email_data)  ? $email_data['email_replyTo'] : '';
                    $data['email_attach'] = array_key_exists('email_attach', $email_data)  ? $email_data['email_attach'] : '';
                    // return $data;

                    //return $data;

                    Mail::send($email_template, $data, function ($message) use ($data, $files ) {
                        $message->from($data['email_from'], $data['email_from_name']);
                        $message->to($data['email']);
                        $message->subject($data['email_subject']);
                        if($data['email_cc']){

                            $message->cc($data['email_cc']);
                        }
                        if($data['email_bcc']){

                            $message->bcc($data['email_bcc']);
                        }
                        if($data['email_replyTo']){

                            $message->replyTo($data['email_replyTo']);
                        }
                        if($files){

                            foreach ($files as $file){
                                $message->attach($file);
                            }
                        }
                        // $message ->replyTo($data['email_replyTo']) ?? '';
                        // $message->attach($data['email_attach']) ?? '';
                    });
                    return true;
                }
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    public function SendEmailDriven($data_arr)
    {

        $email_data = [];
        ## sending email
        $email_data['email_cc'] = $data_arr['email_cc'];
        $email_data['email_bcc'] = $data_arr['email_bcc'];

        $email_data['email'] = $data_arr['email'];
        $email_data['email_subject'] = $data_arr['subject'];
        $email_data['email_template'] = "emails.auth.hello";
        $email_data['template_data'] = ['body' => $data_arr['message']];
        $email_data['attach'] = $data_arr['attach'];
        // return $email_data;
         $check = $this->send_email_sms($email_data, []);
        if ($check) {
            $this->response['status'] = true;
            $this->response['status_code'] = 200;
            $this->response['data']= $check;
            $this->response['message'] = "Email sent successfully" ;
            return response()->json($this->response);
        } else {
            $this->response['status'] = true;
            $this->response['status_code'] = 201;
            $this->response['data']= $check;

            $this->response['message'] = "Something went wrong" ;
            return response()->json($this->response);
        }
    }
}
