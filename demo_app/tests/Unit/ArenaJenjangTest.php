<?php

namespace Tests\Unit;

use App\Support\ArenaJenjang;
use PHPUnit\Framework\TestCase;

class ArenaJenjangTest extends TestCase
{
    public function test_infer_jenjang_dari_grade_level(): void
    {
        $this->assertSame('sd', ArenaJenjang::fromGradeLevel('SD 3–4'));
        $this->assertSame('smp', ArenaJenjang::fromGradeLevel('SMP 7–8'));
        $this->assertSame('sma', ArenaJenjang::fromGradeLevel('SMA/SMK 10–11'));
        $this->assertSame('sma', ArenaJenjang::fromGradeLevel('SMK 11'));
    }

    public function test_meta_jenjang_lebih_diutamakan(): void
    {
        $this->assertSame('smp', ArenaJenjang::fromGradeLevel('Kelas 8', ['jenjang' => 'smp']));
    }

    public function test_rekomendasi_memiliki_tiga_jenjang_dengan_mark_permainan(): void
    {
        $rek = ArenaJenjang::rekomendasi();

        $this->assertArrayHasKey('sd', $rek);
        $this->assertArrayHasKey('smp', $rek);
        $this->assertArrayHasKey('sma', $rek);
        $this->assertCount(3, $rek['sd']);
        $this->assertCount(3, $rek['smp']);
        $this->assertCount(3, $rek['sma']);
        $this->assertStringContainsString('Angka', $rek['sd'][0]['title']);
        $this->assertStringContainsString('Gaya', $rek['smp'][0]['title']);
        $this->assertStringContainsString('Persamaan', $rek['sma'][0]['title']);
    }

    public function test_tren_rekomendasi_2025_2026_per_jenjang(): void
    {
        $tren = ArenaJenjang::trenRekomendasi();

        $this->assertCount(3, $tren['sd']);
        $this->assertCount(3, $tren['smp']);
        $this->assertCount(3, $tren['sma']);
        $this->assertStringContainsString('Jeda Layar', $tren['sd'][0]['title']);
        $this->assertStringContainsString('Kenalan dengan AI', $tren['smp'][1]['title']);
        $this->assertStringContainsString('Deepfake', $tren['sma'][2]['title']);
        $this->assertSame('Literasi AI', $tren['sma'][0]['tren_tag']);
    }
}
