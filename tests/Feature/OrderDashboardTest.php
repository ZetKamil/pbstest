<?php

namespace Tests\Feature;

use App\Livewire\OrderDashboard;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Class OrderDashboardTest
 * 
 * Feature tests for OrderDashboard Livewire component: rendering and real-time search filtering.
 */
class OrderDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the homepage loads successfully and includes the OrderDashboard component.
     */
    public function test_it_renders_the_dashboard_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeLivewire(OrderDashboard::class);
    }

    /**
     * Test searching orders by user name filters the list in real-time.
     */
    public function test_it_can_search_by_user_name(): void
    {
        $userA = User::factory()->create(['name' => 'Alice Johnson']);
        $userB = User::factory()->create(['name' => 'Bob Smith']);

        Order::factory()->create(['user_id' => $userA->id, 'amount' => 150.00]);
        Order::factory()->create(['user_id' => $userB->id, 'amount' => 300.00]);

        Livewire::test(OrderDashboard::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Johnson')
            ->assertDontSee('Bob Smith');
    }
}
