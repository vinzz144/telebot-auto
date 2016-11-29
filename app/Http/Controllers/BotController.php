<?php

namespace App\Http\Controllers;

use App\Models\MessageModel;
use App\Services\TelegramAPIService;

class BotController extends Controller
{
    private $telegramAPIService;
    private $message_model;

    function __construct(){
        $this->telegramAPIService=new TelegramAPIService();
        $this->message_model=new MessageModel();
    }

    public function get_me(){
        $this->pre($this->telegramAPIService->get_me());
    }

    private function pre($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function get_updates(){
        $response=$this->telegramAPIService->get_updates();
        $new_response=$response;
        $new_data=0;

        if($new_response){
            foreach($new_response['result'] as $row){

                $check=MessageModel::where('update_id',$row['update_id'])->first();
                if(!$check){
                    if(isset($row['message']['entities'])){
                        $this->save_to_db($row);
                        $new_data++;
                    }
                }
            }
            return $new_data;
        }
        else{
            return 0;
        }
    }

    private function save_to_db($data){
        $message_model=new MessageModel();
        $message_model->update_id=$data['update_id'];
        $message_model->data=json_encode($data);
        $message_model->replied=0;
        $message_model->from_id=isset($data['message']['from']['id'])?$data['message']['from']['id']:'';
        $message_model->first_name=isset($data['message']['from']['first_name'])?$data['message']['from']['first_name']:'';
        $message_model->last_name=isset($data['message']['from']['last_name'])?$data['message']['from']['last_name']:'';
        $message_model->text=isset($data['message']['text'])?$data['message']['text']:'';
        $message_model->chat_id=isset($data['message']['chat']['id'])?$data['message']['chat']['id']:'';
        $message_model->message_id=isset($data['message']['message_id'])?$data['message']['message_id']:'';
        $message_model->chat_type=isset($data['message']['chat']['type'])?$data['message']['chat']['type']:'private';
        $message_model->response_data='';
        $message_model->save();
    }

    public function reply_message(){
        $message_model=new MessageModel();
        $unreplied_list=$message_model->where('replied',0)->get();

        if($unreplied_list){
            foreach($unreplied_list as $row){
                $this->do_reply($row);
            }
        }
    }

    private function generate_message($message,$username,$user_id){
        date_default_timezone_set('Asia/Jakarta');
        $bot_username=env('BOT_USERNAME');

        switch($message){
            case '/sleep':
            case '/sleep'.$bot_username:
                $message_to_send='beymex lagi bobo';
                break;

            case '/hug':
            case '/hug'.$bot_username:
                $message_to_send='sini beymex peluk';
                break;

            case '/time':
            case '/time'.$bot_username :
                $message_to_send  = "$username, waktu lokal bot sekarang adalah :\n";
                $message_to_send .= date("d M Y")."\nPukul ".date("H:i:s");
                break;

            case '/id':
            case '/id'.$bot_username:
                $message_to_send=$username.' id kamu adalah '.$user_id;
                break;

            case '/run':
            case '/run'.$bot_username:
                $message_to_send='ayo kita lari';
                break;

            case '/sit':
            case '/sit'.$bot_username:
                $message_to_send='ayo kita duduk';
                break;

            case '/help':
            case '/help'.$bot_username:
                $message_to_send="minta tolong nih yeee. wkwkwkwk.\n";
                $message_to_send.="available commands: /sleep /hug /time /id /run /sit /hand /growl /start";
                break;

            case '/hand':
            case '/hand'.$bot_username:
                $message_to_send='ayo ulurkan tangan';
                break;

            case '/growl':
            case '/growl'.$bot_username:
                $message_to_send='ayo kita growl';
                break;

            case '/start':
            case '/start'.$bot_username:
                $message_to_send='ayo kita mulai ngobrol';
                break;

            case '/?':
                $message_to_send='mang ngapa?';
                break;

            default:
                $message_to_send='';
                break;
        }

        return $message_to_send;
    }

    private function do_reply($data){
        $username=$data->first_name.' '.$data->last_name;
        $user_id=$data->from_id;
        $message_id=$data->message_id;
        $message=$data->text;

        $message_to_send=$this->generate_message($message,$username,$user_id);

        if($message_to_send!=''){
            $response=$this->telegramAPIService->send_message($data['chat_id'],$message_to_send,$message_id);
            $response=json_encode($response);
        }else{
            $response='no message';
        }

        $this->message_model->find($data->id)->update(['response_data'=>$response,'replied'=>1]);
    }

    public function auto_responder(){
        $new_data=$this->get_updates();

        if($new_data!=0)
        $this->reply_message();
    }
}
