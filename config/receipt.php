<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Receipt Image Configuration - Modern Design
    |--------------------------------------------------------------------------
    | Configuration for receipt image generation with modern, clean design
    | Inspired by contemporary receipt UI patterns
    */

    'image' => [
        // Image dimensions (4" x 6" at 300 DPI = 1200x1800px)
        'width' => 1200,
        'height' => 1800,
        'compression' => 9, // PNG compression level 0-9
    ],

    'colors' => [
        'white' => [255, 255, 255],
        'background' => [248, 249, 250],            // Light background
        'background_gradient' => [240, 242, 245],   // Gradient end
        'primary' => [102, 126, 234],               // Purple (#667eea)
        'primary_dark' => [118, 75, 162],           // Dark Purple (#764ba2)
        'text_dark' => [30, 41, 59],                // Slate 900
        'text_light' => [100, 116, 139],            // Slate 500
        'border' => [226, 232, 240],                // Slate 200
        'border_light' => [203, 213, 225],          // Slate 100
    ],

    'accent_bar' => [
        'enabled' => true,
        'height' => 8,
        'start_color' => [102, 126, 234],           // #667eea
        'end_color' => [118, 75, 162],              // #764ba2
    ],

    'logo' => [
        'enabled' => true,
        'path' => 'logo.jpeg',                      // Path relative to public_path()
        'width' => 200,                             // 120px scaled for 300 DPI (200 for preview)
        'top_margin' => 60,
        'shadow_enabled' => false,                  // Modern design uses no shadow
        'shadow_offset' => 0,
    ],

    'company' => [
        'name' => function_exists('env') ? env('APP_NAME', '11 Freight') : '11 Freight',
        'top_margin_after_logo' => 20,              // Space below logo
        'font_size' => 16,                          // 11px scaled (16 for 300 DPI)
        'letter_spacing' => 2,                      // letter-spacing effect
        'color' => 'text_light',
    ],

    'header' => [
        'title' => 'OFFICIAL RECEIPT',
        'top_margin' => 40,                         // Space above header
        'font_size' => 19,                          // 13px scaled
        'letter_spacing' => 2,                      // letter-spacing: 2px
        'color' => 'text_dark',
    ],

    'receipt_number' => [
        'top_margin' => 12,                         // Space below title
        'font_size' => 29,                          // 20px scaled
        'color' => 'purple',
        'letter_spacing' => 1,
    ],

    'info_section' => [
        'top_margin' => 40,                         // Padding before info rows
        'bottom_margin' => 40,                      // Padding after info rows
        'row_height' => 21,                         // Padding per row (14px)
        'left_margin' => 60,
        'right_margin' => 60,
        'label_font_size' => 16,                    // 11px scaled
        'value_font_size' => 19,                    // 13px scaled
        'label_color' => 'text_light',
        'value_color' => 'text_dark',
        'line_color' => 'border',
        'line_thickness' => 1,
    ],

    'qr_code' => [
        'enabled' => true,
        'size' => 320,                              // 160px * 2 for quality
        'container_padding' => 24,                  // Padding around QR (16px scaled)
        'container_border' => 1,                    // Container border
        'container_border_color' => 'border',       // Container border color
        'container_background' => 'white',          // Container background
        'bottom_margin_to_qr' => 450,               // Distance from bottom where QR starts
        'shadow_enabled' => false,                  // No shadow for modern design
    ],

    'qr_note' => [
        'text' => 'SCAN TO VERIFY AUTHENTICITY',
        'top_margin' => 24,                         // Space below QR
        'font_size' => 15,                          // 10px scaled
        'color' => 'text_light',
        'letter_spacing' => 1,
    ],

    'footer' => [
        'disclaimer' => 'DIGITALLY GENERATED • VALID WITHOUT SIGNATURE',
        'bottom_margin' => 30,                      // Space from bottom
        'font_size' => 13,                          // 9px scaled
        'color' => 'border_light',
        'letter_spacing' => 1,
    ],

    'fields' => [
        // Customize which fields appear and their labels
        ['key' => 'type', 'label' => 'TYPE', 'transform' => 'upper'],
        ['key' => 'linked_id', 'label' => 'LINKED ID'],
        ['key' => 'created_at', 'label' => 'ISSUE DATE', 'format' => 'M d, Y • h:i A'],
    ],

    'layout' => [
        // Overall layout proportions
        'gradient_background' => true,              // Use gradient background
        'use_borders' => true,                      // Show borders between rows
        'modern_spacing' => true,                   // Use modern spacing ratios
    ],
];
