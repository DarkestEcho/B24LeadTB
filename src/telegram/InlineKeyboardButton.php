<?php


class InlineKeyboardButton
{
    var $text;
    var $callback_data;

    function __construct($text){
        $this->text = $text;
        $this->callback_data = $text;
    }
}