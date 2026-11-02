<h1>{{ isset($detailPeminjaman) ? 'Edit' : 'Create' }} DetailPeminjamans</h1>
<form method="post" action="{{ isset($detailPeminjaman) ? route('detail-peminjamans.update', $detailPeminjaman) : route('detail-peminjamans.store') }}">
@csrf
@isset($detailPeminjaman) @method('PUT') @endisset
    <label>Peminjaman Id <input name="peminjaman_id" value="{{ old('peminjaman_id', $detailPeminjaman->peminjaman_id ?? '') }}"></label>
    <label>Alat Id <input name="alat_id" value="{{ old('alat_id', $detailPeminjaman->alat_id ?? '') }}"></label>
    <label>Jumlah <input name="jumlah" value="{{ old('jumlah', $detailPeminjaman->jumlah ?? '') }}"></label>
    <label>Status <input name="status" value="{{ old('status', $detailPeminjaman->status ?? '') }}"></label>
<button type="submit">Save</button></form>