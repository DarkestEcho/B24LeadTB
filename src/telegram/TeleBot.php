<?php

include_once "InlineKeyboardButton.php";
include_once "InlineKeyboardMarkup.php";
include_once "ReplyKeyboardMarkup.php";
include_once "KeyboardButton.php";

class TeleBot
{
    var $token;

    function __construct($token){
        $this->token = $token;
    }

    function getWebhookUpdates(){
        $result = json_decode(file_get_contents('php://input'));
        return json_decode(json_encode($result), true);
    }

    function sendMessage($chat_id, $text, $reply_markup = NULL){
        if($reply_markup!=NULL) {
            $message = [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => json_encode($reply_markup)];
        }
        else{
            $message = [
                'chat_id' => $chat_id,
                'text' => $text
            ];
        }

        return $this->PostHTTPS('/sendMessage', $message);
    }

    function sendPoll($chat_id, $question, $options, $allows_multiple_answers=false, $is_anonymous=true){
        $poll = [
            'chat_id'=>$chat_id,
            'question'=>$question,
            'options'=>$options,
            'allows_multiple_answers'=>$allows_multiple_answers,
            'is_anonymous'=>$is_anonymous
        ];
        return $this->PostHTTPS('/sendPoll', $poll);
    }

    function getYesNoInlineKeyboard() {
        $inline_keyboard = new InlineKeyboardMarkup();
        $inline_keyboard->addButton('Да');
        $inline_keyboard->addButton('Нет');
        return $inline_keyboard;
    }

    function PostHTTPS($method, array $message){
        $myCurl = curl_init();
        curl_setopt_array($myCurl, array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . $this->token . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($message)
        ));
        $response = curl_exec($myCurl);
        curl_close($myCurl);
        return $response;
    }

}