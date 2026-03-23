<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use DateTime;
use PDO;
use Throwable;

class FilesController extends Controller
{
    public function rename(): void
    {
        Auth::requireLogin();

        global $pdo;

        $tempDir = BASE_PATH . '/temp';
        $tempFiles = array();

        if (is_dir($tempDir)) {
            $items = scandir($tempDir);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }

                    $fullPath = $tempDir . '/' . $item;
                    if (!is_file($fullPath)) {
                        continue;
                    }

                    $extension = strtolower((string) pathinfo($item, PATHINFO_EXTENSION));
                    if ($extension === 'txt') {
                        continue;
                    }

                    $tempFiles[] = array(
                        'name' => $item,
                        'size' => filesize($fullPath) ?: 0,
                        'path' => $fullPath,
                    );
                }
            }
        }

        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tempName = trim((string) ($_POST['temp_name'] ?? ''));
            $date = trim((string) ($_POST['date'] ?? ''));
            $categoryId = (int) ($_POST['to_kat'] ?? 0);

            if ($tempName === '' || $date === '' || $categoryId <= 0) {
                $error = 'Bitte Bild, Datum und Kategorie vollständig angeben.';
            } else {
                $sourcePath = $tempDir . '/' . basename($tempName);

                if (!is_file($sourcePath)) {
                    $error = 'Die temporäre Datei wurde nicht gefunden.';
                } else {
                    $dateObject = DateTime::createFromFormat('d.m.Y', $date);

                    if (!$dateObject) {
                        $error = 'Das Datum ist ungültig.';
                    } else {
                        $timestamp = $dateObject->setTime(0, 0, 0)->getTimestamp();
                        $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
                        $finalName = $timestamp . '.' . $extension;
                        $targetPath = BASE_PATH . '/uploads/' . $finalName;

                        if (file_exists($targetPath)) {
                            $error = 'Für dieses Datum existiert bereits eine Datei.';
                        } else {
                            if (!is_dir(BASE_PATH . '/uploads')) {
                                mkdir(BASE_PATH . '/uploads', 0775, true);
                            }

                            if (!rename($sourcePath, $targetPath)) {
                                $error = 'Die Datei konnte nicht nach uploads verschoben werden.';
                            } else {
                                $fileSizeBytes = (string) ((int) (filesize($targetPath) ?: 0));

                                $flagFieldMap = array(
                                    1  => 'k1',
                                    3  => 'k3',
                                    4  => 'k4',
                                    7  => 'k7',
                                    9  => 'k9',
                                    10 => 'k10',
                                    13 => 'k13',
                                    15 => 'k15',
                                );

                                $flagValues = array(
                                    'k1'  => '0',
                                    'k3'  => '0',
                                    'k4'  => '0',
                                    'k7'  => '0',
                                    'k9'  => '0',
                                    'k10' => '0',
                                    'k13' => '0',
                                    'k15' => '0',
                                );

                                foreach ($flagFieldMap as $categoryDbId => $fieldName) {
                                    if (!empty($_POST['flag_' . $categoryDbId])) {
                                        $flagValues[$fieldName] = '1';
                                    }
                                }

                                $stmt = $pdo->prepare("
                                    INSERT INTO vup_dateien
                                    (
                                        entrytime, name, size, ordner, to_kat, aktivator, video,
                                        k1, k3, k4, k7, k9, k10, k13, k15, empty, downloads
                                    )
                                    VALUES
                                    (
                                        :entrytime, :name, :size, :ordner, :to_kat, 0, '0',
                                        :k1, :k3, :k4, :k7, :k9, :k10, :k13, :k15, '0', 0
                                    )
                                ");

                                $stmt->execute(array(
                                    'entrytime' => (string) $timestamp,
                                    'name' => $finalName,
                                    'size' => $fileSizeBytes,
                                    'ordner' => 'uploads',
                                    'to_kat' => $categoryId,
                                    'k1' => $flagValues['k1'],
                                    'k3' => $flagValues['k3'],
                                    'k4' => $flagValues['k4'],
                                    'k7' => $flagValues['k7'],
                                    'k9' => $flagValues['k9'],
                                    'k10' => $flagValues['k10'],
                                    'k13' => $flagValues['k13'],
                                    'k15' => $flagValues['k15'],
                                ));

                                $newId = (int) $pdo->lastInsertId();

                                header('Location: index.php?page=edit&pid=' . $newId);
                                exit;
                            }
                        }
                    }
                }
            }
        }

        $categoriesStmt = $pdo->query('SELECT id, name FROM vup_kategorien ORDER BY id ASC');
        $categories = $categoriesStmt ? $categoriesStmt->fetchAll(PDO::FETCH_ASSOC) : array();

        $mainCategories = $categories;
        $flagCategoryIds = array(1, 3, 4, 7, 9, 10, 13, 15);
        $flagCategories = array();

        foreach ($categories as $category) {
            $catId = (int) $category['id'];
            if (in_array($catId, $flagCategoryIds, true)) {
                $flagCategories[] = $category;
            }
        }

        usort($flagCategories, function ($a, $b) {
            return (int) $a['id'] <=> (int) $b['id'];
        });

        $this->view('files/rename', array(
            'title' => 'Datei umbenennen',
            'tempFiles' => $tempFiles,
            'mainCategories' => $mainCategories,
            'flagCategories' => $flagCategories,
            'message' => $message,
            'error' => $error,
        ));
    }

    public function listing(): void
    {
        Auth::requireLogin();

        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'delete') {
            $this->deleteFile();
            return;
        }

        $message = (string) Session::get('files_message', '');
        $error = (string) Session::get('files_error', '');

        Session::remove('files_message');
        Session::remove('files_error');

        $stmt = $pdo->query("
            SELECT 
                d.*,
                k.name AS kategorie_name,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM tags t
                        WHERE t.bid = d.id
                          AND TRIM(COALESCE(t.tag, '')) <> ''
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
            FROM vup_dateien d
            LEFT JOIN vup_kategorien k ON k.id = d.to_kat
            ORDER BY d.id DESC
            LIMIT 100
        ");
        $files = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();

        $this->view('files/list', array(
            'title' => 'Dateienliste',
            'files' => $files,
            'message' => $message,
            'error' => $error,
        ));
    }

    private function deleteFile(): void
    {
        global $pdo;

        $fileId = (int) ($_POST['id'] ?? 0);

        if ($fileId <= 0) {
            Session::set('files_error', 'Ungültige Datei-ID.');
            header('Location: index.php?page=files');
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, name FROM vup_dateien WHERE id = :id LIMIT 1');
        $stmt->execute(array('id' => $fileId));
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            Session::set('files_error', 'Datei wurde nicht gefunden.');
            header('Location: index.php?page=files');
            exit;
        }

        $fileName = basename((string) ($file['name'] ?? ''));
        $uploadPath = BASE_PATH . '/uploads/' . $fileName;
        $thumbPath = BASE_PATH . '/thumbs/' . $fileName;

        $pdo->beginTransaction();

        try {
            $deleteTagsStmt = $pdo->prepare('DELETE FROM tags WHERE bid = :bid');
            $deleteTagsStmt->execute(array('bid' => $fileId));

            $deleteDescriptionStmt = $pdo->prepare('DELETE FROM beschreibung WHERE id = :id');
            $deleteDescriptionStmt->execute(array('id' => $fileId));

            $deleteFileStmt = $pdo->prepare('DELETE FROM vup_dateien WHERE id = :id LIMIT 1');
            $deleteFileStmt->execute(array('id' => $fileId));

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            Session::set('files_error', 'Datei konnte nicht gelöscht werden.');
            header('Location: index.php?page=files');
            exit;
        }

        if ($fileName !== '') {
            if (is_file($uploadPath)) {
                @unlink($uploadPath);
            }

            if (is_file($thumbPath)) {
                @unlink($thumbPath);
            }
        }

        Session::set('files_message', 'Datei wurde inklusive Tags, Beschreibung und Bilddateien gelöscht.');
        header('Location: index.php?page=files');
        exit;
    }
}