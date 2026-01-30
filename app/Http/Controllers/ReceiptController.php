<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ReceiptService;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function index()
    {
        $receipts = Receipt::latest()->paginate(20);
        return Inertia::render('Receipts/Index', ['receipts' => $receipts]);
    }

    public function create()
    {
        return Inertia::render('Receipts/Create');
    }

    public function store(Request $request, ReceiptService $receiptService)
    {
        // coerce empty linked_id to null to satisfy integer rule
        if ($request->input('linked_id') === '') {
            $request->merge(['linked_id' => null]);
        }

        $data = $request->validate([
            'receipt_number' => 'nullable|string|max:100|unique:receipts,receipt_number',
            'type' => 'required|in:PR,WR,SR,AR,DR,SS',
            'linked_id' => 'nullable|integer',
        ]);

        if (empty($data['receipt_number'])) {
            $now = now();
            $date = $now->format('Ymd');
            $ms = (int) (microtime(true) * 1000) % 10000; // keep 4-digit millisecond fragment
            $ms = str_pad((string) $ms, 4, '0', STR_PAD_LEFT);
            $type = $data['type'] ?? 'PR';
            $data['receipt_number'] = sprintf('%s-11F-%s-%s', $type, $date, $ms);
        }

        try {
            $receipt = $receiptService->create($data);

            if ($request->wantsJson() || $request->headers->get('accept') === 'application/json') {
                return response()->json($receipt, 201);
            }

            return redirect()->route('receipts.show', $receipt->id)->with('success','Receipt created.');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->headers->get('accept') === 'application/json') {
                return response()->json(['error' => 'Failed to generate receipt QR code: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['qr' => 'Failed to generate receipt QR code: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $receipt = Receipt::findOrFail($id);
        if ($receipt->qr_code && Storage::disk('public')->exists($receipt->qr_code)) {
            $receipt->qr_code = Storage::disk('public')->get($receipt->qr_code);
        }
        return Inertia::render('Receipts/Show', ['receipt' => $receipt]);
    }
}
