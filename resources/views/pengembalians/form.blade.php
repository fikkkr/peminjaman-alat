<h1>{{ isset($pengembalian) ? 'Edit' : 'Create' }} Pengembalians</h1>
<form method="post" action="{{ isset($pengembalian) ? route('pengembalians.update', $pengembalian) : route('pengembalians.store') }}">
@csrf
@isset($pengembalian) @method('PUT') @endisset
    <label>Peminjaman Id <input name="peminjaman_id" value="{{ old('peminjaman_id', $pengembalian->peminjaman_id ?? '') }}"></label>
    <label>Catatan <input name="catatan" value="{{ old('catatan', $pengembalian->catatan ?? '') }}"></label>
<button type="submit">Save</button></form>