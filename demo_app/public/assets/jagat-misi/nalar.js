const narrativeStory = {
  title: "Jejak Pagi di Hutan",
  start: "pagi",
  nodes: {
    pagi: {
      tag: "Awal misi",
      prompt: "Seorang penjaga taman menemukan jejak air yang hilang.",
      body: "Pilih langkah awal yang paling masuk akal untuk menemukan sumber masalah.",
      score: 0,
      choices: [
        { label: "Periksa sumber air", detail: "Cari tanda kebocoran di hulu jalur air.", next: "hulu", points: 20 },
        { label: "Tanya pengunjung", detail: "Kumpulkan petunjuk dari orang yang lewat.", next: "pengunjung", points: 10 },
        { label: "Tebak lokasi", detail: "Langsung menuju titik acak tanpa bukti.", next: "tebak", points: 0 }
      ]
    },
    hulu: {
      tag: "Bukti ditemukan",
      prompt: "Di hulu, ada batang kayu menghalangi aliran.",
      body: "Langkah berikutnya harus menyeimbangkan bukti dan tindakan cepat.",
      score: 20,
      choices: [
        { label: "Bersihkan aliran", detail: "Hapus hambatan agar air kembali lancar.", next: "lanjut", points: 25 },
        { label: "Tandai lokasi", detail: "Simpan bukti untuk laporan lengkap.", next: "lapor", points: 15 }
      ]
    },
    pengunjung: {
      tag: "Bukti sosial",
      prompt: "Pengunjung melihat suara retak dari jalur kayu.",
      body: "Kamu punya petunjuk, tapi harus memilih tindak lanjut yang lebih tajam.",
      score: 10,
      choices: [
        { label: "Cari suara retak", detail: "Ikuti petunjuk ke arah struktur yang rusak.", next: "lanjut", points: 20 },
        { label: "Buat catatan", detail: "Simpan info dan kembali ke pos awal.", next: "lapor", points: 10 }
      ]
    },
    tebak: {
      tag: "Langkah lemah",
      prompt: "Tanpa petunjuk, area yang didatangi tidak relevan.",
      body: "Pilihan ini tidak menambah banyak bukti. Kamu masih bisa memperbaiki arah.",
      score: 0,
      choices: [
        { label: "Ulang dari awal", detail: "Kembali ke data yang paling kuat.", next: "pagi", points: 0 },
        { label: "Minta bantuan", detail: "Ajukan pertanyaan ke penjaga lain.", next: "pengunjung", points: 5 }
      ]
    },
    lanjut: {
      tag: "Misi hampir selesai",
      prompt: "Aliran sudah dipulihkan dan sumber masalah ketemu.",
      body: "Sisa langkah adalah memastikan semua bukti terdokumentasi.",
      score: 45,
      end: true,
      choices: [
        { label: "Selesaikan laporan", detail: "Tutup misi dengan ringkasan bukti.", next: "finish", points: 20 },
        { label: "Tinjau ulang", detail: "Cek apakah ada detail yang terlewat.", next: "lapor", points: 10 }
      ]
    },
    lapor: {
      tag: "Penutup",
      prompt: "Laporan siap dikirim.",
      body: "Tulis satu kalimat kesimpulan yang mencerminkan alur bukti yang kamu ikuti.",
      score: 30,
      end: true,
      choices: [
        { label: "Kirim laporan", detail: "Selesaikan misi dengan narasi rapi.", next: "finish", points: 15 }
      ]
    },
    finish: {
      tag: "Selesai",
      prompt: "Narasi selesai dengan jejak tindakan yang jelas.",
      body: "Misi ini menguji kemampuan menyusun bukti dan memilih langkah logis.",
      score: 100,
      end: true,
      choices: []
    }
  }
};

