<?php

declare(strict_types=1);

use Akira\Spectra\Services\FakerValueGenerator;

beforeEach(function () {
    $this->generator = app(FakerValueGenerator::class);
});

it('generates email for email field', function () {
    $result = $this->generator->generate('email', 'string', '');

    expect($result)->toBeString()
        ->and($result)->toContain('@');
});

it('generates email when email in rules', function () {
    $result = $this->generator->generate('user_email', 'string', 'email');

    expect($result)->toBeString()
        ->and($result)->toContain('@');
});

it('generates first name for first_name field', function () {
    $result = $this->generator->generate('first_name', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates last name for last_name field', function () {
    $result = $this->generator->generate('last_name', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates full name for name field', function () {
    $result = $this->generator->generate('name', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates password for password field', function () {
    $result = $this->generator->generate('password', 'string', '');

    expect($result)->toBe('password123');
});

it('generates phone number for phone field', function () {
    $result = $this->generator->generate('phone', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates mobile for mobile field', function () {
    $result = $this->generator->generate('mobile', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates address for address field', function () {
    $result = $this->generator->generate('address', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates street for street field', function () {
    $result = $this->generator->generate('street', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates city for city field', function () {
    $result = $this->generator->generate('city', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates state for state field', function () {
    $result = $this->generator->generate('state', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates country for country field', function () {
    $result = $this->generator->generate('country', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates postal code for zip field', function () {
    $result = $this->generator->generate('zip', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates url for url field', function () {
    $result = $this->generator->generate('website', 'string', '');

    expect($result)->toBeString()
        ->and(str_starts_with($result, 'http'))->toBeTrue();
});

it('generates date for date field', function () {
    $result = $this->generator->generate('date_field', 'string', 'date');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates birthdate for birth date field', function () {
    $result = $this->generator->generate('date_of_birth', 'string', 'date');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates time for time field', function () {
    $result = $this->generator->generate('time_field', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates company for company field', function () {
    $result = $this->generator->generate('company', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates title for title field', function () {
    $result = $this->generator->generate('title', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('generates description for description field', function () {
    $result = $this->generator->generate('description', 'string', '');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(5);
});

it('generates price for price field', function () {
    $result = $this->generator->generate('price', 'float', '');

    expect($result)->toBeNumeric()
        ->and($result)->toBeGreaterThan(0);
});

it('generates amount for amount field', function () {
    $result = $this->generator->generate('amount', 'float', '');

    expect($result)->toBeNumeric()
        ->and($result)->toBeGreaterThan(0);
});

it('generates boolean for boolean field', function () {
    config()->set('spectra.auto_fill_faker', true);

    $result = $this->generator->generate('is_active', 'boolean', '');

    expect(is_bool($result))->toBeTrue();
});

it('returns default value when auto_fill disabled', function () {
    config()->set('spectra.auto_fill_faker', false);

    $result = $this->generator->generate('any_field', 'string', '');

    expect($result)->toBe('');
});

it('returns default integer when auto_fill disabled', function () {
    config()->set('spectra.auto_fill_faker', false);

    $result = $this->generator->generate('number_field', 'integer', '');

    expect($result)->toBe(0);
});

it('case insensitive field matching', function () {
    $result1 = $this->generator->generate('EMAIL', 'string', '');
    $result2 = $this->generator->generate('email', 'string', '');

    expect($result1)->toBeString()
        ->and($result1)->toContain('@')
        ->and($result2)->toBeString()
        ->and($result2)->toContain('@');
});

it('case insensitive rule matching', function () {
    $result1 = $this->generator->generate('field', 'string', 'EMAIL');
    $result2 = $this->generator->generate('field', 'string', 'email');

    expect($result1)->toBeString()
        ->and($result1)->toContain('@')
        ->and($result2)->toBeString()
        ->and($result2)->toContain('@');
});
