<?php

namespace Tests\Feature;

use App\Support\QuizDocument;
use App\Support\QuizDocxBuilder;
use Tests\TestCase;

class QuizDocumentTest extends TestCase
{
    public function test_kop_judul_identitas_dan_petunjuk_terbaca(): void
    {
        $doc = QuizDocument::parse($this->keluaranAi());

        $this->assertTrue($doc['parsed']);
        $this->assertSame('YAYASAN BUMI MAITRI', $doc['kop'][0]);
        $this->assertSame('SOAL EVALUASI PENDIDIKAN AGAMA BUDDHA', $doc['title']);
        $this->assertSame('Kelas VIII - Tingkat Kesulitan Sedang', $doc['subtitle']);
        $this->assertSame(['label' => 'Mata Pelajaran', 'value' => 'Pendidikan Agama Buddha'], $doc['identity'][0]);
        $this->assertSame('Petunjuk Pengerjaan', $doc['petunjuk']['heading']);
        $this->assertCount(2, $doc['petunjuk']['lines']);
    }

    public function test_bagian_soal_pilihan_ganda_dan_esai_terpisah(): void
    {
        $doc = QuizDocument::parse($this->keluaranAi());

        $this->assertCount(2, $doc['sections']);

        [$pg, $esai] = $doc['sections'];
        $this->assertSame('Bagian A - Pilihan Ganda', $pg['heading']);
        $this->assertCount(2, $pg['questions']);
        $this->assertSame('1', $pg['questions'][0]['number']);
        $this->assertSame('Mengapa bentuk simbol Buddha berbeda-beda?', $pg['questions'][0]['text']);
        $this->assertCount(4, $pg['questions'][0]['options']);
        $this->assertSame(['label' => 'B', 'text' => 'Karena pengaruh budaya setempat.'], $pg['questions'][0]['options'][1]);

        $this->assertSame('Bagian B - Esai', $esai['heading']);
        $this->assertSame(['Jawablah dengan uraian 150-200 kata.'], $esai['intro']);
        $this->assertSame('3', $esai['questions'][0]['number']);
        $this->assertTrue($esai['questions'][0]['answer_space']);
    }

    public function test_kunci_jawaban_pg_esai_dan_rubrik_terbaca(): void
    {
        $kunci = QuizDocument::parse($this->keluaranAi())['kunci'];

        $this->assertSame('Kunci Jawaban & Pedoman Penilaian', $kunci['heading']);
        $this->assertSame('(Untuk Guru)', $kunci['subtitle']);
        $this->assertSame([
            ['number' => '1', 'answer' => 'B'],
            ['number' => '2', 'answer' => 'A'],
        ], $kunci['pg']);
        $this->assertSame('Soal 3', $kunci['esai'][0]['heading']);
        $this->assertSame(['Ajaran Buddha menekankan welas asih.'], $kunci['esai'][0]['lines']);
        $this->assertSame('Rubrik Penilaian Esai (masing-masing 4 poin)', $kunci['rubrik']['heading']);
        $this->assertSame(['Pemahaman konsep (2 poin): memaparkan ide utama dengan benar.'], $kunci['rubrik']['lines']);
    }

    public function test_soal_yang_terpotong_dua_baris_disatukan(): void
    {
        $doc = QuizDocument::parse(implode("\n", [
            'SOAL EVALUASI IPA',
            'Bagian A - Pilihan Ganda',
            '1. Apa yang terjadi pada air',
            'ketika dipanaskan hingga mendidih?',
            'A. Menguap',
            'B. Membeku',
        ]));

        $this->assertSame(
            'Apa yang terjadi pada air ketika dipanaskan hingga mendidih?',
            $doc['sections'][0]['questions'][0]['text'],
        );
    }

