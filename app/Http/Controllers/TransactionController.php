<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Transactions/Index', [
            'transactions' => Transaction::latest()->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:INCOME,EXPENSE',
            'amount' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $validated['transaction_no'] = 'TRX-' . strtoupper(uniqid());

        Transaction::create($validated);

        return to_route('transactions.index')->with('success', 'Transaction recorded successfully.');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:INCOME,EXPENSE',
            'amount' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);

        return to_route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return to_route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
