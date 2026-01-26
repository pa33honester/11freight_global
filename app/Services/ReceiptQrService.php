<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class ReceiptQrService
{
    public function generateSvg(string $content, int $size = 300): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        return $writer->writeString($content);
    }

    public function storeSvg(string $svg, string $filename): string
    {
        $path = 'receipts_qr/' . ltrim($filename, '/');
        Storage::disk('public')->put($path, $svg);
        return $path;
    }
}
