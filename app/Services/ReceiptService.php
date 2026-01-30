<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Shipment;
use App\Models\SupplierSettlement;
use BaconQrCode\Renderer\Image\SvgImageBackEnd as QRSvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer as QRImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle as QRRendererStyle;
use BaconQrCode\Writer as QRWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\PureGdReceiptGenerator;
use Throwable;

class ReceiptService
{
    /**
     * Create a warehouse receipt for a shipment and generate/store QR code.
     */
    public function warehouseReceipt(Shipment $shipment): Receipt
    {
        $receipt = Receipt::create([
            'type' => 'WR',
            'linked_id' => $shipment->id,
            'qr_code' => null,
            'created_at' => now(),
        ]);

        // generate QR and store

        $content = json_encode(['id' => $receipt->id, 'receipt_number' => $receipt->receipt_number]);
        $svg = $this->generateSvg($content, 400);
        $filename = $receipt->receipt_number.'.svg';
        $path = $this->storeSvg($svg, $filename);

        $receipt->qr_code = $path;
        $receipt->save();

        // attempt to generate a composed receipt image (logo + content + qr)
        try {
            $generator = new PureGdReceiptGenerator();
            $generator->generateReceiptImage($receipt);
        } catch (\Throwable $e) {
            Log::error('Failed to generate receipt image (warehouseReceipt): '.$e->getMessage(), [
                'exception' => $e,
                'receipt_id' => $receipt->id,
            ]);
        }

        return $receipt;
    }

    /**
     * Create a payment receipt (PR) linked to a Payment record.
     */
    public function paymentReceipt(Payment $payment): Receipt
    {
        return $this->create([
            'type' => 'PR',
            'linked_id' => $payment->id,
        ]);
    }

    /**
     * Create a supplier settlement receipt (SS) linked to a SupplierSettlement record.
     */
    public function supplierSettlementReceipt(SupplierSettlement $settlement): Receipt
    {
        return $this->create([
            'type' => 'SS',
            'linked_id' => $settlement->id,
        ]);
    }

    /**
     * Create a shipping receipt (SR) linked to a Shipment.
     */
    public function shippingReceipt(Shipment $shipment): Receipt
    {
        return $this->create([
            'type' => 'SR',
            'linked_id' => $shipment->id,
        ]);
    }

    /**
     * Create an arrival receipt (AR) linked to a Shipment.
     */
    public function arrivalReceipt(Shipment $shipment): Receipt
    {
        return $this->create([
            'type' => 'AR',
            'linked_id' => $shipment->id,
        ]);
    }

    /**
     * Create a delivery receipt (DR) linked to a Shipment.
     */
    public function deliveryReceipt(Shipment $shipment): Receipt
    {
        return $this->create([
            'type' => 'DR',
            'linked_id' => $shipment->id,
        ]);
    }

    /**
     * Create a receipt record and generate/store its QR code.
     *
     * @throws \Throwable
     */
    public function create(array $data): Receipt
    {
        $receipt = Receipt::create([
            'type' => $data['type'] ?? 'PR',
            'linked_id' => $data['linked_id'] ?? null,
            'qr_code' => null,
            'created_at' => now(),
        ]);

        // generate QR content and store; cleanup on failure
        try {
            $content = json_encode(['id' => $receipt->id, 'receipt_number' => $receipt->receipt_number]);
            $svg = $this->generateSvg($content, 400);
            $filename = 'receipt-'.$receipt->id.'.svg';
            $path = $this->storeSvg($svg, $filename);

            $receipt->qr_code = $path;
            $receipt->save();

            // attempt to generate a composed receipt image (logo + content + qr)
            try {
                (new PureGdReceiptGenerator())->generateReceiptImage($receipt);
            } catch (\Throwable $e) {
                Log::error('Failed to generate receipt image (create): '.$e->getMessage(), [
                    'exception' => $e,
                    'receipt_id' => $receipt->id,
                ]);
            }

            return $receipt;
        } catch (Throwable $e) {
            try {
                $receipt->delete();
            } catch (Throwable $_) {
                // ignore
            }

            throw $e;
        }
    }

    /**
     * Generate an SVG QR string for given content.
     */
    protected function generateSvg(string $content, int $size = 300): string
    {
        $renderer = new QRImageRenderer(new QRRendererStyle($size), new QRSvgImageBackEnd);
        $writer = new QRWriter($renderer);

        return $writer->writeString($content);
    }

    /**
     * Store an SVG string to the receipts QR folder and return path.
     */
    protected function storeSvg(string $svg, string $filename): string
    {
        $path = 'receipts_qr/'.ltrim($filename, '/');
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    /**
     * Verify a QR code input and return validation info.
     *
     * Accepts one of:
     * - a JSON payload string (the original encoded content),
     * - a receipt number string,
     * - a stored QR filename or storage path (as saved in `Receipt->qr_code`).
     *
     * Returns an array with keys: `valid` (bool), `receipt` (Receipt|null), `reason` (string).
     *
     * Note: decoding raw image QR payloads is out of scope without a decoder dependency; prefer
     * passing the JSON payload or stored filename/receipt number.
     *
     * @return array{valid:bool,receipt:?Receipt,reason:string}
     */
    public function verify(string $qrInput): array
    {
        $qrInput = trim($qrInput);

        if ($qrInput === '') {
            return ['valid' => false, 'receipt' => null, 'reason' => 'empty_input'];
        }

        // 1) Try JSON payload (expected format used when generating QR)
        $decoded = json_decode($qrInput, true);
        if (is_array($decoded) && isset($decoded['id'], $decoded['receipt_number'])) {
            $receipt = Receipt::find($decoded['id']);
            if ($receipt && $receipt->receipt_number === $decoded['receipt_number']) {
                return ['valid' => true, 'receipt' => $receipt, 'reason' => 'matched_by_payload'];
            }

            return ['valid' => false, 'receipt' => null, 'reason' => 'payload_mismatch'];
        }

        // 2) If input matches a stored qr_code path exactly
        $receipt = Receipt::where('qr_code', $qrInput)->first();
        if ($receipt) {
            return ['valid' => true, 'receipt' => $receipt, 'reason' => 'matched_by_qr_path'];
        }

        // 3) Try matching by basename of a path (e.g., filename.svg)
        $basename = basename($qrInput);
        if ($basename !== $qrInput) {
            $receipt = Receipt::where('qr_code', 'like', "%{$basename}")->first();
            if ($receipt) {
                return ['valid' => true, 'receipt' => $receipt, 'reason' => 'matched_by_qr_basename'];
            }
        }

        // 4) Direct receipt number lookup
        $receipt = Receipt::where('receipt_number', $qrInput)->first();
        if ($receipt) {
            return ['valid' => true, 'receipt' => $receipt, 'reason' => 'matched_by_receipt_number'];
        }

        return ['valid' => false, 'receipt' => null, 'reason' => 'not_found'];
    }
}
