<?php

namespace App\Sarpras\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aset extends SarprasModel
{
    protected $table = 'sarpras_aset';

    protected $fillable = [
        'school_id', 'kode', 'nama', 'kategori_id', 'ruangan_id', 'merk',
        'spesifikasi', 'kondisi', 'status', 'tgl_perolehan',
        'nilai_perolehan', 'masa_manfaat_tahun', 'sumber_dana', 'foto_path',
    ];

    protected $casts = [
        'spesifikasi' => 'array',          // spec key-value JSON
        'tgl_perolehan' => 'date',
        'nilai_perolehan' => 'integer',    // UANG integer rupiah (BCMath)
        'masa_manfaat_tahun' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(DenahRuangan::class, 'ruangan_id');
    }

    public function laporanKerusakan(): HasMany
    {
        return $this->hasMany(LaporanKerusakan::class, 'aset_id');
    }

    public function perbaikan(): HasMany
    {
        return $this->hasMany(Perbaikan::class, 'aset_id');
    }

    public function jadwalPemeliharaan(): HasMany
    {
        return $this->hasMany(JadwalPemeliharaan::class, 'aset_id');
    }

    public function peminjamanItem(): HasMany
    {
        return $this->hasMany(PeminjamanItem::class, 'aset_id');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'aset_id');
    }

    /** Tampilan: "Rp 1.500.000". */
    public function getNilaiPerolehanRpAttribute(): string
    {
        return $this->rupiah('nilai_perolehan');
    }

    // ─────────────── Penyusutan (garis lurus) & Nilai Buku ───────────────

    /** Penyusutan per tahun (integer rupiah) = nilai perolehan / masa manfaat. */
    public function penyusutanTahunan(): int
    {
        $tahun = (int) ($this->masa_manfaat_tahun ?: 0);
        if ($tahun <= 0) {
            return 0;
        }
        return intdiv((int) $this->nilai_perolehan, $tahun);
    }

    /**
     * Akumulasi penyusutan s/d tanggal (default sekarang). Dibatasi masa manfaat
     * & tidak melebihi nilai perolehan.
     */
    public function akumulasiPenyusutan(?\Carbon\Carbon $asOf = null): int
    {
        $asOf ??= now();
        if (! $this->tgl_perolehan) {
            return 0;
        }
        $tahunBerjalan = (int) floor($this->tgl_perolehan->floatDiffInYears($asOf));
        $tahunBerjalan = max(0, min($tahunBerjalan, (int) ($this->masa_manfaat_tahun ?: 0)));

        return min($this->penyusutanTahunan() * $tahunBerjalan, (int) $this->nilai_perolehan);
    }

    /** Nilai buku = nilai perolehan − akumulasi penyusutan (minimal 0). */
    public function nilaiBuku(?\Carbon\Carbon $asOf = null): int
    {
        return max(0, (int) $this->nilai_perolehan - $this->akumulasiPenyusutan($asOf));
    }

    public function getNilaiBukuRpAttribute(): string
    {
        return \App\Sarpras\Support\Rupiah::format($this->nilaiBuku());
    }

    /** Label kondisi yang ramah dibaca. */
    public function getKondisiLabelAttribute(): string
    {
        return match ($this->kondisi) {
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            'hilang' => 'Hilang',
            default => ucfirst((string) $this->kondisi),
        };
    }
}
