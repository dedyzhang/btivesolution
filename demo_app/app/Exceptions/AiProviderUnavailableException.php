<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Provider AI gagal karena sebab yang BUKAN kesalahan permintaan: kuota/limit habis,
 * koneksi putus, atau layanan sedang error (5xx). Hanya kegagalan jenis ini yang
 * membuat GeminiService berpindah ke provider cadangan (lihat ai.fallback_providers);
 * kesalahan konfigurasi/permintaan (4xx) tetap dilempar apa adanya agar cepat ketahuan.
 */
class AiProviderUnavailableException extends RuntimeException {}