const decisionRounds = [
  {
    prompt: "Banjir kecil muncul di kawasan sekolah. Apa langkah pertama?",
    body: "Pilih tindakan yang menjaga stabilitas kota tanpa memakan biaya besar.",
    choices: [
      { label: "Bersihkan drainase", detail: "Stabilitas naik, biaya sedang.", effects: { stability: 18, trust: 8, budget: -12 } },
      { label: "Pasang pompa darurat", detail: "Cepat, tapi biaya lebih tinggi.", effects: { stability: 12, trust: 4, budget: -20 } },
      { label: "Tunggu laporan lengkap", detail: "Biaya aman, risiko membesar.", effects: { stability: -8, trust: -6, budget: 0 } }
    ]
  },
  {
    prompt: "Warga minta transparansi tentang penggunaan anggaran.",
    body: "Pertahankan kepercayaan sambil tetap mengamankan sisa dana.",
    choices: [
      { label: "Rilis papan informasi", detail: "Trust naik, biaya kecil.", effects: { stability: 6, trust: 16, budget: -6 } },
      { label: "Buka forum warga", detail: "Trust naik besar, proses lebih lambat.", effects: { stability: 3, trust: 20, budget: -10 } },
      { label: "Tunda komunikasi", detail: "Biaya aman, trust turun.", effects: { stability: 0, trust: -14, budget: 0 } }
    ]
  },
  {
    prompt: "Sumber air tambahan bisa dibuka, tapi jalurnya sempit.",
    body: "Keputusan akhir harus mengamankan stabilitas jangka pendek dan ruang dana.",
    choices: [
      { label: "Buka jalur tambahan", detail: "Stabilitas naik, biaya moderat.", effects: { stability: 20, trust: 8, budget: -12 } },
      { label: "Rancang jalur permanen", detail: "Hasil kuat, tapi biaya besar.", effects: { stability: 16, trust: 10, budget: -22 } },
      { label: "Tolak perubahan", detail: "Anggaran aman, risiko tetap tinggi.", effects: { stability: -10, trust: -8, budget: 4 } }
    ]
  }
];

const puzzleTarget = [
  { id: "survey", label: "Survey lokasi dan risiko" },
  { id: "materials", label: "Siapkan material utama" },
  { id: "foundation", label: "Pasang fondasi penguat" },
  { id: "bridge", label: "Rakit struktur jembatan" },
  { id: "test", label: "Uji beban dan amankan jalur" }
];

const STORAGE_KEY = "jagatmisi.latest_result";

const state = {
  module: "narrative",
  narrativeNode: narrativeStory.start,
  narrativePath: [],
  narrativePoints: 0,
  decisionIndex: 0,
  decisionStats: { stability: 70, trust: 55, budget: 60 },
  decisionLog: [],
  puzzleOrder: puzzleTarget.map((item) => item.id),
  puzzleSolved: false,
  completed: false
};

const el = {
  moduleTabs: document.querySelectorAll(".module-tab"),
  panels: {
    narrative: document.getElementById("panel-narrative"),
    decision: document.getElementById("panel-decision"),
    puzzle: document.getElementById("panel-puzzle")
  },
  resetModule: document.getElementById("resetModule"),
  randomModule: document.getElementById("randomModule"),
  activeModuleLabel: document.getElementById("activeModuleLabel"),
  moduleStatus: document.getElementById("moduleStatus"),
  totalPoints: document.getElementById("totalPoints"),
  completeModule: document.getElementById("completeModule"),
  toast: document.getElementById("toast"),
  narrativeTitle: document.getElementById("narrativeTitle"),
  narrativeScore: document.getElementById("narrativeScore"),
  narrativeTag: document.getElementById("narrativeTag"),
  narrativePrompt: document.getElementById("narrativePrompt"),
  narrativeBody: document.getElementById("narrativeBody"),
  narrativeChoices: document.getElementById("narrativeChoices"),
  narrativePath: document.getElementById("narrativePath"),
  decisionRound: document.getElementById("decisionRound"),
  stabilityValue: document.getElementById("stabilityValue"),
  trustValue: document.getElementById("trustValue"),
  budgetValue: document.getElementById("budgetValue"),
  stabilityBar: document.getElementById("stabilityBar"),
  trustBar: document.getElementById("trustBar"),
  budgetBar: document.getElementById("budgetBar"),
  decisionTag: document.getElementById("decisionTag"),
  decisionPrompt: document.getElementById("decisionPrompt"),
  decisionBody: document.getElementById("decisionBody"),
  decisionChoices: document.getElementById("decisionChoices"),
  decisionLog: document.getElementById("decisionLog"),
  puzzleScore: document.getElementById("puzzleScore"),
  sequenceList: document.getElementById("sequenceList"),
  targetList: document.getElementById("targetList"),
  shufflePuzzle: document.getElementById("shufflePuzzle"),
  checkPuzzle: document.getElementById("checkPuzzle"),
  finishNalar: document.getElementById("finishNalar")
};

