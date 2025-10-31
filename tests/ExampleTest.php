<?php

declare(strict_types=1);

it('loads service provider', function () {
    expect(config('spectra'))->toBeArray()
        ->and(config('spectra.enabled'))->toBeTrue();
});
