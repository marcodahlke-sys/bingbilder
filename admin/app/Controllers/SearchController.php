<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use PDO;

class SearchController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        global $pdo;

        $rawTag = trim((string)($_GET['tag'] ?? ''));
        $searchTag = trim(html_entity_decode(strip_tags($rawTag), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $results = [];

        if ($searchTag !== '') {
            $stmt = $pdo->prepare("
                SELECT 
                    d.*,
                    k.name AS kategorie_name,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM tags t2
                            WHERE t2.bid = d.id
                              AND TRIM(COALESCE(t2.tag, '')) <> ''
                              AND t2.aktiv = '1'
                        ) THEN 1
                        ELSE 0
                    END AS has_tags,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM beschreibung b
                            WHERE b.id = d.id
                              AND TRIM(COALESCE(b.beschreibung, '')) <> ''
                        ) THEN 1
                        ELSE 0
                    END AS has_description
                FROM tags t
                INNER JOIN vup_dateien d ON d.id = t.bid
                LEFT JOIN vup_kategorien k ON k.id = d.to_kat
                WHERE TRIM(COALESCE(t.tag, '')) = :tag
                  AND t.aktiv = '1'
                GROUP BY d.id
                ORDER BY d.id DESC
            ");
            $stmt->execute([
                'tag' => $searchTag,
            ]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('search/index', [
            'title' => 'Suchergebnisse',
            'searchTag' => $searchTag,
            'results' => $results,
        ]);
    }
}