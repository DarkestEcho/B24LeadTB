<?php

class KeyboardButton
{
    var $text;
    var $request_contact;
    var $request_location;

    function __construct($text, $request_contact = false, $request_location = false){
        $this->text = $text;
        $this->request_contact = $request_contact;
        $this->request_location = $request_location;
    }
}

