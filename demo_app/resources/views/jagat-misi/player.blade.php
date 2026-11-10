@extends('layouts.app')
@section('title', $mission->title . ' — Player')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/jagat-misi/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/jagat-misi/player.css') }}">
@endpush

@section('content')
<div class="space-y-4" id="missionPlayerApp"
     data-api="{{ route('jagat-misi.api.player.show', $mission) }}"
     data-submit="{{ route('jagat-misi.api.player.attempts', $mission) }}"
     data-csrf="{{ csrf_token() }}"
     @isset($assignment) data-assignment-id="{{ $assignment->uuid }}" @endisset>
    <a href="{{ isset($classroom) ? route('classroom.jagat.show', [$classroom, $mission]) : route('jagat-misi.index') }}" class="text-xs text-slate-500 hover:text-primary">← {{ isset($classroom) ? $classroom->title : 'Katalog Misi' }}</a>
    <h1 class="text-2xl font-black">{{ $mission->title }}</h1>
    <p class="text-sm text-slate-500">{{ $mission->summary }}</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <section id="quizPanel" class="rounded-2xl border p-5 bg-white dark:bg-slate-900">
                <h2 class="font-bold mb-2">Recall Quiz</h2>
                <div id="quizContent" class="text-sm text-slate-500">Memuat soal…</div>
                <button type="button" id="submitQuiz" class="mt-4 px-4 py-2 rounded-xl bg-primary text-white text-sm font-bold">Jawab Quiz</button>
            </section>
            <section id="matchPanel" class="rounded-2xl border p-5 bg-white dark:bg-slate-900">
                <h2 class="font-bold mb-2">Menjodohkan</h2>
                <div id="matchContent" class="space-y-2 text-sm"></div>
            </section>
        </div>
        <aside class="rounded-2xl border p-5 bg-white dark:bg-slate-900">
            <p class="text-xs text-slate-500">Timer</p>
            <p id="timerText" class="text-3xl font-black">03:00</p>
            <button type="button" id="finishMission" class="mt-4 w-full px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">Kumpulkan Misi</button>
            <p id="resultBox" class="mt-3 text-sm hidden"></p>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const root = document.getElementById('missionPlayerApp');
  const state = { quizAnswers: [], matches: {}, secondsLeft: 180, steps: {} };
  const timerEl = document.getElementById('timerText');
  setInterval(() => {
    if (state.secondsLeft <= 0) return;
    state.secondsLeft--;
    const m = String(Math.floor(state.secondsLeft / 60)).padStart(2, '0');
    const s = String(state.secondsLeft % 60).padStart(2, '0');
    timerEl.textContent = `${m}:${s}`;
  }, 1000);

  fetch(root.dataset.api, { headers: { Accept: 'application/json' } })
    .then(r => r.json())
    .then(({ data }) => {
      data.steps.forEach(step => { state.steps[step.module_key] = step; });
      renderQuiz(state.steps.recall_quiz);
      renderMatch(state.steps.matching);
    });

  function renderQuiz(step) {
    if (!step) return;
    const qs = step.payload?.questions || [];
    document.getElementById('quizContent').innerHTML = qs.map((q, i) => `
      <div class="mb-3">
        <p class="font-semibold">${i + 1}. ${q.question}</p>
        ${(q.options || []).map(o => `<label class="block mt-1"><input type="radio" name="q${i}" value="${o}"> ${o}</label>`).join('')}
      </div>`).join('');
    document.getElementById('submitQuiz').onclick = () => {
      state.quizAnswers = qs.map((_, i) => document.querySelector(`input[name="q${i}"]:checked`)?.value || '');
    };
  }

  function renderMatch(step) {
    if (!step) return;
    const pairs = step.payload?.pairs || [];
    const opts = step.payload?.options || [];
    document.getElementById('matchContent').innerHTML = pairs.map(p => `
      <div class="flex items-center gap-2">
        <span class="w-28 font-semibold">${p.term}</span>
        <select data-term="${p.term}" class="border rounded px-2 py-1 text-sm flex-1">
          <option value="">Pilih</option>
          ${opts.map(o => `<option value="${o}">${o}</option>`).join('')}
        </select>
      </div>`).join('');
    document.querySelectorAll('#matchContent select').forEach(sel => {
      sel.addEventListener('change', () => { state.matches[sel.dataset.term] = sel.value; });
    });
  }

  document.getElementById('finishMission').addEventListener('click', async () => {
    const res = await fetch(root.dataset.submit, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': root.dataset.csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        duration_seconds: 180 - state.secondsLeft,
        assignment_id: root.dataset.assignmentId || null,
        responses: {
          recall_quiz: { answers: state.quizAnswers },
          matching: { matches: state.matches },
        },
      }),
    });
    const data = await res.json();
    const box = document.getElementById('resultBox');
    box.classList.remove('hidden');
    if (res.ok) {
      box.textContent = `Skor ${data.data.score}% — ${data.message}`;
      if (data.data.debrief_url) {
        box.innerHTML += ` <a href="${data.data.debrief_url}" class="text-primary font-bold">Lanjut debrief →</a>`;
      }
    } else {
      box.textContent = data.message || 'Gagal submit.';
    }
  });
})();
</script>
@endpush
