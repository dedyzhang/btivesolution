{{--
    Header tabel yang bisa diklik untuk mengurutkan (asc/desc), mempertahankan query
    string lain (search, dsb) dan mereset ke halaman 1 saat urutan berubah.
    Props: $field (nama kolom sort), $label, $align (left|center|right, default left).
--}}
@props(['field', 'label', 'align' => 'left'])
@php
    $dir = request('dir') === 'desc' ? 'desc' : 'asc';
    $isActive = request('sort') === $field;
    $nextDir = $isActive && $dir === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir, 'page' => null]);
    $justify = $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : 'justify-start');
@endphp
<th {{ $attributes->merge(['class' => $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : '')]) }}>
    <a href="{{ $url }}" class="inline-flex items-center gap-1 {{ $justify }} hover:text-primary transition select-none">
        <span>{{ $label }}</span>
        @if($isActive)
        <i data-lucide="{{ $dir === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-3 h-3 text-primary"></i>
        @else
        <i data-lucide="arrow-up-down" class="w-3 h-3 opacity-30"></i>
        @endif
    </a>
</th>
