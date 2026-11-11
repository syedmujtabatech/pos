<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index()
    {
        $requests = ApprovalRequest::with(['requestedBy', 'approver'])->latest('id')->paginate(25);
        return view('approvals.index', compact('requests'));
    }

    public function decide(Request $request, ApprovalRequest $approval)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'approver_notes' => 'nullable|string',
        ]);

        if (!auth()->user()->isManager()) {
            return back()->with('error', 'Only Managers or Owners can approve exception requests.');
        }

        DB::transaction(function () use ($approval, $request) {
            $approval->status = $request->status;
            $approval->approver_id = auth()->id();
            $approval->approver_notes = $request->approver_notes;
            $approval->responded_at = now();
            $approval->save();

            // If approved and it was a bill void, process the void!
            if ($request->status === 'approved' && $approval->request_type === 'bill_void' && $approval->reference_type === 'Invoice') {
                $invoice = Invoice::find($approval->reference_id);
                if ($invoice && $invoice->status === 'posted') {
                    $invoice->load('items');
                    foreach ($invoice->items as $item) {
                        StockMovement::recordMovement(
                            $item->product_id,
                            $invoice->location_id,
                            $item->quantity,
                            'sale_return',
                            'VOID',
                            $invoice->invoice_number,
                            $item->unit_cost,
                            $item->batch_id,
                            "Void approved by Manager",
                            auth()->id()
                        );
                    }
                    if ($invoice->due_amount > 0) {
                        $invoice->customer->decrement('current_balance', $invoice->due_amount);
                    }
                    $invoice->status = 'voided';
                    $invoice->void_reason = $approval->reason;
                    $invoice->void_approved_by = auth()->id();
                    $invoice->save();
                }
            }

            // If approved and it was a stock adjustment
            if ($request->status === 'approved' && $approval->request_type === 'stock_adjustment' && $approval->reference_type === 'StockAdjustment') {
                $adj = StockAdjustment::find($approval->reference_id);
                if ($adj && $adj->status === 'pending_approval') {
                    $adj->load('items.product');
                    foreach ($adj->items as $it) {
                        $movType = $it->diff_qty > 0 ? 'adjustment_in' : 'adjustment_out';
                        StockMovement::recordMovement(
                            $it->product_id,
                            $adj->location_id,
                            $it->diff_qty,
                            $movType,
                            'ADJ',
                            $adj->adjustment_number,
                            $it->unit_cost,
                            null,
                            "Approved stock adjustment",
                            auth()->id()
                        );
                    }
                    $adj->status = 'approved';
                    $adj->approved_by = auth()->id();
                    $adj->save();
                }
            }

            AuditLog::log('approval_decision', $approval, null, ['decision' => $request->status], "Approval request #{$approval->id} {$request->status}");
        });

        return back()->with('success', "Request marked as {$request->status}.");
    }
}