    public function test_kunci_jawaban_tipe_soal_baru_tetap_terbaca(): void
    {
        $content = implode("\n", [
            'SOAL EVALUASI IPA',
            'Bagian A - Pilihan Ganda Kompleks',
            '1. Manakah yang termasuk komponen biotik?',
            'A. Air',
            'B. Rumput',
            'C. Kucing',
            'D. Batu',
            'Bagian B - Benar/Salah',
            '2. Air termasuk komponen abiotik.',
            'Kunci Jawaban & Pedoman Penilaian',
            '(Untuk Guru)',
            'Pilihan Ganda Kompleks',
            '1. B, C',
            'Benar/Salah',
            '2. Benar',
        ]);

        $doc = QuizDocument::parse($content);

        $this->assertTrue($doc['parsed']);
        $this->assertSame('Pilihan Ganda Kompleks', $doc['kunci']['lainnya'][0]['heading']);
        $this->assertSame(['1. B, C'], $doc['kunci']['lainnya'][0]['lines']);
        $this->assertSame('Benar/Salah', $doc['kunci']['lainnya'][1]['heading']);
        $this->assertSame(['2. Benar'], $doc['kunci']['lainnya'][1]['lines']);

        $xml = QuizDocxBuilder::documentXml($doc);
        $this->assertStringContainsString('Pilihan Ganda Kompleks', $xml);
        $this->assertStringContainsString('2. Benar', $xml);
    }

    public function test_konten_tanpa_format_soal_tidak_dianggap_parsed(): void
    {
        $doc = QuizDocument::parse("Catatan bebas guru.\nTidak berformat soal sama sekali.");

        $this->assertFalse($doc['parsed']);
        $this->assertStringContainsString('Catatan bebas guru.', $doc['text']);
    }

    public function test_docx_soal_berisi_kop_judul_soal_dan_tabel_kunci(): void
    {
        $xml = QuizDocxBuilder::documentXml(QuizDocument::parse($this->keluaranAi()));

        $this->assertStringContainsString('YAYASAN BUMI MAITRI', $xml);
        $this->assertStringContainsString('SOAL EVALUASI PENDIDIKAN AGAMA BUDDHA', $xml);
        $this->assertStringContainsString('Bagian A - Pilihan Ganda', $xml);
        $this->assertStringContainsString('Mengapa bentuk simbol Buddha berbeda-beda?', $xml);
        $this->assertStringContainsString('Kunci Jawaban &amp; Pedoman Penilaian', $xml);
        // Kunci jawaban dimulai di halaman baru dan jawaban PG tampil sebagai tabel.
        $this->assertStringContainsString('<w:br w:type="page"/>', $xml);
        $this->assertStringContainsString('<w:tbl>', $xml);
    }

    /** Keluaran generator soal sesuai format acuan soal-agama-buddha.docx. */
    private function keluaranAi(): string
    {
        return implode("\n", [
            'YAYASAN BUMI MAITRI',
            'SMP MAITREYAWIRA TANJUNGPINANG',
            'TERAKREDITASI A',
            'Jl. Prof. Ir. Sutami No. 38  Telp (0771) 4505723  Email smpmai.tpi@gmail.com',
            'SOAL EVALUASI PENDIDIKAN AGAMA BUDDHA',
            'Kelas VIII - Tingkat Kesulitan Sedang',
            '',
            'Mata Pelajaran : Pendidikan Agama Buddha',
            'Kelas / Semester : VIII / Ganjil',
            'Nama : ...............................................................',
            'Nilai : ...............................................................',
            '',
            'Petunjuk Pengerjaan',
            'Kerjakan soal pilihan ganda dengan memberi tanda silang (X) pada jawaban yang benar.',
            'Jawablah soal esai secara jelas dan terstruktur.',
            '',
            'Bagian A - Pilihan Ganda',
            '1. Mengapa bentuk simbol Buddha berbeda-beda?',
            'A. Karena diciptakan penguasa politik.',
            'B. Karena pengaruh budaya setempat.',
            'C. Karena berubah seiring waktu.',
            'D. Karena dipilih secara acak.',
            '2. Festival Kue Bulan dirayakan pada tanggal berapa kalender Lunar?',
            'A. 15 bulan 8',
            'B. 1 bulan 1',
            'C. 30 bulan 10',
            'D. 1 bulan 5',
            '',
            'Bagian B - Esai',
            'Jawablah dengan uraian 150-200 kata.',
            '3. Jelaskan mengapa budaya Buddhis dapat beradaptasi dengan budaya lain.',
            '_______________________________________________________________________',
            '',
            'Kunci Jawaban & Pedoman Penilaian',
            '(Untuk Guru)',
            '',
            'Pilihan Ganda',
            '1. B',
            '2. A',
            '',
            'Esai - Poin Jawaban Ideal',
            'Soal 3',
            'Ajaran Buddha menekankan welas asih.',
            '',
            'Rubrik Penilaian Esai (masing-masing 4 poin)',
            'Pemahaman konsep (2 poin): memaparkan ide utama dengan benar.',
        ]);
    }
}
