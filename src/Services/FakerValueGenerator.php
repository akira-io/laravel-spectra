<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Faker\Factory;
use Faker\Generator;

final readonly class FakerValueGenerator
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function generate(string $fieldName, string $type, string $rules): mixed
    {
        if (! config('spectra.auto_fill_faker', true)) {
            return $this->getDefaultValue($type);
        }

        $fieldLower = mb_strtolower($fieldName);
        $rulesLower = mb_strtolower($rules);

        if (str_contains($rulesLower, 'email') || str_contains($fieldLower, 'email')) {
            return $this->faker->email();
        }

        if (str_contains($fieldLower, 'first_name') || str_contains($fieldLower, 'firstname')) {
            return $this->faker->firstName();
        }
        if (str_contains($fieldLower, 'last_name') || str_contains($fieldLower, 'lastname')) {
            return $this->faker->lastName();
        }
        if (str_contains($fieldLower, 'name') || str_contains($fieldLower, 'full_name')) {
            return $this->faker->name();
        }

        if (str_contains($fieldLower, 'password')) {
            return 'password123';
        }

        if (str_contains($fieldLower, 'phone') || str_contains($fieldLower, 'mobile') || str_contains($fieldLower, 'tel')) {
            return $this->faker->phoneNumber();
        }

        if (str_contains($fieldLower, 'address')) {
            return $this->faker->address();
        }
        if (str_contains($fieldLower, 'street')) {
            return $this->faker->streetAddress();
        }
        if (str_contains($fieldLower, 'city')) {
            return $this->faker->city();
        }
        if (str_contains($fieldLower, 'state') || str_contains($fieldLower, 'province')) {
            return $this->faker->state();
        }
        if (str_contains($fieldLower, 'country')) {
            return $this->faker->country();
        }
        if (str_contains($fieldLower, 'zip') || str_contains($fieldLower, 'postal')) {
            return $this->faker->postcode();
        }

        if (str_contains($rulesLower, 'url') || str_contains($fieldLower, 'url') || str_contains($fieldLower, 'website')) {
            return $this->faker->url();
        }

        if (str_contains($rulesLower, 'date')) {
            if (str_contains($fieldLower, 'birth')) {
                return $this->faker->date('Y-m-d', '-18 years');
            }

            return $this->faker->date();
        }

        // Time
        if (str_contains($fieldLower, 'time')) {
            return $this->faker->time();
        }

        if (str_contains($fieldLower, 'company')) {
            return $this->faker->company();
        }

        if (str_contains($fieldLower, 'title')) {
            return $this->faker->title();
        }

        if (str_contains($fieldLower, 'description') || str_contains($fieldLower, 'content') || str_contains($fieldLower, 'bio')) {
            return $this->faker->paragraph();
        }

        if (str_contains($fieldLower, 'price') || str_contains($fieldLower, 'amount') || str_contains($fieldLower, 'cost')) {
            return $this->faker->randomFloat(2, 10, 1000);
        }

        if (str_contains($fieldLower, 'quantity') || str_contains($fieldLower, 'stock') || str_contains($fieldLower, 'qty')) {
            return $this->faker->numberBetween(1, 100);
        }

        if (str_contains($fieldLower, 'age')) {
            return $this->faker->numberBetween(18, 80);
        }

        if (str_contains($fieldLower, 'username') || str_contains($fieldLower, 'user_name')) {
            return $this->faker->userName();
        }

        if (str_contains($fieldLower, 'slug')) {
            return $this->faker->slug();
        }

        if (str_contains($fieldLower, 'uuid')) {
            return $this->faker->uuid();
        }

        if (str_contains($fieldLower, 'color') || str_contains($fieldLower, 'colour')) {
            return $this->faker->hexColor();
        }

        if (str_contains($fieldLower, 'image') || str_contains($fieldLower, 'photo') || str_contains($fieldLower, 'picture')) {
            return $this->faker->imageUrl();
        }

        return match ($type) {
            'integer' => str_contains($rulesLower, 'min:')
                ? $this->faker->numberBetween($this->extractMin($rules), 1000)
                : $this->faker->numberBetween(1, 100),
            'boolean' => $this->faker->boolean(),
            'array' => [],
            default => $this->faker->words(3, true),
        };
    }

    private function getDefaultValue(string $type): mixed
    {
        return match ($type) {
            'integer' => 0,
            'boolean' => false,
            'array' => [],
            default => '',
        };
    }

    private function extractMin(string $rules): int
    {
        if (preg_match('/min:(\d+)/', $rules, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
