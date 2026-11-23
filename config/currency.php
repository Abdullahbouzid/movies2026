<?php

return [
    /*
     * Single source of truth for money presentation. Every price shown to a
     * user — public pages, Filament tables, treasury reports — reads these
     * values, so switching currency is a one-line change.
     */
    'code' => 'LYD',
    'symbol' => 'د.ل',
    'decimals' => 2,
    'thousands_separator' => ',',
    'decimal_separator' => '.',
];
