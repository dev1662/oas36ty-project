<?php

namespace App\Jobs;

use App\Models\Mailbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestQueueRecieveEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $host = '{'.$this->data['mail_host'].':'.$this->data['mail_port'].'/'.$this->data['mail_transport'].'/'.$this->data['mail_encryption'].'}';
        // return $host;
        // / Your gmail credentials /
        $user = $this->data['mail_username'];
        $password = $this->data['mail_password'];
        
        // / Establish a IMAP connection /
        $conn = imap_open($host, $user, $password)
        
        or die('unable to connect Gmail: ' . imap_last_error());
        $mails = imap_search($conn, 'ALL');
        // / loop through each email id mails are available. /
        if ($mails) {
            rsort($mails);
            // / For each email /
            foreach ($mails as $email_number) {
                $headers = imap_fetch_overview($conn, $email_number, 0);
        
                // $structure = imap_fetchstructure($conn, $email_number);
        
                // $attachments = array();
        
                // /* if any attachments found... */
                // if(isset($structure->parts) && count($structure->parts)) 
                // {
                //     for($i = 0; $i < count($structure->parts); $i++) 
                //     {
                //         $attachments[$i] = array(
                //             'is_attachment' => false,
                //             'filename' => '',
                //             'name' => '',
                //             'attachment' => ''
                //         );
        
                //         if($structure->parts[$i]->ifdparameters) 
                //         {
                //             foreach($structure->parts[$i]->dparameters as $object) 
                //             {
                //                 if(strtolower($object->attribute) == 'filename') 
                //                 {
                //                     $attachments[$i]['is_attachment'] = true;
                //                     $attachments[$i]['filename'] = $object->value;
                //                 }
                //             }
                //         }
        
                //         if($structure->parts[$i]->ifparameters) 
                //         {
                //             foreach($structure->parts[$i]->parameters as $object) 
                //             {
                //                 if(strtolower($object->attribute) == 'name') 
                //                 {
                //                     $attachments[$i]['is_attachment'] = true;
                //                     $attachments[$i]['name'] = $object->value;
                //                 }
                //             }
                //         }
        
                //         if($attachments[$i]['is_attachment']) 
                //         {
                //             $attachments[$i]['attachment'] = imap_fetchbody($conn, $email_number, $i+1);
        
                //             /* 3 = BASE64 encoding */
                //             if($structure->parts[$i]->encoding == 3) 
                //             { 
                //                 $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                //             }
                //             /* 4 = QUOTED-PRINTABLE encoding */
                //             elseif($structure->parts[$i]->encoding == 4) 
                //             { 
                //                 $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                //             }
                //         }
                //     }
                // }
                // Log::info($attachments);
        
                // Log::info($headers);
                $message = imap_fetchbody($conn, $email_number, '1');
                $subMessage = substr($message, 0, 150);
                $finalMessage = trim(quoted_printable_decode($subMessage));
                // Log::info($finalMessage);die;
                $details_of_email = [];
                foreach($headers as $index => $header){
                    $details_of_email[$index] =[
                        'subject' => $header->subject,
                        'from_name' => $header->from,
                        'from_email' => $header->from,
                        'message_id' => $header->message_id,
                        'to_email' => $header->to,
                        'message' => $finalMessage,
                        'date' => $header->date,
                        'u_date' => $header->udate,
        
                    ];
                    
                   $insert= Mailbox::create($details_of_email[$index]);
                    
                    
                }
        
                // return;
            }// End foreach
        
        }//endif
        
  
        imap_close($conn);


    }
}
