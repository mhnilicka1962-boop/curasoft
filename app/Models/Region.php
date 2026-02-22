<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regionen';

    protected $fillable = ['kuerzel', 'bezeichnung'];

    public function tarife()
    {
        return $this->hasMany(Leistungsregion::class, 'region_id');
    }
}
