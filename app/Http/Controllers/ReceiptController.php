<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ReceiptQrService;
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

    public function store(Request $request, ReceiptQrService $qrService)
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

        $data['receipt_number'] = $data['receipt_number'] ?? 'RCT-' . time() . '-' . rand(1000,9999);

        // create record first to get id
        $receipt = Receipt::create([
            'receipt_number' => $data['receipt_number'],
            'type' => $data['type'],
            'linked_id' => $data['linked_id'] ?? null,
            'qr_code' => null,
            'created_at' => now(),
        ]);

        // generate QR content (we'll encode receipt id and number)
        try {
            $content = json_encode(['id' => $receipt->id, 'receipt_number' => $receipt->receipt_number]);
            $svg = $qrService->generateSvg($content, 400);
            $filename = 'receipt-' . $receipt->id . '.svg';
            $path = $qrService->storeSvg($svg, $filename);

            $receipt->qr_code = $path;
            $receipt->save();

            if ($request->wantsJson() || $request->headers->get('accept') === 'application/json') {
                return response()->json($receipt, 201);
            }

            return redirect()->route('receipts.show', $receipt->id)->with('success','Receipt created.');
        } catch (\Throwable $e) {
            // cleanup created record if QR generation/storage failed
            try {
                $receipt->delete();
            } catch (\Throwable $_) {
                // ignore
            }

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
