<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait HandlesBookings
{
    /**
     * Validate the customer fields shared by every booking type.
     *
     * @return array<string, mixed>
     */
    protected function validateCustomer(Request $request): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:8'],
            'country_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp_country_code' => ['required', 'string', 'max:8'],
            'whatsapp' => ['required', 'string', 'max:30'],
        ]);

        if (count(preg_split('/\s+/', trim($data['full_name']))) < 3) {
            throw ValidationException::withMessages([
                'full_name' => 'يرجى إدخال الاسم الثلاثي كاملاً',
            ]);
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $errors
     */
    protected function bookingFailed(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        return back()->withErrors($errors)->withInput();
    }

    /**
     * Reject a slot whose start time has already passed on the requested date.
     *
     * Showtimes and play times are recurring, so a date that is "today" still
     * lists every slot of the day. Without this guard a customer can book a
     * screening that already started.
     *
     * @param  string  $date  Y-m-d
     * @param  string  $startTime  H:i or H:i:s
     */
    protected function slotHasPassed(string $date, ?string $startTime): bool
    {
        if ($startTime === null || $startTime === '') {
            return false;
        }

        $day = Carbon::parse($date)->startOfDay();

        if (! $day->isSameDay(Carbon::today())) {
            return $day->isBefore(Carbon::today());
        }

        return Carbon::parse($day->format('Y-m-d') . ' ' . substr($startTime, 0, 5))->isPast();
    }

    /**
     * Day labels are English (Sunday, Monday, ...) with a numeric d/m/Y date.
     *
     * @return array<int, array{name:string, nameAr:string, date:string, iso:string, dow:int, isToday:bool}>
     */
    protected function weekDays(): array
    {
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i);
            $days[] = [
                'name' => Showtime::$dayNames[$date->dayOfWeek],
                'nameAr' => Showtime::$dayNamesAr[$date->dayOfWeek],
                'date' => $date->format('d/m/Y'),
                'iso' => $date->format('Y-m-d'),
                'dow' => $date->dayOfWeek,
                'isToday' => $i === 0,
            ];
        }

        return $days;
    }
}
