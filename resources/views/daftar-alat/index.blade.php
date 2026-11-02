@extends('layouts.app')
@section('title','Daftar Alat')
@section('content')<section class="card"><div class="table-wrap"><table class="table"><thead><tr><th>Nama Alat</th><th>Kategori</th><th>Stok</th></tr></thead><tbody>@foreach($alats as $alat)<tr><td>{{ $alat->nama_alat }}</td><td>{{ $alat->kategori?->nama_kategori }}</td><td><span class="badge {{ $alat->stok<=2?'badge-warning':'badge-success' }}">{{ $alat->stok }}</span></td></tr>@endforeach</tbody></table></div>{{ $alats->links() }}</section>@endsection
