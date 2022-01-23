<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Mail;

class MailController extends Controller
{
    public function basic_email()
    {
        $data = array('name' => "Virat Gandhi");
        Mail::send(['text' => 'mail'], $data, function ($message) {
            $message->to('abc@gmail.com', 'Tutorials Point')->subject
                ('Laravel Basic Testing Mail');
            $message->from('xyz@gmail.com', 'Virat Gandhi');
        });
        dd("bruh");
        echo "Basic Email Sent. Check your inbox.";
    }
    public function html_email()
    {
        $data = array('name' => "Virat Gandhi");
        Mail::send('mail', $data, function ($message) {
            $message->to('abc@gmail.com', 'Tutorials Point')->subject
                ('Laravel HTML Testing Mail');
            $message->from('xyz@gmail.com', 'Virat Gandhi');
        });
        echo "HTML Email Sent. Check your inbox.";
    }
    public function attachment_email()
    {
        $data = array('name' => "Virat Gandhi");
        Mail::send('mail', $data, function ($message) {
            $message->to('abc@gmail.com', 'Tutorials Point')->subject
                ('Laravel Testing Mail with Attachment');
            $message->from('xyz@gmail.com', 'Virat Gandhi');
        });
        echo "Email Sent with attachment. Check your inbox.";
    }
}
