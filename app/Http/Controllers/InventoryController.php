<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\DocumentSequence;
use App\Models\InventoryBatch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockBalance;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // --- Stock Overview (FR-111, FR-112) ---
    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->get();
        $selectedLocation = $request->location_id ? Location::find($request->location_id) : null;

        $query = Product::with(['category', 'baseUnit', 'stockBalances.location', 'batches'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(25)->withQueryString();

        return view('inventory.index', compact('products', 'locations', 'selectedLocation'));
    }

    // --- Stock Ledger (Golden Rule FR-113, FR-114) ---
    public function ledger(Request $request)
    {
        $query = StockMovement::with(['product', 'location', 'batch', 'user'])->latest('id');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('movement_date', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $movements = $query->paginate(30)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->get();

        return view('inventory.ledger', compact('movements', 'products', 'locations'));
    }

    // --- Stock Transfers (Godown <-> Shop) (FR-117 - FR-119) ---
    public function transfers()
    {
        $transfers = StockTransfer::with(['fromLocation', 'toLocation', 'dispatchedBy', 'receivedBy', 'items.product'])
            ->latest('id')
            ->paginate(20);

        return view('inventory.transfers.index', compact('transfers'));
    }

    public function createTransfer()
    {
        $locations = Location::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('stockBalances')->get();

        return view('inventory.transfers.create', compact('locations', 'products'));
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $trfNumber = DocumentSequence::getNextNumber('TRF');

            $transfer = StockTransfer::create([
                'transfer_number' => $trfNumber,
                'from_location_id' => $request->from_location_id,
                'to_location_id' => $request->to_location_id,
                'status' => 'in_transit', // In transit: source reduced, destination not yet added (no double count)
                'dispatched_by' => auth()->id(),
                'dispatched_at' => now(),
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $prod = Product::find($item['product_id']);
                $qty = (float)$item['quantity'];

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $prod->id,
                    'unit_id' => $prod->base_unit_id,
                    'quantity' => $qty,
                    'base_quantity' => $qty,
                ]);

                // Reduce from source immediately (FR-119)
                StockMovement::recordMovement(
                    $prod->id,
                    $request->from_location_id,
                    -$qty,
                    'transfer_out',
                    'TRF',
                    $trfNumber,
                    $prod->purchase_cost,
                    null,
                    "Dispatched in transfer {$trfNumber} to " . Location::find($request->to_location_id)->name,
                    auth()->id()
                );
            }

            AuditLog::log('create', $transfer, null, $transfer->toArray(), "Transfer {$trfNumber} dispatched");
        });

        return redirect()->route('inventory.transfers')->with('success', 'Stock transfer dispatched. Stock marked In-Transit.');
    }

    public function receiveTransfer(StockTransfer $transfer)
    {
        if ($transfer->status !== 'in_transit') {
            return back()->with('error', 'Only in-transit transfers can be received.');
        }

        DB::transaction(function () use ($transfer) {
            $transfer->load('items.product');

            foreach ($transfer->items as $item) {
                // Add to destination location now!
                StockMovement::recordMovement(
                    $item->product_id,
                    $transfer->to_location_id,
                    $item->quantity,
                    'transfer_in',
                    'TRF',
                    $transfer->transfer_number,
                    $item->product->purchase_cost,
                    null,
                    "Stock received from transfer {$transfer->transfer_number}",
                    auth()->id()
                );
            }

            $transfer->status = 'received';
            $transfer->received_by = auth()->id();
            $transfer->received_at = now();
            $transfer->save();

            AuditLog::log('update', $transfer, null, ['status' => 'received'], "Transfer {$transfer->transfer_number} received at destination");
        });

        return redirect()->route('inventory.transfers')->with('success', 'Transfer received successfully. Destination inventory updated.');
    }

    // --- Stock Adjustments (FR-116) ---
    public function adjustments()
    {
        $adjustments = StockAdjustment::with(['location', 'requestedBy', 'approvedBy', 'items.product'])
            ->latest('id')
            ->paginate(20);

        return view('inventory.adjustments.index', compact('adjustments'));
    }

    public function createAdjustment()
    {
        $locations = Location::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('stockBalances')->get();

        return view('inventory.adjustments.create', compact('locations', 'products'));
    }

    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'type' => 'required|in:increase,decrease,damage',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.diff_qty' => 'required|numeric|not_in:0',
        ]);

        $isManager = auth()->user()->isManager();

        DB::transaction(function () use ($request, $isManager) {
            $adjNumber = DocumentSequence::getNextNumber('ADJ');

            $adj = StockAdjustment::create([
                'adjustment_number' => $adjNumber,
                'location_id' => $request->location_id,
                'type' => $request->type,
                'status' => $isManager ? 'approved' : 'pending_approval',
                'requested_by' => auth()->id(),
                'approved_by' => $isManager ? auth()->id() : null,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $it) {
                $prod = Product::find($it['product_id']);
                $diff = (float)$it['diff_qty'];

                $bal = StockBalance::where('product_id', $prod->id)->where('location_id', $request->location_id)->first();
                $cur = $bal ? $bal->on_hand : 0;

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adj->id,
                    'product_id' => $prod->id,
                    'current_qty' => $cur,
                    'adjusted_qty' => $cur + $diff,
                    'diff_qty' => $diff,
                    'unit_cost' => $prod->purchase_cost,
                ]);

                // If manager created it, apply immediately! Otherwise wait for approval queue.
                if ($isManager) {
                    $movType = $diff > 0 ? 'adjustment_in' : 'adjustment_out';
                    StockMovement::recordMovement(
                        $prod->id,
                        $request->location_id,
                        $diff,
                        $movType,
                        'ADJ',
                        $adjNumber,
                        $prod->purchase_cost,
                        null,
                        "Stock adjustment ({$request->reason})",
                        auth()->id()
                    );
                }
            }

            if (!$isManager) {
                ApprovalRequest::create([
                    'request_type' => 'stock_adjustment',
                    'requested_by' => auth()->id(),
                    'status' => 'pending',
                    'reference_type' => 'StockAdjustment',
                    'reference_id' => $adj->id,
                    'reason' => $request->reason,
                ]);
            }

            AuditLog::log('create', $adj, null, $adj->toArray(), "Stock adjustment {$adjNumber} created");
        });

        $msg = $isManager ? 'Stock adjustment posted and inventory updated.' : 'Stock adjustment submitted for Manager approval.';
        return redirect()->route('inventory.adjustments')->with('success', $msg);
    }

    // --- Stock Counts / Stock Take (FR-120, FR-121) ---
    public function counts()
    {
        $counts = StockCount::with(['location', 'conductedBy', 'approvedBy', 'items.product'])->latest('id')->paginate(20);
        return view('inventory.counts.index', compact('counts'));
    }

    public function createCount()
    {
        $locations = Location::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('stockBalances')->get();

        return view('inventory.counts.create', compact('locations', 'products'));
    }

    public function storeCount(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'count_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.counted_qty' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $cntNumber = DocumentSequence::getNextNumber('CNT');

            $stockCount = StockCount::create([
                'count_number' => $cntNumber,
                'location_id' => $request->location_id,
                'count_date' => $request->count_date,
                'status' => 'approved',
                'conducted_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $it) {
                $prod = Product::find($it['product_id']);
                $bal = StockBalance::where('product_id', $prod->id)->where('location_id', $request->location_id)->first();
                $systemQty = $bal ? (float)$bal->on_hand : 0;
                $countedQty = (float)$it['counted_qty'];
                $variance = $countedQty - $systemQty;

                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $prod->id,
                    'system_qty' => $systemQty,
                    'counted_qty' => $countedQty,
                    'variance_qty' => $variance,
                    'unit_cost' => $prod->purchase_cost,
                ]);

                // If variance exists, adjust stock to match physical count (FR-121)
                if ($variance != 0) {
                    $movType = $variance > 0 ? 'adjustment_in' : 'adjustment_out';
                    StockMovement::recordMovement(
                        $prod->id,
                        $request->location_id,
                        $variance,
                        $movType,
                        'COUNT',
                        $cntNumber,
                        $prod->purchase_cost,
                        null,
                        "Physical stock take reconciliation (Count {$cntNumber})",
                        auth()->id()
                    );
                }
            }

            AuditLog::log('create', $stockCount, null, $stockCount->toArray(), "Stock count {$cntNumber} completed and variance reconciled");
        });

        return redirect()->route('inventory.counts')->with('success', 'Physical stock count recorded and variances reconciled.');
    }
}