function showToast(message) {
  el.toast.textContent = message;
  el.toast.classList.add("show");
  window.clearTimeout(showToast.timer);
  showToast.timer = window.setTimeout(() => el.toast.classList.remove("show"), 2400);
}

function shuffle(list) {
  const clone = [...list];
  for (let i = clone.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [clone[i], clone[j]] = [clone[j], clone[i]];
  }
  return clone;
}

function setModule(moduleName) {
  state.module = moduleName;
  el.moduleTabs.forEach((tab) => tab.classList.toggle("active", tab.dataset.module === moduleName));
  Object.entries(el.panels).forEach(([key, panel]) => panel.classList.toggle("active", key === moduleName));
  el.activeModuleLabel.textContent = moduleLabel(moduleName);
  renderAll();
}

function moduleLabel(moduleName) {
  return {
    narrative: "Interactive Narrative",
    decision: "Strategic Decision",
    puzzle: "Puzzle Sequencing"
  }[moduleName];
}

function renderAll() {
  renderNarrative();
  renderDecision();
  renderPuzzle();
  updateSummary();
}

function renderNarrative() {
  const node = narrativeStory.nodes[state.narrativeNode];
  el.narrativeTitle.textContent = narrativeStory.title;
  el.narrativeTag.textContent = node.tag;
  el.narrativePrompt.textContent = node.prompt;
  el.narrativeBody.textContent = node.body;
  el.narrativeScore.textContent = `${state.narrativePoints} poin`;
  el.narrativeChoices.innerHTML = node.choices.map((choice, index) => `
    <button class="choice-button" type="button" data-narrative-choice="${index}">
      <strong>${choice.label}</strong>
      <span>${choice.detail}</span>
    </button>
  `).join("") || '<div class="feedback-chip">Narasi selesai. Lanjutkan modul lain atau reset.</div>';
  el.narrativePath.innerHTML = state.narrativePath.length
    ? state.narrativePath.map((item) => `<span class="path-pill">${item}</span>`).join("")
    : '<span class="path-pill">Belum ada langkah</span>';
  el.narrativeChoices.querySelectorAll(".choice-button").forEach((button) => {
    button.addEventListener("click", () => {
      const choice = node.choices[Number(button.dataset.narrativeChoice)];
      state.narrativePoints += choice.points;
      state.narrativePath.push(choice.label);
      state.narrativeNode = choice.next;
      if (choice.next === "finish") state.completed = true;
      renderNarrative();
      updateSummary();
      flashModuleState();
    });
  });
}

