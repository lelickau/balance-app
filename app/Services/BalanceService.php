<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BalanceService
{
    public function deposit(int $userId, string $amount, ?string $comment = null): Balance
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $balance = Balance::firstOrCreate(['user_id' => $userId]);

            $balance->amount = bcadd($balance->amount, $amount, 2);
            $balance->save();

            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return $balance;
        });
    }

    public function withdraw(int $userId, string $amount, ?string $comment = null): Balance
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $balance = Balance::firstOrCreate(['user_id' => $userId]);

            if (bccomp($balance->amount, $amount, 2) === -1) {
                throw new HttpException(409, 'Недостаточно средств');
            }

            $balance->amount = bcsub($balance->amount, $amount, 2);
            $balance->save();

            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return $balance;
        });
    }

    public function getBalance(int $user_id): Balance
    {
        $user = User::find($user_id);
        if (!$user) {
            throw new HttpException(404, 'Пользователь не найден');
        }

        return DB::transaction(function () use ($user_id) {
            return Balance::firstOrCreate(['user_id' => $user_id]);
        });
    }

    public function transfer(int $fromUserId, int $toUserId, string $amount, ?string $comment = null): array
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {
            $fromBalance = Balance::firstOrCreate(['user_id' => $fromUserId]);
            $toBalance = Balance::firstOrCreate(['user_id' => $toUserId]);

            if ($fromUserId === $toUserId) {
                abort(422, 'Нельзя переводить деньги самому себе');
            }

            if (bccomp($fromBalance->amount, $amount, 2) === -1) {
                throw new HttpException(409, 'Недостаточно средств');
            }

            $fromBalance->amount = bcsub($fromBalance->amount, $amount, 2);
            $fromBalance->save();

            $toBalance->amount = bcadd($toBalance->amount, $amount, 2);
            $toBalance->save();

            Transaction::create([
                'user_id' => $fromUserId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            Transaction::create([
                'user_id' => $toUserId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return [
                'from_user_id' => $fromUserId,
                'from_balance' => $fromBalance->amount,
                'to_user_id' => $toUserId,
                'to_balance' => $toBalance->amount,
            ];
        });
    }
}
