<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('contact_person', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'ntn' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'opening_balance' => 'required|numeric|min:0',
            'payment_terms' => 'nullable|string',
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'ntn' => $request->ntn,
            'credit_limit' => $request->credit_limit,
            'opening_balance' => $request->opening_balance,
            'current_balance' => $request->opening_balance,
            'payment_terms' => $request->payment_terms,
            'is_active' => true,
        ]);

        AuditLog::log('create', $supplier, null, $supplier->toArray(), 'Supplier created');

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'ntn' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'payment_terms' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $old = $supplier->toArray();

        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'ntn' => $request->ntn,
            'credit_limit' => $request->credit_limit,
            'payment_terms' => $request->payment_terms,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::log('update', $supplier, $old, $supplier->toArray(), 'Supplier updated');

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function ledger(Supplier $supplier, Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(90)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Invoices from supplier (Credit = we owe supplier)
        $invoices = SupplierInvoice::where('supplier_id', $supplier->id)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get()
            ->map(function ($inv) {
                return [
                    'date' => $inv->invoice_date,
                    'type' => 'Invoice (GRN)',
                    'ref' => $inv->invoice_number,
                    'credit' => (float)$inv->total_amount,
                    'debit' => 0.0,
                    'notes' => 'Goods received / Supplier bill',
                ];
            });

        // Payments to supplier (Debit = we paid supplier, balance reduces)
        $payments = SupplierPayment::where('supplier_id', $supplier->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get()
            ->map(function ($pay) {
                return [
                    'date' => $pay->payment_date,
                    'type' => 'Payment',
                    'ref' => $pay->payment_number,
                    'credit' => 0.0,
                    'debit' => (float)$pay->amount,
                    'notes' => 'Payment made via ' . ucfirst($pay->payment_method),
                ];
            });

        $entries = $invoices->concat($payments)->sortBy('date');

        $runningBalance = (float)$supplier->opening_balance;
        $ledgerRows = [];

        foreach ($entries as $e) {
            $runningBalance += ($e['credit'] - $e['debit']);
            $e['balance'] = $runningBalance;
            $ledgerRows[] = $e;
        }

        return view('suppliers.ledger', compact('supplier', 'ledgerRows', 'startDate', 'endDate'));
    }
}
