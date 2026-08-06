<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\PaketLayanan;
use App\Models\Transaksi;
use App\Services\RandomForestService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpressLaundryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_and_monitoring_page_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SINDORY');
    }

    public function test_random_forest_service_calculation()
    {
        $rfService = new RandomForestService();
        $result = $rfService->predictDuration(5.0, 'Cuci Komplit', 'Pakaian Harian', 2);

        $this->assertEquals('success', $result['status']);
        $this->assertGreaterThan(0, $result['predicted_duration_hours']);
        $this->assertGreaterThan(0.8, $result['confidence_score']);
        $this->assertCount(50, $result['tree_predictions']);
    }

    public function test_kasir_can_create_transaction_with_rf_estimation()
    {
        $kasir = User::where('role', 'karyawan')->first() ?? User::where('role', 'kasir')->first();
        $paket = PaketLayanan::first();

        $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
            'nama_pelanggan' => 'Pelanggan Baru Test',
            'no_telepon' => '089999888777',
            'alamat' => 'Jl. Test No. 123',
            'id_paket' => $paket->id_paket,
            'berat_qty' => 4.5,
            'kategori_pakaian' => 'Pakaian Harian',
        ]);

        $this->assertDatabaseHas('transaksi', [
            'id_kasir' => $kasir->id_user,
            'status_pengerjaan' => 'Cuci',
        ]);

        $this->assertDatabaseHas('prediksi_analisis', [
            'durasi_estimasi_jam' => 5.34,
        ]);
    }

    public function test_pemilik_can_access_dashboard()
    {
        $pemilik = User::where('role', 'pemilik')->first();

        $response = $this->actingAs($pemilik)->get(route('pemilik.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Executive Dashboard SINDORY');
    }

    public function test_pelanggan_login_redirects_successfully()
    {
        $pelanggan = User::where('role', 'pelanggan')->first();

        // 1. Test Login via Username
        $responseUsername = $this->post('/login', [
            'login' => $pelanggan->username,
            'password' => 'password123',
        ]);
        $responseUsername->assertRedirect(route('monitoring'));
        $this->assertAuthenticatedAs($pelanggan);

        // Logout
        $this->post('/logout');

        // 2. Test Login via Email
        $responseEmail = $this->post('/login', [
            'login' => $pelanggan->email,
            'password' => 'password123',
        ]);
        $responseEmail->assertRedirect(route('monitoring'));
        $this->assertAuthenticatedAs($pelanggan);
    }
}
