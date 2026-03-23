<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use PDO;

class TagsController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        global $pdo;

        $stmt = $pdo->query("
            SELECT 
                TRIM(tag) AS tag,
                COUNT(*) AS anzahl,
                MAX(id) AS latest_id
            FROM tags
            WHERE TRIM(COALESCE(tag, '')) <> ''
              AND aktiv = '1'
            GROUP BY TRIM(tag)
            ORDER BY latest_id DESC, tag ASC
        ");
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('tags/index', [
            'title' => 'Tags verwalten',
            'tags' => $tags,
        ]);
    }

    public function tagcloud(): void
    {
        Auth::requireLogin();

        global $pdo;

        $stmt = $pdo->query("
            SELECT 
                TRIM(tag) AS tag,
                COUNT(*) AS anzahl,
                MAX(id) AS latest_id
            FROM tags
            WHERE TRIM(COALESCE(tag, '')) <> ''
              AND aktiv = '1'
            GROUP BY TRIM(tag)
            ORDER BY latest_id DESC, tag ASC
        ");
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('tags/tagcloud', [
            'title' => 'Tag-Cloud',
            'tags' => $tags,
        ]);
    }

    public function empty(): void
    {
        Auth::requireLogin();

        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'delete') {
            $tagId = (int)($_POST['id'] ?? 0);

            if ($tagId > 0) {
                $deleteStmt = $pdo->prepare('DELETE FROM tags WHERE id = :id LIMIT 1');
                $deleteStmt->execute([
                    'id' => $tagId,
                ]);

                Session::set('tags_message', 'Der leere Tag wurde gelöscht.');
            } else {
                Session::set('tags_error', 'Ungültige Tag-ID.');
            }

            header('Location: index.php?page=empty-tags');
            exit;
        }

        $message = (string)Session::get('tags_message', '');
        $error = (string)Session::get('tags_error', '');

        Session::remove('tags_message');
        Session::remove('tags_error');

        $stmt = $pdo->query("
            SELECT t.id, t.tag
            FROM tags t
            LEFT JOIN vup_dateien d ON d.id = t.bid
            WHERE d.id IS NULL
            ORDER BY t.id DESC
        ");
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('tags/empty', [
            'title' => 'Leere Tags',
            'tags' => $tags,
            'message' => $message,
            'error' => $error,
        ]);
    }
}