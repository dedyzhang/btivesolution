const STORAGE_KEYS = {
  progression: "jagatmisi.progression",
  latestResult: "jagatmisi.latest_result"
};

const seedProgression = {
  student: "Siswa Player",
  level: 3,
  xp: 320,
  nextLevelXp: 500,
  streak: 4,
  missionsCompleted: 8,
  rank: 2,
  leaderboardVisible: true,
  unlocked: ["forest", "logic", "streak3"],
  latestReward: null
};

const collections = [
  { id: "forest", title: "Penjelajah Hutan", type: "Lencana", hint: "Selesaikan misi IPA pertama." },
  { id: "logic", title: "Pemikir Nalar", type: "Lencana", hint: "Tuntaskan mekanik nalar." },
  { id: "streak3", title: "Api 3 Hari", type: "Streak", hint: "Belajar 3 hari beruntun." },
  { id: "report", title: "Pembaca Laporan", type: "Koleksi", hint: "Buka laporan belajar." },
  { id: "level5", title: "Navigator Level 5", type: "Level", hint: "Capai level 5." },
  { id: "top1", title: "Puncak Kelas", type: "Leaderboard", hint: "Masuk peringkat 1." }
];

const leaderboardSeed = [
  { name: "Ayu Lestari", xp: 720, current: false },
  { name: "Siswa Player", xp: 640, current: true },
  { name: "Elina Putri", xp: 610, current: false },
  { name: "Dimas Arkan", xp: 530, current: false },
  { name: "Bima Pratama", xp: 420, current: false }
];

function loadProgression() {
  const stored = loadJson(STORAGE_KEYS.progression, {});
  return {
    ...seedProgression,
    ...stored,
    unlocked: Array.isArray(stored.unlocked) ? stored.unlocked : seedProgression.unlocked
  };
}

const state = {
  progression: loadProgression()
};

const el = {
  profileName: document.getElementById("profileName"),
  profileSummary: document.getElementById("profileSummary"),
  profileLevel: document.getElementById("profileLevel"),
  levelOrb: document.getElementById("levelOrb"),
  xpLabel: document.getElementById("xpLabel"),
  xpTrack: document.getElementById("xpTrack"),
  progressStats: document.getElementById("progressStats"),
  milestoneList: document.getElementById("milestoneList"),
  streakLabel: document.getElementById("streakLabel"),
  streakWeek: document.getElementById("streakWeek"),
  streakNote: document.getElementById("streakNote"),
  collectionCount: document.getElementById("collectionCount"),
  collectionGrid: document.getElementById("collectionGrid"),
  leaderboardToggle: document.getElementById("leaderboardToggle"),
  leaderboardList: document.getElementById("leaderboardList"),
  syncProgress: document.getElementById("syncProgress"),
  toast: document.getElementById("toast")
};

function loadJson(key, fallback) {
  try {
    const value = window.localStorage.getItem(key);
    return value ? JSON.parse(value) : fallback;
  } catch (_error) {
    return fallback;
  }
}

function saveJson(key, value) {
  window.localStorage.setItem(key, JSON.stringify(value));
}

function showToast(message) {
  el.toast.textContent = message;
  el.toast.classList.add("show");
  window.clearTimeout(showToast.timer);
  showToast.timer = window.setTimeout(() => el.toast.classList.remove("show"), 2200);
}

function applyLatestResult() {
  const latest = loadJson(STORAGE_KEYS.latestResult, null);
  if (!latest || state.progression.latestReward === latest.completedAt) return false;
  const rewardXp = Math.max(40, Math.min(120, Math.round((latest.score || 70) * 0.9)));
  state.progression.xp += rewardXp;
  state.progression.missionsCompleted += 1;
  state.progression.streak = Math.max(state.progression.streak, 5);
  state.progression.latestReward = latest.completedAt;
  if (!state.progression.unlocked.includes("report") && latest.source === "player") state.progression.unlocked.push("report");
  while (state.progression.xp >= state.progression.nextLevelXp) {
    state.progression.level += 1;
    state.progression.xp -= state.progression.nextLevelXp;
    state.progression.nextLevelXp += 150;
  }
  if (state.progression.level >= 5 && !state.progression.unlocked.includes("level5")) state.progression.unlocked.push("level5");
  saveJson(STORAGE_KEYS.progression, state.progression);
  return true;
}

