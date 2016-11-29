<?php
namespace App\Services;

class TelegramAPIService {

    private $bot_token;
    private $telegram_api_url;

    function __construct(){
        $this->bot_token=env('BOT_TOKEN');
        $this->telegram_api_url=env('TELEGRAM_API_URL');
    }

    private function call_bot($api_method,$curl_type='get',$params=[]){
        $bot_token=$this->bot_token;
        $telegram_api_url=$this->telegram_api_url;
        $curl_type=strtolower($curl_type);

        $new_url=$telegram_api_url.$bot_token.'/'.$api_method;

        $ch = curl_init($new_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($curl_type=='post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,true);
    }

    public function get_me(){
        $api_method='getMe';
        $response=$this->call_bot($api_method);

        return $response;
    }

    public function get_updates(){
        $api_method='getUpdates';
        $response=$this->call_bot($api_method);

        return $response;
    }

    public function send_message($chat_id='',$text='',$message_id=''){
        $api_method='sendMessage';

        if(!$chat_id && !$text)return die('chat_id and text is required');

        $params=array(
            'chat_id'=>$chat_id,
            'text'=>$text
        );

        if($message_id)$params['message_id']=$message_id;

        $response=$this->call_bot($api_method,$curl_type='post',$params);

        return $response;
    }

}
