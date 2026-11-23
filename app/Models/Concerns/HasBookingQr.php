<?php

namespace App\Models\Concerns;

use App\Support\QrCode;

trait HasBookingQr
{
    /** Reference prefix, e.g. CP-XXXXXX for cinema tickets. */
    protected static function refPrefix(): string
    {
        return 'BK';
    }

    public static function generateRef(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $ref = static::refPrefix() . '-';
            for ($i = 0; $i < 6; $i++) {
                $ref .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('booking_ref', $ref)->exists());

        return $ref;
    }

    /**
     * Persist the QR image once per booking.
     *
     * The payload is a plain URL so any phone camera can open it, and so the
     * bytes stay in QR's 8-bit character set (Arabic text would not encode).
     */
    public function ensureQr(): string
    {
        if (blank($this->qr_path)) {
            $this->qr_path = QrCode::store($this->booking_ref, $this->verifyUrl());
            $this->saveQuietly();
        }

        return $this->qr_path;
    }

    public function qrUrl(): ?string
    {
        return QrCode::url($this->qr_path);
    }

    /** Absolute URL encoded into the QR image. */
    abstract public function verifyUrl(): string;

    /**
     * Pre-filled WhatsApp link the customer can tap to receive their ticket.
     * Returns null when no WhatsApp number was captured.
     */
    public function whatsappUrl(): ?string
    {
        $number = self::internationalNumber($this->whatsapp, $this->whatsapp_country_code);

        if ($number === null) {
            return null;
        }

        $message = implode("\n", array_filter([
            'تذكرة سينما بلاس — ' . $this->booking_ref,
            $this->whatsappSummary(),
            'رابط التذكرة: ' . $this->verifyUrl(),
        ]));

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }

    /**
     * Build the wa.me digits for a phone number.
     *
     * Customers type local numbers with a trunk zero ("0925271793") but often
     * paste the full international form ("+218925271793") while the country
     * selector still shows their country. Prefixing the dial code in that case
     * produced "218218925271793", so the dial code is dropped when the number
     * already starts with it.
     */
    public static function internationalNumber(?string $phone, ?string $countryCode): ?string
    {
        $local = ltrim(preg_replace('/\D+/', '', (string) $phone), '0');
        $dial = preg_replace('/\D+/', '', (string) $countryCode);

        if ($local === '') {
            return null;
        }

        if ($dial !== '' && str_starts_with($local, $dial) && strlen($local) - strlen($dial) >= 6) {
            return $local;
        }

        return $dial . $local;
    }

    /** Short human summary of what was booked, used in the WhatsApp message. */
    protected function whatsappSummary(): string
    {
        return '';
    }
}
