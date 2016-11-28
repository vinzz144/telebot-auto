<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MessageModel;

class BotController extends Controller
{

    private function call_bot($api_method,$curl_type='get',$params=[]){

        $curl_type=strtolower($curl_type);

        $new_url=env('TELEGRAM_API_URL').env('BOT_TOKEN').'/'.$api_method;

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
        return $result;
    }

    public function get_me(){
        $api_method='getMe';
        $response=$this->call_bot($api_method);
        echo $response;
    }


    function pre($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function get_updates(){
        // echo '===========================';echo "<br>";
        // echo '== receiving new updates ==';echo "<br>";
        // echo '===========================';echo "<br>";

        $api_method='getUpdates';
        $response=$this->call_bot($api_method);
        $new_response=json_decode($response,true);
        // $this->pre($new_response);
        $new_data=0;

        if($new_response){
            foreach($new_response['result'] as $row){

                $check=MessageModel::where('update_id',$row['update_id'])->first();
                //echo $row['update_id'].'<br>';
                if(!$check){
                    //echo 'sess';
                    //$this->pre($check);
                    $this->save_to_db($row);
                    $new_data++;
                }
            }

            return $new_data;
            // echo '======= update saved =======';echo "<br>";
            // echo '============================';
        }
        else{
            return 0;
            // echo '====== no new updates ======';echo "<br>";
            // echo '============================';
        }
    }

    //
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
                // $this->pre($row->id);
                // exit();
                $this->do_reply($row);
            }
        }
    }


    private function generate_message($message,$username,$user_id){
        $message_to_send='';
        date_default_timezone_get('Asia/Jakarta');
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

            case '':
                $message_to_send='tulis pesan dong';
                break;

            case '/?':
                $message_to_send='mang ngapa?';
                break;

            default:
                $message_to_send = 'Terimakasih, pesan telah kami terima.';
                break;
        }

        return $message_to_send;
    }


    private function do_reply($data){
        $username=$data->first_name.' '.$data->last_name;
        $user_id=$data->from_id;
        $text=$data->text;
        $message_id=$data->message_id;
        $message=$data->text;

        $message_to_send=$this->generate_message($message,$username,$user_id);

        $api_method='sendMessage';
        $params=array(
            'chat_id'=>$data->chat_id,'text'=>$message_to_send,
            'reply_to_message_id' => $message_id
        );
        $response=$this->call_bot($api_method,$curl_type='post',$params);

        // $message_model=new MessageModel();
        // $message_model->find($data->id);
        // $message_model->replied=1;
        // $message_model->save();
        MessageModel::find($data->id)->update(['replied'=>1,'response'=>$response]);
    }

    public function auto_responder(){
        $new_data=$this->get_updates();

        if($new_data!=0)
        $this->reply_message();
    }
}
