<?php

declare(strict_types=1);

it('form request exists', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    expect($request)->toBeInstanceOf(\Illuminate\Foundation\Http\FormRequest::class);
});

it('form request has authorize method', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    expect(method_exists($request, 'authorize'))->toBeTrue();
});

it('form request has rules method', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    expect(method_exists($request, 'rules'))->toBeTrue();
});

it('authorize returns boolean', function () {
    config()->set('spectra.require_auth', false);

    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    expect(is_bool($request->authorize()))->toBeTrue();
});

it('rules returns array', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(is_array($rules))->toBeTrue();
});

it('rules includes endpoint', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['endpoint']))->toBeTrue();
});

it('rules includes method', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['method']))->toBeTrue();
});

it('rules includes auth_mode', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['auth_mode']))->toBeTrue();
});

it('rules includes path_params', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['path_params']))->toBeTrue();
});

it('rules includes query', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['query']))->toBeTrue();
});

it('rules includes headers', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['headers']))->toBeTrue();
});

it('rules includes body', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['body']))->toBeTrue();
});

it('rules includes bearer_token', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['bearer_token']))->toBeTrue();
});

it('rules includes basic_user', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['basic_user']))->toBeTrue();
});

it('rules includes basic_pass', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['basic_pass']))->toBeTrue();
});

it('rules includes impersonate_id', function () {
    $request = new \Akira\Spectra\Http\Requests\ExecuteRequest();

    $rules = $request->rules();

    expect(isset($rules['impersonate_id']))->toBeTrue();
});
