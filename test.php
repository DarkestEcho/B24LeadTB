<?php

//phpinfo();

include_once "src/telegram/TeleBot.php";
include_once "src/telegram/bx24.php";
//
$token = "1561862662:AAFUZ_y_t4SHCnpSIwFNxRDIvKGZ1rONbjk";
//
$id = "1022217576";
//
$teleBot = new TeleBot($token);

define("B24_WEBHOOK", "https://b24-5efj6x.bitrix24.ru/rest/1/hvk5r2bpo1mcxa1z/");

$bx24 = new BX24(B24_WEBHOOK);


//$res = $bx24->crm_lead_add(
//    "Имя",
//    "Отчество",
//    "Фамилия",
//    3739988445,
//    "email@test.ru"
//);



$res = $bx24->crm_lead_update(60,true,
    array("Открытие", "ФИНАМ", "БКС", "ВТБ", "АльфаДирект"));


$array = json_decode(json_encode($res), true);

print_r($array);

//
////$options = array("first", "second", "third");
//
//$options = array("2", "3", "5");
//$options = json_encode($options);
//
//$res = $teleBot->sendPoll($id, 'Test quest', $options, true);
//
//$poll = ["option"=>["hi", "po"]];
//$mark = json_encode($poll);
//$text = "poll";
//$text = urlencode($text);
//$url = "https://api.telegram.org/bot$token/sendPoll?chat_id=$id&question=$text&options=$mark";
//echo $res;