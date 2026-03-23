<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use PDO;

class StatsController extends Controller
{
    public function downloads(): void
    {
        Auth::requireLogin();

        global $pdo;

        $stmt = $pdo->query("
            SELECT id, name, size, ordner, downloads
            FROM vup_dateien
            ORDER BY downloads DESC, id DESC
            LIMIT 100
        ");
        $downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('stats/downloads', [
            'title' => 'Top Downloads',
            'downloads' => $downloads,
        ]);
    }

    public function likes(): void
    {
        Auth::requireLogin();

        global $pdo;

        $stmt = $pdo->query("
            SELECT
                d.id,
                d.name,
                d.size,
                d.ordner,
                COUNT(l.id) AS likes
            FROM vup_dateien d
            INNER JOIN likes l ON l.datei_id = d.id
            GROUP BY d.id, d.name, d.size, d.ordner
            ORDER BY likes DESC, d.id DESC
            LIMIT 100
        ");
        $likes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('stats/likes', [
            'title' => 'Top Likes',
            'likes' => $likes,
        ]);
    }
}