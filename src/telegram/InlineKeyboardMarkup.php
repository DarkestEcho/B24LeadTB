<?php

class InlineKeyboardMarkup
{
    var $inline_keyboard;
    var $level = 0;

    function __construct(){}

    function addButton($text, $next = false){
        $button = new InlineKeyboardButton($text);
        if ($next)
            $this->level++;
        $this->inline_keyboard[$this->level][] = $button;
    }
}