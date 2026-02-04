<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FavoredDebt;
use App\Models\FavoredTransaction;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    /**
     * Get client credit balance.
     * GET /api/companies/{company}/client/credit-balance
     */
    public function balance(Request $request, Company $company)
    {
        $clientUser = auth('sanctum')->user();
        $this->authorizeClientAccess($company, $clientUser);

        $debt = FavoredDebt::where('company_id', $company->id)
            ->where('client_id', $clientUser->client_id)
            ->first();

        if (! $debt) {
            return response()->json([
                'success' => true,
                'data' => [
                    'credit_limit' => 0,
                    'used_credit' => 0,
                    'available_credit' => 0,
                    'total_debt' => 0,
                    'overdue_amount' => 0,
                ],
            ]);
        }

        $overdue = FavoredTransaction::where('company_id', $company->id)
            ->where('client_id', $clientUser->client_id)
            ->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'credit_limit' => $debt->credit_limit,
                'used_credit' => $debt->total_debt,
                'available_credit' => $debt->available_credit,
                'total_debt' => $debt->total_debt,
                'overdue_amount' => $overdue,
            ],
        ]);
    }

    /**
     * Get transaction history.
     * GET /api/companies/{company}/client/transaction-history
     */
    public function history(Request $request, Company $company)
    {
        $clientUser = auth('sanctum')->user();
        $this->authorizeClientAccess($company, $clientUser);

        $query = FavoredTransaction::where('company_id', $company->id)
            ->where('client_id', $clientUser->client_id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('payment_status', $request->get('status'));
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $transactions = $query->with('client', 'product')
            ->latest('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Get upcoming payments.
     * GET /api/companies/{company}/client/upcoming-payments
     */
    public function upcomingPayments(Request $request, Company $company)
    {
        $clientUser = auth('sanctum')->user();
        $this->authorizeClientAccess($company, $clientUser);

        // Get transactions not yet paid, ordered by due date
        $transactions = FavoredTransaction::where('company_id', $company->id)
            ->where('client_id', $clientUser->client_id)
            ->where('payment_status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Group by due date
        $grouped = $transactions->groupBy(function ($transaction) {
            return $transaction->due_date->format('Y-m-d');
        })->map(function ($group) {
            return [
                'date' => $group->first()->due_date,
                'total_due' => $group->sum('amount'),
                'transactions' => $group->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Verify client has access to company.
     */
    private function authorizeClientAccess(Company $company, $clientUser): void
    {
        if (! $clientUser || ! $clientUser->companies()->where('companies.id', $company->id)->exists()) {
            abort(403, 'Unauthorized access to this company');
        }
    }
}
