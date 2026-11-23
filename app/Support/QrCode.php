<?php

namespace App\Support;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCode
{
    /**
     * Render the payload to a PNG under storage/app/public/qrcodes and return
     * the relative path. Rendering locally keeps booking data off third-party
     * services and works without outbound network access.
     */
    public static function store(string $reference, string $payload): string
    {
        $path = 'qrcodes/' . $reference . '.png';

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        $writer = new Writer(new GDLibRenderer(400, 2));

        Storage::disk('public')->put($path, $writer->writeString($payload));

        return $path;
    }

    public static function url(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }
}
