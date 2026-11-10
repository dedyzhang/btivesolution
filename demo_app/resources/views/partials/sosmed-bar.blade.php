{{-- Bilah ikon media sosial sekolah. Param: $sosmed (array key => ['label','href']).
     Warna latar & hover tiap ikon mengikuti warna resmi platformnya masing-masing. --}}
@php
    // Warna brand resmi tiap platform. Instagram dapat gradasi khas saat hover.
    $sosmedBrand = [
        'youtube'   => ['c' => '#FF0000'],
        'instagram' => ['c' => '#E1306C', 'gradient' => ['#f09433', '#dc2743', '#bc1888']],
        'tiktok'    => ['c' => '#000000'],
        'whatsapp'  => ['c' => '#25D366'],
        'facebook'  => ['c' => '#1877F2'],
        'twitter'   => ['c' => '#000000'],
    ];
@endphp
@if(!empty($sosmed))
<div class="mt-8 flex flex-col items-center gap-3">
    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ikuti Kami</p>
    <div class="flex flex-wrap items-center justify-center gap-2.5">
        @foreach($sosmed as $key => $s)
            @php $b = $sosmedBrand[$key] ?? null; @endphp
            <a href="{{ $s['href'] }}" target="_blank" rel="noopener noreferrer"
               title="{{ $s['label'] }}" aria-label="{{ $s['label'] }}"
               @if($b)
                   @if(isset($b['gradient']))
                   class="grid place-items-center w-11 h-11 rounded-2xl bg-transparent text-[{{ $b['c'] }}] transition hover:text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-gradient-to-br hover:from-[{{ $b['gradient'][0] }}] hover:via-[{{ $b['gradient'][1] }}] hover:to-[{{ $b['gradient'][2] }}]"
                   @else
                   class="grid place-items-center w-11 h-11 rounded-2xl bg-transparent text-[{{ $b['c'] }}] transition hover:bg-[{{ $b['c'] }}] hover:text-white hover:-translate-y-0.5 hover:shadow-lg"
                   @endif
               @else
                   class="grid place-items-center w-11 h-11 rounded-2xl bg-transparent text-primary transition hover:bg-primary hover:text-white hover:-translate-y-0.5 hover:shadow-lg"
               @endif>
                @include('partials.sosmed-icon', ['key' => $key, 'cls' => 'w-5 h-5'])
            </a>
        @endforeach
    </div>
</div>
@endif
