@extends('layouts.app')
@section('title', 'Debrief Guru')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-black">Panel Debrief Guru</h1>
    <div class="space-y-3">
        @forelse($reflections as $reflection)
        <div class="rounded-2xl border p-4 bg-white dark:bg-slate-900 flex justify-between gap-3 items-start">
            <div>
                <p class="font-bold">{{ $reflection->user->displayName() }}</p>
                <p class="text-sm text-slate-500">{{ $reflection->attempt->mission->title }}</p>
                <p class="text-sm mt-2">{{ Str::limit($reflection->understand, 120) }}</p>
            </div>
            <div class="text-right text-xs">
                <span class="px-2 py-1 rounded-full bg-slate-100">{{ $reflection->reviewed_at ? 'Reviewed' : ($reflection->confirmed ? 'Reflected' : 'Pending') }}</span>
                @if(!$reflection->reviewed_at)
                <form method="post" action="{{ route('jagat-misi.api.debrief.review', $reflection) }}" class="mt-2">
                    @csrf
                    <button class="text-primary font-bold">Tandai dibahas</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-500">Belum ada refleksi siswa.</p>
        @endforelse
    </div>
</div>
@endsection
