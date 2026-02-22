<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BexioSync extends Model
{
    protected $table = 'bexio_sync';
    protected $fillable = ['organisation_id', 'entity_typ', 'entity_id', 'bexio_id', 'letzter_sync', 'sync_status', 'fehler_meldung'];
    protected $casts = ['letzter_sync' => 'datetime'];

    public static function fuerEntity(string $typ, int $id): ?self
    {
        return self::where('entity_typ', $typ)->where('entity_id', $id)->latest()->first();
    }
}
