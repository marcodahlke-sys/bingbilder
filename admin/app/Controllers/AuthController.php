<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use PDO;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('index.php?page=home');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim((string)($_POST['login'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $remember = !empty($_POST['remember']);

            if ($login === '' || $password === '') {
                $error = 'Bitte Login und Passwort eingeben.';
            } else {
                global $pdo;

                $stmt = $pdo->prepare('
                    SELECT *
                    FROM zugriff1
                    WHERE login = :login_value OR name = :name_value
                    LIMIT 1
                ');
                $stmt->execute([
                    'login_value' => $login,
                    'name_value' => $login,
                ]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && !empty($user['pass']) && password_verify($password, (string)$user['pass'])) {
                    Auth::login($user);

                    $update = $pdo->prepare('UPDATE zugriff1 SET lastlogin = :lastlogin WHERE userid = :userid');
                    $update->execute([
                        'lastlogin' => time(),
                        'userid' => (int)($user['userid'] ?? 0),
                    ]);

                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (365 * 24 * 60 * 60);
                        $isHttps = (
                            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
                        );

                        $rememberStmt = $pdo->prepare('
                            UPDATE zugriff1
                            SET remember_token = :token, token_expiry = :expiry
                            WHERE userid = :userid
                        ');
                        $rememberStmt->execute([
                            'token' => $token,
                            'expiry' => $expiry,
                            'userid' => (int)($user['userid'] ?? 0),
                        ]);

                        setcookie(
                            'remember_token',
                            $token,
                            [
                                'expires' => $expiry,
                                'path' => '/',
                                'secure' => $isHttps,
                                'httponly' => true,
                                'samesite' => 'Lax',
                            ]
                        );
                    } else {
                        $clearStmt = $pdo->prepare('
                            UPDATE zugriff1
                            SET remember_token = NULL, token_expiry = NULL
                            WHERE userid = :userid
                        ');
                        $clearStmt->execute([
                            'userid' => (int)($user['userid'] ?? 0),
                        ]);

                        Auth::clearRememberCookie();
                    }

                    $this->redirect('index.php?page=home');
                } else {
                    $error = 'Login oder Passwort ist falsch.';
                }
            }
        }

        $this->view('auth/login', [
            'title' => 'Login',
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
        global $pdo;

        if (Auth::check()) {
            $user = Auth::user();

            if (!empty($user['user_id'])) {
                $stmt = $pdo->prepare('UPDATE zugriff1 SET remember_token = NULL, token_expiry = NULL WHERE userid = :userid');
                $stmt->execute([
                    'userid' => (int)$user['user_id'],
                ]);
            }
        }

        Auth::clearRememberCookie();
        Auth::logout();

        $this->redirect('index.php?page=login');
    }
}