<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Customer::with(['area', 'assignedSalesman']);

        // Salesman restriction per FR-4.5
        if ($user->isSalesman()) {
            $query->where('assigned_salesman_id', $user->id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('shop_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('salesman_id')) {
            $query->where('assigned_salesman_id', $request->salesman_id);
        }

        if ($request->filled('type')) {
            $query->where('customer_type', $request->type);
        }

        $customers = $query->orderBy('shop_name')->paginate(20)->withQueryString();
        $areas = Area::all();
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->get();

        return view('customers.index', compact('customers', 'areas', 'salesmen'));
    }

    public function create()
    {
        $areas = Area::all();
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->get();

        return view('customers.create', compact('areas', 'salesmen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'shop_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
            'assigned_salesman_id' => 'nullable|exists:users,id',
            'customer_type' => 'required|in:wholesale,retail',
            'credit_limit' => 'required|numeric|min:0',
            'opening_balance' => 'required|numeric|min:0',
            'payment_terms_days' => 'required|integer|min:0',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'shop_name' => $request->shop_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'area_id' => $request->area_id,
            'assigned_salesman_id' => $request->assigned_salesman_id,
            'customer_type' => $request->customer_type,
            'credit_limit' => $request->credit_limit,
            'opening_balance' => $request->opening_balance,
            'current_balance' => $request->opening_balance,
            'payment_terms_days' => $request->payment_terms_days,
            'is_active' => true,
        ]);

        AuditLog::log('create', $customer, null, $customer->toArray(), 'New customer created');

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        $areas = Area::all();
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->get();

        return view('customers.edit', compact('customer', 'areas', 'salesmen'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'shop_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
            'assigned_salesman_id' => 'nullable|exists:users,id',
            'customer_type' => 'required|in:wholesale,retail',
            'credit_limit' => 'required|numeric|min:0',
            'payment_terms_days' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $old = $customer->toArray();

        $customer->update([
            'name' => $request->name,
            'shop_name' => $request->shop_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'area_id' => $request->area_id,
            'assigned_salesman_id' => $request->assigned_salesman_id,
            'customer_type' => $request->customer_type,
            'credit_limit' => $request->credit_limit,
            'payment_terms_days' => $request->payment_terms_days,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::log('update', $customer, $old, $customer->toArray(), 'Customer updated');

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function ledger(Customer $customer, Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(90)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Gather all invoices (Debit = amount customer owes)
        $invoices = Invoice::where('customer_id', $customer->id)
            ->where('status', 'posted')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get()
            ->map(function ($inv) {
                return [
                    'date' => $inv->invoice_date,
                    'type' => 'Invoice',
                    'ref' => $inv->invoice_number,
                    'debit' => (float)$inv->grand_total,
                    'credit' => (float)$inv->paid_amount, // If paid on spot
                    'notes' => 'Sales Invoice (' . ucfirst($inv->payment_type) . ')',
                ];
            });

        // Gather customer recovery receipts (Credit = payment received from customer)
        $receipts = CustomerReceipt::where('customer_id', $customer->id)
            ->whereBetween('receipt_date', [$startDate, $endDate])
            ->get()
            ->map(function ($rec) {
                return [
                    'date' => $rec->receipt_date,
                    'type' => 'Receipt',
                    'ref' => $rec->receipt_number,
                    'debit' => 0.0,
                    'credit' => (float)$rec->amount,
                    'notes' => 'Payment received via ' . ucfirst($rec->payment_method),
                ];
            });

        $entries = $invoices->concat($receipts)->sortBy('date');

        // Calculate running balance starting with opening balance
        $runningBalance = (float)$customer->opening_balance;
        $ledgerRows = [];

        foreach ($entries as $e) {
            $runningBalance += ($e['debit'] - $e['credit']);
            $e['balance'] = $runningBalance;
            $ledgerRows[] = $e;
        }

        return view('customers.ledger', compact('customer', 'ledgerRows', 'startDate', 'endDate'));
    }

    public function aging()
    {
        // 0-30, 31-60, 61-90, 90+ days aging calculation (FR-136)
        $customers = Customer::where('is_active', true)
            ->where('current_balance', '>', 0)
            ->with(['assignedSalesman', 'area'])
            ->get();

        $agingData = [];
        $totals = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0, 'total' => 0];

        foreach ($customers as $c) {
            $pendingInvoices = Invoice::where('customer_id', $c->id)
                ->where('status', 'posted')
                ->where('due_amount', '>', 0)
                ->get();

            $c0_30 = 0;
            $c31_60 = 0;
            $c61_90 = 0;
            $c90_plus = 0;

            foreach ($pendingInvoices as $inv) {
                $days = now()->diffInDays(\Carbon\Carbon::parse($inv->invoice_date));
                $amount = (float)$inv->due_amount;

                if ($days <= 30) {
                    $c0_30 += $amount;
                } elseif ($days <= 60) {
                    $c31_60 += $amount;
                } elseif ($days <= 90) {
                    $c61_90 += $amount;
                } else {
                    $c90_plus += $amount;
                }
            }

            // If customer has opening balance not fully attributed to an invoice
            $invoiceDueSum = $pendingInvoices->sum('due_amount');
            if ($c->current_balance > $invoiceDueSum) {
                $diff = $c->current_balance - $invoiceDueSum;
                $c90_plus += $diff; // attribute unallocated opening balance to 90+ days
            }

            $custTotal = $c0_30 + $c31_60 + $c61_90 + $c90_plus;

            $agingData[] = [
                'customer' => $c,
                '0_30' => $c0_30,
                '31_60' => $c31_60,
                '61_90' => $c61_90,
                '90_plus' => $c90_plus,
                'total' => $custTotal,
                'is_overdue_blocked' => ($c31_60 + $c61_90 + $c90_plus) > 0 && ($c61_90 + $c90_plus) > 0,
            ];

            $totals['0_30'] += $c0_30;
            $totals['31_60'] += $c31_60;
            $totals['61_90'] += $c61_90;
            $totals['90_plus'] += $c90_plus;
            $totals['total'] += $custTotal;
        }

        return view('customers.aging', compact('agingData', 'totals'));
    }
}
