<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatNachricht extends Model
{
    protected $table = 'chat_nachrichten';
    protected $fillable = ['chat_id', 'absender_id', 'inhalt', 'geloescht_am'];
    protected $casts = ['geloescht_am' => 'datetime'];

    public function absender()
    {
        return $this->belongsTo(Benutzer::class, 'absender_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
