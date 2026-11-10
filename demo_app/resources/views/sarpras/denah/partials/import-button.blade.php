{{--
    Tombol Import Denah dari file gambar (jpg/jpeg/png/webp/gif/bmp).
    Variabel: $denah (wajib), $gaya ('tombol' default | 'link').
--}}
@php($gaya = $gaya ?? 'tombol')
<form method="POST" action="{{ route('sarpras.denah.import', $denah) }}" enctype="multipart/form-data" class="inline-block">
    @csrf
    @if ($gaya === 'link')
        <label class="cursor-pointer text-amber-600 hover:underline">
            Import
            <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp"
                   class="hidden" onchange="this.form.submit()">
        </label>
    @else
        <label class="cursor-pointer inline-flex items-center gap-1.5 bg-[#eafaf1] text-[#065f46] border border-[#a7f3d0] px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 shadow-sm hover:bg-[#d1fae5]">
            <i data-lucide="upload" class="w-3.5 h-3.5"></i> Import Denah
            <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp"
                   class="hidden" onchange="this.form.submit()">
        </label>
    @endif
</form>