function renderDecision() {
  const finished = state.decisionIndex >= decisionRounds.length;
  const round = decisionRounds[Math.min(state.decisionIndex, decisionRounds.length - 1)];
  el.decisionRound.textContent = `Putaran ${Math.min(state.decisionIndex + 1, decisionRounds.length)}/${decisionRounds.length}`;
  el.decisionTag.textContent = finished ? "Ringkasan keputusan" : "Putaran aktif";
  el.decisionPrompt.textContent = finished ? "Semua putaran selesai." : round.prompt;
  el.decisionBody.textContent = finished ? "Evaluasi akhir akan muncul di ringkasan." : round.body;
  el.decisionChoices.innerHTML = finished
    ? '<div class="feedback-chip">Semua putaran sudah selesai. Tinjau ringkasan di samping.</div>'
    : round.choices.map((choice, index) => `
    <button class="choice-button" type="button" data-decision-choice="${index}">
      <strong>${choice.label}</strong>
      <span>${choice.detail}</span>
    </button>
  `).join("");
  if (!finished) {
    el.decisionChoices.querySelectorAll(".choice-button").forEach((button) => {
      button.addEventListener("click", () => {
        const choice = round.choices[Number(button.dataset.decisionChoice)];
        state.decisionStats.stability = clamp(state.decisionStats.stability + choice.effects.stability, 0, 100);
        state.decisionStats.trust = clamp(state.decisionStats.trust + choice.effects.trust, 0, 100);
        state.decisionStats.budget = clamp(state.decisionStats.budget + choice.effects.budget, 0, 100);
        state.decisionLog.push(choice.label);
        state.decisionIndex = Math.min(state.decisionIndex + 1, decisionRounds.length);
        renderDecision();
        updateSummary();
        flashModuleState();
      });
    });
  }
  el.decisionLog.innerHTML = state.decisionLog.length
    ? state.decisionLog.map((item) => `<span class="path-pill">${item}</span>`).join("")
    : '<span class="path-pill">Belum ada keputusan</span>';
  el.stabilityValue.textContent = state.decisionStats.stability;
  el.trustValue.textContent = state.decisionStats.trust;
  el.budgetValue.textContent = state.decisionStats.budget;
  el.stabilityBar.style.width = `${state.decisionStats.stability}%`;
  el.trustBar.style.width = `${state.decisionStats.trust}%`;
  el.budgetBar.style.width = `${state.decisionStats.budget}%`;
}

function renderPuzzle() {
  const solvedCount = state.puzzleOrder.filter((item, index) => item === puzzleTarget[index].id).length;
  el.puzzleScore.textContent = `${solvedCount}/${puzzleTarget.length} benar`;
  el.sequenceList.innerHTML = state.puzzleOrder.map((id, index) => {
    const item = puzzleTarget.find((entry) => entry.id === id);
    return `
      <div class="sequence-item" draggable="true" data-seq-item="${id}" data-index="${index}">
        <strong>${index + 1}. ${item.label}</strong>
        <span class="sequence-handle">Drag</span>
      </div>
    `;
  }).join("");
  el.targetList.innerHTML = puzzleTarget.map((item, index) => `
    <div class="target-item ${state.puzzleOrder[index] === item.id ? "correct" : ""}">
      <strong>${index + 1}. ${item.label}</strong>
      <span>${state.puzzleOrder[index] === item.id ? "Sesuai" : "Target"}</span>
    </div>
  `).join("");

  let draggingId = null;
  el.sequenceList.querySelectorAll(".sequence-item").forEach((item) => {
    item.addEventListener("dragstart", () => {
      draggingId = item.dataset.seqItem;
      item.classList.add("dragging");
    });
    item.addEventListener("dragend", () => {
      item.classList.remove("dragging");
      el.sequenceList.querySelectorAll(".sequence-item").forEach((node) => node.classList.remove("drop-target"));
    });
    item.addEventListener("dragover", (event) => event.preventDefault());
    item.addEventListener("dragenter", () => item.classList.add("drop-target"));
    item.addEventListener("dragleave", () => item.classList.remove("drop-target"));
    item.addEventListener("drop", (event) => {
      event.preventDefault();
      const targetId = item.dataset.seqItem;
      if (!draggingId || draggingId === targetId) return;
      const next = [...state.puzzleOrder];
      const from = next.indexOf(draggingId);
      const to = next.indexOf(targetId);
      next.splice(from, 1);
      next.splice(to, 0, draggingId);
      state.puzzleOrder = next;
      renderPuzzle();
      updateSummary();
      flashModuleState();
    });
  });
}

