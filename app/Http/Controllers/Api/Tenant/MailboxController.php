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
        $result= Mailbox::all();

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
