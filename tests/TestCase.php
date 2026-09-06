<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function asOwner(): static
    {
        return $this->withSession([
            (string) config('owner.session_key') => true,
        ]);
    }
}
