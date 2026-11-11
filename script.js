document.querySelector('[data-menu]')?.addEventListener('click', () => {
  document.querySelector('[data-nav]')?.classList.toggle('open');
});

document.querySelector('[data-demo-form]')?.addEventListener('submit', (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const message = form.querySelector('[data-form-message]');
  message.textContent = 'Terima kasih. Form demo ini siap dihubungkan ke endpoint lead marketing SIMS.';
  message.classList.add('show');
  form.reset();
});

const pageImages = [...document.querySelectorAll('main img')];

if (pageImages.length) {
  const lightbox = document.createElement('div');
  lightbox.className = 'image-lightbox';
  lightbox.setAttribute('role', 'dialog');
  lightbox.setAttribute('aria-modal', 'true');
  lightbox.setAttribute('aria-label', 'Pratinjau gambar aplikasi');
  lightbox.innerHTML = `
    <div class="lightbox-toolbar">
      <span class="lightbox-title" data-lightbox-title></span>
      <div class="lightbox-controls">
        <button type="button" data-zoom-out aria-label="Perkecil gambar">&minus;</button>
        <button type="button" data-zoom-reset aria-label="Kembalikan ukuran gambar">100%</button>
        <button type="button" data-zoom-in aria-label="Perbesar gambar">+</button>
        <button type="button" data-lightbox-close aria-label="Tutup pratinjau">&times;</button>
      </div>
    </div>
    <div class="lightbox-stage" data-lightbox-stage><img data-lightbox-image alt=""></div>
    <p class="lightbox-help">Gunakan tombol &minus; / + atau roda mouse untuk mengatur ukuran. Tekan Esc untuk menutup.</p>
  `;
  document.body.appendChild(lightbox);

  const stage = lightbox.querySelector('[data-lightbox-stage]');
  const preview = lightbox.querySelector('[data-lightbox-image]');
  const title = lightbox.querySelector('[data-lightbox-title]');
  const resetButton = lightbox.querySelector('[data-zoom-reset]');
  const closeButton = lightbox.querySelector('[data-lightbox-close]');
  let zoom = 1;
  let lastFocusedImage = null;

  const applyZoom = () => {
    const availableWidth = Math.max(280, stage.clientWidth - 52);
    const baseWidth = Math.min(preview.naturalWidth || availableWidth, availableWidth, 1500);
    preview.style.width = `${Math.round(baseWidth * zoom)}px`;
    resetButton.textContent = `${Math.round(zoom * 100)}%`;
  };

  const setZoom = (nextZoom) => {
    zoom = Math.min(3, Math.max(.5, Math.round(nextZoom * 10) / 10));
    applyZoom();
  };

  const openLightbox = (sourceImage) => {
    lastFocusedImage = sourceImage;
    zoom = 1;
    preview.src = sourceImage.currentSrc || sourceImage.src;
    preview.alt = sourceImage.alt;
    title.textContent = sourceImage.alt || 'Tampilan aplikasi Edutive';
    lightbox.classList.add('open');
    document.body.classList.add('lightbox-open');
    requestAnimationFrame(applyZoom);
    closeButton.focus();
  };

  const closeLightbox = () => {
    lightbox.classList.remove('open');
    document.body.classList.remove('lightbox-open');
    preview.removeAttribute('src');
    lastFocusedImage?.focus();
  };

  pageImages.forEach((image) => {
    image.classList.add('zoomable-image');
    image.tabIndex = 0;
    image.title = 'Klik untuk melihat gambar lebih jelas';
    image.setAttribute('role', 'button');
    image.setAttribute('aria-label', `${image.alt}. Klik untuk memperbesar.`);
    image.addEventListener('click', () => openLightbox(image));
    image.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openLightbox(image);
      }
    });
  });

  lightbox.querySelector('[data-zoom-out]').addEventListener('click', () => setZoom(zoom - .2));
  lightbox.querySelector('[data-zoom-in]').addEventListener('click', () => setZoom(zoom + .2));
  resetButton.addEventListener('click', () => setZoom(1));
  closeButton.addEventListener('click', closeLightbox);
  preview.addEventListener('load', applyZoom);
  stage.addEventListener('wheel', (event) => {
    event.preventDefault();
    setZoom(zoom + (event.deltaY < 0 ? .2 : -.2));
  }, { passive: false });
  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox || event.target === stage) closeLightbox();
  });
  document.addEventListener('keydown', (event) => {
    if (!lightbox.classList.contains('open')) return;
    if (event.key === 'Escape') closeLightbox();
    if (event.key === '+' || event.key === '=') setZoom(zoom + .2);
    if (event.key === '-') setZoom(zoom - .2);
  });
  window.addEventListener('resize', () => {
    if (lightbox.classList.contains('open')) applyZoom();
  });
}

