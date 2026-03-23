<?php

declare(strict_types=1);

class AuthService
{
    public function __construct(private UserRepository $users, private array $config)
    {
    }

    public function restoreFromCookie(): void
    {
        if (!empty($_SESSION['user']) || empty($_COOKIE[$this->config['remember_cookie']])) {
            return;
        }
        $user = $this->users->findByRememberToken($_COOKIE[$this->config['remember_cookie']]);
        if ($user) {
            $_SESSION['user'] = $user;
        }
    }

    public function attempt(string $login, string $password, bool $remember): bool
    {
        $user = $this->users->findByLogin($login);
        if (!$user || !password_verify($password, (string) $user['pass'])) {
            return false;
        }
        $_SESSION['user'] = $user;
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + $this->config['remember_lifetime'];
            $this->users->updateRememberToken((int) $user['userid'], $token, $expiry);
            setcookie($this->config['remember_cookie'], $token, ['expires' => $expiry, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        }
        return true;
    }

    public function logout(): void
    {
        if (!empty($_SESSION['user']['userid'])) {
            $this->users->updateRememberToken((int) $_SESSION['user']['userid'], null, null);
        }
        setcookie($this->config['remember_cookie'], '', time() - 3600, '/');
        $_SESSION = [];
        session_destroy();
    }
}
