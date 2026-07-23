<?php

test('every shipped Blade template compiles to valid PHP', function () {
    // Blade never validates its output; ob:views:lint parses every compiled
    // template (regression guard for e.g. adjacent @endif@endfeature).
    $this->artisan('ob:views:lint')->assertExitCode(0);
});
