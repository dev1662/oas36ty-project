<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use Illuminate\Http\Request;

class MailboxController extends Controller
{
    //
    
    public function fetchEmails()
    {
        // $result= Mailbox::all();
         $result = [
        'emails' => [
            [
                
                'id' => 1,

                // 'from' => [

                // ],
                'avatar' => "https://ui-avatars.com/api/?name=dev+sindhwani",
                'from_name' => 'Dev Sindhwani',
                "from_email" => "dev16sindh@gmail.com",
                "to_email" => ['devoas36ty@gmail.com'],
                'subject' => 'User-friendly value-added application 😊',
                'message' => '<p>Hey John,</p>

                <p>wellish laminable ineunt popshop catalyte prismatize campimetrical lentisk excluding portlet coccinellid impestation Bangash Lollardist perameloid procerebrum presume cashmerette washbasin nainsook Odontolcae Alea holcodont welted</p>
                
                <p>cibarious terrifical uploop naphthaleneacetic containable nonsailor Zwinglian blighty benchful guar porch fallectomy building coinvolve eidolism warmth unclericalize seismographic recongeal ethanethial clog regicidal regainment legific</p>',
                'attachments' => 7,
               
                    'label' => 'personal',
            
                'isStarred' => false,
            ],
            [
                'id' => 2,

                // 'from' => [

                // ],
                'avatar' => "https://ui-avatars.com/api/?name=abhishek+sindhwani",
                'from_name' => 'Abhishek Sindhwani',
                'subject' => 'User-friendly value-added application 😊',
                "from_email" => 'abhi99sindh@gmail.com',

                "to_email" =>['devoas36ty@gmail.com'],

                'message' => '<p>Hey John,</p>

                <p>wellish laminable ineunt popshop catalyte prismatize campimetrical lentisk excluding portlet coccinellid impestation Bangash Lollardist perameloid procerebrum presume cashmerette washbasin nainsook Odontolcae Alea holcodont welted</p>
                
                <p>cibarious terrifical uploop naphthaleneacetic containable nonsailor Zwinglian blighty benchful guar porch fallectomy building coinvolve eidolism warmth unclericalize seismographic recongeal ethanethial clog regicidal regainment legific</p>',
                'attachments' => 4,
                
                    'label' => 'company',

                'isStarred'=> false
            ],


        ],



    ];

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