// ==========================================
// KALKULATOR SIMULATOR BIAYA SISWA (EDUTIVE)
// ==========================================
const initPricingSimulator = () => {
  const slider = document.getElementById('calc-slider');
  const numberInput = document.getElementById('calc-number');
  const priceDisplay = document.getElementById('calc-price');
  const priceWords = document.getElementById('calc-price-words');
  const tierBadge = document.getElementById('calc-tier-badge');
  const perMonthDisplay = document.getElementById('calc-per-month');
  const perYearDisplay = document.getElementById('calc-per-year');
  const ctaBtn = document.getElementById('calc-cta');
  const presetBtns = document.querySelectorAll('[data-calc-preset]');
  const tierCards = document.querySelectorAll('[data-tier-card]');
  const tierSelectBtns = document.querySelectorAll('[data-select-tier]');

  if (!slider || !numberInput) return;

  const calculateTier = (students) => {
    const count = Math.max(1, parseInt(students, 10) || 1);
    let price = 0;
    let priceText = '';
    let priceWordsText = '';
    let tierName = '';
    let tierId = 1;
    let tierCode = 'under-100';

    if (count < 100) {
      price = 6000000;
      priceText = 'Rp 6.000.000';
      priceWordsText = '6 Juta Rupiah / tahun';
      tierName = 'Tier Pratama (< 100 Siswa)';
      tierId = 1;
      tierCode = 'under-100';
    } else if (count <= 700) {
      price = 9000000;
      priceText = 'Rp 9.000.000';
      priceWordsText = '9 Juta Rupiah / tahun';
      tierName = 'Tier Reguler (100 – 700 Siswa)';
      tierId = 2;
      tierCode = '100-700';
    } else if (count <= 1000) {
      price = 15000000;
      priceText = 'Rp 15.000.000';
      priceWordsText = '15 Juta Rupiah / tahun';
      tierName = 'Tier Utama (701 – 1.000 Siswa)';
      tierId = 3;
      tierCode = '701-1000';
    } else {
      price = 22000000;
      priceText = 'Rp 22.000.000';
      priceWordsText = '22 Juta Rupiah / tahun';
      tierName = 'Tier Kompleks (> 1.000 Siswa)';
      tierId = 4;
      tierCode = 'above-1000';
    }

    const perYearPerStudent = Math.round(price / count);
    const perMonthPerStudent = Math.round(price / (count * 12));

    return {
      count,
      price,
      priceText,
      priceWordsText,
      tierName,
      tierId,
      tierCode,
      perYearPerStudent,
      perMonthPerStudent,
    };
  };

  const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

  const updateUI = (count) => {
    const data = calculateTier(count);

    slider.value = Math.min(2000, Math.max(20, data.count));
    numberInput.value = data.count;

    if (priceDisplay) priceDisplay.textContent = data.priceText;
    if (priceWords) priceWords.textContent = `(${data.priceWordsText})`;
    if (tierBadge) tierBadge.textContent = data.tierName;
    if (perMonthDisplay) perMonthDisplay.textContent = `~Rp ${formatNumber(data.perMonthPerStudent)}`;
    if (perYearDisplay) perYearDisplay.textContent = `Rp ${formatNumber(data.perYearPerStudent)}`;

    if (ctaBtn) {
      ctaBtn.textContent = `Konsultasi Paket Ini (${formatNumber(data.count)} Siswa) →`;
      ctaBtn.href = `kontak.html?siswa=${data.count}&tier=${data.tierCode}&biaya=${encodeURIComponent(data.priceText)}`;
    }

    // Update active tier card
    tierCards.forEach((card) => {
      const cardTierId = parseInt(card.getAttribute('data-tier-card'), 10);
      const indicator = card.querySelector('.active-tier-indicator');
      if (cardTierId === data.tierId) {
        card.classList.add('active-tier');
        if (indicator) indicator.style.display = 'inline-flex';
      } else {
        card.classList.remove('active-tier');
        if (indicator) indicator.style.display = 'none';
      }
    });

    // Update preset chips
    presetBtns.forEach((btn) => {
      const presetVal = parseInt(btn.getAttribute('data-calc-preset'), 10);
      if (presetVal === data.count) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });
  };

  slider.addEventListener('input', (e) => {
    updateUI(e.target.value);
  });

  numberInput.addEventListener('input', (e) => {
    const val = parseInt(e.target.value, 10);
    if (!isNaN(val)) {
      updateUI(val);
    }
  });

  numberInput.addEventListener('blur', (e) => {
    let val = parseInt(e.target.value, 10);
    if (isNaN(val) || val < 1) val = 1;
    updateUI(val);
  });

  presetBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const val = parseInt(btn.getAttribute('data-calc-preset'), 10);
      updateUI(val);
    });
  });

  tierSelectBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const targetVal = parseInt(btn.getAttribute('data-select-tier'), 10);
      updateUI(targetVal);
      document.getElementById('kalkulator')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // Default calculation
  updateUI(parseInt(numberInput.value, 10) || 350);
};

// ==========================================
// PRE-FILL FORM KONTAK DARI PARAMETER URL
// ==========================================
const initContactPageParams = () => {
  const params = new URLSearchParams(window.location.search);
  const siswaParam = params.get('siswa');
  const tierParam = params.get('tier');
  const biayaParam = params.get('biaya');

  if (!siswaParam && !tierParam && !biayaParam) return;

  const siswaSelect = document.getElementById('siswa');
  const pesanTextarea = document.getElementById('pesan');

  if (siswaSelect) {
    if (tierParam) {
      siswaSelect.value = tierParam;
    } else if (siswaParam) {
      const count = parseInt(siswaParam, 10);
      if (count < 100) siswaSelect.value = 'under-100';
      else if (count <= 700) siswaSelect.value = '100-700';
      else if (count <= 1000) siswaSelect.value = '701-1000';
      else siswaSelect.value = 'above-1000';
    }
  }

  if (pesanTextarea && !pesanTextarea.value.trim()) {
    const studentCountText = siswaParam ? `${siswaParam} siswa` : 'sekolah kami';
    const costText = biayaParam ? ` (simulasi biaya ${biayaParam}/tahun)` : '';
    pesanTextarea.value = `Halo Tim Edutive, kami ingin konsultasi implementasi paket SIMS untuk estimasi ${studentCountText}${costText}. Mohon info jadwal demo dan skenario persiapannya.`;
  }
};

initPricingSimulator();
initContactPageParams();
