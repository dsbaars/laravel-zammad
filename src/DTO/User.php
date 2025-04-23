<?php

namespace CodebarAg\Zammad\DTO;

use Carbon\Carbon;

class User
{
    public static function fromJson(array $data, bool $expanded = false): self
    {
        $params = [];
        
        if (isset($data['id'])) {
            $params['id'] = $data['id'];
        }
        if (isset($data['firstname'])) {
            $params['first_name'] = $data['firstname'];
        }
        if (isset($data['lastname'])) {
            $params['last_name'] = $data['lastname'];
        }
        if (isset($data['login'])) {
            $params['login'] = $data['login'];
        }
        if (isset($data['email'])) {
            $params['email'] = $data['email'];
        }
        if (isset($data['last_login'])) {
            $params['last_login_at'] = Carbon::parse($data['last_login']);
        }
        if (isset($data['updated_at'])) {
            $params['updated_at'] = Carbon::parse($data['updated_at']);
        }
        if (isset($data['created_at'])) {
            $params['created_at'] = Carbon::parse($data['created_at']);
        }
        
        if ($expanded) {
            $params['expanded'] = $data;
        }

        return new self(...$params);
    }

    public function __construct(
        public int $id = 0,
        public string $first_name = '',
        public string $last_name = '',
        public string $login = '',
        public string $email = '',
        public ?Carbon $last_login_at = null,
        public ?Carbon $updated_at = null,
        public ?Carbon $created_at = null,
        public ?array $expanded = null,
    ) {}

    public static function fake(
        ?int $id = null,
        ?string $first_name = null,
        ?string $last_name = null,
        ?string $login = null,
        ?string $email = null,
        ?Carbon $last_login_at = null,
        ?Carbon $updated_at = null,
        ?Carbon $created_at = null,
        ?array $expanded = null,
    ): self {
        return new self(
            id: $id ?? random_int(1, 1000),
            first_name: $first_name ?? 'Max',
            last_name: $last_name ?? 'Mustermann',
            login: $login ?? 'max.mustermann@codebar.ch',
            email: $email ?? 'max.mustermann@codebar.ch',
            last_login_at: $last_login_at ?? now(),
            updated_at: $updated_at ?? now(),
            created_at: $created_at ?? now()->subDay(),
            expanded: $expanded ?? null,
        );
    }
}
