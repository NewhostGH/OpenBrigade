<?php

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

uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');
