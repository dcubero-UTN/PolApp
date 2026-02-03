<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DailyVisit;
use App\Models\Sale;
use App\Models\User;
use App\Models\CollectionAttempt;
use App\Livewire\PaymentModal;
use App\Livewire\ClientIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnifiedVisitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_vendedor_can_record_a_payment_visit()
    {
        $vendedor = User::factory()->create();
        $vendedor->assignRole('vendedor');

        $client = Client::factory()->create(['user_id' => $vendedor->id, 'current_balance' => 10000]);
        $sale = Sale::factory()->create([
            'client_id' => $client->id,
            'user_id' => $vendedor->id,
            'total_amount' => 10000,
            'current_balance' => 10000,
            'suggested_quota' => 2000
        ]);

        Livewire::actingAs($vendedor)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->set('amount', 2000)
            ->set('payment_method', 'efectivo')
            ->set('attempt_latitude', 9.9333)
            ->set('attempt_longitude', -84.0833)
            ->call('finalizeVisit')
            ->assertSet('showModal', true) // Remains open to show WhatsApp button
            ->assertSeeHtml('wa.me'); // Check if link was generated

        // Assertions
        $this->assertDatabaseHas('payments', [
            'amount' => 2000,
            'sale_id' => $sale->id
        ]);

        $this->assertDatabaseHas('daily_visits', [
            'client_id' => $client->id,
            'user_id' => $vendedor->id,
            'completed' => true,
            'result' => 'abono'
        ]);

        $this->assertDatabaseHas('collection_attempts', [
            'client_id' => $client->id,
            'reason' => 'pago_realizado',
            'latitude' => 9.9333
        ]);

        $this->assertEquals(8000, $client->refresh()->current_balance);
        $this->assertEquals(8000, $sale->refresh()->current_balance);
    }

    public function test_vendedor_can_record_a_no_payment_visit()
    {
        $vendedor = User::factory()->create();
        $vendedor->assignRole('vendedor');

        $client = Client::factory()->create(['user_id' => $vendedor->id, 'collection_day' => 'Lunes']);
        $sale = Sale::factory()->create(['client_id' => $client->id, 'current_balance' => 5000]);

        Livewire::actingAs($vendedor)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->set('amount', 0)
            ->set('attempt_reason', 'no_estaba')
            ->set('attempt_notes', 'Fui a las 10am y no habia nadie')
            ->call('finalizeVisit')
            ->assertSet('showModal', false) // Closes directly if no payment receipt needed
            ->assertDispatched('visit-completed');

        $this->assertDatabaseHas('daily_visits', [
            'client_id' => $client->id,
            'completed' => true,
            'result' => 'incumplimiento'
        ]);

        $this->assertDatabaseHas('collection_attempts', [
            'client_id' => $client->id,
            'reason' => 'no_estaba',
            'notes' => 'Fui a las 10am y no habia nadie'
        ]);
    }

    public function test_reason_is_mandatory_if_amount_is_zero()
    {
        $vendedor = User::factory()->create();
        $vendedor->assignRole('vendedor');
        $client = Client::factory()->create();
        $sale = Sale::factory()->create(['client_id' => $client->id, 'current_balance' => 1000]);

        Livewire::actingAs($vendedor)
            ->test(PaymentModal::class)
            ->call('openPaymentModal', $client->id)
            ->set('amount', 0)
            ->set('attempt_reason', '')
            ->call('finalizeVisit')
            ->assertHasErrors(['attempt_reason' => 'required']);
    }

    public function test_client_index_shows_progress_bar_and_visited_status()
    {
        $vendedor = User::factory()->create();
        $vendedor->assignRole('vendedor');
        $this->actingAs($vendedor);
        \Illuminate\Support\Facades\Auth::login($vendedor);

        $day = date('l');
        $daysMap = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];
        $todaySpanish = $daysMap[$day];

        $client1 = Client::factory()->create(['user_id' => $vendedor->id, 'collection_day' => $todaySpanish]);
        $client2 = Client::factory()->create(['user_id' => $vendedor->id, 'collection_day' => $todaySpanish]);

        $todayStr = date('Y-m-d');

        // Visit one
        DailyVisit::create([
            'user_id' => $vendedor->id,
            'client_id' => $client1->id,
            'visit_date' => $todayStr,
            'completed' => true,
            'result' => 'abono'
        ]);

        Livewire::actingAs($vendedor)
            ->test(ClientIndex::class)
            ->assertViewHas('totalRoute', 2)
            ->assertViewHas('completedRoute', 1)
            ->assertSee('1/2 completadas');
    }
}
