<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTeilnehmer extends Model
{
    protected $table = 'chat_teilnehmer';
    protected $fillable = ['chat_id', 'benutzer_id'];

    public function benutzer()
    {
        return $this->belongsTo(Benutzer::class, 'benutzer_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
