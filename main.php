<?php

include_once "src/telegram/TeleBot.php";
include_once "src/telegram/bx24.php";

// Telegram bot token
define("BOT_TOKEN", "1561862662:AAFUZ_y_t4SHCnpSIwFNxRDIvKGZ1rONbjk");

define("B24_WEBHOOK", "https://b24-5efj6x.bitrix24.ru/rest/1/hvk5r2bpo1mcxa1z/");

$teleBot = new TeleBot(BOT_TOKEN);

$result = $teleBot->getWebhookUpdates();

$text = array_key_exists("message", $result)?
    $result["message"]["text"] : $result["callback_query"]["data"];

$chat_id = array_key_exists("message", $result)?
    $result["message"]["chat"]["id"] : $result["callback_query"]["from"]["id"];


if($text == "/start"){
    $teleBot->sendMessage(
        $chat_id,
        "Добро пожаловать в бота!\n".
        "Для создания новой заявки Вам нужно последовательно ввести следующие данные:\n".
        "1. ФИО\n".
        "2. Номер телефона\n".
        "3. Email"
    );
    $teleBot->sendMessage(
        $chat_id,
        "Введите ФИО...\n(в одном сообщении в следующем порядке: \"Фамилия Имя Отчество\")"
    );
    // Сохрание данных пользователя в JSON
    $user_data = [
        'id' => $chat_id,
        'step' => 'fio'
    ];
    file_put_contents("user_data.txt", print_r($user_data, 1), 8);
    save_json($user_data, $chat_id);
}
else if(!file_exists("users/$chat_id.json")){
    $teleBot->sendMessage(
        $chat_id,
        "Для создания новой заявки введите команду: /start"
    );
}
else if (file_exists("users/$chat_id.json")){
    $json_data = file_get_contents("users/$chat_id.json");
    $user_data = json_decode($json_data, true);

    /*
     * Получение и обработка данных о ФИО пользователя
     */
    if($user_data['step'] == 'fio' || $user_data['step'] == 'edit_fio'){

        // Разделение строки ФИО на отдельные элементы массива
        $fio = explode(" ", $text);
        $count_fio = count($fio);

        $user_data['lastname'] = array_key_exists(0, $fio)? $fio[0] : '';
        $user_data['firstname'] = array_key_exists(1, $fio)? $fio[1] : '';
        $user_data['secondname'] = array_key_exists(2, $fio)? $fio[2] : '';

        if($user_data['step'] == 'fio') {
            $teleBot->sendMessage(
                $chat_id,
                "Записал.\nВведите номер телефона..."
            );

            // Перевод пользователя в стадию ожидания подтверждения ФИО
            $user_data['step'] = 'phone';
            save_json($user_data, $chat_id);
        }
        else {
            data_verification($user_data, $teleBot);
        }


    }
    /*
     * Получение и обработка данных о телефоне пользователя
     */
    else if($user_data['step'] == 'phone' || $user_data['step'] == 'edit_phone'){
        $phone = $text;
        if($phone[0]=='+')
            $phone = substr($phone, 1, strlen($phone)-1);
        if(!ctype_digit($phone)){
            $teleBot->sendMessage(
                $chat_id,
                "Номер введен неверно.\nВведите номер телефона..."
            );
        }
        else {
            $user_data['phone'] = '+'.$phone;

            if ($user_data['step'] == 'phone') {
                $user_data['step'] = 'email';
                $teleBot->sendMessage(
                    $chat_id,
                    "Записал.\nВведите email..."
                );
                save_json($user_data, $chat_id);
            } else {
                data_verification($user_data, $teleBot);
            }
        }

    }
    /*
     * Получение и обработка данных о почте пользователя
     */
    else if($user_data['step'] == 'email' || $user_data['step'] == 'edit_email'){
        $email = $text;
        if(!strpos($email, '@')){
            $teleBot->sendMessage(
                $chat_id,
                "Email введен неверно.\nВведите email..."
            );
        }
        else if(
            !strpos(substr($email, strpos($email, '@')),
            '.')){
            $teleBot->sendMessage(
                $chat_id,
                "Email введен неверно.\nВведите email..."
            );
        }
        else {
            $user_data['email'] = $email;

            if ($user_data['step'] == 'email') {
                $teleBot->sendMessage(
                    $chat_id,
                    "Записал."
                );
            }
            data_verification($user_data, $teleBot);
        }
    }
    /*
     * Проверка правильности ввода пользователем
     */
    else if($user_data['step'] == 'data_verification'){
        if($text == "Нет"){
            $inline_keyboard = new InlineKeyboardMarkup();
            $inline_keyboard->addButton('ФИО');
            $inline_keyboard->addButton('Номер телефона', true);
            $inline_keyboard->addButton('Email', true);
            $inline_keyboard->addButton('Заполнить заново', true);
            $inline_keyboard->addButton('Назад', true);
            $teleBot->sendMessage($chat_id, "Что нужно исправить?", $inline_keyboard);
            $user_data['step'] = 'edit';
            save_json($user_data, $chat_id);
        }
        // отправляю в битрикс
        else if($text == "Да"){
            $bx24 = new BX24(B24_WEBHOOK);

            $res = $bx24->crm_lead_add(
                $user_data['firstname'],
                $user_data['secondname'],
                $user_data['lastname'],
                $user_data['phone'],
                $user_data['email']
            );
            $res_ar = json_decode(json_encode($res), true);

            $user_data['lead_id'] = $res_ar['result'];

            $teleBot->sendMessage(
                $chat_id,
                "У Вас есть брокерский счет?",
                $teleBot->getYesNoInlineKeyboard());
            $user_data['step'] = 'first_question';
            save_json($user_data, $chat_id);
        }
    }
    /*
     * Обработка этапа выбора данных для редактирования
     */
    else if($user_data['step'] == 'edit'){
        if($text == 'ФИО'){
            $teleBot->sendMessage(
                $chat_id,
                "Введите ФИО...\n(в одном сообщении в следующем порядке: \"Фамилия Имя Отчество\")"
            );
            $user_data['step'] = 'edit_fio';

            $user_data = del_fio($user_data);

            save_json($user_data, $chat_id);
        }
        else if($text == 'Номер телефона'){
            $teleBot->sendMessage(
                $chat_id,
                "Введите номер телефона..."
            );
            $user_data['step'] = 'edit_phone';
            save_json($user_data, $chat_id);
        }
        else if($text == 'Email'){
            $teleBot->sendMessage(
                $chat_id,
                "Введите email..."
            );
            $user_data['step'] = 'edit_email';
            save_json($user_data, $chat_id);
        }
        else if($text == 'Заполнить заново'){
            $user_data = del_fio($user_data);
            unset($user_data['phone']);
            unset($user_data['email']);

            $teleBot->sendMessage(
                $chat_id,
                "Введите ФИО...\n(в одном сообщении в следующем порядке: \"Фамилия Имя Отчество\")"
            );
            $user_data['step'] = 'fio';

            save_json($user_data, $chat_id);
        }
        else if($text == 'Назад'){
            data_verification($user_data, $teleBot);
        }
    }
    /*
     * Обработка ответа на первый вопрос
     */
    else if($user_data['step'] == 'first_question'){
        if($text == 'Нет'){
            $teleBot->sendMessage(
                $chat_id,
                "Приглашение на конференцию будет Вам отправлено в Telegram, на почту, смс");
            $bx24 = new BX24(B24_WEBHOOK);
            $bx24->crm_lead_update(
                $user_data['lead_id'],
                false
            );
            unlink("users/$chat_id.json");
        }
        else if($text == 'Да'){
            $inline_keyboard = new InlineKeyboardMarkup();
            $inline_keyboard->addButton('БКС');
            $inline_keyboard->addButton('Открытие', true);
            $inline_keyboard->addButton('ФИНАМ', true);
            $inline_keyboard->addButton('ВТБ', true);
            $inline_keyboard->addButton('АльфаДирект', true);
            $inline_keyboard->addButton('Другой брокер', true);
            $inline_keyboard->addButton('Отправить', true);
            $teleBot->sendMessage(
                $chat_id,
                "У какого брокера Вы открывали счет?\n(Выберите необходимых и нажмите \"Отправить\")",
                $inline_keyboard
            );
            $user_data['step'] = 'second_question';
            save_json($user_data, $chat_id);
        }
    }
    else if($user_data['step'] == 'second_question'){
        if(in_array($text, array('БКС', 'Открытие', 'ФИНАМ', 'ВТБ', 'АльфаДирект', 'Другой брокер'))){
            if(!array_key_exists('brok_accounts', $user_data)) {
                $user_data['brok_accounts'] = array($text);
            }
            else if(!in_array($text, $user_data['brok_accounts'])){
                array_push($user_data['brok_accounts'], $text);
            }
            save_json($user_data, $chat_id);
        }
        else if($text == 'Отправить'){
            $teleBot->sendMessage(
                $chat_id,
                "Приглашение на конференцию будет Вам отправлено в Telegram, на почту, смс");
            // Отправка в битрикс
            $bx24 = new BX24(B24_WEBHOOK);

            $bx24->crm_lead_update(
                $user_data['lead_id'],
                true,
                array_key_exists('brok_accounts', $user_data)? $user_data['brok_accounts'] : array()
            );
            unlink("users/$chat_id.json");
        }
    }
}

function data_verification($user_data,$teleBot){
    $teleBot->sendMessage(
        $user_data['id'],
        "Данные:\n".
        "ФИО: $user_data[lastname] $user_data[firstname] $user_data[secondname]\n".
        "Номер телефона: $user_data[phone]\n".
        "Email: $user_data[email]\n\n".
        "Все верно?", $teleBot->getYesNoInlineKeyboard()
    );
    $user_data['step'] = 'data_verification';
    save_json($user_data, $user_data['id']);
}

function del_fio($user_data){
    unset($user_data['lastname']);
    unset($user_data['firstname']);
    unset($user_data['secondname']);

    return $user_data;
}

function save_json($data, $chat_id){
    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
    file_put_contents("users/$chat_id.json", $json_data);
}

