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

class MailboxController extends Controller
{
    //
    // public function __construct(Request $req)
    // {
    //     $this->user_id = json_decode($req->header('currrent'))->id;
    // }

    public function fetchEmails(Request $req)
    {




        $user_id = json_decode($req['currrent'])->id;
        $emails = json_decode($req['currrent'])->email;

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
            $inbound_array[$index] = $email_inbound;
        }
        // foreach loop to check inbound username
        $result = [];
        // return $inbound_array;
        $total_count = [];
        foreach ($inbound_array as $index => $username) {

            // return $username->mail_username;
            // $result[$index]= Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->paginate(20);
            if($req->folder == 'sent'){

                $result[$index] = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Sent Mail'])->orderBy('u_date', 'desc')->offset($offset)->limit(10)->get();
            }
            if(!$req->folder){

                $result[$index] = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'INBOX'])->orderBy('u_date', 'desc')->offset($offset)->limit(10)->get();
            }
            if($req->folder == 'starred'){
                $result[$index] = Mailbox::where(['to_email' => $username->mail_username, 'isStarred' => 1])->orderBy('u_date', 'desc')->offset($offset)->limit(10)->get();

            }
            // if($req->folder == 'starred'){
            //     $result[$index] = Mailbox::where(['to_email' => $username->mail_username, 'isStarred' => 1])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();

            // }
            // $total_count[$index] =  Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->get();

            $total_count[$index] =  ['count' => UserEmail::select('inbound_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
        }
        // return $total_count;
        // return count($result);
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
            $count_email =  count($result);
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

  
    public function updateEmails(Request $req)
    {
        // return $req->all();
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
    public function sendEmail(Request $request)
    {
        $centralUser =  CentralUser::where('email', json_decode($request->header('currrent'))->email)->first();

        $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
        tenancy()->initialize($tenant);
      $user_setting  = UserEmail::where('user_id', json_decode($request->header('currrent'))->id)->get();
      $details_outbound = [];
      foreach ($user_setting as $index => $user_emails) {
        $details_outbound = EmailsSetting::where(['id'=> $user_emails->emails_setting_id, 'outBound_status' => 'tick'])->first();
        if($details_outbound){
            break;
        }else{
            continue;
        }
      }
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
        // return Config::get('mail');
        $message =  $request->data['message'];
        $subject = $request->data['subject'];
        // return $subject;
        // $message $

        $status = [];
        foreach($request->data['to'] as $email){
            $data_arr= [
              'message' => $message, 'subject' => $subject, 'email' => $email['email']
            ];
            $status = $this->SendEmailDriven($data_arr);
        //   $status =  Mail::to('devoas36ty@gmail.com')->send(new MailBoxSendMail($message, $subject));
        //      config(['mail.mailers.smtp.username' => 'robin@gmail.com']);
        //      config(['mail.mailers.smtp.password' => 'robin@gmail.com']);
        //      config(['mail.mailers.smtp.username' => 'robin@gmail.com']);

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
                    $data = [];
                    $email_template = array_key_exists('email_template', $email_data)  ? $email_data['email_template'] : '';
                    $data['email'] = $email;
                    $data['template_data'] = array_key_exists('template_data', $email_data)  ? $email_data['template_data'] : '';
                    $data['email_subject'] = array_key_exists('email_subject', $email_data)  ? $email_data['email_subject'] : 'EMail from Oas36ty';
                    $data['email_from'] = array_key_exists('email_from', $email_data) ? $email_data['email_from'] : 'robinoas36ty@gmail.com';

                    $data['email_from_name'] = array_key_exists('email_from_name', $email_data) ? $email_data['email_from_name'] : 'Oas36ty';


                    //return $data;
                    Mail::send($email_template, $data, function ($message) use ($data) {
                        $message->from($data['email_from'], $data['email_from_name']);
                        $message->to($data['email']);
                        $message->subject($data['email_subject']);
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
        $email_data['email'] = $data_arr['email'];
        $email_data['email_subject'] = $data_arr['subject'];
        $email_data['email_template'] = "emails.auth.hello";
        $email_data['template_data'] = ['body' => $data_arr['message']];
         $check = $this->send_email_sms($email_data, []);
        if ($check) {
            $this->response['status'] = true;
            $this->response['status_code'] = 200;
            $this->response['message'] = "Email sent successfully" ;
            return response()->json($this->response);
        } else {
            $this->response['status'] = true;
            $this->response['status_code'] = 201;
            $this->response['message'] = "Something went wrong" ;
            return response()->json($this->response);
        }
    }
}
