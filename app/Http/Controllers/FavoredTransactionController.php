<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FavoredTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoredTransactionController extends Controller
{
    public function index(?Client $client = null): JsonResponse
    {
        if ($client) {
            $this->authorize('view', $client);
        }

        $query = FavoredTransaction::with(['client', 'product', 'category'])
            ->where('company_id', auth()->user()->companies->first()->id);

        if ($client) {
            $query->where('client_id', $client->id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'client' => $client,
            'transactions' => $transactions,
            'total_debt' => $transactions->sum('favored_total'),
            'total_paid' => $transactions->sum('favored_paid_amount'),
            'total_items' => $transactions->sum('quantity'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'favored_total' => 'required|numeric|min:0',
            'favored_paid_amount' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:products_categories,id',
        ]);

        $validated['uuid'] = \Illuminate\Support\Str::uuid();
        $validated['company_id'] = auth()->user()->companies->first()->id;
        $validated['active'] = true;
        $validated['total_amount'] = $validated['amount'];

        $transaction = FavoredTransaction::create($validated);

        return response()->json([
            'message' => 'Transação de fiado criada com sucesso',
            'transaction' => $transaction,
        ], 201);
    }

    public function update(Request $request, FavoredTransaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'favored_total' => 'required|numeric|min:0',
            'favored_paid_amount' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:products_categories,id',
            'active' => 'boolean',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transação de fiado atualizada com sucesso',
            'transaction' => $transaction->fresh(),
        ]);
    }

    public function destroy(FavoredTransaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->json([
            'message' => 'Transação de fiado removida com sucesso',
        ]);
    }

    public function payDebt(Request $request, FavoredTransaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:'.$transaction->getRemainingBalance(),
        ]);

        $newPaidAmount = $transaction->favored_paid_amount + $validated['amount'];
        $transaction->update(['favored_paid_amount' => $newPaidAmount]);

        return response()->json([
            'message' => 'Pagamento registrado com sucesso',
            'transaction' => $transaction->fresh(),
            'remaining_balance' => $transaction->getRemainingBalance(),
        ]);
    }

    public function getClientsWithTransactions(): JsonResponse
    {
        $companyId = auth()->user()->companies->first()->id;

        $clients = DB::table('clients as c')
            ->leftJoin('favored_transactions as ft', function ($join) {
                $join->on('c.id', '=', 'ft.client_id');
            })
            ->select([
                'c.id',
                'c.uuid',
                'c.name',
                'c.document',
                'c.email',
                'c.phone',
                'c.type',
                'c.active',
                DB::raw('COUNT(ft.id) as transaction_count'),
                DB::raw('SUM(ft.favored_total) as total_debt'),
                DB::raw('SUM(ft.favored_paid_amount) as paid_amount'),
            ])
            ->where('c.company_id', $companyId)
            ->where('c.active', true)
            ->groupBy('c.id', 'c.uuid', 'c.name', 'c.document', 'c.email', 'c.phone', 'c.type', 'c.active')
            ->orderBy('c.name')
            ->get();

        return response()->json([
            'clients' => $clients,
            'total' => $clients->count(),
        ]);
    }
}
