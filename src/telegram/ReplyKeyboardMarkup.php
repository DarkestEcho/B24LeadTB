<?php

class ReplyKeyboardMarkup
{
    var $resize_keyboard;
    var $one_time_keyboard;
    var $keyboard;
    var $level = 0;

    function __construct($resize_keyboard = false, $one_time_keyboard = false){
        $this->resize_keyboard = $resize_keyboard;
        $this->one_time_keyboard = $one_time_keyboard;
    }

    function addButton($text,
                       $next = false,
                       $request_contact = false,
                       $request_location = false){
        $button = new KeyboardButton($text, $request_contact, $request_location);
        if ($next)
            $this->level++;
        $this->keyboard[$this->level][] = $button;
    }
}