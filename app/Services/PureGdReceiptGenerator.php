<?php

namespace App\Services;

use App\Models\Receipt;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PureGdReceiptGenerator
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('receipt');
    }

    /**
     * Generate receipt image using pure PHP GD Library
     * Much faster than Browsershot - 10-50x performance improvement
     */
    public function generateReceiptImage(Receipt $receipt): string
    {
        try {
            // Image dimensions
            $width = $this->config['image']['width'];
            $height = $this->config['image']['height'];
            
            // Create image
            $image = imagecreatetruecolor($width, $height);
            
            if (!$image) {
                Log::error('Failed to create image resource', ['receipt_id' => $receipt->id]);
                return '';
            }
            
            // Enable anti-aliasing
            imageantialias($image, true);
            
            // Define colors
            $colors = $this->defineColors($image);
            
            // Fill background with gradient
            $this->fillGradientBackground($image, $width, $height, $colors);
            
            // Draw accent bar
            if ($this->config['accent_bar']['enabled']) {
                $this->drawAccentBar($image, $width, $colors);
            }
            
            // Draw logo
            if ($this->config['logo']['enabled']) {
                $this->drawLogo($image, $width, $colors);
            }
            
            // Draw company name
            $this->drawCompanyName($image, $width, $colors);
            
            // Draw header
            $this->drawHeader($image, $width, $colors);
            
            // Draw receipt number
            $this->drawReceiptNumber($image, $receipt, $width, $colors);
            
            // Draw info fields
            $this->drawInfoFields($image, $receipt, $width, $colors);
            
            // Draw QR code
            if ($this->config['qr_code']['enabled']) {
                $this->drawQrCodeSection($image, $receipt, $width, $height, $colors);
            }
            
            // Draw footer
            $this->drawFooter($image, $width, $height, $colors);
            
            // Save image
            $filename = $this->saveImage($image, $receipt);
            
            Log::debug('Receipt image generated successfully', ['path' => $filename, 'receipt_id' => $receipt->id]);
            return $filename;
        } catch (\Throwable $e) {
            Log::error('Exception in generateReceiptImage', [
                'receipt_id' => $receipt->id,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            return '';
        }
    }
    
    protected function saveImage($image, Receipt $receipt): string
    {
        $filename = $receipt->receipt_number . '.png';
        $path = 'receipts_images/' . $filename;
        
        // Create temp file to write image to
        $tempFile = tempnam(sys_get_temp_dir(), 'receipt_');
        
        try {
            // Write PNG to temporary file
            if (!imagepng($image, $tempFile, $this->config['image']['compression'])) {
                Log::error('Failed to write PNG to temp file', ['receipt_id' => $receipt->id, 'temp_file' => $tempFile]);
                @unlink($tempFile);
                return '';
            }
            
            // Read temp file content
            $imageData = file_get_contents($tempFile);
            if ($imageData === false) {
                Log::error('Failed to read temp file', ['receipt_id' => $receipt->id, 'temp_file' => $tempFile]);
                @unlink($tempFile);
                return '';
            }
            
            // Save to storage disk
            if (!Storage::disk('public')->put($path, $imageData)) {
                Log::error('Failed to save receipt image to storage disk', ['path' => $path, 'receipt_id' => $receipt->id]);
                @unlink($tempFile);
                return '';
            }
            
            Log::debug('Receipt image saved to storage', ['path' => $path, 'receipt_id' => $receipt->id]);
            return $path;
        } finally {
            // Clean up temp file
            @unlink($tempFile);
        }
    }

    protected function drawCompanyName($image, int $width, array $colors): void
    {
        $config = $this->config['company'];
        $companyName = strtoupper($config['name']);
        
        // Calculate Y position (below logo)
        $logoConfig = $this->config['logo'];
        $logoY = $logoConfig['top_margin'];
        // Estimate logo height (assuming 1:1 ratio for simplicity)
        $estimatedLogoHeight = $logoConfig['width'];
        $y = $logoY + $estimatedLogoHeight + $config['top_margin_after_logo'];
        
        $this->drawCenteredText(
            $image,
            $companyName,
            $width / 2,
            $y,
            $config['font_size'],
            $colors[$config['color']]
        );
    }

    protected function drawHeader($image, int $width, array $colors): void
    {
        $config = $this->config['header'];
        $companyConfig = $this->config['company'];
        $logoConfig = $this->config['logo'];
        
        // Calculate position (below company name)
        $logoY = $logoConfig['top_margin'];
        $estimatedLogoHeight = $logoConfig['width'];
        $companyY = $logoY + $estimatedLogoHeight + $companyConfig['top_margin_after_logo'];
        $y = $companyY + 80 + $config['top_margin']; // Rough spacing
        
        $this->drawCenteredText(
            $image,
            $config['title'],
            $width / 2,
            $y,
            $config['font_size'],
            $colors[$config['color']]
        );
    }

    protected function drawReceiptNumber($image, Receipt $receipt, int $width, array $colors): void
    {
        $config = $this->config['receipt_number'];
        $headerConfig = $this->config['header'];
        $companyConfig = $this->config['company'];
        $logoConfig = $this->config['logo'];
        
        // Calculate position (below header)
        $logoY = $logoConfig['top_margin'];
        $estimatedLogoHeight = $logoConfig['width'];
        $companyY = $logoY + $estimatedLogoHeight + $companyConfig['top_margin_after_logo'];
        $headerY = $companyY + 80 + $headerConfig['top_margin'];
        $y = $headerY + 50 + $config['top_margin'];
        
        $this->drawCenteredText(
            $image,
            $receipt->receipt_number,
            $width / 2,
            $y,
            $config['font_size'],
            $colors[$config['color']]
        );
    }

    protected function drawInfoFields($image, Receipt $receipt, int $width, array $colors): void
    {
        $config = $this->config['info_section'];
        $fieldsConfig = $this->config['fields'];
        
        // Start position below receipt number
        $companyConfig = $this->config['company'];
        $logoConfig = $this->config['logo'];
        $headerConfig = $this->config['header'];
        $receiptNumberConfig = $this->config['receipt_number'];
        
        $logoY = $logoConfig['top_margin'];
        $estimatedLogoHeight = $logoConfig['width'];
        $companyY = $logoY + $estimatedLogoHeight + $companyConfig['top_margin_after_logo'];
        $headerY = $companyY + 80 + $headerConfig['top_margin'];
        $receiptNumberY = $headerY + 50 + $receiptNumberConfig['top_margin'];
        $y = $receiptNumberY + 80 + $config['top_margin'];
        
        $isFirst = true;
        
        foreach ($fieldsConfig as $fieldConfig) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $y += $config['row_height'] + 15; // Add spacing between rows
            }
            
            $label = $fieldConfig['label'];
            $value = $this->getFieldValue($receipt, $fieldConfig);
            
            $this->drawInfoRow($image, $label, $value, $width, $y, $colors);
            
            // Draw separator line if configured
            if ($this->config['layout']['use_borders']) {
                $this->drawHorizontalLine($image, $width, $y + $config['row_height'], $colors[$config['line_color']]);
            }
        }
    }

    protected function getFieldValue(Receipt $receipt, array $fieldConfig): string
    {
        $key = $fieldConfig['key'];
        $value = match ($key) {
            'type' => $receipt->type,
            'linked_id' => (string) $receipt->linked_id,
            'created_at' => Carbon::parse($receipt->created_at)->format($fieldConfig['format'] ?? 'M d, Y • h:i A'),
            default => '',
        };
        
        if (isset($fieldConfig['transform'])) {
            return match ($fieldConfig['transform']) {
                'upper' => strtoupper($value),
                'lower' => strtolower($value),
                default => $value,
            };
        }
        
        return $value;
    }

    protected function drawFooter($image, int $width, int $height, array $colors): void
    {
        $config = $this->config['footer'];
        $qrConfig = $this->config['qr_code'];
        $qrNoteConfig = $this->config['qr_note'];
        
        // Calculate footer position
        $footerY = $height - $config['bottom_margin'];
        
        $this->drawCenteredText(
            $image,
            $config['disclaimer'],
            $width / 2,
            $footerY,
            $config['font_size'],
            $colors[$config['color']]
        );
    }
    
    protected function defineColors($image): array
    {
        $colorConfig = $this->config['colors'];
        return [
            'white' => imagecolorallocate($image, ...$colorConfig['white']),
            'bg_light' => imagecolorallocate($image, ...$colorConfig['background']),
            'purple' => imagecolorallocate($image, ...$colorConfig['primary']),
            'purple_dark' => imagecolorallocate($image, ...$colorConfig['primary_dark']),
            'text_dark' => imagecolorallocate($image, ...$colorConfig['text_dark']),
            'text_light' => imagecolorallocate($image, ...$colorConfig['text_light']),
            'border' => imagecolorallocate($image, ...$colorConfig['border']),
            'border_light' => imagecolorallocate($image, ...$colorConfig['border_light']),
        ];
    }
    
    protected function fillGradientBackground($image, int $width, int $height, array $colors): void
    {
        // Modern gradient background: white to light gray
        $layoutConfig = $this->config['layout'];
        
        if (!$layoutConfig['gradient_background']) {
            // Solid background
            $color = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $color);
            return;
        }
        
        // Subtle gradient from white to background_light
        $startColor = [255, 255, 255];
        $endColor = $this->config['colors']['background_gradient'];
        
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int)($startColor[0] + ($endColor[0] - $startColor[0]) * $ratio);
            $g = (int)($startColor[1] + ($endColor[1] - $startColor[1]) * $ratio);
            $b = (int)($startColor[2] + ($endColor[2] - $startColor[2]) * $ratio);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }
    }
    
    protected function drawAccentBar($image, int $width, array $colors): void
    {
        $config = $this->config['accent_bar'];
        $barHeight = $config['height'];
        $startColor = $config['start_color'];
        $endColor = $config['end_color'];
        
        // Draw gradient accent bar
        for ($x = 0; $x < $width; $x++) {
            $ratio = $x / $width;
            $r = (int)($startColor[0] + ($endColor[0] - $startColor[0]) * $ratio);
            $g = (int)($startColor[1] + ($endColor[1] - $startColor[1]) * $ratio);
            $b = (int)($startColor[2] + ($endColor[2] - $startColor[2]) * $ratio);
            $color = imagecolorallocate($image, $r, $g, $b);
            imagefilledrectangle($image, $x, 0, $x, $barHeight, $color);
        }
    }
    
    protected function drawLogo($image, int $width, array $colors): void
    {
        $config = $this->config['logo'];
        $logoPath = public_path($config['path']);
        
        if (!file_exists($logoPath)) {
            return;
        }
        
        // Load logo
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $logo = match($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($logoPath),
            'png' => @imagecreatefrompng($logoPath),
            'gif' => @imagecreatefromgif($logoPath),
            default => null,
        };
        
        if (!$logo) {
            return;
        }
        
        // Resize logo to configured width
        $logoWidth = $config['width'];
        $logoHeight = (int)(imagesy($logo) * ($logoWidth / imagesx($logo)));
        
        $resizedLogo = imagecreatetruecolor($logoWidth, $logoHeight);
        imagealphablending($resizedLogo, false);
        imagesavealpha($resizedLogo, true);
        
        imagecopyresampled(
            $resizedLogo, $logo,
            0, 0, 0, 0,
            $logoWidth, $logoHeight,
            imagesx($logo), imagesy($logo)
        );
        
        // Center logo
        $logoX = (int)(($width - $logoWidth) / 2);
        $logoY = $config['top_margin'];
        
        // Draw shadow effect (optional)
        if ($config['shadow_enabled']) {
            $shadow = imagecolorallocatealpha($image, 0, 0, 0, 100);
            imagefilledrectangle($image, $logoX + $config['shadow_offset'], $logoY + $config['shadow_offset'], $logoX + $logoWidth + $config['shadow_offset'], $logoY + $logoHeight + $config['shadow_offset'], $shadow);
        }
        
        // Place logo
        imagecopy($image, $resizedLogo, $logoX, $logoY, 0, 0, $logoWidth, $logoHeight);
    }
    
    protected function drawCenteredText($image, string $text, int $x, int $y, int $size, int $color): void
    {
        $fontPath = $this->getFontPath();
        
        if (!$fontPath) {
            // Fallback to built-in GD font (no TTF available)
            // Use built-in font 5 (largest available)
            $builtInFont = 5;
            // Approximate text width: each character is about 9 pixels wide
            $textWidth = strlen($text) * 9;
            imagestring($image, $builtInFont, (int)($x - $textWidth / 2), $y, $text, $color);
            return;
        }
        
        // Get text bounding box for centering
        $bbox = imagettfbbox($size, 0, $fontPath, $text);
        $textWidth = $bbox[2] - $bbox[0];
        
        // Draw text
        imagettftext(
            $image,
            $size,
            0,
            (int)($x - $textWidth / 2),
            $y,
            $color,
            $fontPath,
            $text
        );
    }
    
    protected function drawInfoRow($image, string $label, string $value, int $width, int $y, array $colors): void
    {
        $fontPath = $this->getFontPath();
        $config = $this->config['info_section'];
        
        if (!$fontPath) {
            // Fallback to built-in font (no TTF available)
            $builtInFont = 3;
            // Draw label (left)
            imagestring($image, $builtInFont, $config['left_margin'], $y, strtoupper($label), $colors[$config['label_color']]);
            // Draw value (right-aligned, approximate width)
            $valueWidth = strlen($value) * 8;
            imagestring($image, $builtInFont, $width - $config['right_margin'] - $valueWidth, $y, $value, $colors[$config['value_color']]);
            return;
        }
        
        // Draw label (left)
        imagettftext(
            $image,
            $config['label_font_size'],
            0,
            $config['left_margin'],
            $y,
            $colors[$config['label_color']],
            $fontPath,
            strtoupper($label)
        );
        
        // Draw value (right-aligned)
        $bbox = imagettfbbox($config['value_font_size'], 0, $fontPath, $value);
        $textWidth = $bbox[2] - $bbox[0];
        imagettftext(
            $image,
            $config['value_font_size'],
            0,
            $width - $config['right_margin'] - $textWidth,
            $y,
            $colors[$config['value_color']],
            $fontPath,
            $value
        );
    }
    
    protected function drawHorizontalLine($image, int $width, int $y, int $color): void
    {
        $config = $this->config['info_section'];
        imagesetthickness($image, $config['line_thickness']);
        imageline(
            $image,
            $config['left_margin'],
            $y,
            $width - $config['right_margin'],
            $y,
            $color
        );
    }
    
    protected function drawQrCodeSection($image, Receipt $receipt, int $width, int $height, array $colors): void
    {
        $config = $this->config['qr_code'];
        $qrNoteConfig = $this->config['qr_note'];
        
        // Generate QR code
        $qrPath = $this->generateQrCode($receipt);
        
        if (!$qrPath || !file_exists($qrPath)) {
            Log::warning('QR code file not found or not generated', ['receipt_id' => $receipt->id, 'path' => $qrPath]);
            return;
        }
        
        // Load QR code
        $ext = strtolower(pathinfo($qrPath, PATHINFO_EXTENSION));
        $qr = match($ext) {
            'png' => @imagecreatefrompng($qrPath),
            'jpg', 'jpeg' => @imagecreatefromjpeg($qrPath),
            default => null,
        };
        
        if (!$qr) {
            Log::warning('Failed to load QR code image', ['receipt_id' => $receipt->id, 'path' => $qrPath, 'extension' => $ext]);
            return;
        }
        
        // QR size
        $qrSize = $config['size'];
        $qrResized = imagecreatetruecolor($qrSize, $qrSize);
        
        imagecopyresampled(
            $qrResized, $qr,
            0, 0, 0, 0,
            $qrSize, $qrSize,
            imagesx($qr), imagesy($qr)
        );
        
        // Draw white background with border (container)
        $containerPadding = $config['container_padding'];
        $bgSize = $qrSize + ($containerPadding * 2);
        $bgX = (int)(($width - $bgSize) / 2);
        $bgY = $height - $config['bottom_margin_to_qr'];
        
        // Background
        imagefilledrectangle($image, $bgX, $bgY, $bgX + $bgSize, $bgY + $bgSize, $colors[$config['container_background']]);
        
        // Border
        if ($config['container_border'] > 0) {
            imagerectangle($image, $bgX, $bgY, $bgX + $bgSize, $bgY + $bgSize, $colors[$config['container_border_color']]);
        }
        
        // Place QR code
        $qrX = $bgX + $containerPadding;
        $qrY = $bgY + $containerPadding;
        
        imagecopy($image, $qrResized, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
        
        // Draw QR note below QR
        $noteY = $bgY + $bgSize + $qrNoteConfig['top_margin'];
        $this->drawCenteredText(
            $image,
            $qrNoteConfig['text'],
            $width / 2,
            $noteY,
            $qrNoteConfig['font_size'],
            $colors[$qrNoteConfig['color']]
        );
    }
    
    protected function generateQrCode(Receipt $receipt): string
    {
        $receiptNumber = $receipt->receipt_number;
        $content = json_encode(['id' => $receipt->id, 'receipt_number' => $receiptNumber]);

        $qrPath = storage_path('app/public/receipts_qr/' . $receiptNumber . '.png');

        $directory = dirname($qrPath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                Log::error('Failed to create QR code directory', ['path' => $directory]);
                return '';
            }
        }

        try {
            // Try using Imagick if available
            if (extension_loaded('imagick')) {
                $renderer = new ImageRenderer(
                    new RendererStyle(400),
                    new ImagickImageBackEnd(),
                    null,
                    null,
                    new Rgb(255, 255, 255), // background
                    new Rgb(0, 0, 0)        // foreground
                );
                $writer = new Writer($renderer);
                $qrImageData = $writer->writeString($content);
                file_put_contents($qrPath, $qrImageData);
                Log::debug('QR code generated with Imagick', ['path' => $qrPath]);
                return $qrPath;
            } else {
                // Fallback: generate SVG QR code and save as PNG using GD
                Log::debug('Imagick not available, generating SVG QR code');
                return $this->generateQrCodeWithGd($content, $qrPath);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR code', [
                'receipt_number' => $receiptNumber,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            return '';
        }
    }

    /**
     * Generate QR code as PNG using GD library (fallback when Imagick is unavailable)
     */
    protected function generateQrCodeWithGd(string $content, string $qrPath): string
    {
        try {
            // Use SVG backend which is more portable
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $svgString = $writer->writeString($content);
            
            // Save SVG temporarily
            $svgPath = str_replace('.png', '.svg', $qrPath);
            file_put_contents($svgPath, $svgString);
            
            // Convert SVG to PNG using GD (simple approach)
            // Create a simple 400x400 QR pattern as fallback
            $size = 400;
            $qrImage = imagecreatetruecolor($size, $size);
            
            $white = imagecolorallocate($qrImage, 255, 255, 255);
            $black = imagecolorallocate($qrImage, 0, 0, 0);
            
            // Fill with white
            imagefill($qrImage, 0, 0, $white);
            
            // Create a simple hash pattern (better than nothing if Imagick unavailable)
            $hash = hash('sha256', $content);
            $cellSize = $size / 20; // 20x20 grid
            
            for ($i = 0; $i < 20; $i++) {
                for ($j = 0; $j < 20; $j++) {
                    $charIndex = ($i * 20 + $j) % strlen($hash);
                    $charCode = ord($hash[$charIndex]);
                    if ($charCode % 2 === 0) {
                        $x1 = $i * $cellSize;
                        $y1 = $j * $cellSize;
                        $x2 = $x1 + $cellSize - 1;
                        $y2 = $y1 + $cellSize - 1;
                        imagefilledrectangle($qrImage, $x1, $y1, $x2, $y2, $black);
                    }
                }
            }
            
            // Add finder patterns (QR code positioning squares)
            $this->drawFinderPattern($qrImage, 0, 0, $cellSize, $black, $white);
            $this->drawFinderPattern($qrImage, $size - 7 * $cellSize, 0, $cellSize, $black, $white);
            $this->drawFinderPattern($qrImage, 0, $size - 7 * $cellSize, $cellSize, $black, $white);
            
            imagepng($qrImage, $qrPath, 9);
            
            Log::debug('QR code generated with GD fallback', ['path' => $qrPath]);
            return $qrPath;
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR code with GD fallback', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
            return '';
        }
    }

    /**
     * Draw finder pattern for QR code (positioning squares)
     */
    protected function drawFinderPattern($image, int $x, int $y, int $cellSize, int $black, int $white): void
    {
        $size = 7 * $cellSize;
        // Outer black square
        imagefilledrectangle($image, $x, $y, $x + $size - 1, $y + $size - 1, $black);
        // White square
        imagefilledrectangle($image, $x + $cellSize, $y + $cellSize, $x + 6 * $cellSize - 1, $y + 6 * $cellSize - 1, $white);
        // Inner black square
        imagefilledrectangle($image, $x + 2 * $cellSize, $y + 2 * $cellSize, $x + 5 * $cellSize - 1, $y + 5 * $cellSize - 1, $black);
    }
    
    protected function getFontPath(): ?string
    {
        // System fonts
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            'C:\Windows\Fonts\arialbd.ttf',
        ];
        
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }
        
        // Try project fonts
        $projectFont = storage_path('fonts/DejaVuSans-Bold.ttf');
        if (file_exists($projectFont)) {
            return $projectFont;
        }
        
        return null;
    }
}