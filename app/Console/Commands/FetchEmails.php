<?php

namespace App\Console\Commands;

use App\Http\Resources\TenantResource;
use App\Models\CentralUser;
use App\Models\EmailInbound;
use App\Models\Mailbox;
use App\Models\MailboxAttachment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserEmail;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDO;
use Webklex\PHPIMAP\ClientManager;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Emails from mail server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function switchingDB($dbName)
    {
        Config::set("database.connections.mysql", [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
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
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = Tenant::select('id')->get();
        tenancy()->runForMultiple($tenants, function ($tenants) {
            $users = User::select('id')->get();
            foreach ($users as $user) {
                # code...
                $user_setting  = UserEmail::where('user_id', $user->id)->get();
                $details_inbound = [];
                $function = [];
                // return json_decode($request->currrent)->id;
                foreach ($user_setting as $index => $user_emails) {
                    // return $user_emails;
                    $details_inbound[$index] = EmailInbound::where('id', $user_emails->emails_setting_id)->first();
                }

                // return $details_inbound;
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

                            $this->info('connection not established');
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
                            // $inbox_messages = $inbox->messages()->all()->setFetchOrder("desc")->get();
                            if($inbox){
                            if ($check) {
                              try{
                                $totalMessages = $inbox->query()->all()->count();


                                if ($totalMessages) {
                                    UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                        'inbound_msg_count' => $totalMessages
                                    ]);
                                }

                                $inbox_messages = $inbox->messages()->all()->setFetchOrder("desc")->limit(20,1)->get() ?? []; //$inbox->query()->get();
                                // $inbox_messages = $inbox->messages()->all()->limit(20, $request->page)->get();//$inbox->query()->get();
                              }catch(Exception $e){
                                $inbox_messages = [];
                                continue;
                              }

                            } else {
                              try{
                                // $inbox = $client->getFolderByName('INBOX');
                                // $inbox_messages = $inbox->messages()->all()->setFetchOrder("desc")->get() ?? [];
                                $totalMessages = $inbox->query()->all()->count();

                                if ($totalMessages) {

                                    UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                        'inbound_msg_count' => $totalMessages
                                    ]);
                                }
                                $inbox_messages = $inbox->messages()->all()->setFetchOrder('desc')->get() ?? [];
                              }catch(Exception $ex){
                                $inbox_messages = [];
                                continue;
                              }
                            }
                          }
                            else{
                              $inbox_messages = [];
                            }

                            $sent = $client->getFolderByName('Sent Mail');
                            if($sent){
                            if($check1){
                              try{

                                $totalMessages = $sent->query()->all()->count();

                                if ($totalMessages) {

                                    UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                        'sent_msg_count' => $totalMessages
                                    ]);
                                }
                               
                              $sent_messages = $sent->messages()->all()->setFetchOrder("desc")->limit(10,1)->get() ?? [];//$sent->messages()->all()->limit(20, $request->page)->get();
                            }catch(Exception $ex){
                              $sent_messages = [];
                              continue;
                            }
                            
                            }else{
                              try{

                                $totalMessages = $sent->query()->all()->count();

                                if ($totalMessages) {

                                    UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                        'sent_msg_count' => $totalMessages
                                    ]);
                                }
                                // $sent = $client->getFolderByName('Sent Mail');
                                $sent_messages = $sent->messages()->all()->setFetchOrder("desc")->get() ?? [];
                              }catch(Exception $ex){
                                $sent_messages =[];
                                continue;
                              }
                              }
                            }else{
                              $sent_messages = [];
                            }

                            if($draft){
                              if($draft_check){
                                try{
                                  $totalMessages = $draft->query()->all()->count();

                                  if ($totalMessages) {
  
                                      UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                          'draft_msg_count' => $totalMessages
                                      ]);
                                  }

                              $draft_messages = $draft->messages()->all()->setFetchOrder("desc")->limit(10,1)->get() ?? [];//$sent->messages()->all()->limit(20, $request->page)->get();
                            }catch(Exception $ex){
                              $draft_messages = [];
                              continue;
                            }
                              }else{
                                try{
                                  $totalMessages = $draft->query()->all()->count();

                                  if ($totalMessages) {
  
                                      UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                          'draft_msg_count' => $totalMessages
                                      ]);
                                  }

                                $draft_messages = $draft->messages()->all()->setFetchOrder("desc")->get() ?? [];
                              }catch(Exception $ex){
                                $draft_messages = [];
                                continue;
                              }
                              }
                            }else{
                              $draft_messages = [];
                            }
                              if($trash){
                              if($trash_check){
                                try{
                                  $totalMessages = $trash->query()->all()->count();

                                  if ($totalMessages) {
  
                                      UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                          'trash_msg_count' => $totalMessages
                                      ]);
                                  }

                                $trash_messages = $trash->messages()->all()->setFetchOrder("desc")->limit(10,1)->get() ?? [];//$sent->messages()->all()->limit(20, $request->page)->get();
                              }catch(Exception $ex){
                                $trash_messages =[];
                                continue;
                              }
                                }else{
                                  try{
                                    $totalMessages = $trash->query()->all()->count();

                                    if ($totalMessages) {
    
                                        UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                            'trash_msg_count' => $totalMessages
                                        ]);
                                    }

                                  $trash_messages = $trash->messages()->all()->setFetchOrder("desc")->get() ?? [];
                                }catch(Exception $ex){
                                  $trash_messages =[];
                                  continue;
                                }
                                }
                              }else{
                                $trash_messages =[];
                              }

                              if($spam){
                              if($spam_check){
                                try{
                                  $totalMessages = $spam->query()->all()->count();

                                  if ($totalMessages) {
  
                                      UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                          'spam_msg_count' => $totalMessages
                                      ]);
                                  }

                                $spam_messages = $spam->messages()->all()->setFetchOrder("desc")->limit(10,1)->get() ?? [];//$sent->messages()->all()->limit(20, $request->page)->get();
                              }catch(Exception $ex){
                                $spam_messages =[];
                                continue;
                              }
                                }else{
                                  try{
                                    $totalMessages = $spam->query()->all()->count();

                                    if ($totalMessages) {
    
                                        UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $data->id])->update([
                                            'spam_msg_count' => $totalMessages
                                        ]);
                                    }

                                  $spam_messages = $spam->messages()->all()->setFetchOrder("desc")->get() ?? [];
                                }catch(Exception $ex){
                                  $spam_messages =[];
                                  continue;
                                }
                                }
                              }else{
                                $spam_messages =[];
                              }

                              $code_inbox= 0;
                                foreach ($inbox_messages as $n => $oMessage) {
                                    // $reply[]=$oMessage->cc;
                                    // $oMessage->setFlag(['Seen', 'Flagged']);  
                                    // $oMessage->peek();       
                                    $message ='';
                                    $subject = $oMessage->subject ?? '';
                                    $from_email = $oMessage->sender[0]->mail ?? '';
                                    $from_name = $oMessage->sender ?? '';
                                    $message_id = $oMessage->message_id ?? '';
                                    $to_email = $oMessage->to ?? '';
                                    $references = str_replace('<','',$oMessage->references) ?? '';
                                    $references = str_replace('>',',', $references) ?? '';
                                    $references = explode(',',$references);
                                    $in_reply_to  = str_replace('<','',$oMessage->in_reply_to) ?? '';
                                    $in_reply_to = str_replace('>','',$in_reply_to) ?? '';
                                    $original_ref1 = $oMessage->references;
                                    $original_ref = $original_ref1[0] ?? '';
                                    $u_date = $oMessage->t ?? '';
                                    $date = $oMessage->date ?? '';
                                    $ccaddress = $oMessage->cc ?? '';
                                    $bccaddress = $oMessage->bcc ?? '';
                                    // if($oMessage->hasHTMLBody()){
                                    //     // return "htmlbody";
                                    //     $message = $oMessage->getHTMLBody(true);
                                    //   }
                                    //    elseif($oMessage->hasTextBody()){
                                    //       // return "textbody";
                                    //         $message =$oMessage->getTextBody();
                                    //       }else{
                                    //     $message =$oMessage->getBodies();
                                    //     // return "getbody ". $message;
                                    //   }
                                   $message = $oMessage->getHTMLBody();
                                   if(!$message){
                                    $message = $oMessage->getHTMLBody(true);
                                    $message = $message;
                                   }
                                    $attachments = $oMessage->getAttachments()->count();
                                    //  return $oMessage->getXFailedRecipients();
                                    // return $oMessage;
                                    // return $oMessage->getBodies();
                                    
                                    $check_email = Mailbox::where(['message_id'=> $message_id,'folder' => 'INBOX'])->first();
                                    // $check_email = "";
                                    // return $check_email;
                                    if (!$check_email) {
                                      //  return "h";

                                       //--------------------------------------- Download Attachments of messages ----------------------------
                                     $attachments_file = $oMessage->getAttachments();

                                      $is_parent = null;
                                      if($in_reply_to){
                                      // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                                      $check_parent = Mailbox::where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])
                                      ->where(function($query) use ($in_reply_to,$references){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('message_id','LIKE','%'.$references[0].'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')->orWhere('references','LIKE','%'.$references[0].'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$references[0].'%');
                                           })->first();

                                      if(!empty($check_parent)){
                                        $is_parent = 0;
                                      $update =  Mailbox::where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])
                                        ->where('is_parent',1)
                                      ->where(function($query) use ($in_reply_to){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%');
                                           })->first();
                                      if($update){
                                        if($update->u_date < strtotime($date)){
                                          Mailbox::where('id',$update->id)->update(['u_date'=>strtotime($date)]);
                                        }
                                      }
                                      }else{
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
                                        'references'=> $original_ref ?? '',
                                        'in_reply_to' => $in_reply_to ?? '',
                                        'attachments'=> $attachments ?? 0,
                                        'is_parent'=> $is_parent ?? 1,
                                        'ccaddress' =>$ccaddress ?? '',
                                        'bccaddress' => $bccaddress ?? '',
                                        'to_replyEmails'=>$to_email ?? ''
                                        //    'recent' => $header->recent,
                                        
                                      ];
                                      // return $details_of_email[$n];
                                      //  return $attachments;
                                      try {
                               if($insert_file = Mailbox::create($details_of_email)){
                                          
                                if($attachments_file){
                                  foreach($attachments_file as $key => $attach){
                                    // $attach_files[$key] = $attach_file->name ?? '';
    
                                    $masked = $attach->setMask(AttachmentMask::class);
                                    $temp = [];
                                    $temp['mask'] = $masked->mask();
    
                                    $filebase64 = $temp['mask']->getImageSrc();
                                    // $filebase64 = str_replace('"','',$filebase64);
                                    // $filebase64 = explode('base64,',$filebase64);
                                    // $temp['file'] 
                                    $file = $filebase64;
                                    // $temp['name'] 
                                    $name = $temp['mask']->getName();
                                    // $temp['disposition'] = $temp['mask']->getDisposition();
                                    $temp['size'] = $temp['mask']->getSize();
                                  //array_push()
                                    if($file && $name)
                                  {
                                  $avatar = $this->getFileName($file, trim($name), null);
                                  try{
                                    
                                    Storage::disk('s3')->put('inbox-email-files/' . $avatar['name'] ,  base64_decode($avatar['file']), 'public');
                                    
                                    $url = Storage::disk('s3')->url('inbox-email-files/' . $avatar['name']);
                                    
                                    $insert_arr = [
                                      'mailbox_id' => $insert_file->id ?? '',
                                      'attachment_url' => $url ?? '',
                                      'attachment_name' => $name ?? '',
                                      'folder' => $inbox->name ?? ''
                                    ];
                                    $check = MailboxAttachment::create($insert_arr);
  
                                  if(!$check){
                                    continue;
                                  }
                                  
                                }catch(Exception $e){
                                  continue;
                                }
                                  }
                                  
    
                                  }
                             
                                }
                                          $code_inbox = 200;
                                        }
                                        
                                      } catch (Exception $ex) {
                                        Log::info("======= While inserting new email message : ".$ex." ==========");
                                        continue;
                                      }
                                    }
                                  }
                                  if($code_inbox == 200){
                                    $this->info('Messages Fetched');
                                  }
                                  $code_inbox = 0;
                                 
                                  foreach ($sent_messages as $n => $oMessage) {
                                    
                                    $attachments = $oMessage->getAttachments()->count() ?? '';
                                    $subject = $oMessage->subject ?? '';
                                    $from_email = $oMessage->sender[0]->mail ?? '';
                                    $from_name = $oMessage->sender ?? '';
                                    $message_id = $oMessage->message_id ?? '';
                                    $to_email = $oMessage->to ?? '';
                                    $u_date = $oMessage->t ?? '';
                                    $date = $oMessage->date ?? '';
                                    
                                    $references = str_replace('<','',$oMessage->references) ?? '';
                                    $references = str_replace('>',',', $references) ?? '';
                                    $references = explode(',',$references);
                                    $in_reply_to  = str_replace('<','',$oMessage->in_reply_to) ?? '';
                                    $in_reply_to = str_replace('>','',$in_reply_to) ?? '';

                                    $original_ref1 = $oMessage->references;
                                    $original_ref = $original_ref1[0] ?? '';
                                    $ccaddress = $oMessage->cc ?? '';
                                    $bccaddress = $oMessage->bcc ?? '';
                                    if($oMessage->hasTextBody()){
                                      $message =$oMessage->getTextBody();
                                    }
                                    if($oMessage->hasHTMLBody()){
                                      $message = $oMessage->getHTMLBody(true);
                                    }
                                   
                                    
                                    // return $oMessage->getHeader();
                          
                          
                                    $check_email = Mailbox::where(['message_id'=> $message_id, 'folder' => 'Sent Mail'])->first();
                                    // $check_email = "";
                                    // return $check_email;
                                    if (!$check_email) {
                                     
                                      $attachments_file =[];
                                      $attachments_file = $oMessage->getAttachments();
        
                                      //  return "h";
                          
                                      $is_parent = null;
                                      if($in_reply_to){
                                      // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                                      $check_parent = Mailbox::where(['from_email'=>$from_email, 'folder'=>$sent->name])
                                      ->where(function($query) use ($in_reply_to,$references){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('message_id','LIKE','%'.$references[0].'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')->orWhere('references','LIKE','%'.$references[0].'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$references[0].'%');
                                           })->first();

                                      if(!empty($check_parent)){
                                        $is_parent = 0;
                                      $update =  Mailbox::where(['from_email'=>$from_email, 'folder'=>$sent->name])
                                        ->where('is_parent',1)
                                      ->where(function($query) use ($in_reply_to){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%');
                                           })->first();
                                      if($update){
                                        if($update->u_date < strtotime($date)){
                                          Mailbox::where('id',$update->id)->update(['u_date'=>strtotime($date)]);
                                        }
                                      }
                                      }else{
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
                                        'attachments'=> $attachments ?? 0,
                                        'folder' => $sent->name,

                                        'references'=> $original_ref ?? '',
                                        'in_reply_to' => $in_reply_to ?? '',
                                        'is_parent'=> $is_parent ?? 1,
                                        'ccaddress' =>$ccaddress ?? '',
                                        'bccaddress' => $bccaddress ?? '',
                                        'to_replyEmails'=>$to_email ?? ''
                                        //    'recent' => $header->recent,
                          
                                      ];
                                      // return $details_of_email[$n];
                                      //  return $attachments;
                                      try {
                                        $insert_file = [];
                                        $insert_file = Mailbox::create($details_of_email);
                                        
                                        if($attachments_file){
                                          foreach($attachments_file as $key => $attach){
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
                                            if($temp){
                                              $avatar = $this->getFileName2($temp['file'], trim($temp['name']), null);
                                              try{
                                              
                                            Storage::disk('s3')->put('inbox-email-files/' . $avatar['name'] ,  base64_decode($avatar['file']), 'public');
                                            
                                            $url = Storage::disk('s3')->url('inbox-email-files/' . $avatar['name']);
                                            
                                            $insert_arr = [
                                              'mailbox_id' => $insert_file->id ?? '',
                                              'attachment_url' => $url ?? '',
                                              'attachment_name' => $temp['name'] ?? '',
                                              'folder' => $sent->name ?? ''
                                            ];
                                            $check = MailboxAttachment::create($insert_arr);
                                            // return $check;
                                            // $this->info($check->folder);
          
                                          if(!$check){
                                            continue;
                                          }
                                          
                                        }catch(Exception $e){
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
                                    $to_email = $oMessage->to ?? '';
                                    $u_date = $oMessage->t ?? '';
                                    $date = $oMessage->date ?? '';
                                    $references = str_replace('<','',$oMessage->references) ?? '';
                                    $references = str_replace('>',',', $references) ?? '';
                                    $references = explode(',',$references);
                                    $in_reply_to  = str_replace('<','',$oMessage->in_reply_to) ?? '';
                                    $in_reply_to = str_replace('>','',$in_reply_to) ?? '';

                                    $original_ref1 = $oMessage->references;
                                    $original_ref = $original_ref1[0] ?? '';
                                    $ccaddress = $oMessage->cc ?? '';
                                    $bccaddress = $oMessage->bcc ?? '';

                                    if($oMessage->hasTextBody()){
                                      $message =$oMessage->getTextBody();
                                    }
                                    if($oMessage->hasHTMLBody()){
                                      $message = $oMessage->getHTMLBody(true);
                                    }
                                   
                                    
                                    // return $oMessage->getHeader();
                          
                          
                                    $check_email = Mailbox::where(['message_id'=> $message_id, 'folder'=>'Drafts'])->first();
                                    // $check_email = "";
                                    // return $check_email;
                                    if (!$check_email) {
                                      //  return "h";
                                      $is_parent = null;
                                      if($in_reply_to){
                                      // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                                      $check_parent =  Mailbox::where(['from_email'=>$from_email, 'folder'=>$draft->name])
                                      ->where(function($query) use ($in_reply_to,$references){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('message_id','LIKE','%'.$references[0].'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')->orWhere('references','LIKE','%'.$references[0].'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$references[0].'%');
                                           })->first();

                                      if(!empty($check_parent)){
                                        $is_parent = 0;
                                      $update =  Mailbox::where(['from_email'=>$from_email, 'folder'=>$draft->name])
                                        ->where('is_parent',1)
                                      ->where(function($query) use ($in_reply_to){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%');
                                           })->first();
                                      if($update){
                                        if($update->u_date < strtotime($date)){
                                          Mailbox::where('id',$update->id)->update(['u_date'=>strtotime($date)]);
                                        }
                                      }
                                      }else{
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
                                        'attachments'=> $attachments ?? 0,
                                        'references'=> $original_ref ?? '',
                                        'in_reply_to' => $in_reply_to ?? '',
                                        'folder' => $draft->name,
                                        'is_parent'=> $is_parent ?? 1,
                                        'ccaddress' =>$ccaddress ?? '',
                                        'bccaddress' => $bccaddress ?? '',
                                        'to_replyEmails'=>$to_email ?? ''
                                        //    'recent' => $header->recent,
                          
                                      ];
                                      // return $details_of_email[$n];
                                      //  return $attachments;
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
                                    $to_email = $oMessage->to ?? '';
                                    $u_date = $oMessage->t ?? '';
                                    $date = $oMessage->date ?? '';
                                    $references = str_replace('<','',$oMessage->references) ?? '';
                                    $references = str_replace('>',',', $references) ?? '';
                                    $references = explode(',',$references);
                                    $in_reply_to  = str_replace('<','',$oMessage->in_reply_to) ?? '';
                                    $in_reply_to = str_replace('>','',$in_reply_to) ?? '';

                                    $original_ref1 = $oMessage->references;
                                    $original_ref = $original_ref1[0] ?? '';
                                    $ccaddress = $oMessage->cc ?? '';
                                    $bccaddress = $oMessage->bcc ?? '';

                                    if($oMessage->hasTextBody()){
                                      $message =$oMessage->getTextBody();
                                    }
                                    if($oMessage->hasHTMLBody()){
                                      $message = $oMessage->getHTMLBody(true);
                                    }
                                   
                                    
                                    // return $oMessage->getHeader();
                          
                          
                                    $check_email = Mailbox::where(['message_id'=> $message_id, 'folder' => 'Spam'])->first();
                                    // $check_email = "";
                                    // return $check_email;
                                    if (!$check_email) {
                                      //  return "h";

                                      $is_parent = null;
                                      if($in_reply_to){
                                      // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                                      $check_parent =  Mailbox::where(['to_email' => $to_email, 'folder'=>$spam->name])
                                      ->where(function($query) use ($in_reply_to,$references){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('message_id','LIKE','%'.$references[0].'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')->orWhere('references','LIKE','%'.$references[0].'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$references[0].'%');
                                           })->first();

                                      if(!empty($check_parent)){
                                        $is_parent = 0;
                                      $update =  Mailbox::where(['to_email' => $to_email, 'folder'=>$spam->name])
                                        ->where('is_parent',1)
                                      ->where(function($query) use ($in_reply_to){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%');
                                           })->first();
                                      if($update){
                                        if($update->u_date < strtotime($date)){
                                          Mailbox::where('id',$update->id)->update(['u_date'=>strtotime($date)]);
                                        }
                                      }
                                      }else{
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
                                        'attachments'=> $attachments ?? 0,
                                        'references'=> $original_ref ?? '',
                                        'in_reply_to' => $in_reply_to[0] ?? '',
                                        'folder' => $spam->name,
                                        'is_parent' => $is_parent ?? 1
                                        //    'recent' => $header->recent,
                          
                                      ];
                                      // return $details_of_email[$n];
                                      //  return $attachments;
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
                                    $to_email = $oMessage->to ?? '';
                                    $u_date = $oMessage->t ?? '';
                                    
                                    $date = $oMessage->date ?? '';
                                    $references = str_replace('<','',$oMessage->references) ?? '';
                                    $references = str_replace('>',',', $references) ?? '';
                                    $references = explode(',',$references);
                                    $in_reply_to  = str_replace('<','',$oMessage->in_reply_to) ?? '';
                                    $in_reply_to = str_replace('>','',$in_reply_to) ?? '';
                                    $original_ref1 = $oMessage->references;
                                    $original_ref = $original_ref1[0] ?? '';
                                    $ccaddress = $oMessage->cc ?? '';
                                    $bccaddress = $oMessage->bcc ?? '';
                                    if($oMessage->hasTextBody()){
                                      $message =$oMessage->getTextBody();
                                    }
                                    if($oMessage->hasHTMLBody()){
                                      $message = $oMessage->getHTMLBody(true);
                                    }
                                   
                                    
                                    // return $oMessage->getHeader();
                          
                          
                                    $check_email = Mailbox::where(['message_id'=> $message_id, 'folder' => 'Trash'])->first();
                                    // $check_email = "";
                                    // return $check_email;
                                    if (!$check_email) {
                                      //  return "h";
                                      $is_parent = null;
                                      if($in_reply_to){
                                      // $check_parent = Mailbox::where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$in_reply_to.'%')->where(['to_email'=>$data->mail_username, 'folder'=>$inbox->name])->first();

                                      $check_parent =  Mailbox::where(['folder'=>$trash->name])
                                      ->where(function($query) use ($to_email,$from_email){
                                        $query->where(['to_email' => $to_email])
                                        ->orWhere('from_email',$from_email);
                                      })
                                      ->where(function($query) use ($in_reply_to,$references){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')->orWhere('message_id','LIKE','%'.$references[0].'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')->orWhere('references','LIKE','%'.$references[0].'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%')->orWhere('in_reply_to','LIKE','%'.$references[0].'%');
                                           })->first();

                                      if(!empty($check_parent)){
                                        $is_parent = 0;
                                      $update =  Mailbox::where(['to_email' => $to_email, 'folder'=>$trash->name])
                                        ->where('is_parent',1)
                                      ->where(function($query) use ($in_reply_to){
                                        $query->where('message_id','LIKE','%'.$in_reply_to.'%')
                                        ->orWhere('references', 'LIKE', '%'.$in_reply_to.'%')
                                        ->orWhere('in_reply_to', 'LIKE', '%'.$in_reply_to.'%');
                                           })->first();
                                      if($update){
                                        if($update->u_date < strtotime($date)){
                                          Mailbox::where('id',$update->id)->update(['u_date'=>strtotime($date)]);
                                        }
                                      }
                                      }else{
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
                                        'attachments'=> $attachments ?? 0,
                                        'references'=> $original_ref ?? '',
                                        'in_reply_to' => $in_reply_to ?? '',
                                        'folder' => $trash->name,
                                        'is_parent'=> $is_parent ?? 1,
                                        'ccaddress' =>$ccaddress ?? '',
                                        'bccaddress' => $bccaddress ?? '',
                                        'to_replyEmails'=>$to_email ?? ''
                                        //    'recent' => $header->recent,
                          
                                      ];
                                      // return $details_of_email[$n];
                                      //  return $attachments;
                                      try {
                                        $insert = Mailbox::create($details_of_email);
                                      
                                      } catch (Exception $ex) {
                                        continue;
                                      }
                                    }
                                  }
                        }

                        //   if($imap_array){

                        //       $this->info(response()->json($imap_array));
                        //     }
                    }
                }
            }
        });
    }

    private function getFileName($image, $name, $index)
    {
        list($type, $file) = explode(';', $image);
        list(, $extension) = explode('/', $type);
        list(, $file) = explode(',', $file);
        // $result['name'] = 'oas36ty'.now()->timestamp . '.' . $extension;
        $result['name'] = now()->timestamp.$name ;//str_replace(' ', '',explode('.', $name)[0]). now()->timestamp.'.'. $extension;
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
        $result['name'] = now()->timestamp.$name ;//str_replace(' ', '',explode('.', $name)[0]). now()->timestamp.'.'. $extension;
        // $result['data'] = ;
        $result['file'] = $file;
        return $result;
    }


}
