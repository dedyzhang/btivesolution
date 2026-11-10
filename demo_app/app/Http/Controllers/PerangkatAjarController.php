<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\PerangkatAjar;
use App\Models\PerangkatAjarGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Perangkat Ajar (RPP/Modul Ajar/Prota/dst) — replikasi fitur "Perangkat
 * Pembelajaran" smp_ver5. Guru upload dokumen sendiri; admin/kurikulum/kepala
 * (siapa pun dgn permission `manage_perangkat`) bisa pantau semua guru.
 *
 * Perbaikan dari app lama: validasi file server-side (bukan cuma client JS)
 * + guard kepemilikan (guru cuma boleh upload/hapus milik sendiri).
 */
class PerangkatAjarController extends Controller
{
    private function bisaPantau(): bool
    {
        return auth()->user()->canAccess('manage_perangkat');
    }

    /** Guru boleh kelola file guru ini kalau dia guru itu sendiri, atau punya izin pantau. */
    private function guardAksesGuru(Guru $guru): void
    {
        $sendiri = auth()->user()->guru?->uuid === $guru->uuid;
        abort_unless($sendiri || $this->bisaPantau(), 403, 'Anda tidak punya akses ke perangkat ajar guru ini.');
    }

    /** Master jenis dokumen + grid monitoring semua guru (jumlah file terupload). */
    public function index()
    {
        abort_unless($this->bisaPantau(), 403, 'Hanya admin/kurikulum/kepala sekolah yang dapat memantau perangkat ajar.');

        $list = PerangkatAjar::orderBy('perangkat')->get();
        $guruList = Guru::orderBy('nama')->withCount('perangkatUploads')->get();

        return view('perangkat.index', compact('list', 'guruList'));
    }

    public function store(Request $request)
    {
        abort_unless($this->bisaPantau(), 403);
        $data = $request->validate(['perangkat' => 'required|string|max:150']);
        PerangkatAjar::create($data);
        return back()->with('success', 'Jenis perangkat ajar ditambahkan.');
    }

    public function update(Request $request, PerangkatAjar $list)
    {
        abort_unless($this->bisaPantau(), 403);
        $data = $request->validate(['perangkat' => 'required|string|max:150']);
        $list->update($data);
        return back()->with('success', 'Jenis perangkat ajar diperbarui.');
    }

    public function destroy(PerangkatAjar $list)
    {
        abort_unless($this->bisaPantau(), 403);
        foreach ($list->uploads as $upload) {
            $this->hapusFileFisik($upload);
            $upload->delete();
        }
        $list->delete();
        return back()->with('success', 'Jenis perangkat ajar & seluruh filenya dihapus.');
    }

    /** Shortcut: guru login melihat perangkat ajarnya sendiri. */
    public function self()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403, 'Akun Anda tidak punya profil guru.');
        return redirect()->route('perangkat.show', $guru->uuid);
    }

    /** Detail satu guru: semua jenis dokumen + file yang sudah diupload. Dipakai monitoring & guru sendiri. */
    public function show(Guru $guru)
    {
        $this->guardAksesGuru($guru);

        $list = PerangkatAjar::orderBy('perangkat')->get();
        $uploads = PerangkatAjarGuru::where('id_guru', $guru->uuid)->orderBy('created_at', 'desc')->get()->groupBy('id_list');
        $isSelf = auth()->user()->guru?->uuid === $guru->uuid;
        $bisaPantau = $this->bisaPantau();

        return view('perangkat.show', compact('guru', 'list', 'uploads', 'isSelf', 'bisaPantau'));
    }

    public function upload(Request $request, Guru $guru, PerangkatAjar $list)
    {
        $this->guardAksesGuru($guru);

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480', // 20MB, PDF saja
        ]);

        $file = $request->file('file');
        $namaAsli = $file->getClientOriginalName();
        $namaFile = now()->format('YmdHis') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $namaAsli);
        $path = $file->storeAs("perangkat/{$guru->uuid}", $namaFile, 'public');

        PerangkatAjarGuru::create([
            'id_guru'   => $guru->uuid,
            'id_list'   => $list->uuid,
            'nama_asli' => $namaAsli,
            'file'      => $path,
        ]);

        return back()->with('success', "File \"{$namaAsli}\" berhasil diupload.");
    }

    public function download(PerangkatAjarGuru $file)
    {
        $guru = Guru::findOrFail($file->id_guru);
        $this->guardAksesGuru($guru);

        abort_unless(Storage::disk('public')->exists($file->file), 404, 'File tidak ditemukan.');
        return Storage::disk('public')->download($file->file, $file->nama_asli);
    }

    /** Tampilkan PDF langsung di tab baru (inline), tanpa perlu diunduh dulu. Bisa dibaca di mobile via PDF viewer bawaan browser. */
    public function preview(PerangkatAjarGuru $file)
    {
        $guru = Guru::findOrFail($file->id_guru);
        $this->guardAksesGuru($guru);

        abort_unless(Storage::disk('public')->exists($file->file), 404, 'File tidak ditemukan.');
        return response()->file(Storage::disk('public')->path($file->file), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file->nama_asli . '"',
        ]);
    }

    public function destroyFile(PerangkatAjarGuru $file)
    {
        $guru = Guru::findOrFail($file->id_guru);
        $this->guardAksesGuru($guru);

        $this->hapusFileFisik($file);
        $file->delete();

        return back()->with('success', 'File dihapus.');
    }

    /** Unduh semua file perangkat ajar 1 guru sebagai satu file zip. */
    public function zip(Guru $guru)
    {
        $this->guardAksesGuru($guru);

        $uploads = PerangkatAjarGuru::with('list')->where('id_guru', $guru->uuid)->get();
        abort_if($uploads->isEmpty(), 404, 'Belum ada file perangkat ajar untuk diunduh.');

        $zipName = 'Perangkat Ajar - ' . $guru->nama . '.zip';
        $zipPath = storage_path('app/tmp_' . uniqid() . '.zip');

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($uploads as $u) {
            $full = Storage::disk('public')->path($u->file);
            if (file_exists($full)) {
                $jenis = preg_replace('/[^A-Za-z0-9 _-]/', '_', $u->list?->perangkat ?? 'Lainnya');
                $zip->addFile($full, "{$jenis}/{$u->nama_asli}");
            }
        }
        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    private function hapusFileFisik(PerangkatAjarGuru $upload): void
    {
        if ($upload->file && Storage::disk('public')->exists($upload->file)) {
            Storage::disk('public')->delete($upload->file);
        }
    }
}
