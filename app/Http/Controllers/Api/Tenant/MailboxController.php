<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\EmailInbound;
use App\Models\EmailsSetting;
use App\Models\Mailbox;
use App\Models\UserEmail;
use Illuminate\Http\Request;

class MailboxController extends Controller
{
    //
    // public function __construct(Request $req)
    // {
    //     $this->user_id = json_decode($req->header('currrent'))->id;
    // }
    
    public function fetchEmails(Request $req)
    {
        // $emails = json_decode($req->header('currrent'))->email;
        // $user_id = json_decode($_GET['currrent'])->id;
        // // return $user_id;
        // $check_assigned_emails = UserEmail::where('user_id', $user_id)->whereNotNull('emails_setting_id')->with('EmailsSetting')->get();
        // $inbound_array = [];
        // // foreach loop to get inbound details
        // foreach($check_assigned_emails as $index => $emails){
        //     $email_setting_id = $emails->emails_setting_id;
        //     $email_inbound = EmailInbound::where('id', $email_setting_id)->first();
        //     $inbound_array[$index] = $email_inbound;
           

        // }
        // // foreach loop to check inbound username
        // $result = [];
        // // return $inbound_array;
        // foreach($inbound_array as $index => $username)
        // {
        //     // return $username->mail_username;
        //     $result[$index]= Mailbox::where('to_email', $username->mail_username)->get();
        // }
        // return count($result);
        // $check_user_emails_exists = 
            $result = Mailbox::all();
        // if($result){
        //     $result = $result[0];
        // }
        $meta = [
            'emailsMeta' => count($result)
        ];
            $this->response['status'] = true;
            $this->response['message'] = 'data fetched';
            $this->response['data'] = $result;
            $this->response['meta'] = $meta;
            return response()->json($this->response);
        
        }
    public function updateEmails()
    {
        return;
    }
}
