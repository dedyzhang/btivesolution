@extends('layouts.app')
@section('title', 'Debrief — ' . $attempt->mission->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <a href="{{ route('jagat-misi.index') }}" class="text-xs text-slate-500">← Katalog Misi</a>
    <div class="rounded-2xl border p-5 bg-white dark:bg-slate-900">
        <p class="text-xs text-slate-500">Misi selesai — skor {{ $attempt->score }}%</p>
        <h1 class="text-2xl font-black">{{ $attempt->mission->title }}</h1>
        <p class="text-sm text-slate-500">{{ $attempt->mission->subject }} · {{ $attempt->mission->grade_level }}</p>
        <p class="mt-2 text-sm">Status: <strong>{{ $attempt->status }}</strong></p>
    </div>

    <form id="reflectionForm" class="rounded-2xl border p-5 bg-white dark:bg-slate-900 space-y-4"
          data-action="{{ route('jagat-misi.api.debrief', $attempt) }}" data-csrf="{{ csrf_token() }}">
        <h2 class="font-black">Refleksi</h2>
        @foreach($attempt->mission->reflectionPrompts as $prompt)
        <p class="text-sm text-slate-600">{{ $prompt->prompt_text }}</p>
        @endforeach
        <div>
            <label class="text-sm font-semibold">Apa yang kamu pahami?</label>
            <textarea name="understand" class="w-full border rounded-xl p-3 text-sm" rows="3" required>{{ $attempt->reflection?->understand }}</textarea>
        </div>
        <div>
            <label class="text-sm font-semibold">Kendala</label>
            <textarea name="barrier" class="w-full border rounded-xl p-3 text-sm" rows="2">{{ $attempt->reflection?->barrier }}</textarea>
        </div>
        <div>
            <label class="text-sm font-semibold">Langkah berikutnya</label>
            <textarea name="next_step" class="w-full border rounded-xl p-3 text-sm" rows="2">{{ $attempt->reflection?->next_step }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="confirmed" value="1" {{ $attempt->reflection?->confirmed ? 'checked' : '' }}>
            Saya sudah mengecek refleksi dan siap menyelesaikan misi.
        </label>
        <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-sm font-bold">Simpan Refleksi</button>
        <p id="reflectionMsg" class="text-sm hidden"></p>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('reflectionForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const res = await fetch(form.dataset.action, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': form.dataset.csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({
      understand: form.understand.value,
      barrier: form.barrier.value,
      next_step: form.next_step.value,
      confirmed: form.confirmed.checked,
      mood: 'siap',
    }),
  });
  const data = await res.json();
  const msg = document.getElementById('reflectionMsg');
  msg.classList.remove('hidden');
  msg.textContent = data.message || 'Tersimpan.';
});
</script>
@endpush
