<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageModel extends Model
{
    protected $table = 'message_tb';
    public $timestamps = true;
    protected $fillable = ['message_id','chat_type','update_id','chat_id','first_name','last_name','from_id','text','data','replied','response_data'];
}
