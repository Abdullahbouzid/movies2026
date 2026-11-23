<?php

return [
    'default_country' => '+218',

    // Values shown in the public stats bar.
    'halls' => 3,
    'satisfaction' => 98,

    // Default seat capacity for generated showtimes (public booking grid).
    'seats_capacity' => 60,

    // Showtime slots generated per day for the rolling schedule.
    'showtime_slots' => [
        '10:00', '11:30', '14:00', '16:30', '19:00', '20:30', '21:30', '23:00',
    ],

    // How many days ahead the rolling schedule keeps populated.
    'schedule_horizon_days' => 14,
];
