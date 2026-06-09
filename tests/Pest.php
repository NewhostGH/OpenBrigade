<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure passed to uses() will be bound to each test case.  The
| RefreshDatabase trait is not used globally because most tests only
| need HTTP-level assertions against a mocked or seeded database.
|
*/

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
