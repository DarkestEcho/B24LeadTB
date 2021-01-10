<?php

class BX24{
    var $webhook;
    function __construct($webhook){
        $this->webhook = $webhook;
    }

    function crm_lead_add($name, $second_name, $last_name, $phone, $email){
        $data = array(
            "fields" => array(
                "TITLE" => "$last_name $name $second_name",
                "NAME" => $name,
                "SECOND_NAME" => $second_name,
                "LAST_NAME" => $last_name,
                "STATUS_ID" => "NEW",
                "PHONE" => array(array("VALUE" => $phone)),
                "EMAIL" => array(array("VALUE" => $email))
            ),
            'params'=> array(
                "REGISTER_SONET_EVENT"=> "Y"
            )
        );

        return $this->PostHTTPS("crm.lead.add", $data);
    }

    function crm_lead_update($id, $is_brok, array $brok_accounts = []){
        $data = array(
            "id"=>$id,
            "fields" => array(
                "UF_CRM_1610182533241"=> $is_brok? 44 : 46  //есть ли брокерский счет
            )
        );
        if(count($brok_accounts)>0){
            $val_brok = array(    // значения множественных полей в поле Брокеры
                "БКС"=>48,
                "Открытие"=>50,
                "ФИНАМ"=>52,
                "ВТБ"=>54,
                "АльфаДирект"=>56,
                "Другой брокер"=>58
            );
            $data["fields"]["UF_CRM_1610182747801"] = array();
            foreach ($brok_accounts as $value){
                array_push(
                    $data["fields"]["UF_CRM_1610182747801"],
                    $val_brok[$value]
                );
            }

        }
        return $this->PostHTTPS("crm.lead.update", $data);
    }

    function PostHTTPS($method, array $data)
    {
        $myCurl = curl_init();
        curl_setopt_array($myCurl, array(
            CURLOPT_URL => $this->webhook.$method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ));
        $response = curl_exec($myCurl);
        curl_close($myCurl);
        return json_decode($response);
    }
}
