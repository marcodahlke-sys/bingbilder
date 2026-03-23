<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use PDO;

class HomeController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        global $pdo;

        $month = isset($_GET['amonat']) ? max(1, min(12, (int)$_GET['amonat'])) : (int)date('n');
        $year = isset($_GET['ajahr']) ? max(2000, min(2100, (int)$_GET['ajahr'])) : (int)date('Y');
        $selectedTimestamp = isset($_GET['dat']) && ctype_digit((string)$_GET['dat']) ? (int)$_GET['dat'] : null;

        $counterStmt = $pdo->query('SELECT count FROM counter LIMIT 1');
        $counter = (int)($counterStmt->fetchColumn() ?: 0);

        $latestStmt = $pdo->query("
            SELECT 
                d.*,
                k.name AS kategorie_name,
                CASE
                    WHEN b.id IS NOT NULL AND TRIM(COALESCE(b.beschreibung, '')) <> '' THEN 1
                    ELSE 0
                END AS has_description,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM tags t
                        WHERE t.bid = d.id
                          AND TRIM(COALESCE(t.tag, '')) <> ''
                    ) THEN 1
                    ELSE 0
                END AS has_tags
            FROM vup_dateien d
            LEFT JOIN vup_kategorien k ON k.id = d.to_kat
            LEFT JOIN beschreibung b ON b.id = d.id
            ORDER BY CAST(d.entrytime AS UNSIGNED) DESC, d.id DESC
            LIMIT 31
        ");
        $latestImages = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

        $images = $latestImages;

        if ($selectedTimestamp !== null) {
            $dayStart = mktime(0, 0, 0, (int)date('n', $selectedTimestamp), (int)date('j', $selectedTimestamp), (int)date('Y', $selectedTimestamp));
            $dayEnd = mktime(23, 59, 59, (int)date('n', $selectedTimestamp), (int)date('j', $selectedTimestamp), (int)date('Y', $selectedTimestamp));

            $dayStmt = $pdo->prepare("
                SELECT 
                    d.*,
                    k.name AS kategorie_name,
                    CASE
                        WHEN b.id IS NOT NULL AND TRIM(COALESCE(b.beschreibung, '')) <> '' THEN 1
                        ELSE 0
                    END AS has_description,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM tags t
                            WHERE t.bid = d.id
                              AND TRIM(COALESCE(t.tag, '')) <> ''
                        ) THEN 1
                        ELSE 0
                    END AS has_tags
                FROM vup_dateien d
                LEFT JOIN vup_kategorien k ON k.id = d.to_kat
                LEFT JOIN beschreibung b ON b.id = d.id
                WHERE CAST(d.entrytime AS UNSIGNED) BETWEEN :dayStart AND :dayEnd
                ORDER BY CAST(d.entrytime AS UNSIGNED) ASC, d.id ASC
            ");
            $dayStmt->execute([
                'dayStart' => $dayStart,
                'dayEnd' => $dayEnd,
            ]);
            $images = $dayStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $monthStart = mktime(0, 0, 0, $month, 1, $year);
        $monthEnd = mktime(23, 59, 59, $month + 1, 0, $year);

        $calendarStmt = $pdo->prepare("
            SELECT 
                d.id,
                d.entrytime,
                d.name,
                CASE
                    WHEN b.id IS NOT NULL AND TRIM(COALESCE(b.beschreibung, '')) <> '' THEN 1
                    ELSE 0
                END AS has_description,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM tags t
                        WHERE t.bid = d.id
                          AND TRIM(COALESCE(t.tag, '')) <> ''
                    ) THEN 1
                    ELSE 0
                END AS has_tags
            FROM vup_dateien d
            LEFT JOIN beschreibung b ON b.id = d.id
            WHERE CAST(d.entrytime AS UNSIGNED) BETWEEN :monthStart AND :monthEnd
            ORDER BY CAST(d.entrytime AS UNSIGNED) ASC, d.id ASC
        ");
        $calendarStmt->execute([
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
        ]);
        $calendarItems = $calendarStmt->fetchAll(PDO::FETCH_ASSOC);

        $imagesByDay = [];
        foreach ($calendarItems as $item) {
            $ts = (int)$item['entrytime'];
            $day = (int)date('j', $ts);
            $imagesByDay[$day][] = $item;
        }

        $this->view('home/index', [
            'title' => 'Startseite',
            'counter' => number_format($counter, 0, ',', '.'),
            'month' => $month,
            'year' => $year,
            'selectedTimestamp' => $selectedTimestamp,
            'images' => $images,
            'imagesByDay' => $imagesByDay,
        ]);
    }
}