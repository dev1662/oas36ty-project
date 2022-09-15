<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MailboxController extends Controller
{
    //
    public $result = [
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
                'labels' => [
                    'label' => 'personal',
                ],
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
                'labels' => [
                    'label' => 'company',
                ],
                'isStarred'=> false
            ],


        ],



    ];
    public function fetchEmails()
    {

        $meta = [
            'emailsMeta' => count($this->result['emails'])
        ];
        if($_GET['folder'] === 'inbox'){

            $this->response['status'] = true;
            $this->response['message'] = 'data fetched';
            $this->response['data'] = $this->result;
            $this->response['meta'] = $meta;
            return response()->json($this->response);
        }
        if($_GET['folder'] === 'starred'){
            $this->result['emails'][0]['isStarred'] = true;
            $this->response['status'] = true;
            $this->response['message'] = 'data fetched';
            $this->response['data'] = $this->result;
            $this->response['meta'] = $meta;
            return response()->json($this->response);
        }
    }
    public function updateEmails()
    {
        return;
    }
}
