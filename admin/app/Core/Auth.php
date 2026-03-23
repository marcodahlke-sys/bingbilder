<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Auth
{
    public static function check(): bool
    {
        Session::start();

        if (Session::has('user_id')) {
            return true;
        }

        self::restoreFromRememberCookie();

        return Session::has('user_id');
    }

    public static function user(): array
    {
        Session::start();

        self::restoreFromRememberCookie();

        return [
            'user_id'  => Session::get('user_id'),
            'login'    => Session::get('login'),
            'name'     => Session::get('name'),
            'level'    => Session::get('level', 0),
        ];
    }

    public static function login(array $user): void
    {
        Session::start();

        Session::set('user_id', (int)($user['userid'] ?? 0));
        Session::set('login', (string)($user['login'] ?? ''));
        Session::set('name', (string)($user['name'] ?? ''));
        Session::set('level', (int)($user['level'] ?? 0));
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: index.php?page=home');
            exit;
        }
    }

    private static function restoreFromRememberCookie(): void
    {
        if (Session::has('user_id')) {
            return;
        }

        $token = (string)($_COOKIE['remember_token'] ?? '');
        if ($token === '') {
            return;
        }

        global $pdo;

        if (!$pdo instanceof PDO) {
            return;
        }

        $stmt = $pdo->prepare('
            SELECT *
            FROM zugriff1
            WHERE remember_token = :token
              AND token_expiry IS NOT NULL
              AND token_expiry >= :now
            LIMIT 1
        ');
        $stmt->execute([
            'token' => $token,
            'now' => time(),
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            self::clearRememberCookie();
            return;
        }

        self::login($user);
    }

    public static function clearRememberCookie(): void
    {
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        );

        setcookie(
            'remember_token',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }
}