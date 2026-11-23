<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pin the clock to 08:00 so seeded "today" slots (11:30 onward) are still
        // upcoming. Without this the suite fails whenever it runs after a slot's
        // start time, because booking a started slot is now rejected.
        $this->travelTo(now()->startOfDay()->addHours(8));
    }
}
