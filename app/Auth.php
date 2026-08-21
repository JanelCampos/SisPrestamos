<?php

namespace App;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION[config('app.session_key')] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        $_SESSION[config('app.session_key')] = $user;
    }

    public static function logout(): void
    {
        unset($_SESSION[config('app.session_key')]);
    }

    public static function hasRole(string $role): bool
    {
        return (self::user()['role_name'] ?? null) === $role;
    }

    public static function hasAnyRole(array $roles): bool
    {
        $roleName = self::user()['role_name'] ?? null;

        return in_array($roleName, $roles, true);
    }
}
