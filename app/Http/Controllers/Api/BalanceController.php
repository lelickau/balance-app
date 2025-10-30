<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BalanceService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BalanceController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    public function deposit(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        $balance = $this->balanceService->deposit(
            $data['user_id'],
            $data['amount'],
            $data['comment'] ?? null
        );

        return response()->json([
            'user_id' => $balance->user_id,
            'new_balance' => $balance->amount,
        ], 200);
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        $balance = $this->balanceService->withdraw(
            $data['user_id'],
            $data['amount'],
            $data['comment'] ?? null
        );

        return response()->json([
            'user_id' => $balance->user_id,
            'new_balance' => $balance->amount,
        ], 200);
    }

    public function getBalance($user_id)
    {
        if (!is_numeric($user_id)) {
            throw new HttpException(422, 'Неверный формат user_id');
        }

        $balance = $this->balanceService->getBalance((int)$user_id);

        return response()->json([
            'user_id' => $balance->user_id,
            'balance' => $balance->amount,
        ], 200);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        $result = $this->balanceService->transfer(
            $data['from_user_id'],
            $data['to_user_id'],
            $data['amount'],
            $data['comment'] ?? null
        );

        return response()->json($result, 200);
    }
}
