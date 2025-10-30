<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Requests;

use Akira\Spectra\Dto\AuthMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ExecuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('use-spectra') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string'],
            'method' => ['required', 'string', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'path_params' => ['sometimes', 'array'],
            'query' => ['sometimes', 'array'],
            'headers' => ['sometimes', 'array'],
            'body' => ['sometimes'],
            'auth_mode' => ['required', 'string', Rule::enum(AuthMode::class)],
            'impersonate_id' => ['required_if:auth_mode,impersonate', 'integer'],
            'bearer_token' => ['required_if:auth_mode,bearer', 'string'],
            'basic_user' => ['required_if:auth_mode,basic', 'string'],
            'basic_pass' => ['required_if:auth_mode,basic', 'string'],
        ];
    }
}
