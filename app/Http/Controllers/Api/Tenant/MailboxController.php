<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Events\MailboxEmailsFetched;
use App\Http\Controllers\Controller;
use App\Mail\ForgotOrganization;
use App\Mail\MailBoxSendMail;
use App\Models\CentralUser;
use App\Models\EmailInbound;
use App\Models\EmailOutbound;
use App\Models\EmailsSetting;
use App\Models\Mailbox;
use App\Models\MailboxAttachment;
use App\Models\UserEmail;
use App\Models\UserMailbox;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\Return_;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Support\Masks\MessageMask;

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

    $page = $req->page;
    if ($page > 1) {

      $offset = ($page - 1) * 20;
    } else {
      $offset = 0;
    }

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
    $total_count = [];
    $stared_emails = [];
    foreach ($inbound_array as $index => $username) {
      if ($username != null) {
        // $result[$index]= Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->paginate(20);
        if ($req->folder == 'sent') {
          if ($req->q) {
            $results= Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('to_email', 'LIKE', '%' . $req->q . '%')->orderBy('u_date', 'desc')->offset($offset)->limit(20)
            // ->with([
            //   'attachments_file',
            //   'userMailbox' => function ($q) use ($user_id) {
            //     $q->where(['user_id' => $user_id])->get();
            //   },
            //   'taskStatus',

            // ])
            ->get();
            // return $results;
            foreach ($results as $key => $res) {
              $eamils_arr = [];
              // if(!empty($res['in_reply_to'])){
              $eamils_arr = Mailbox::whereIn('folder', ['INBOX', 'Sent Mail'])
                ->where('message_id', '!=', $res['message_id'])
                ->where(function ($query) use ($res) {
                  $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                  if (!empty($res['in_reply_to'])) {
                    $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%')
                      ->orWhere('message_id', 'LIKE', '%' . $res['in_reply_to'] . '%');
                  }
                })
                ->where(function ($query) use ($username) {
                  $query->where(['to_email' => $username->mail_username])
                    ->orWhere(['from_email' => $username->mail_username]);
                })->with('attachments_file')
                ->orderBy('u_date')->get();
              // }    
              if (count($eamils_arr) > 0) {
                // if($res['ccaddress']){

                // }
                // if($eamils_arr['ccaddress']){

                // }
                $result[] = ['parent' => $res, 'childs' => $eamils_arr];
              } else {
                $result[] = ['parent' => $res];
              }
            }
            // $results = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('is_parent', 1)->where('subject', 'LIKE', '%' . $req->q . '%')->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
          }
          if (!$req->q) {

            $spam_trash_id = UserMailbox::select('mailbox_id','message_id')->where( function($query){ 
              $query->where(['is_spam'=>1])->orWhere(['is_trash'=>1]);
             })->where('user_id',$user_id)
               ->get();
               $spam_trash_ids = [];
               $spamTrash_messageId = [];
               foreach($spam_trash_id as $row){
                 $spam_trash_ids[] = $row->mailbox_id;
                  $spamTrash_messageId[] = $row->message_id ?? '';
               }
            $results = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('is_parent', 1)
            ->whereNotIn('id',$spam_trash_ids)
            ->whereNotIn('message_id',$spamTrash_messageId)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with(['attachments_file','taskStatus'])->get();
            $stared_emails = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('is_parent', 1)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with('attachments_file')->get();
          }
          foreach ($results as $key => $res) {

            // $eamils_arr = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('references','LIKE','%'.$res['message_id'].'%')->get();
            // $eamils_arr = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])
            // ->where('message_id','!=',$res['message_id'])
            // ->where(function($query) use ($res){
            //     $query->orWhere('references', 'LIKE', '%'.$res['message_id'].'%');
            //     if(!empty($res['in_reply_to'])){
            //         $query->orWhere('in_reply_to', 'LIKE', '%'.$res['in_reply_to'].'%');
            //     }
            //        })->get();
            $eamils_arr = Mailbox::whereIn('folder', ['INBOX', 'Sent Mail'])
              ->where('message_id', '!=', $res['message_id'])
              ->where(function ($query) use ($res) {
                $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                if (!empty($res['in_reply_to'])) {
                  $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%')
                    ->orWhere('message_id', 'LIKE', '%' . $res['in_reply_to'] . '%');
                }
              })
              ->where(function ($query) use ($username) {
                $query->where(['from_email' => $username->mail_username])
                  ->orWhere(['to_email' => $username->mail_username]);
              })->with('attachments_file')
              ->orderBy('u_date')->get();

            if (count($eamils_arr) > 0) {
              $result[] = ['parent' => $res, 'childs' => $eamils_arr];
            } else {
              $result[] = ['parent' => $res];
            }
          }
          $total_count =  ['count' => UserEmail::select('sent_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
          $total_count = $total_count['count']->sent_msg_count;
        }
        if ($req->folder == 'draft') {

          $results = Mailbox::where(['from_email' => $username->mail_username])->where('folder', '=', 'Drafts')->where('is_parent', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
          $stared_emails = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Drafts'])->where('is_parent', 1)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with('attachments_file')->get();

          foreach ($results as $key => $res) {

            // $eamils_arr = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Drafts'])->where('references','LIKE','%'.$res['message_id'].'%')->get();
            $eamils_arr = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Drafts'])
              ->where('message_id', '!=', $res['message_id'])
              ->where(function ($query) use ($res) {
                $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                if (!empty($res['in_reply_to'])) {
                  $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%');
                }
              })->get();
            if (count($eamils_arr) > 0) {

              $result[] = ['parent' => $res, 'childs' => $eamils_arr];
            } else {
              $result[] = ['parent' => $res];
            }
          }
          $total_count =  ['count' => UserEmail::select('draft_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
          $total_count = $total_count['count']->draft_msg_count;
        }
        if ($req->folder == 'spam') {

          $spam_trash_id = UserMailbox::select('mailbox_id')->where( function($query){ 
            $query->where(['is_spam'=>1]);
           })->where('user_id',$user_id)
             ->get();
             $spam_trash_ids = [];
             foreach($spam_trash_id as $row){
               $spam_trash_ids[] = $row->mailbox_id;
             }

          $results = Mailbox::
          // where(['to_email' => $username->mail_username])->
          whereIn('id',$spam_trash_ids)->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
          $stared_emails = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Spam'])->where('is_parent', 1)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with('attachments_file')->get();

          foreach ($results as $key => $res) {

            // $eamils_arr = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Spam'])->where('references','LIKE','%'.$res['message_id'].'%')->get();
            $eamils_arr = [];
            // if(!empty($res['in_reply_to'])){
            $eamils_arr = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Spam'])
              ->where('message_id', '!=', $res['message_id'])
              ->where(function ($query) use ($res) {
                $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                if (!empty($res['in_reply_to'])) {
                  $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%');
                }
              })->get();
            // }    
            if (count($eamils_arr) > 0) {
              $result[] = ['parent' => $res, 'childs' => $eamils_arr];
            } else {
              $result[] = ['parent' => $res];
            }

            // if(count($eamils_arr)>0){
            //     $result[] = ['parent'=>$res,'childs'=>$eamils_arr];
            // }else{
            //     $result[]= ['parent'=>$res];
            // }
          }
          $total_count =  ['count' => UserEmail::select('spam_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
          $total_count = $total_count['count']->spam_msg_count;
        }
        if ($req->folder == 'trash') {
          $spam_trash_id = UserMailbox::select('mailbox_id')->where( function($query){ 
            $query->where(['is_trash'=>1]);
           })->where('user_id',$user_id)
             ->get();
             $spam_trash_ids = [];
             foreach($spam_trash_id as $row){
               $spam_trash_ids[] = $row->mailbox_id;
             }

             
             $results = Mailbox::
            //  where(['to_email' => $username->mail_username])->
             whereIn('id',$spam_trash_ids)->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();
          $stared_emails = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Trash'])->where('is_parent', 1)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with('attachments_file')->get();

          foreach ($results as $key => $res) {

            // $eamils_arr = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'Trash'])->where('references','LIKE','%'.$res['message_id'].'%')->get();
            $eamils_arr = [];
            // if(!empty($res['in_reply_to'])){
            $eamils_arr = Mailbox::
            where(['to_email' => $username->mail_username, 'folder' => 'Trash'])
              ->where('message_id', '!=', $res['message_id'])
              ->where(function ($query) use ($res) {
                $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                if (!empty($res['in_reply_to'])) {
                  $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%');
                }
              })->get();

            if (count($eamils_arr) > 0) {
              $result[] = ['parent' => $res, 'childs' => $eamils_arr];
            } else {
              $result[] = ['parent' => $res];
            }
          }
          $total_count =  ['count' => UserEmail::select('trash_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
          $total_count = $total_count['count']->trash_msg_count;
        }
        if (!$req->folder) {
          if ($req->q) {

            $results= Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'INBOX'])->where('from_email', 'LIKE', '%' . $req->q . '%')->orderBy('u_date', 'desc')->offset($offset)->limit(20)
            ->with([
              'attachments_file',
              'userMailbox' => function ($q) use ($user_id) {
                $q->where(['user_id' => $user_id])->get();
              },
              'taskStatus',

            ])
            ->get();
            // return $results;
            foreach ($results as $key => $res) {
              $eamils_arr = [];
              // if(!empty($res['in_reply_to'])){
              $eamils_arr = Mailbox::whereIn('folder', ['INBOX', 'Sent Mail'])
                ->where('message_id', '!=', $res['message_id'])
                ->where(function ($query) use ($res) {
                  $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                  if (!empty($res['in_reply_to'])) {
                    $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%')
                      ->orWhere('message_id', 'LIKE', '%' . $res['in_reply_to'] . '%');
                  }
                })
                ->where(function ($query) use ($username) {
                  $query->where(['to_email' => $username->mail_username])
                    ->orWhere(['from_email' => $username->mail_username]);
                })->with('attachments_file')
                ->orderBy('u_date')->get();
              // }    
              if (count($eamils_arr) > 0) {
                // if($res['ccaddress']){

                // }
                // if($eamils_arr['ccaddress']){

                // }
                $result[] = ['parent' => $res, 'childs' => $eamils_arr];
              } else {
                $result[] = ['parent' => $res];
              }
            }
          }
          if (!$req->q) {
            $spam_trash_id = UserMailbox::select('mailbox_id','message_id')->where( function($query){ 
             $query->where(['is_spam'=>1])->orWhere(['is_trash'=>1]);
            })->where('user_id',$user_id)
              ->get();
              $spam_trash_ids = [];
              $spamTrash_messageId = [];
              foreach($spam_trash_id as $row){
                $spam_trash_ids[] = $row->mailbox_id;
                $spamTrash_messageId[] = $row->message_id ?? '';
              }

            $results = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'INBOX'])->orderBy('u_date', 'desc')->where('is_parent', 1)
            ->whereNotIn('id',$spam_trash_ids)
            ->whereNotIn('message_id',$spamTrash_messageId)->offset($offset)->limit(50)->with([
              'attachments_file',
              'userMailbox' => function ($q) use ($user_id) {
                $q->where(['user_id' => $user_id])->get();
              },
              'taskStatus',

            ])->get();
            

            $stared_emails = Mailbox::where(['to_email' => $username->mail_username, 'folder' => 'INBOX'])->where('is_parent', 1)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->get();

            foreach ($results as $key => $res) {
              $eamils_arr = [];
              // if(!empty($res['in_reply_to'])){
              $eamils_arr = Mailbox::whereIn('folder', ['INBOX', 'Sent Mail'])
                ->where('message_id', '!=', $res['message_id'])
                ->where(function ($query) use ($res) {
                  $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                  if (!empty($res['in_reply_to'])) {
                    $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%')
                      ->orWhere('message_id', 'LIKE', '%' . $res['in_reply_to'] . '%');
                  }
                })
                ->where(function ($query) use ($username) {
                  $query->where(['to_email' => $username->mail_username])
                    ->orWhere(['from_email' => $username->mail_username]);
                })->with('attachments_file')
                ->orderBy('u_date')->get();
              // }    
              if (count($eamils_arr) > 0) {
                // if($res['ccaddress']){

                // }
                // if($eamils_arr['ccaddress']){

                // }
                $result[] = ['parent' => $res, 'childs' => $eamils_arr];
              } else {
                $result[] = ['parent' => $res];
              }
            }
          }
          $total_count =  ['count' => UserEmail::select('inbound_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
          $total_count = $total_count['count']->inbound_msg_count;
        }
        if ($req->folder == 'starred') {
          $stared_emails = Mailbox::where(['to_email' => $username->mail_username])->where('is_parent', 1)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->get();

          $results = Mailbox::where(['isStarred' => 1])->where('is_parent', 1)
            ->where(function ($query) use ($username) {
              $query->where(['to_email' => $username->mail_username])
                ->orWhere(['from_email' => $username->mail_username]);
            })
            ->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with([
              'attachments_file',
              'userMailbox' => function ($q) use ($user_id) {
                $q->where(['user_id' => $user_id])->get();
              },
              'taskStatus',
            ])->get();

          $starred_count = Mailbox::where(['isStarred' => 1])->where('is_parent', 1)
            ->where(function ($query) use ($username) {
              $query->where(['to_email' => $username->mail_username])
                ->orWhere(['from_email' => $username->mail_username]);
            })
            ->count();
          foreach ($results as $key => $res) {

            // $eamils_arr = Mailbox::where('references','LIKE','%'.$res['message_id'].'%')->where(['to_email' => $username->mail_username, 'isStarred' => 1])->get();
            $eamils_arr = Mailbox::where(['to_email' => $username->mail_username])
              ->where('message_id', '!=', $res['message_id'])
              ->where(function ($query) use ($res) {
                $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
                if (!empty($res['in_reply_to'])) {
                  $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%');
                }
              })->with('attachments_file')->get();
            if (count($eamils_arr) > 0) {
              $result[] = ['parent' => $res, 'childs' => $eamils_arr];
            } else {
              $result[] = ['parent' => $res];
            }
          }
          $total_count = $starred_count;
        }

        // if($req->folder == 'starred'){
        //     $result[$index] = Mailbox::where(['to_email' => $username->mail_username, 'isStarred' => 1])->orderBy('u_date', 'desc')->offset($offset)->limit(20)->get();

        // }
        // $total_count[$index] =  Mailbox::where('to_email', $username->mail_username)->orderBy('id', 'DESC')->get();

        // $total_count =  ['count' => UserEmail::select('inbound_msg_count')->where(['user_id' => $user_id, 'emails_setting_id' => $username->id])->first() ?? 0];
      }
    }
    // $result = Mailbox::all();
    if ($result) {
      $result = $result;
      $total_count = $total_count ?? [];
    }
    if ($total_count) {

      $count_of_msg = $total_count;
    } else {
      $count_of_msg = 0;
    }

    // $msg = [];

    if ($page > 1) {
      $count_email = ($page - 1) * 20 + count($result);
    } else {
      $count_email = 0; //count($result);
    }

    // $unstared_emails = Mailbox::where(['from_email' => $username->mail_username, 'folder' => 'Sent Mail'])->where('is_parent',0)->where('isStarred', 1)->orderBy('u_date', 'desc')->offset($offset)->limit(50)->with('attachments_file')->get();
    // $user_mailbox =  DB::table('user_mailboxes')->join('mailbox', 'id','=','mailbox_id')->get(); //UserMailbox::with(['mailbox'])->get();
    $meta = [
      'emailsMeta' =>  $count_email,
      'email_count' => $count_of_msg,
      'msg_stared' => count($stared_emails) > 0 ? 'all stared' : 'not all stared',
      // 'user_mailbox' => $user_mailbox
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
    $user = $req->user();
    if ((!(is_array($req->emailIds)) && $req->folder == 'inbox') || $req->folder == 'starred') {

      if($req->dataToUpdate){
      if ($req->dataToUpdate['isRead'] && $req->dataToUpdate['mailbox_id']) {
        $check = UserMailbox::where(['mailbox_id' => $req->dataToUpdate['mailbox_id'], 'user_id' => $req->dataToUpdate['user_id']])->first();
        if ($check) {
          if ($req->dataToUpdate['isRead'] == false) {
            UserMailbox::where('id', $check->id)->update(['is_read' => false]);
          } else if ($req->dataToUpdate['isRead'] == true) {
            UserMailbox::where('id', $check->id)->update(['is_read' => true]);
          }
        } else {
          $data_arr = [
            'user_id' => $req->dataToUpdate['user_id'],
            'mailbox_id' => $req->dataToUpdate['mailbox_id'],
            'is_read' => true
          ];
          UserMailbox::create($data_arr);
        }
      }
    }

    
  }
    if (is_array($req->emailIds)) {

      if($req->folder == 'inbox'){
        
        foreach($req->emailIds as $row){
          if($req->dataToUpdate){
           
            if ($req->dataToUpdate['isRead'] && $row) {
              $check = UserMailbox::where(['mailbox_id' => $row, 'user_id' => $user->id])->first();
              if ($check) {
                if ($req->dataToUpdate['isRead'] === 'false') {
                  UserMailbox::find($check->id)->update(['is_read' => false]);
                } else if ($req->dataToUpdate['isRead'] == true) {
                  UserMailbox::find($check->id)->update(['is_read' => true]);
                }
              } else {
                $data_arr = [
                  'user_id' =>  $user->id,
                  'mailbox_id' => $row,
                  'is_read' => true
                ];
                UserMailbox::create($data_arr);
              }
            }

          }
        }

      }else if($req->folder === 'spam'){
        foreach($req->emailIds as $row){
          if ($req->folder == 'spam' && $row) {
            $check = UserMailbox::where(['mailbox_id' => $row['id'], 'user_id' => $user->id])->first();
            if ($check) {
              if ($req->dataToUpdate['isSpam'] === 'false') {
                UserMailbox::where('id', $check->id)->update(['is_spam' => false,'message_id'=>$row['message_id']]);
              } else if ($req->dataToUpdate['isSpam'] == true) {
                UserMailbox::where('id', $check->id)->update(['is_spam' => true,'is_trash' => false,'message_id'=>$row['message_id']]);
              }
            } else {
              $data_arr = [
                'user_id' => $user->id,
                'mailbox_id' => $row['id'],
                'is_spam' => true,
                'message_id'=>$row['message_id']
              ];
              UserMailbox::create($data_arr);
            }
          }
        }

      }else if($req->folder === 'trash'){
        foreach($req->emailIds as $row){
        if($req->folder == 'trash' && $row) {
          $check = UserMailbox::where(['mailbox_id' => $row['id'], 'user_id' => $user->id])->first();
          if ($check) {
            if ($req->dataToUpdate['isTrash'] === 'false') {
              UserMailbox::where('id', $check->id)->update(['is_trash' => false,'message_id'=>$row['message_id']]);
            } else if($req->dataToUpdate['isTrash'] == true) {
              
  
              UserMailbox::where('id', $check->id)->update(['is_trash' => true,'is_spam' => false,'message_id'=>$row['message_id']]);
            }
          } else {
            $data_arr = [
              'user_id' => $user->id,
              'mailbox_id' => $row['id'],
              'is_trash' => true,
              'message_id'=>$row['message_id']
            ];
            UserMailbox::create($data_arr);
          }
        }
      }

      }else
      if($req->status){
      foreach ($req->emailIds as $id) {
        // $check_not_stared = Mailbox::where(['id' => $id, 'isStarred' => 0])->first();
        // $check_stared = Mailbox::where(['id' => $id, 'isStarred' => 1])->first();

        if ($req->status == 'add') {

          Mailbox::find($id)->update([
            'isStarred' => 1
          ]);
        }
        if ($req->status == 'remove') {

          Mailbox::find($id)->update([
            'isStarred' => 0
          ]);
        }
      }
    }
    
    return $this->fetchEmails($req);
      // $this->response['message'] = 'starred all mails';
      // $this->response['status'] = true;
      // $this->response['data'] = ;

    }
    if (array_key_exists('isStarred', $req->dataToUpdate) != 1) {
      $this->response['status'] = true;
      $this->response['message'] = 'single mail fetched';
      return response()->json($this->response);
    }
    if ($req->dataToUpdate['isStarred'] == true) {

      $email_id = $req->emailIds;

      Mailbox::find($email_id)->update([
        'isStarred' => 1
      ]);
    } else if ($req->dataToUpdate['isStarred'] == false) {
      $email_id = $req->emailIds;

      Mailbox::find($email_id)->update([
        'isStarred' => 0
      ]);
    }

  }

  public function markedAs_spam_trash(Request $req){
    $user = $req->user();
    
    if ($req->folder == 'spam' || $req->folder == 'trash') {
      // if($req->dataToUpdate['isSpam'] == false){
      //   return 'false';
      // }
      
      if($req->dataToUpdate){
      if ($req->folder == 'spam' && $req->dataToUpdate['mailbox_id']) {
        $check = UserMailbox::where(['mailbox_id' => $req->dataToUpdate['mailbox_id'], 'user_id' => $req->dataToUpdate['user_id']])->first();
        if ($check) {
          if ($req->dataToUpdate['isSpam'] === 'false') {
            UserMailbox::where('id', $check->id)->update(['is_spam' => false,'message_id'=>$req->dataToUpdate['message_id']]);
          } else if ($req->dataToUpdate['isSpam'] == true) {
            UserMailbox::where('id', $check->id)->update(['is_spam' => true,'is_trash' => false,'message_id'=>$req->dataToUpdate['message_id']]);
          }
        } else {
          $data_arr = [
            'user_id' => $req->dataToUpdate['user_id'],
            'mailbox_id' => $req->dataToUpdate['mailbox_id'],
            'is_spam' => true,
            'message_id'=>$req->dataToUpdate['message_id']
          ];
          UserMailbox::create($data_arr);
        }
      }else if($req->folder == 'trash' && $req->dataToUpdate['mailbox_id']) {
        $check = UserMailbox::where(['mailbox_id' => $req->dataToUpdate['mailbox_id'], 'user_id' => $req->dataToUpdate['user_id']])->first();
        if ($check) {
          if ($req->dataToUpdate['isTrash'] == false) {
            UserMailbox::where('id', $check->id)->update(['is_trash' => false,'message_id'=>$req->dataToUpdate['message_id']]);
          } else if($req->dataToUpdate['isTrash'] == true) {
            

            UserMailbox::where('id', $check->id)->update(['is_trash' => true,'is_spam' => false,'message_id'=>$req->dataToUpdate['message_id']]);
          }
        } else {
          $data_arr = [
            'user_id' => $req->dataToUpdate['user_id'],
            'mailbox_id' => $req->dataToUpdate['mailbox_id'],
            'is_trash' => true,
            'message_id'=>$req->dataToUpdate['message_id']
          ];
          UserMailbox::create($data_arr);
        }
      }


    }

  }
  return $this->fetchEmails($req);

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
  private function getFileName($image, $name, $index)
  {
    list($type, $file) = explode(';', $image);
    list(, $extension) = explode('/', $type);
    list(, $file) = explode(',', $file);
    // $result['name'] = 'oas36ty'.now()->timestamp . '.' . $extension;
    $result['name'] = str_replace(' ', '', explode('.', $name)[0]) . now()->timestamp . '.' . $extension;
    // $result['data'] = ;
    $result['file'] = $file;
    return $result;
  }
  private function getFileName2($image, $name, $index)
  {
    list($type, $file) = explode(';', $image);
    list(, $extension) = explode('/', $type);
    list(, $file) = explode(',', $file);
    // $result['name'] = 'oas36ty'.now()->timestamp . '.' . $extension;
    $result['name'] = now()->timestamp . $name; //str_replace(' ', '',explode('.', $name)[0]). now()->timestamp.'.'. $extension;
    // $result['data'] = ;
    $result['file'] = $file;
    return $result;
  }

  public function sendEmail(Request $request)
  {
   
    $user = $request->user();
    $bcc =  $request->data['bcc'] ?? '';
    $cc =  $request->data['cc'] ?? '';

    $attach = $request->data['attach_url'];
    $f = [];

    $outbound_id = $request->data['from']['id'];
    $centralUser =  CentralUser::where('email', json_decode($request->header('currrent'))->email)->first();

    $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
    tenancy()->initialize($tenant);
    $user_setting  = UserEmail::where(['user_id' => json_decode($request->header('currrent'))->id, 'emails_setting_id' => $outbound_id])->get();

    if ($user_setting) {

      $details_outbound = EmailsSetting::where(['id' => $outbound_id, 'outBound_status' => 'tick'])->first();

      if ($details_outbound) {

        $mailsetting = EmailOutbound::where(['id' => $details_outbound->id])->first();

        if ($mailsetting) {
          $data = [
            'driver'            => $mailsetting->mail_transport,
            'host'              => $mailsetting->mail_host,
            'port'              => $mailsetting->mail_port,
            'encryption'        => $mailsetting->mail_encryption,
            'username'          => $mailsetting->mail_username,
            'password'          => $mailsetting->mail_password,
            'from'              => [
              'name'   => 'Oas36ty'
            ]
          ];
          Config::set('mail', $data);
        }
      }
    }
    $message =  $request->data['message'] ?? '';
    $subject = $request->data['subject'] ?? '';

    $status = [];
    foreach ($request->data['to'] as $email) {
      $data_arr = [
        'message' => $message ?? '', 'subject' => $subject ?? '', 'email' => $email ?? '', 'email_bcc' => $bcc, 'email_cc' => $cc, 'attach' => $attach,
        'email_from' => $request->data['from']['email'],'plain_text'=>$request->data['plain_text'] ?? ''
      ];

      $status = $this->SendEmailDriven($data_arr);
    }
    return $status;
  }



  public function send_email_sms($email_data = [], $sms_data = [])
  {
    ## sending email

    try {
      if (!empty($email_data) && array_key_exists('email', $email_data)) {
        $email = $email_data['email'] ?? '';
        if ($email) {
          $files = $email_data['attach'];
          $email_replyTo = $email_data['email_replyTo'] ?? '';
          $data = [];
          $email_template = array_key_exists('email_template', $email_data)  ? $email_data['email_template'] : '';
          $data['email'] = $email;
          $data['template_data'] = array_key_exists('template_data', $email_data)  ? $email_data['template_data'] : '';
          $data['plain_text'] = array_key_exists('plain_text', $email_data)  ? $email_data['plain_text'] : '';

          $data['email_subject'] = array_key_exists('email_subject', $email_data)  ? $email_data['email_subject'] : 'EMail from Oas36ty';
          $data['email_from'] = array_key_exists('email_from', $email_data) ? $email_data['email_from'] : 'info@gmail.com';

          $data['email_from_name'] = array_key_exists('email_from_name', $email_data) ? $email_data['email_from_name'] : 'Oas36ty';
          $data['email_cc'] = array_key_exists('email_cc', $email_data)  ? $email_data['email_cc'] : '';
          $data['email_bcc'] = array_key_exists('email_bcc', $email_data)  ? $email_data['email_bcc'] : '';
          if ($email_replyTo) {
            $data['email_replyTo'] = array_key_exists('email_replyTo', $email_data)  ?   $email_replyTo[0] : '';
            $data['message_id'] = array_key_exists('message_id', $email_data)  ? $email_data['message_id'] : '';
            $data['references'] = array_key_exists('references', $email_data)  ? $email_data['references'] : '';
            // $data['email'] = $email  ?? '';
          } else {
            $data['email_replyTo'] = '';
          }

          // $data['email_attach'] = array_key_exists('email_attach', $email_data)  ? $email_data['email_attach'] : '';

          Mail::send([], [], function ($message) use ($data, $files) {
            $message->from($data['email_from'], $data['email_from_name']);
            $message->to($data['email']);
            $message->subject($data['email_subject']);
            $message->setBody('<html>'. $data['template_data'].'</html>', 'text/html' ); // dont miss the '<html></html>' or your spam score will increase !
            $message->addPart($data['plain_text'], 'text/plain');
            if ($data['email_cc']) {

              $message->cc($data['email_cc']);
            }
            if ($data['email_bcc']) {

              $message->bcc($data['email_bcc']);
            }
            if ($data['email_replyTo']) {
              $references = $data['references'] . '<' . $data['message_id'] . '>';
              $message->getHeaders()->addTextHeader('In-Reply-To', $data['message_id']);
              $message->getHeaders()->addTextHeader('References', $references);
              // $message->getHeaders()->addTextHeader('Message-ID', $data['message_id']);

              $message->replyTo($data['email_from']);
            }
            if ($files) {

              foreach ($files as $file) {
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
    $email_data['plain_text'] = $data_arr['plain_text'];

    $email_data['email_bcc'] = $data_arr['email_bcc'];
    $email_data['message_id'] = $data_arr['message_id'] ?? '';
    $email_data['references'] = $data_arr['references'] ?? '';
    $email_data['email_replyTo'] = $data_arr['email_replyTo'] ?? '';
    $email_data['email_from'] = $data_arr['email_from'] ?? '';

    $email_data['email'] = $data_arr['email'];
    $email_data['email_subject'] = $data_arr['subject'];
    $email_data['email_template'] = "emails.auth.hello";
    $email_data['template_data'] = ['body' => $data_arr['message']];
    $email_data['template_data'] = $data_arr['message'];
    $email_data['attach'] = $data_arr['attach'];
     $check = $this->send_email_sms($email_data, []);
    if ($check) {
      $this->response['status'] = true;
      $this->response['status_code'] = 200;
      $this->response['data'] = $check;
      $this->response['message'] = "Email sent successfully";
      return response()->json($this->response);
    } else {
      $this->response['status'] = true;
      $this->response['status_code'] = 201;
      $this->response['data'] = $check;

      $this->response['message'] = "Something went wrong";
      return response()->json($this->response);
    }
  }

  public function fetch_latestEmails(Request $request)
  {


    $centralUser =  CentralUser::where('email', $request->currrent['email'])->first();

    $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
    tenancy()->initialize($tenant);
    $user_id = $request->currrent['id'];


    $check = Schema::hasTable('emails_inbound_setting');
    $check1 = Schema::hasTable('emails_settings');
    $check2 = Schema::hasTable('mailbox');


    if ($check && $check1 && $check2) {

      $user_setting  = UserEmail::where('user_id', $request->currrent['id'])->get();
      $details_inbound = [];
      $function = [];
      foreach ($user_setting as $index => $user_emails) {
        $details_inbound[$index] = EmailInbound::where('id', $user_emails->emails_setting_id)->first();
      }
      foreach ($details_inbound as  $data) {
        if ($data) {

          $imap_array = [
            'mail_host' => $data->mail_host,
            'mail_transport' => $data->mail_transport,
            'mail_encryption' => $data->mail_encryption,
            'mail_username' => $data->mail_username,
            'mail_password' => $data->mail_password,
            'mail_port' => (int)$data->mail_port,
          ];
          $cm  = new ClientManager();
          // $cm->account()
          $client = $cm->make([
            'host'          => $imap_array['mail_host'],
            'port'          => $imap_array['mail_port'],
            'encryption'    => $imap_array['mail_encryption'] ?? 'ssl',
            'validate_cert' => true,
            'username'      => $imap_array['mail_username'],
            'password'      => $imap_array['mail_password'],
            'protocol'      => $imap_array['mail_transport'] ?? 'imap',
            // 'options' => [
            //   'delimiter' => '/',
            //   'fetch' => PHPIMAPIMAP::FT_UID,
            //   'fetch_body' => true,
            //   'fetch_attachment' => true,
            //   'fetch_flags' => true,
            //   'message_key' => 'id',
            //   'fetch_order' => 'asc',
            //   'open' => [
            //       // 'DISABLE_AUTHENTICATOR' => 'GSSAPI'
            //   ],
            //   'decoder' => [
            //       'message' => [
            //           'subject' => 'utf-8' // mimeheader
            //       ],
            //       'attachment' => [
            //           'name' => 'utf-8' // mimeheader
            //       ]
            //   ]
            // ],

            // 'masks' => [
            //     'message' => MessageMask::class,
            //     'attachment' => AttachmentMask::class
            // ]
          ]);


          if ($client->connect() == 'false') {

            return ['connection not established'];
          } else {
            $check = Mailbox::where(['to_email' => $imap_array['mail_username'], 'folder' => 'INBOX'])->first();
            $check1 = Mailbox::where(['from_email' => $imap_array['mail_username'], 'folder' => 'Sent Mail'])->first();
            $trash_check =  Mailbox::where(['from_email' => $imap_array['mail_username'], 'folder' => 'Trash'])->first();
            $draft_check =  Mailbox::where(['from_email' => $imap_array['mail_username'], 'folder' => 'Drafts'])->first();
            $spam_check =  Mailbox::where(['from_email' => $imap_array['mail_username'], 'folder' => 'Spam'])->first();
            $inbox = $client->getFolderByName('INBOX');
            $trash = $client->getFolderByName('Trash');
            $draft = $client->getFolderByName('Drafts');
            $spam = $client->getFolderByName('Spam');
            $all_mail = $client->getFolderByName('All Mail');

            // $inbox_messages = $inbox->messages()->all()->setFetchOrder("desc")->get();
            // $aMessage = $client->($client->getFolder('INBOX'));
            // $client->setDefaultMessageMask(MessageMask::class);
            // return $messages = $all_mail->query()->from('noreply@digest.groww.in')->get();

            // return $messages = $all_mail->query()->from('jos@internshala.com')->getFetchFlags();

          //   $valu = $inbox->query()->whereMessageId( "da81a9d9170c76bd07a5fb632c1737d0@127.0.0.1" )->get();
          //   if($valu->move('Trash') == true){
          //     return 'Message has ben moved in trash';
          // }else{
          //     return 'Message could not be moved';
          // }
          //   return [$valu];


            if ($inbox) {
              if ($check) {
                try {
                  $totalMessages = $inbox->query()->all()->count();


                  if ($totalMessages) {
                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'inbound_msg_count' => $totalMessages
                    ]);
                  }

                  $inbox_messages = $inbox->messages()->all()
                    ->setFetchOrder("desc")->limit(20, 1)->get() ?? []; //$inbox->query()->get();
                  // $inbox_messages = $inbox->messages()->all()->limit(20, $request->page)->get();//$inbox->query()->get();
                } catch (Exception $e) {
                  $inbox_messages = [];
                  continue;
                }
              } else {
                try {
                  // Artisan::call('fetch:emails');

                  $totalMessages = $inbox->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'inbound_msg_count' => $totalMessages
                    ]);
                  }
                  // $inbox = $client->getFolderByName('INBOX');
                  $inbox_messages = $inbox->messages()->all()->setFetchOrder("desc")
                    ->limit(100, 1)->get() ?? [];
                } catch (Exception $ex) {
                  $inbox_messages = [];
                  continue;
                }
              }
            } else {
              $inbox_messages = [];
            }

            $sent = $client->getFolderByName('Sent Mail');
            if ($sent) {
              if ($check1) {
                try {
                  $totalMessages = $sent->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'sent_msg_count' => $totalMessages
                    ]);
                  }

                  $sent_messages = $sent->messages()->all()->setFetchOrder("desc")->limit(20, 1)->get() ?? []; //$sent->messages()->all()->limit(20, $request->page)->get();
                } catch (Exception $ex) {
                  $sent_messages = [];
                  continue;
                }
              } else {
                try {
                  // $sent = $client->getFolderByName('Sent Mail');
                  $totalMessages = $sent->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'sent_msg_count' => $totalMessages
                    ]);
                  }

                  $sent_messages = $sent->messages()->all()->setFetchOrder("desc")->limit(100, 1)->get() ?? [];
                } catch (Exception $ex) {
                  $sent_messages = [];
                  continue;
                }
              }
            } else {
              $sent_messages = [];
            }

            if ($draft) {
              if ($draft_check) {
                try {
                  $totalMessages = $draft->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'draft_msg_count' => $totalMessages
                    ]);
                  }

                  $draft_messages = $draft->messages()->all()->setFetchOrder("desc")->limit(20, 1)->get() ?? []; //$sent->messages()->all()->limit(20, $request->page)->get();
                } catch (Exception $ex) {
                  $draft_messages = [];
                  continue;
                }
              } else {
                try {
                  $totalMessages = $draft->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'draft_msg_count' => $totalMessages
                    ]);
                  }

                  $draft_messages = $draft->messages()->all()->setFetchOrder("desc")->limit(100, 1)->get() ?? [];
                } catch (Exception $ex) {
                  $draft_messages = [];
                  continue;
                }
              }
            } else {
              $draft_messages = [];
            }
            if ($trash) {
              if ($trash_check) {
                try {
                  $totalMessages = $trash->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'trash_msg_count' => $totalMessages
                    ]);
                  }

                  $trash_messages = $trash->messages()->all()->setFetchOrder("desc")->limit(5, 1)->get() ?? []; //$sent->messages()->all()->limit(20, $request->page)->get();
                } catch (Exception $ex) {
                  $trash_messages = [];
                  continue;
                }
              } else {
                try {
                  $totalMessages = $trash->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'trash_msg_count' => $totalMessages
                    ]);
                  }

                  $trash_messages = $trash->messages()->all()->setFetchOrder("desc")->limit(100, 1)->get() ?? [];
                } catch (Exception $ex) {
                  $trash_messages = [];
                  continue;
                }
              }
            } else {
              $trash_messages = [];
            }

            if ($spam) {
              if ($spam_check) {
                try {

                  $totalMessages = $spam->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'spam_msg_count' => $totalMessages
                    ]);
                  }
                  $spam_messages = $spam->messages()->all()->setFetchOrder("desc")->limit(5, 1)->get() ?? []; //$sent->messages()->all()->limit(20, $request->page)->get();
                } catch (Exception $ex) {
                  $spam_messages = [];
                  continue;
                }
              } else {
                try {
                  $totalMessages = $spam->messages()->all()->count();
                  if ($totalMessages) {

                    UserEmail::where(['user_id' => $user_id, 'emails_setting_id' => $data->id])->update([
                      'spam_msg_count' => $totalMessages
                    ]);
                  }

                  $spam_messages = $spam->messages()->all()->setFetchOrder("desc")->limit(100, 1)->get() ?? [];
                } catch (Exception $ex) {
                  $spam_messages = [];
                  continue;
                }
              }
            } else {
              $spam_messages = [];
            }
        
            foreach ($inbox_messages as $n => $oMessage) {
              // $reply[]=$oMessage->cc;
              // $oMessage->setFlag(['Seen', 'Flagged']);  
              // $oMessage->peek();     
              // $currentThread = null;
              // $threads = $oMessage->thread($client->getFolder('Sent Mail'), $currentThread, $client->getFolder('INBOX'));  
              // $thread_html = [];
              // foreach ($threads as $key => $thread) {

              //   $reply = $thread->in_reply_to == '' ? null : $thread->in_reply_to;
              //   if($reply != null){
              //     $message ='';
              //     $subject = $thread->subject ?? '';
              //     $from_email = $thread->sender[0]->mail ?? '';
              //     $from_name = $thread->sender ?? '';
              //     $message_id = $thread->message_id ?? '';
              //     $to_email = $thread->to ?? '';
              //     $references = str_replace('<','',$thread->references) ?? '';
              //     $references = str_replace('>',',', $references) ?? '';
              //     $references = explode(',',$references);
              //     $in_reply_to  = str_replace('<','',$thread->in_reply_to) ?? '';
              //     $in_reply_to = str_replace('>','',$in_reply_to) ?? '';
              //     $original_ref1 = $thread->references;
              //     $original_ref = $original_ref1[0] ?? '';
              //     $u_date = $thread->t ?? '';
              //     $date = $thread->date ?? '';

              //     $details_of_email2[] = [
              //       'subject' => $subject ?? "",
              //       'from_name' => $from_name ?? "",
              //       'from_email' => $from_email ?? "",
              //       'message_id' =>  $message_id ?? "",
              //       'to_email' => $data->mail_username ?? "", //$header_info[$n]->to[0]->mailbox. '@'. $header_info[$n]->to[0]->host,
              //       // 'message' => preg_replace('/[^A-Za-z0-9\-]/', ' ', $message[$n]) ?? ""
              //       "message" => $message ?? "",
              //       'date' =>  $date ?? "",
              //       'u_date' => strtotime($date),
              //       'folder' => $inbox->name,
              //       'references'=> $original_ref ?? '',
              //           'in_reply_to' => $in_reply_to ?? '',
              //           // 'attachments'=> $attachments ?? 0,
              //           // 'is_parent'=> $is_parent ?? 1
              //           //    'recent' => $header->recent,

              //         ];

              // }
              // }

              $message = '';
              $subject = $oMessage->subject ?? '';
              $from_email = $oMessage->sender[0]->mail ?? '';
              $from_name = $oMessage->sender ?? '';
              $message_id = $oMessage->message_id ?? '';
              $to_email = $oMessage->to ?? null;
              $references = str_replace('<', '', $oMessage->references) ?? '';
              $references = str_replace('>', ',', $references) ?? '';
              $references = explode(',', $references);
              $in_reply_to  = str_replace('<', '', $oMessage->in_reply_to) ?? '';
              $in_reply_to = str_replace('>', '', $in_reply_to) ?? '';
              $original_ref1 = $oMessage->references;
              $original_ref = $original_ref1[0] ?? '';
              $u_date = $oMessage->t ?? '';
              $date = $oMessage->date ?? '';

              $ccaddress = $oMessage->cc ?? null;
              $bccaddress = $oMessage->bcc ?? null;
              // if($ccaddress || $bccaddress){
              // //$bcc_cc[] = ['cc'=>$ccaddress ?? '', 'bcc'=>$bccaddress ?? ''] ;
              // // $bcc_cc[] = ['cc'=>explode('<',$ccaddress) ?? '', 'bcc'=>explode('<',$bccaddress) ?? '' ];
              // }
              $attach_files = [];
              $message = $oMessage->getHTMLBody();
              if (!$message) {
                $message = $oMessage->getHTMLBody(true);
                $message = $message;
              }
              $plain_text_messages = $oMessage->getTextBody() ?? '';
              $attachments = $oMessage->getAttachments()->count();

              $check_email = Mailbox::where(['message_id' => $message_id, 'folder' => 'INBOX'])->first();
              // $check_email = "";
              if (!$check_email) {

                //--------------------------------------- Download Attachments of messages ----------------------------
                $attachments_file = $oMessage->getAttachments();


                $is_parent = null;
                if ($in_reply_to) {
                  // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                  $check_parent = Mailbox::where(['to_email' => $data->mail_username, 'folder' => 'INBOX'])
                    ->where(function ($query) use ($in_reply_to, $references) {
                      $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')->orWhere('message_id', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')->orWhere('references', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%')->orWhere('in_reply_to', 'LIKE', '%' . $references[0] . '%');
                    })->first();
                  if (!empty($check_parent)) {
                    $is_parent = 0;
                    $update =  Mailbox::where(['to_email' => $data->mail_username, 'folder' => $inbox->name])
                      ->where('is_parent', 1)
                      ->where(function ($query) use ($in_reply_to) {
                        $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%');
                      })->first();
                    if ($update) {
                      if ($update->u_date < strtotime($date)) {
                        Mailbox::where('id', $update->id)->update(['u_date' => strtotime($date)]);
                        UserMailbox::where('mailbox_id', $update->id)->update(['is_read' => false]);
                      }
                    }
                  } else {
                    $is_parent = 1;
                  }
                }
                $details_of_email = [
                  'subject' => $subject ?? "",
                  'from_name' => $from_name ?? "",
                  'from_email' => $from_email ?? "",
                  'message_id' =>  $message_id ?? "",
                  'to_email' => $data->mail_username ?? "", //$header_info[$n]->to[0]->mailbox. '@'. $header_info[$n]->to[0]->host,
                  // 'message' => preg_replace('/[^A-Za-z0-9\-]/', ' ', $message[$n]) ?? ""
                  "message" => $message ?? "",
                  'date' =>  $date ?? "",
                  'u_date' => strtotime($date),
                  'folder' => $inbox->name,
                  'references' => $original_ref ?? '',
                  'in_reply_to' => $in_reply_to ?? '',
                  'attachments' => $attachments ?? 0,
                  'is_parent' => $is_parent ?? 1,
                  'ccaddress' => $ccaddress ?? null,
                  'bccaddress' => $bccaddress ?? null,
                  'to_replyEmails' => $to_email ?? null,
                  'plainText_messages'=> $plain_text_messages ?? ''
                  //    'recent' => $header->recent,

                ];
             
                try {
                  $insert_file = Mailbox::create($details_of_email);


                  if ($attachments_file) {
                    foreach ($attachments_file as $key => $attach) {
                      // $attach_files[$key] = $attach_file->name ?? '';

                      $masked = $attach->setMask(AttachmentMask::class);
                      $temp = [];
                      $temp['mask'] = $masked->mask();

                      $filebase64 = $temp['mask']->getImageSrc();
                      // $filebase64 = str_replace('"','',$filebase64);
                      // $filebase64 = explode('base64,',$filebase64);
                      $temp['file'] = $filebase64;
                      $temp['name'] = $temp['mask']->getName();
                      // $temp['disposition'] = $temp['mask']->getDisposition();
                      $temp['size'] = $temp['mask']->getSize();
                      //array_push()
                      if ($temp) {
                        $avatar = $this->getFileName2($temp['file'], trim($temp['name']), null);
                        try {

                          Storage::disk('s3')->put('inbox-email-files/' . $avatar['name'],  base64_decode($avatar['file']), 'public');

                          $url = Storage::disk('s3')->url('inbox-email-files/' . $avatar['name']);

                          $insert_arr = [
                            'mailbox_id' => $insert_file->id ?? '',
                            'attachment_url' => $url ?? '',
                            'attachment_name' => $temp['name'] ?? '',
                            'folder' => $inbox->name ?? ''
                          ];
                          $check = MailboxAttachment::create($insert_arr);

                          if (!$check) {
                            continue;
                          }
                        } catch (Exception $e) {
                          continue;
                        }
                      }
                    }
                  }
                  // $reply[] = $details_of_email;

                } catch (Exception $ex) {
                  Log::info("======= While inserting new email message : " . $ex . " ==========");
                  continue;
                }
              }
            }
           

            foreach ($sent_messages as $n => $oMessage) {
              // $attachments_file2 = $oMessage->getAttachments();
              // $attach3[] = $attachments_file2[0] ?? '';

              $attachments = $oMessage->getAttachments()->count() ?? '';
              $subject = $oMessage->subject ?? '';
              $from_email = $oMessage->sender[0]->mail ?? '';
              $from_name = $oMessage->sender ?? '';
              $message_id = $oMessage->message_id ?? '';
              $to_email = $oMessage->to ?? null;
              $u_date = $oMessage->t ?? '';
              $date = $oMessage->date ?? '';

              $references = str_replace('<', '', $oMessage->references) ?? '';
              $references = str_replace('>', ',', $references) ?? '';
              $references = explode(',', $references);
              $in_reply_to  = str_replace('<', '', $oMessage->in_reply_to) ?? '';
              $in_reply_to = str_replace('>', '', $in_reply_to) ?? '';
              $ccaddress = $oMessage->cc ?? null;
              $bccaddress = $oMessage->bcc ?? null;
              // $reply_toaddress =  $oMessage->reply_toaddress ?? '';
              // $rep_add = explode('<',$reply_toaddress) ?? '';
              // $repadd[]= str_replace('>','',$rep_add[1] ?? $rep_add[0]) ??  $reply_toaddress;//explode('<',$reply_toaddress[0]);

              $original_ref1 = $oMessage->references;
              $original_ref = $original_ref1[0] ?? '';
              if ($oMessage->hasTextBody()) {
                $message = $oMessage->getTextBody();
              }
              if ($oMessage->hasHTMLBody()) {
                $message = $oMessage->getHTMLBody(true);
              }
              $plain_text_messages = $oMessage->getTextBody() ?? '';

              $check_email = Mailbox::where(['message_id' => $message_id, 'folder' => 'Sent Mail'])->first();
              // $check_email = "";
              if (!$check_email) {
                $attachments_file = [];
                $attachments_file = $oMessage->getAttachments();

                $is_parent = null;
                if ($in_reply_to) {
                  // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                  $check_parent = Mailbox::where(['from_email' => $from_email, 'folder' => $sent->name])
                    ->where(function ($query) use ($in_reply_to, $references) {
                      $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')->orWhere('message_id', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')->orWhere('references', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%')->orWhere('in_reply_to', 'LIKE', '%' . $references[0] . '%');
                    })->first();

                  if (!empty($check_parent)) {
                    $is_parent = 0;
                    $update =  Mailbox::where(['from_email' => $from_email, 'folder' => $sent->name])
                      ->where('is_parent', 1)
                      ->where(function ($query) use ($in_reply_to) {
                        $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%');
                      })->first();
                    if ($update) {
                      if ($update->u_date < strtotime($date)) {
                        Mailbox::where('id', $update->id)->update(['u_date' => strtotime($date)]);
                      }
                    }
                  } else {
                    $is_parent = 1;
                  }
                }

                $details_of_email = [
                  'subject' => $subject ?? "",
                  'from_name' => $from_name ?? "",
                  'from_email' => $from_email ?? "",
                  'message_id' =>  $message_id ?? "",
                  'to_email' => $to_email ?? "", //$header_info[$n]->to[0]->mailbox. '@'. $header_info[$n]->to[0]->host,
                  // 'message' => preg_replace('/[^A-Za-z0-9\-]/', ' ', $message[$n]) ?? ""
                  "message" => $message ?? "",
                  'date' =>  $date ?? "",
                  'u_date' => strtotime($date),
                  'attachments' => $attachments ?? 0,
                  'folder' => $sent->name,

                  'references' => $original_ref ?? '',
                  'in_reply_to' => $in_reply_to ?? '',
                  'is_parent' => $is_parent ?? 1,
                  'ccaddress' => $ccaddress ?? null,
                  'bccaddress' => $bccaddress ?? null,
                  'to_replyEmails' => $to_email ?? null,
                  'plainText_messages'=> $plain_text_messages ?? ''


                  //    'recent' => $header->recent,

                ];
               
                try {
                  $insert_file = [];
                  $insert_file = Mailbox::create($details_of_email);

                  if ($attachments_file) {
                    foreach ($attachments_file as $key => $attach) {
                      // $attach_files[$key] = $attach_file->name ?? '';

                      $masked = $attach->setMask(AttachmentMask::class);
                      $temp = [];
                      $temp['mask'] = $masked->mask();
                      $filebase64 = $temp['mask']->getImageSrc();
                      // $filebase64 = str_replace('"','',$filebase64);
                      // $filebase64 = explode('base64,',$filebase64);
                      $temp['file'] = $filebase64;
                      $temp['name'] = $temp['mask']->getName();
                      // $temp['disposition'] = $temp['mask']->getDisposition();
                      $temp['size'] = $temp['mask']->getSize();
                      //array_push()
                      if ($temp) {
                        $avatar = $this->getFileName2($temp['file'], trim($temp['name']), null);
                        try {

                          Storage::disk('s3')->put('inbox-email-files/' . $avatar['name'],  base64_decode($avatar['file']), 'public');

                          $url = Storage::disk('s3')->url('inbox-email-files/' . $avatar['name']);

                          $insert_arr = [
                            'mailbox_id' => $insert_file->id ?? '',
                            'attachment_url' => $url ?? '',
                            'attachment_name' => $temp['name'] ?? '',
                            'folder' => $sent->name ?? ''
                          ];
                          $check = MailboxAttachment::create($insert_arr);

                          if (!$check) {
                            continue;
                          }
                        } catch (Exception $e) {
                          continue;
                        }
                      }
                    }
                  }
                } catch (Exception $ex) {
                  continue;
                }
              }
            }

            foreach ($draft_messages as $n => $oMessage) {

              $attachments = $oMessage->getAttachments()->count() ?? '';
              $subject = $oMessage->subject ?? '';
              $from_email = $oMessage->sender[0]->mail ?? '';
              $from_name = $oMessage->sender ?? '';
              $message_id = $oMessage->message_id ?? '';
              $to_email = $oMessage->to ?? null;
              $u_date = $oMessage->t ?? '';
              $date = $oMessage->date ?? '';
              $references = str_replace('<', '', $oMessage->references) ?? '';
              $references = str_replace('>', ',', $references) ?? '';
              $references = explode(',', $references);
              $in_reply_to  = str_replace('<', '', $oMessage->in_reply_to) ?? '';
              $in_reply_to = str_replace('>', '', $in_reply_to) ?? '';

              $original_ref1 = $oMessage->references;
              $original_ref = $original_ref1[0] ?? '';
              $ccaddress = $oMessage->cc ?? null;
              $bccaddress = $oMessage->bcc ?? null;

              if ($oMessage->hasTextBody()) {
                $message = $oMessage->getTextBody();
              }
              if ($oMessage->hasHTMLBody()) {
                $message = $oMessage->getHTMLBody(true);
              }
              $plain_text_messages = $oMessage->getTextBody() ?? '';

              $check_email = Mailbox::where(['message_id' => $message_id, 'folder' => 'Drafts'])->first();
              // $check_email = "";
              if (!$check_email) {
                $is_parent = null;
                if ($in_reply_to) {
                  // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                  $check_parent =  Mailbox::where(['from_email' => $from_email, 'folder' => $draft->name])
                    ->where(function ($query) use ($in_reply_to, $references) {
                      $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')->orWhere('message_id', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')->orWhere('references', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%')->orWhere('in_reply_to', 'LIKE', '%' . $references[0] . '%');
                    })->first();

                  if (!empty($check_parent)) {
                    $is_parent = 0;
                    $update =  Mailbox::where(['from_email' => $from_email, 'folder' => $draft->name])
                      ->where('is_parent', 1)
                      ->where(function ($query) use ($in_reply_to) {
                        $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%');
                      })->first();
                    if ($update) {
                      if ($update->u_date < strtotime($date)) {
                        Mailbox::where('id', $update->id)->update(['u_date' => strtotime($date)]);
                      }
                    }
                  } else {
                    $is_parent = 1;
                  }
                }


                $details_of_email = [
                  'subject' => $subject ?? "",
                  'from_name' => $from_name ?? "",
                  'from_email' => $from_email ?? "",
                  'message_id' =>  $message_id ?? "",
                  'to_email' => $to_email ?? "", //$header_info[$n]->to[0]->mailbox. '@'. $header_info[$n]->to[0]->host,
                  // 'message' => preg_replace('/[^A-Za-z0-9\-]/', ' ', $message[$n]) ?? ""
                  "message" => $message ?? "",
                  'date' =>  $date ?? "",
                  'u_date' => strtotime($date),
                  'attachments' => $attachments ?? 0,
                  'references' => $original_ref ?? '',
                  'in_reply_to' => $in_reply_to ?? '',
                  'folder' => $draft->name,
                  'is_parent' => $is_parent ?? 1,
                  'ccaddress' => $ccaddress ?? null,
                  'bccaddress' => $bccaddress ?? null,
                  'to_replyEmails' => $to_email ?? null,
                  'plainText_messages'=> $plain_text_messages ?? ''


                  //    'recent' => $header->recent,

                ];
              
                try {
                  $insert = Mailbox::create($details_of_email);
                } catch (Exception $ex) {
                  continue;
                }
              }
            }

            foreach ($spam_messages as $n => $oMessage) {

              $attachments = $oMessage->getAttachments()->count() ?? '';
              $subject = $oMessage->subject ?? '';
              $from_email = $oMessage->sender[0]->mail ?? '';
              $from_name = $oMessage->sender ?? '';
              $message_id = $oMessage->message_id ?? '';
              $to_email = $oMessage->to ?? null;
              $u_date = $oMessage->t ?? '';
              $date = $oMessage->date ?? '';
              $references = str_replace('<', '', $oMessage->references) ?? '';
              $references = str_replace('>', ',', $references) ?? '';
              $references = explode(',', $references);
              $in_reply_to  = str_replace('<', '', $oMessage->in_reply_to) ?? '';
              $in_reply_to = str_replace('>', '', $in_reply_to) ?? '';
              $original_ref1 = $oMessage->references;
              $original_ref = $original_ref1[0] ?? '';
              $ccaddress = $oMessage->cc ?? null;
              $bccaddress = $oMessage->bcc ?? null;

              if ($oMessage->hasTextBody()) {
                $message = $oMessage->getTextBody();
              }
              if ($oMessage->hasHTMLBody()) {
                $message = $oMessage->getHTMLBody(true);
              }
              $plain_text_messages = $oMessage->getTextBody() ?? '';

              $check_email = Mailbox::where(['message_id' => $message_id, 'folder' => 'Spam'])->first();
              // $check_email = "";
              if (!$check_email) {

                $is_parent = null;
                if ($in_reply_to) {
                  // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                  $check_parent =  Mailbox::where(['to_email' => $to_email, 'folder' => $spam->name])
                    ->where(function ($query) use ($in_reply_to, $references) {
                      $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')->orWhere('message_id', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')->orWhere('references', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%')->orWhere('in_reply_to', 'LIKE', '%' . $references[0] . '%');
                    })->first();

                  if (!empty($check_parent)) {
                    $is_parent = 0;
                    $update =  Mailbox::where(['to_email' => $to_email, 'folder' => $spam->name])
                      ->where('is_parent', 1)
                      ->where(function ($query) use ($in_reply_to) {
                        $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%');
                      })->first();
                    if ($update) {
                      if ($update->u_date < strtotime($date)) {
                        Mailbox::where('id', $update->id)->update(['u_date' => strtotime($date)]);
                      }
                    }
                  } else {
                    $is_parent = 1;
                  }
                }

                $details_of_email = [
                  'subject' => $subject ?? "",
                  'from_name' => $from_name ?? "",
                  'from_email' => $from_email ?? "",
                  'message_id' =>  $message_id ?? "",
                  'to_email' => $to_email ?? "", //$header_info[$n]->to[0]->mailbox. '@'. $header_info[$n]->to[0]->host,
                  // 'message' => preg_replace('/[^A-Za-z0-9\-]/', ' ', $message[$n]) ?? ""
                  "message" => $message ?? "",
                  'date' =>  $date ?? "",
                  'u_date' => strtotime($date),
                  'attachments' => $attachments ?? 0,
                  'references' => $original_ref ?? '',
                  'in_reply_to' => $in_reply_to[0] ?? '',
                  'folder' => $spam->name,
                  'is_parent' => $is_parent ?? 1,
                  'ccaddress' => $ccaddress ?? null,
                  'bccaddress' => $bccaddress ?? null,
                  'to_replyEmails' => $to_email ?? null,
                  'plainText_messages'=> $plain_text_messages ?? ''


                  //    'recent' => $header->recent,

                ];
             
                try {
                  $insert = Mailbox::create($details_of_email);
                } catch (Exception $ex) {
                  continue;
                }
              }
            }


            foreach ($trash_messages as $n => $oMessage) {

              $attachments = $oMessage->getAttachments()->count() ?? '';
              $subject = $oMessage->subject ?? '';
              $from_email = $oMessage->sender[0]->mail ?? '';
              $from_name = $oMessage->sender ?? '';
              $message_id = $oMessage->message_id ?? '';
              $to_email = $oMessage->to ?? null;
              $u_date = $oMessage->t ?? '';

              $date = $oMessage->date ?? '';
              $references = str_replace('<', '', $oMessage->references) ?? '';
              $references = str_replace('>', ',', $references) ?? '';
              $references = explode(',', $references);
              $in_reply_to  = str_replace('<', '', $oMessage->in_reply_to) ?? '';
              $in_reply_to = str_replace('>', '', $in_reply_to) ?? '';
              $original_ref1 = $oMessage->references;
              $original_ref = $original_ref1[0] ?? '';
              $ccaddress = $oMessage->cc ?? null;
              $bccaddress = $oMessage->bcc ?? null;

              if ($oMessage->hasTextBody()) {
                $message = $oMessage->getTextBody();
              }
              if ($oMessage->hasHTMLBody()) {
                $message = $oMessage->getHTMLBody(true);
              }

              $plain_text_messages = $oMessage->getTextBody() ?? '';

              $check_email = Mailbox::where(['message_id' => $message_id, 'folder' => 'Trash'])->first();
              // $check_email = "";
              if (!$check_email) {
                $is_parent = null;
                if ($in_reply_to) {
                  // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                  $check_parent =  Mailbox::where(['folder' => $trash->name])
                    ->where(function ($query) use ($to_email, $from_email) {
                      $query->where(['to_email' => $to_email])
                        ->orWhere('from_email', $from_email);
                    })
                    ->where(function ($query) use ($in_reply_to, $references) {
                      $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')->orWhere('message_id', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')->orWhere('references', 'LIKE', '%' . $references[0] . '%')
                        ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%')->orWhere('in_reply_to', 'LIKE', '%' . $references[0] . '%');
                    })->first();

                  if (!empty($check_parent)) {
                    $is_parent = 0;
                    $update =  Mailbox::where(['to_email' => $to_email, 'folder' => $trash->name])
                      ->where('is_parent', 1)
                      ->where(function ($query) use ($in_reply_to) {
                        $query->where('message_id', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('references', 'LIKE', '%' . $in_reply_to . '%')
                          ->orWhere('in_reply_to', 'LIKE', '%' . $in_reply_to . '%');
                      })->first();
                    if ($update) {
                      if ($update->u_date < strtotime($date)) {
                        Mailbox::where('id', $update->id)->update(['u_date' => strtotime($date)]);
                      }
                    }
                  } else {
                    $is_parent = 1;
                  }
                }


                $details_of_email = [
                  'subject' => $subject ?? "",
                  'from_name' => $from_name ?? "",
                  'from_email' => $from_email ?? "",
                  'message_id' =>  $message_id ?? "",
                  'to_email' => $to_email ?? "", //$header_info[$n]->to[0]->mailbox. '@'. $header_info[$n]->to[0]->host,
                  // 'message' => preg_replace('/[^A-Za-z0-9\-]/', ' ', $message[$n]) ?? ""
                  "message" => $message ?? "",
                  'date' =>  $date ?? "",
                  'u_date' => strtotime($date),
                  'attachments' => $attachments ?? 0,
                  'references' => $original_ref ?? '',
                  'in_reply_to' => $in_reply_to ?? '',
                  'folder' => $trash->name,
                  'is_parent' => $is_parent ?? 1,
                  'ccaddress' => $ccaddress ?? null,
                  'bccaddress' => $bccaddress ?? null,
                  'to_replyEmails' => $to_email ?? null,
                  'plainText_messages'=> $plain_text_messages ?? ''


                  //    'recent' => $header->recent,

                ];
                try {
                  $insert = Mailbox::create($details_of_email);
                } catch (Exception $ex) {
                  continue;
                }
              }
            }
            // $this->info('Messages Fetched');
          }

          //   if($imap_array){

          //       $this->info(response()->json($imap_array));
          //     }
        }
      }
    }
    $this->response['status'] = true;
    $this->response['message'] = 'Emails Fetched';
    return response()->json($this->response);

    // $req=Request(); 
    // broadcast(new MailboxEmailsFetched('hllo'));

  }

  public function reply_to_all(Request $request)
  {
    $user = $request->user();
    $bcc =  $request->data['bcc'] ?? '';
    $cc =  $request->data['cc'] ?? '';
    $message_id =  $request->data['message_id'] ?? '';
    $references =  $request->data['references'] ?? '';
    $email_replyTo =  $request->data['email_replyTo'] ?? '';

    $attach = $request->data['attach_url'];
        $f = [];
    // if ($request->data['attach']) {

    //   $base64String = $request->data['attach'];

    //   foreach ($base64String as $in => $file) {
    //     $slug = time(); //name prefix
    //     $avatar = $this->getFileName($file['file'], trim($file['name']), $in);

    //     Storage::disk('s3')->put('email-files/' . $avatar['name'],  base64_decode($avatar['file']), 'public');

    //     $url = Storage::disk('s3')->url('email-files/' . $avatar['name']);
    //     $attach[] = $url ?? '';
    //   }
    // }


    $outbound_id = $request->data['from']['id'];
    $centralUser =  CentralUser::where('email', json_decode($request->header('currrent'))->email)->first();

    $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
    tenancy()->initialize($tenant);
    $user_setting  = UserEmail::where(['user_id' => json_decode($request->header('currrent'))->id, 'emails_setting_id' => $outbound_id])->get();

    if ($user_setting) {

      $details_outbound = EmailsSetting::where(['id' => $outbound_id, 'outBound_status' => 'tick'])->first();

      if ($details_outbound) {

        $mailsetting = EmailOutbound::where(['id' => $details_outbound->id])->first();

        if ($mailsetting) {
          $data = [
            'driver'            => $mailsetting->mail_transport,
            'host'              => $mailsetting->mail_host,
            'port'              => $mailsetting->mail_port,
            'encryption'        => $mailsetting->mail_encryption,
            'username'          => $mailsetting->mail_username,
            'password'          => $mailsetting->mail_password,
            'from'              => [
              'name'   => 'Oas36ty'
            ]
          ];
          Config::set('mail', $data);
        }
      }
    }
    $message =  $request->data['message'] ?? '';
    $subject = $request->data['subject'] ?? '';

    $status = [];
    foreach ($request->data['to'] as $email) {
      $data_arr = [
        'plain_text'=> $request->data['plain_text'] ?? '',
        'message' => $message ?? '', 'subject' => $subject ?? '', 'email' => $email ?? '', 'email_bcc' => $bcc, 'email_cc' => $cc, 'attach' => $attach, 'email_replyTo' => $email_replyTo, 'message_id' => $message_id, 'references' => $references,
        'email_from' => $request->data['from']['email']
      ];

      $status = $this->SendEmailDriven($data_arr);
    }
    return $status;
  }



  public function deleteS3File(Request $request)
  {
    try {
      // https://oas36ty-files.s3.ap-south-1.amazonaws.com/email-files/wa1675422343.png
      $filePath = $request->data['attach_url'];
      $file_path = explode('.com/',$filePath);
      if (Storage::disk('s3')->exists($file_path[1])) {
        $check = Storage::disk('s3')->delete($file_path[1]);
        if($check) {
          $this->response['status'] = true;
          $this->response['status_code'] = 200;
          $this->response['message'] = "Attachment deleted successfully";
        } else {
          $this->response['status'] = true;
          $this->response['status_code'] = 201;
          $this->response['message'] = "Something went wrong";
        }
      } else {
        $this->response['status'] = true;
        $this->response['status_code'] = 201;
        $this->response['message'] = "Something went wrong";
      }

      // return true;
    } catch (Exception $ex) {
      $this->response['status'] = false;
      $this->response['status_code'] = 500;
      $this->response['data'] = $ex;
      $this->response['message'] = "Something went wrong";
    }
    return response()->json($this->response);
  }

  public function addAttachS3File(Request $request)
  {
    try {
      if ($request->data['attach']) {

        $base64String = $request->data['attach'];

        foreach ($base64String as $in => $file) {
          $slug = time(); //name prefix
          $avatar = $this->getFileName($file['file'], trim($file['name']), $in);

          Storage::disk('s3')->put('email-files/' . $avatar['name'],  base64_decode($avatar['file']), 'public');

          $url = Storage::disk('s3')->url('email-files/' . $avatar['name']);
          $attach[] = ['url' => $url ?? '', 'fileName' => $file['name'] ?? ''];
        }

        if ($attach) {
          $this->response['status'] = true;
          $this->response['status_code'] = 200;
          $this->response['data'] = $attach;
          $this->response['message'] = "Attachments uploaded successfully";
        } else {
          $this->response['status'] = true;
          $this->response['status_code'] = 201;
          $this->response['data'] = $attach;
          $this->response['message'] = "Something went wrong";
        }
      }
    } catch (Exception $ex) {
      $this->response['status'] = false;
      $this->response['status_code'] = 500;
      $this->response['data'] = $ex;
      $this->response['message'] = "Something went wrong";
    }
    return response()->json($this->response);
  }
}