function checkPuzzle() {
  const correct = state.puzzleOrder.every((item, index) => item === puzzleTarget[index].id);
  if (correct) {
    state.puzzleSolved = true;
    showToast("Urutan sudah tepat.");
  } else {
    showToast("Masih ada urutan yang keliru.");
  }
  renderPuzzle();
  updateSummary();
}

function updateSummary() {
  const totalPoints = state.narrativePoints + state.decisionStats.stability + state.decisionStats.trust + state.decisionStats.budget + (state.puzzleSolved ? 50 : 0);
  el.totalPoints.textContent = totalPoints;
  if (state.completed || state.puzzleSolved || state.decisionIndex >= decisionRounds.length) {
    el.moduleStatus.textContent = "Sedang diproses";
  } else {
    el.moduleStatus.textContent = "Belum selesai";
  }
}

function flashModuleState() {
  const activePanel = el.panels[state.module];
  activePanel.classList.remove("active");
  window.requestAnimationFrame(() => activePanel.classList.add("active"));
}

function completeModule() {
  const totalPoints = Number(el.totalPoints.textContent || "0");
  const doneModules = [];
  if (state.narrativeNode === "finish") doneModules.push("narrative");
  if (state.decisionIndex >= decisionRounds.length) doneModules.push("decision");
  if (state.puzzleSolved) doneModules.push("puzzle");
  const doneCount = doneModules.length;
  el.moduleStatus.textContent = doneCount === 3 ? "Selesai" : "Sebagian selesai";
  if (doneCount === 3) {
    saveLatestResult(totalPoints, doneModules);
    showToast(`Mekanik nalar selesai dengan ${totalPoints} poin.`);
    window.setTimeout(() => {
      window.location.href = "debrief.html";
    }, 450);
    return;
  }
  showToast("Selesaikan semua modul dulu.");
}

function resetModule() {
  state.module = "narrative";
  state.narrativeNode = narrativeStory.start;
  state.narrativePath = [];
  state.narrativePoints = 0;
  state.decisionIndex = 0;
  state.decisionStats = { stability: 70, trust: 55, budget: 60 };
  state.decisionLog = [];
  state.puzzleOrder = puzzleTarget.map((item) => item.id);
  state.puzzleSolved = false;
  state.completed = false;
  el.moduleStatus.textContent = "Belum selesai";
  setModule("narrative");
}

function randomModule() {
  const modules = ["narrative", "decision", "puzzle"];
  setModule(modules[Math.floor(Math.random() * modules.length)]);
}

function clamp(value, min, max) {
  return Math.min(max, Math.max(min, value));
}

function saveLatestResult(totalPoints, doneModules) {
  const payload = {
    source: "nalar",
    mission: {
      title: "Mekanik Nalar",
      subject: "Logika & Strategi",
      grade: "Campuran"
    },
    score: totalPoints,
    duration: "Sesi runtime",
    completedAt: new Date().toISOString(),
    modules: doneModules,
    breakdown: {
      narrativePoints: state.narrativePoints,
      decision: state.decisionStats,
      puzzleSolved: state.puzzleSolved
    }
  };
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
  return payload;
}

function bindEvents() {
  el.moduleTabs.forEach((tab) => {
    tab.addEventListener("click", () => setModule(tab.dataset.module));
  });
  el.resetModule.addEventListener("click", resetModule);
  el.randomModule.addEventListener("click", randomModule);
  el.completeModule.addEventListener("click", completeModule);
  el.checkPuzzle.addEventListener("click", checkPuzzle);
  el.shufflePuzzle.addEventListener("click", () => {
    state.puzzleOrder = shuffle(state.puzzleOrder);
    renderPuzzle();
    updateSummary();
  });
  el.finishNalar.addEventListener("click", completeModule);
}

function boot() {
  bindEvents();
  setModule("narrative");
  updateSummary();
}

boot();

