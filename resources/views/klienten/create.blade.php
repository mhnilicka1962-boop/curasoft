<x-layouts.app :titel="'Neuer Klient'">

<div style="max-width: 720px;">
    <a href="{{ route('klienten.index') }}" class="text-klein link-gedaempt" style="display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 1.25rem;">
        ← Zurück zur Liste
    </a>

    <form method="POST" action="{{ route('klienten.store') }}">
        @csrf
        <div class="karte" style="margin-bottom: 1rem;">
            <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                <select name="region_id" class="feld" required style="width:auto; min-width:80px;">
                    <option value="">— Kanton —</option>
                    @foreach($regionen as $r)
                        <option value="{{ $r->id }}" {{ old('region_id') == $r->id ? 'selected' : '' }}>{{ $r->kuerzel }}</option>
                    @endforeach
                </select>
                <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.8125rem; font-weight:500; cursor:pointer;">
                    <input type="hidden" name="aktiv" value="0">
                    <input type="checkbox" name="aktiv" value="1" checked style="width:1rem; height:1rem;">
                    Aktiv
                </label>
                <div style="margin-left:auto;">
                    <button type="submit" class="btn btn-primaer">Klient anlegen</button>
                </div>
            </div>
        </div>
        @include('klienten._formular')
        <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primaer">Klient anlegen</button>
            <a href="{{ route('klienten.index') }}" class="btn btn-sekundaer">Abbrechen</a>
        </div>
    </form>
</div>

</x-layouts.app>
