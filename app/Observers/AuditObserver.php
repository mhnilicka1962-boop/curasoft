<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Universeller Observer — wird für alle sensitiven Modelle registriert.
 * Loggt automatisch create / update / delete Aktionen.
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        AuditLog::schreiben(
            aktion:       'erstellt',
            modellTyp:    class_basename($model),
            modellId:     $model->getKey(),
            beschreibung: $this->beschreibung($model, 'erstellt'),
            neueWerte:    $this->werte($model->getAttributes()),
        );
    }

    public function updated(Model $model): void
    {
        $geaendert = $model->getDirty();
        if (empty($geaendert)) return;

        $alt = array_intersect_key($model->getOriginal(), $geaendert);

        AuditLog::schreiben(
            aktion:       'geaendert',
            modellTyp:    class_basename($model),
            modellId:     $model->getKey(),
            beschreibung: $this->beschreibung($model, 'geändert'),
            alteWerte:    $this->werte($alt),
            neueWerte:    $this->werte($geaendert),
        );
    }

    public function deleted(Model $model): void
    {
        AuditLog::schreiben(
            aktion:       'geloescht',
            modellTyp:    class_basename($model),
            modellId:     $model->getKey(),
            beschreibung: $this->beschreibung($model, 'gelöscht'),
            alteWerte:    $this->werte($model->getAttributes()),
        );
    }

    private function beschreibung(Model $model, string $aktion): string
    {
        $name = match(true) {
            isset($model->nachname) => ($model->vorname ?? '') . ' ' . $model->nachname,
            isset($model->name)     => $model->name,
            isset($model->bezeichnung) => $model->bezeichnung,
            default                 => '#' . $model->getKey(),
        };

        return class_basename($model) . ' "' . trim($name) . '" ' . $aktion;
    }

    private function werte(array $attribute): array
    {
        return array_diff_key($attribute, array_flip(AuditLog::$ausgeblendet));
    }
}
