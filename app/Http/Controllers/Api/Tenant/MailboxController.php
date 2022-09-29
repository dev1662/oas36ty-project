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
      


     
            $user_id = json_decode($req['currrent'])->id;
            $emails = json_decode($req['currrent'])->email;
            
            // return $req->page;
        
        $page = $req->page;
        if($page > 1){

            $offset = ($page - 1) * 20;
        }else{
            $offset = 0;
        }
        
        // return $user_id;
        $check_assigned_emails = UserEmail::where('user_id', $user_id)->whereNotNull('emails_setting_id')->with('EmailsSetting')->get();
        $inbound_array = [];
        // foreach loop to get inbound details
        foreach($check_assigned_emails as $index => $emails){
            $email_setting_id = $emails->emails_setting_id;
            $email_inbound = EmailInbound::where('id', $email_setting_id)->first();
            $inbound_array[$index] = $email_inbound;
            

        }
        // foreach loop to check inbound username
        $result = [];
        // return $inbound_array;
        $total_count = [];
        foreach($inbound_array as $index => $username)
        {
            
            // return $username->mail_username;
            // $result[$index]= Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->paginate(20);
            $result[$index] = Mailbox::where('to_email', $username->mail_username)->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
            // $total_count[$index] =  Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->get();
            
            $total_count[$index] =  ['count'=>UserEmail::select('inbound_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
        }
        // return $total_count;
        // return count($result);
        // $result = Mailbox::all();
        if($result){
            $result = $result[0];
            $total_count = $total_count[0] ?? [];

        }
        if($total_count){

            $count_of_msg= $total_count['count']->inbound_msg_count;
        }else{
            $count_of_msg = 0;
        }
    
        // return $total_count;
        // $msg = [];
      
        // return $result;
        if($page > 1){
            $count_email = ($page - 1) * 20 + count($result);
        }else{
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
    public function updateEmails()
    {
        return;
    }
}
