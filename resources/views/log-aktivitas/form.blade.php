<h1>{{ isset($logAktivita) ? 'Edit' : 'Create' }} LogAktivitas</h1>
<form method="post" action="{{ isset($logAktivita) ? route('log-aktivitas.update', $logAktivita) : route('log-aktivitas.store') }}">
@csrf
@isset($logAktivita) @method('PUT') @endisset
    <label>User Id <input name="user_id" value="{{ old('user_id', $logAktivita->user_id ?? '') }}"></label>
    <label>Aktivitas <input name="aktivitas" value="{{ old('aktivitas', $logAktivita->aktivitas ?? '') }}"></label>
<button type="submit">Save</button></form>