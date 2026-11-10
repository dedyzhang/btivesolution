{{--
    Motif batik "Kawung" — dua lapis pola garis diagonal empat kelopak, warna mengikuti tema
    (var(--cp) & var(--ca)), diberi mask gradasi radial dari pojok kanan-atas (motif tampak)
    memudar total ke kiri-bawah (area teks) supaya tulisan tetap terbaca jelas.
    Props: $uid (string unik per pemakaian, agar id <pattern>/<mask> tak bentrok).
--}}
@php $uid = $uid ?? 'a'; @endphp
<svg class="absolute inset-0 w-full h-full pointer-events-none select-none" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <defs>
        <pattern id="batik-{{ $uid }}-1" patternUnits="userSpaceOnUse" width="90" height="90">
            <g fill="none" stroke="currentColor" stroke-width=".9" stroke-linecap="round">
                <g transform="translate(22,22) rotate(45)">
                    <ellipse cx="0" cy="-14" rx="7.5" ry="13"/>
                    <ellipse cx="14" cy="0" rx="13" ry="7.5"/>
                    <ellipse cx="0" cy="14" rx="7.5" ry="13"/>
                    <ellipse cx="-14" cy="0" rx="13" ry="7.5"/>
                </g>
                <circle cx="22" cy="22" r="2" fill="currentColor" stroke="none" opacity=".5"/>
                <g transform="translate(67,67) rotate(45)">
                    <ellipse cx="0" cy="-14" rx="7.5" ry="13"/>
                    <ellipse cx="14" cy="0" rx="13" ry="7.5"/>
                    <ellipse cx="0" cy="14" rx="7.5" ry="13"/>
                    <ellipse cx="-14" cy="0" rx="13" ry="7.5"/>
                </g>
                <circle cx="67" cy="67" r="2" fill="currentColor" stroke="none" opacity=".5"/>
            </g>
        </pattern>
        <pattern id="batik-{{ $uid }}-2" patternUnits="userSpaceOnUse" width="90" height="90" patternTransform="translate(45,0)">
            <g fill="none" stroke="currentColor" stroke-width=".8" stroke-linecap="round">
                <g transform="translate(22,22) rotate(45)">
                    <ellipse cx="0" cy="-14" rx="7.5" ry="13"/>
                    <ellipse cx="14" cy="0" rx="13" ry="7.5"/>
                    <ellipse cx="0" cy="14" rx="7.5" ry="13"/>
                    <ellipse cx="-14" cy="0" rx="13" ry="7.5"/>
                </g>
                <circle cx="22" cy="22" r="1.8" fill="currentColor" stroke="none" opacity=".45"/>
            </g>
        </pattern>

        {{-- Gradasi horizontal: tersembunyi di kiri (area teks) → tampak penuh di kanan.
             Konstan dari atas sampai bawah kartu (bukan radial) agar motif tetap menyala
             merata sepanjang tinggi kartu, tak terpotong di bagian bawah. --}}
        <linearGradient id="batik-{{ $uid }}-fade" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%"  stop-color="#000" stop-opacity="0"/>
            <stop offset="42%" stop-color="#000" stop-opacity="0"/>
            <stop offset="68%" stop-color="#fff" stop-opacity=".55"/>
            <stop offset="100%" stop-color="#fff" stop-opacity="1"/>
        </linearGradient>
        <mask id="batik-{{ $uid }}-mask">
            <rect width="100%" height="100%" fill="url(#batik-{{ $uid }}-fade)"/>
        </mask>
    </defs>
    <g mask="url(#batik-{{ $uid }}-mask)">
        <rect width="100%" height="100%" fill="url(#batik-{{ $uid }}-1)" style="color:var(--cp)" opacity=".4"/>
        <rect width="100%" height="100%" fill="url(#batik-{{ $uid }}-2)" style="color:var(--ca)" opacity=".32"/>
    </g>
</svg>
