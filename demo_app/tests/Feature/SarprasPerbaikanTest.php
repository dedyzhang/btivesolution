<?php

namespace Tests\Feature;

use App\Models\User;
use App\Sarpras\Models\Perbaikan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SarprasPerbaikanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tandai_perbaikan_selesai(): void
    {
        $admin = User::create(['username' => 'sap_prb', 'password' => Hash::make('x'), 'access' => 'superadmin']);

        $prb = Perbaikan::create([
            'kode' => 'PRB-TEST-01', 'deskripsi' => 'Servis AC', 'status' => 'dikerjakan', 'biaya' => 500000,
        ]);

        $this->actingAs($admin)->post('/sarpras/perbaikan/' . $prb->id . '/selesai')->assertRedirect();

        $prb->refresh();
        $this->assertSame('selesai', $prb->status);
        $this->assertNotNull($prb->tgl_selesai);
    }
}
