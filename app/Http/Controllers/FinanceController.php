<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BankBookEntry;
use App\Models\CashBookEntry;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\CustomerReceiptAllocation;
use App\Models\DayClosing;
use App\Models\DocumentSequence;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    // --- Customer Receipts (Recovery) (FR-132 - FR-134) ---
    public function receipts(Request $request)
    {
        $user = auth()->user();
        $query = CustomerReceipt::with(['customer', 'salesman', 'allocations.invoice'])->latest('id');

        if ($user->isSalesman()) {
            $query->where('salesman_id', $user->id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $receipts = $query->paginate(20)->withQueryString();
        $customers = Customer::where('is_active', true)->where('current_balance', '>', 0)->get();
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->get();

        return view('finance.receipts.index', compact('receipts', 'customers', 'salesmen'));
    }

    public function storeReceipt(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'receipt_date' => 'nullable|date',
            'payment_method' => 'required|in:cash,bank,bank_transfer,cheque',
            'amount' => 'required|numeric|min:1',
            'reference_no' => 'nullable|string',
            'cheque_no' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'cheque_bank' => 'nullable|string',
            'cheque_date' => 'nullable|date',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        DB::transaction(function () use ($request, $customer) {
            $recNumber = DocumentSequence::getNextNumber('REC');
            $user = auth()->user();

            $receiptDate = $request->receipt_date ?: now()->toDateString();
            $paymentMethod = $request->payment_method === 'bank_transfer' ? 'bank' : $request->payment_method;
            $chequeNo = $request->cheque_no ?: $request->cheque_number;
            $refNo = $request->reference_no ?: $request->cheque_bank;

            $receipt = CustomerReceipt::create([
                'receipt_number' => $recNumber,
                'customer_id' => $customer->id,
                'salesman_id' => $request->salesman_id ?? $customer->assigned_salesman_id ?? $user->id,
                'receipt_date' => $receiptDate,
                'payment_method' => $paymentMethod,
                'amount' => $request->amount,
                'reference_no' => $refNo,
                'cheque_no' => $chequeNo,
                'cheque_date' => $request->cheque_date,
                'status' => $paymentMethod === 'cheque' ? 'pending' : 'cleared',
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            // If cheque, register in cheques table (FR-139)
            if ($paymentMethod === 'cheque') {
                Cheque::create([
                    'type' => 'received_customer',
                    'cheque_number' => $chequeNo ?: 'CHQ-' . time(),
                    'bank_name' => $refNo ?: 'Bank',
                    'issue_date' => $receiptDate,
                    'due_date' => $request->cheque_date ?? $receiptDate,
                    'amount' => $request->amount,
                    'party_type' => 'customer',
                    'party_id' => $customer->id,
                    'status' => 'received',
                ]);
            }

            // Allocate against oldest unpaid invoices (FR-133)
            $amountToAllocate = (float)$request->amount;
            $unpaidInvoices = Invoice::where('customer_id', $customer->id)
                ->where('status', 'posted')
                ->where('due_amount', '>', 0)
                ->orderBy('invoice_date', 'asc')
                ->get();

            foreach ($unpaidInvoices as $inv) {
                if ($amountToAllocate <= 0) break;

                $alloc = min($amountToAllocate, (float)$inv->due_amount);
                $inv->paid_amount += $alloc;
                $inv->due_amount -= $alloc;
                $inv->save();

                CustomerReceiptAllocation::create([
                    'customer_receipt_id' => $receipt->id,
                    'invoice_id' => $inv->id,
                    'allocated_amount' => $alloc,
                ]);

                $amountToAllocate -= $alloc;
            }

            // Deduct customer balance
            $customer->decrement('current_balance', $request->amount);

            // Record cash book entry if cash
            if ($paymentMethod === 'cash') {
                CashBookEntry::record('inflow', $request->amount, 'wholesale_receipt', $recNumber, "Customer collection from {$customer->shop_name}");
            } elseif ($paymentMethod === 'bank') {
                $lastBal = BankBookEntry::latest('id')->value('balance_after') ?? 0;
                BankBookEntry::create([
                    'entry_date' => $receiptDate,
                    'type' => 'deposit',
                    'bank_name' => $refNo ?: 'Online Transfer',
                    'reference_id' => $recNumber,
                    'amount' => $request->amount,
                    'balance_after' => $lastBal + $request->amount,
                    'description' => "Online collection from {$customer->shop_name}",
                    'user_id' => $user->id,
                ]);
            }

            AuditLog::log('create', $receipt, null, $receipt->toArray(), "Customer receipt {$recNumber} for Rs. {$request->amount} recorded");
        });

        return redirect()->route('finance.receipts')->with('success', 'Customer collection receipt recorded successfully.');
    }

    // --- Cheques Management (FR-139, FR-140) ---
    public function cheques()
    {
        $cheques = Cheque::latest('id')->paginate(20);
        return view('finance.cheques.index', compact('cheques'));
    }

    public function bounceCheque(Request $request, Cheque $cheque)
    {
        $request->validate(['bounced_reason' => 'required|string']);

        DB::transaction(function () use ($cheque, $request) {
            $cheque->status = 'bounced';
            $cheque->bounced_reason = $request->bounced_reason;
            $cheque->bounced_at = now();
            $cheque->save();

            // Re-add to customer balance per FR-140!
            if ($cheque->party_type === 'customer') {
                $customer = Customer::find($cheque->party_id);
                if ($customer) {
                    $customer->increment('current_balance', $cheque->amount);
                }
            }

            AuditLog::log('bounce_cheque', $cheque, null, ['status' => 'bounced'], "Cheque #{$cheque->cheque_number} marked bounced. Customer balance restored.");
        });

        return back()->with('warning', 'Cheque marked bounced and customer balance recalculated.');
    }

    public function clearCheque(Cheque $cheque)
    {
        $cheque->status = 'cleared';
        $cheque->save();

        if ($cheque->party_type === 'customer') {
            BankBookEntry::create([
                'entry_date' => now()->toDateString(),
                'type' => 'deposit',
                'bank_name' => $cheque->bank_name,
                'reference_id' => $cheque->cheque_number,
                'amount' => $cheque->amount,
                'balance_after' => 0,
                'description' => "Cheque cleared: {$cheque->cheque_number}",
                'user_id' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Cheque cleared successfully.');
    }

    // --- Expenses (FR-143, FR-144) ---
    public function expenses(Request $request)
    {
        $query = Expense::with(['category', 'location', 'createdBy'])->latest('id');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $categories = ExpenseCategory::all();
        $locations = Location::all();

        return view('finance.expenses.index', compact('expenses', 'categories', 'locations'));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,bank',
            'payee' => 'nullable|string',
            'description' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $expNumber = DocumentSequence::getNextNumber('EXP');

            $expense = Expense::create([
                'expense_number' => $expNumber,
                'category_id' => $request->category_id,
                'location_id' => $request->location_id,
                'expense_date' => $request->expense_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payee' => $request->payee,
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);

            if ($request->payment_method === 'cash') {
                CashBookEntry::record('outflow', $request->amount, 'expense', $expNumber, $request->description);
            }

            AuditLog::log('create', $expense, null, $expense->toArray(), "Expense {$expNumber} recorded");
        });

        return redirect()->route('finance.expenses')->with('success', 'Expense recorded successfully.');
    }

    // --- Cash Book & Bank Book (FR-145, FR-146) ---
    public function cashBook(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $entries = CashBookEntry::with('user')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->latest('id')
            ->paginate(30);

        $totalInflow = CashBookEntry::whereBetween('entry_date', [$startDate, $endDate])->where('type', 'inflow')->sum('amount');
        $totalOutflow = CashBookEntry::whereBetween('entry_date', [$startDate, $endDate])->where('type', 'outflow')->sum('amount');
        $currentBalance = CashBookEntry::latest('id')->value('balance_after') ?? 0;

        return view('finance.cash-book', compact('entries', 'startDate', 'endDate', 'totalInflow', 'totalOutflow', 'currentBalance'));
    }

    public function bankBook(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $entries = BankBookEntry::with('user')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->latest('id')
            ->paginate(30);

        return view('finance.bank-book', compact('entries', 'startDate', 'endDate'));
    }

    // --- Day End Closing (FR-149, FR-150) ---
    public function dayClosing()
    {
        $today = now()->toDateString();
        $shop = Location::where('type', 'retail_shop')->first() ?? Location::first();

        $todaySales = Invoice::where('status', 'posted')->whereDate('invoice_date', $today)->sum('grand_total');
        $todayPurchases = GoodsReceipt::where('status', 'posted')->whereDate('received_date', $today)->sum('landed_cost_total');
        $todayReceipts = CustomerReceipt::whereDate('receipt_date', $today)->sum('amount');
        $todayPayments = SupplierPayment::whereDate('payment_date', $today)->sum('amount');
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');

        $lastCashEntry = CashBookEntry::latest('id')->first();
        $cashInHand = $lastCashEntry ? $lastCashEntry->balance_after : 0;

        $previousClosings = DayClosing::with(['location', 'closedBy'])->latest('closing_date')->take(15)->get();

        $isClosedToday = DayClosing::whereDate('closing_date', $today)->where('location_id', $shop->id)->exists();

        return view('finance.day-closing', compact(
            'today',
            'shop',
            'todaySales',
            'todayPurchases',
            'todayReceipts',
            'todayPayments',
            'todayExpenses',
            'cashInHand',
            'previousClosings',
            'isClosedToday'
        ));
    }

    public function storeDayClosing(Request $request)
    {
        $today = now()->toDateString();
        $shop = Location::where('type', 'retail_shop')->first() ?? Location::first();

        $existing = DayClosing::whereDate('closing_date', $today)->where('location_id', $shop->id)->first();
        if ($existing) {
            return back()->with('info', 'Day end is already closed for today.');
        }

        $todaySales = Invoice::where('status', 'posted')->whereDate('invoice_date', $today)->sum('grand_total');
        $todayPurchases = GoodsReceipt::where('status', 'posted')->whereDate('received_date', $today)->sum('landed_cost_total');
        $todayReceipts = CustomerReceipt::whereDate('receipt_date', $today)->sum('amount');
        $todayPayments = SupplierPayment::whereDate('payment_date', $today)->sum('amount');
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');

        $lastCashEntry = CashBookEntry::latest('id')->first();
        $cashInHand = $lastCashEntry ? $lastCashEntry->balance_after : 0;

        DayClosing::create([
            'closing_date' => $today,
            'location_id' => $shop->id,
            'total_sales' => $todaySales,
            'total_purchases' => $todayPurchases,
            'total_receipts' => $todayReceipts,
            'total_payments' => $todayPayments,
            'total_expenses' => $todayExpenses,
            'cash_in_hand' => $cashInHand,
            'is_closed' => true,
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'notes' => $request->notes,
        ]);

        AuditLog::log('day_closing', null, null, ['date' => $today, 'cash' => $cashInHand], "Day end closed for {$today}");

        return back()->with('success', "Day End successfully closed for {$today}. Cash in hand: Rs. " . number_format($cashInHand));
    }
}
