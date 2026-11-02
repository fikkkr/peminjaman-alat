@extends('layouts.app')
@section('title','Log Aktivitas')
@section('content')<section class="card"><table class="table"><thead><tr><th>Waktu</th><th>User</th><th>Aktivitas</th></tr></thead><tbody>@forelse($logAktivitas as $log)<tr><td>{{ $log->created_at?->format('d-m-Y H:i') }}</td><td>{{ $log->user?->name??'—' }}</td><td>{{ $log->aktivitas }}</td></tr>@empty<tr><td colspan="3">Belum ada aktivitas.</td></tr>@endforelse</tbody></table>{{ $logAktivitas->links() }}</section>@endsection