function renderProfile() {
  const progressPercent = Math.min(100, Math.round((state.progression.xp / state.progression.nextLevelXp) * 100));
  el.profileName.textContent = state.progression.student;
  el.profileLevel.textContent = state.progression.level;
  el.xpLabel.textContent = `${state.progression.xp}/${state.progression.nextLevelXp} XP`;
  el.xpTrack.style.width = `${progressPercent}%`;
  el.profileSummary.textContent = `${state.progression.missionsCompleted} misi selesai, streak ${state.progression.streak} hari, rank #${state.progression.rank} di kelas.`;
  el.progressStats.innerHTML = `
    <div class="stat-box"><span>Misi selesai</span><strong>${state.progression.missionsCompleted}</strong></div>
    <div class="stat-box"><span>Rank kelas</span><strong>#${state.progression.rank}</strong></div>
    <div class="stat-box"><span>Progres level</span><strong>${progressPercent}%</strong></div>
  `;
  el.milestoneList.innerHTML = [
    { title: "Level 4", need: 500, done: state.progression.level >= 4 },
    { title: "Streak 7 hari", need: 7, done: state.progression.streak >= 7 },
    { title: "10 misi selesai", need: 10, done: state.progression.missionsCompleted >= 10 }
  ].map((item) => `
    <article class="milestone-item ${item.done ? "done" : ""}">
      <h4>${item.title}</h4>
      <p>${item.done ? "Tercapai" : "Masih berjalan"}</p>
    </article>
  `).join("");
}

function renderStreak() {
  const days = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];
  el.streakLabel.textContent = `${state.progression.streak} hari`;
  el.streakWeek.innerHTML = days.map((day, index) => `
    <div class="streak-day ${index < Math.min(state.progression.streak, 7) ? "active" : ""}">
      <strong>${day}</strong>
      <span>${index < Math.min(state.progression.streak, 7) ? "Aktif" : "Kosong"}</span>
    </div>
  `).join("");
  el.streakNote.textContent = state.progression.streak >= 7
    ? "Streak mingguan penuh. Siswa siap mendapat tantangan level berikutnya."
    : "Jaga ritme belajar dengan menyelesaikan minimal satu misi hari ini.";
}

function renderCollection() {
  const unlocked = new Set(state.progression.unlocked);
  el.collectionCount.textContent = `${unlocked.size}/${collections.length}`;
  el.collectionGrid.innerHTML = collections.map((item) => {
    const isUnlocked = unlocked.has(item.id);
    return `
      <article class="collection-item ${isUnlocked ? "unlocked" : "locked"}">
        <div class="collection-icon">${isUnlocked ? "OK" : "-"}</div>
        <h4>${item.title}</h4>
        <p>${item.type} - ${isUnlocked ? "Terbuka" : item.hint}</p>
      </article>
    `;
  }).join("");
}

function renderLeaderboard() {
  el.leaderboardToggle.checked = Boolean(state.progression.leaderboardVisible);
  if (!state.progression.leaderboardVisible) {
    el.leaderboardList.innerHTML = '<div class="streak-note">Leaderboard disembunyikan oleh siswa.</div>';
    return;
  }
  const adjusted = leaderboardSeed.map((item) => item.current ? { ...item, xp: state.progression.level * 160 + state.progression.xp } : item)
    .sort((a, b) => b.xp - a.xp);
  state.progression.rank = adjusted.findIndex((item) => item.current) + 1;
  el.leaderboardList.innerHTML = adjusted.map((item, index) => `
    <article class="leaderboard-row ${item.current ? "current" : ""}">
      <div>
        <h4>#${index + 1} ${item.name}</h4>
        <span>${item.current ? "Profil aktif" : "Teman kelas"}</span>
      </div>
      <strong>${item.xp} XP</strong>
    </article>
  `).join("");
}

function renderAll(animate = false) {
  renderProfile();
  renderStreak();
  renderCollection();
  renderLeaderboard();
  if (animate) {
    el.levelOrb.classList.remove("reward-burst");
    window.requestAnimationFrame(() => el.levelOrb.classList.add("reward-burst"));
  }
}

function bindEvents() {
  el.leaderboardToggle.addEventListener("change", (event) => {
    state.progression.leaderboardVisible = event.target.checked;
    saveJson(STORAGE_KEYS.progression, state.progression);
    renderLeaderboard();
  });
  el.syncProgress.addEventListener("click", () => {
    const applied = applyLatestResult();
    renderAll(applied);
    showToast(applied ? "Reward misi terbaru diterapkan." : "Belum ada reward baru.");
  });
}

function boot() {
  const applied = applyLatestResult();
  bindEvents();
  renderAll(applied);
}

boot();