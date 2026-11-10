<table>
    <!-- Spacer rows for Kop are handled by WithEvents -->
    <tbody>
        <tr>
            <!-- Placeholder for A1-A3 -->
        </tr>
        <tr></tr>
        <tr></tr>
        
        <!-- Header Row 1 -->
        <tr>
            <th>No</th>
            <th>Nama</th>
            @foreach($period as $date)
                <th colspan="2">{{ $date->format('d-m-Y') }}</th>
            @endforeach
        </tr>
        
        <!-- Header Row 2 -->
        <tr>
            <th></th>
            <th></th>
            @foreach($period as $date)
                <th>Datang</th>
                <th>Pulang</th>
            @endforeach
        </tr>
        
        <!-- Data Rows -->
        @foreach($gurus as $index => $guru)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $guru->nama }}</td>
                
                @foreach($period as $date)
                    @php
                        $key = $guru->uuid . '_' . $date->format('Y-m-d');
                        $presensi = $presensiData->get($key)?->first();
                        
                        $datang = '-';
                        $pulang = '-';
                        $isTerlambat = false;
                        
                        if ($presensi) {
                            if ($presensi->status == 'hadir') {
                                $datang = $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-';
                                $pulang = $presensi->jam_pulang ? substr($presensi->jam_pulang, 0, 5) : '-';
                                $isTerlambat = $presensi->jam_masuk && substr($presensi->jam_masuk, 0, 5) > $waktuTerlambat;
                            } else {
                                $keterangan = App\Models\PresensiGuru::STATUS[$presensi->status] ?? $presensi->status;
                                $datang = $keterangan;
                                $pulang = $keterangan;
                            }
                        }
                    @endphp
                    <td @if($isTerlambat) style="background-color: #FECACA; color: #991B1B;" @endif>{{ $datang }}</td>
                    <td>{{ $pulang }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
