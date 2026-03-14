<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $fillable = ['organisation_id', 'typ', 'name'];

    public function teilnehmer()
    {
        return $this->hasMany(ChatTeilnehmer::class);
    }

    public function nachrichten()
    {
        return $this->hasMany(ChatNachricht::class);
    }

    public function hatTeilnehmer(int $benutzerId): bool
    {
        return $this->teilnehmer()->where('benutzer_id', $benutzerId)->exists();
    }
}
