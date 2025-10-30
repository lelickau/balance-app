<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Balance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BalanceApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_deposit_money()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'new_balance' => 500.00,
            ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user->id,
            'amount' => 500.00,
        ]);
    }

    #[Test]
    public function it_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();
        Balance::create(['user_id' => $user->id, 'amount' => 100]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 200,
            'comment' => 'Попытка перерасхода',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Недостаточно средств',
            ]);
    }

    #[Test]
    public function it_can_transfer_money_between_users()
    {
        $from = User::factory()->create();
        $to = User::factory()->create();

        Balance::create(['user_id' => $from->id, 'amount' => 300]);
        Balance::create(['user_id' => $to->id, 'amount' => 100]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $from->id,
            'to_user_id' => $to->id,
            'amount' => 150,
            'comment' => 'Перевод другу',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('balances', [
            'user_id' => $from->id,
            'amount' => 150.00,
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $to->id,
            'amount' => 250.00,
        ]);
    }

    #[Test]
    public function it_returns_balance_for_existing_user()
    {
        $user = User::factory()->create();
        Balance::create(['user_id' => $user->id, 'amount' => 350]);

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 350.00,
            ]);
    }

    #[Test]
    public function it_returns_404_if_user_not_found()
    {
        $response = $this->getJson('/api/balance/9999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Пользователь не найден',
            ]);
    }
}
