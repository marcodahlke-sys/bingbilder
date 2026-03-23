<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PDO;

class FileCheckController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $action = (string) ($_POST['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_orphan') {
            $this->deleteOrphanFile();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_all_orphans') {
            $this->deleteAllOrphanFiles();
            return;
        }

        global $pdo;

        $showMissing = isset($_GET['show_missing']) ? (string) $_GET['show_missing'] === '1' : true;
        $showBroken = isset($_GET['show_broken']) ? (string) $_GET['show_broken'] === '1' : true;
        $showOrphans = isset($_GET['show_orphans']) ? (string) $_GET['show_orphans'] === '1' : true;

        $stmt = $pdo->query("
            SELECT
                id,
                name,
                ordner,
                entrytime,
                size,
                to_kat
            FROM vup_dateien
            ORDER BY id DESC
        ");

        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();

        $missingFiles = array();
        $brokenFiles = array();

        $dbUploadsMap = array();
        $dbThumbsMap = array();

        $total = 0;
        $existing = 0;
        $missing = 0;
        $broken = 0;

        foreach ($rows as $row) {
            $total++;

            $filename = trim((string) ($row['name'] ?? ''));
            $folderFromDb = trim((string) ($row['ordner'] ?? ''));
            $folder = $folderFromDb !== '' ? trim($folderFromDb, '/\\') : 'uploads';

            $relativePath = '';
            $absolutePath = '';
            $mimeType = '';
            $realFileSize = 0;
            $width = 0;
            $height = 0;

            if ($filename === '') {
                $missing++;
                $missingFiles[] = array(
                    'id' => (int) $row['id'],
                    'name' => '',
                    'ordner' => $folderFromDb,
                    'entrytime' => (string) ($row['entrytime'] ?? ''),
                    'size' => (string) ($row['size'] ?? ''),
                    'to_kat' => (int) ($row['to_kat'] ?? 0),
                    'relative_path' => '',
                    'absolute_path' => '',
                    'status_text' => 'Kein Dateiname in DB',
                );
                continue;
            }

            $cleanFilename = ltrim(str_replace('\\', '/', $filename), '/');
            $relativePath = $folder . '/' . $cleanFilename;
            $absolutePath = BASE_PATH . '/' . $relativePath;

            $dbUploadsMap[strtolower($relativePath)] = true;
            $dbThumbsMap[strtolower('thumbs/' . $cleanFilename)] = true;

            if (!is_file($absolutePath)) {
                $missing++;
                $missingFiles[] = array(
                    'id' => (int) $row['id'],
                    'name' => $filename,
                    'ordner' => $folderFromDb,
                    'entrytime' => (string) ($row['entrytime'] ?? ''),
                    'size' => (string) ($row['size'] ?? ''),
                    'to_kat' => (int) ($row['to_kat'] ?? 0),
                    'relative_path' => $relativePath,
                    'absolute_path' => $absolutePath,
                    'status_text' => 'Datei fehlt',
                );
                continue;
            }

            $existing++;
            clearstatcache(true, $absolutePath);
            $realFileSize = (int) (@filesize($absolutePath) ?: 0);

            $isBroken = false;
            $statusText = 'OK';

            if ($realFileSize <= 0) {
                $isBroken = true;
                $statusText = 'Datei ist 0 Byte groß';
            } else {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $mimeType = (string) @finfo_file($finfo, $absolutePath);
                    @finfo_close($finfo);
                }

                if ($mimeType === '' || strpos($mimeType, 'image/') !== 0) {
                    $isBroken = true;
                    $statusText = 'Kein gültiges Bild-MIME';
                } else {
                    $imageInfo = @getimagesize($absolutePath);

                    if ($imageInfo === false) {
                        $isBroken = true;
                        $statusText = 'Bilddaten unlesbar / defekt';
                    } else {
                        $width = (int) ($imageInfo[0] ?? 0);
                        $height = (int) ($imageInfo[1] ?? 0);

                        if ($width <= 0 || $height <= 0) {
                            $isBroken = true;
                            $statusText = 'Ungültige Bildabmessungen';
                        }
                    }
                }
            }

            if ($isBroken) {
                $broken++;
                $brokenFiles[] = array(
                    'id' => (int) $row['id'],
                    'name' => $filename,
                    'ordner' => $folderFromDb,
                    'entrytime' => (string) ($row['entrytime'] ?? ''),
                    'size' => (string) ($row['size'] ?? ''),
                    'to_kat' => (int) ($row['to_kat'] ?? 0),
                    'relative_path' => $relativePath,
                    'absolute_path' => $absolutePath,
                    'status_text' => $statusText,
                    'mime_type' => $mimeType,
                    'real_file_size' => $realFileSize,
                    'width' => $width,
                    'height' => $height,
                );
            }
        }

        $orphanUploadsFiles = $this->findFilesWithoutDbEntry(BASE_PATH . '/uploads', $dbUploadsMap);
        $orphanThumbsFiles = $this->findFilesWithoutDbEntry(BASE_PATH . '/thumbs', $dbThumbsMap);

        $orphanServerFiles = array_merge($orphanUploadsFiles, $orphanThumbsFiles);

        usort($orphanServerFiles, static function (array $a, array $b): int {
            return strcmp((string) $a['relative_path'], (string) $b['relative_path']);
        });

        $message = (string) Session::get('files_message', '');
        $error = (string) Session::get('files_error', '');

        Session::remove('files_message');
        Session::remove('files_error');

        $this->view('files/check', array(
            'title' => 'Dateien abgleichen',
            'total' => $total,
            'existing' => $existing,
            'missing' => $missing,
            'broken' => $broken,
            'orphanCount' => count($orphanServerFiles),

            'missingFiles' => $missingFiles,
            'brokenFiles' => $brokenFiles,
            'orphanServerFiles' => $orphanServerFiles,

            'showMissing' => $showMissing,
            'showBroken' => $showBroken,
            'showOrphans' => $showOrphans,

            'message' => $message,
            'error' => $error,
        ));
    }

    private function deleteOrphanFile(): void
    {
        Auth::requireLogin();

        $relativePath = trim((string) ($_POST['relative_path'] ?? ''));

        if ($relativePath === '') {
            Session::set('files_error', 'Keine Datei zum Löschen übergeben.');
            $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
            return;
        }

        if (strtolower(basename($relativePath)) === 'trans.gif') {
            Session::set('files_error', 'trans.gif wird ignoriert und nicht gelöscht.');
            $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $absolutePath = BASE_PATH . '/' . $relativePath;

        $realFilePath = realpath($absolutePath);
        $uploadsBase = realpath(BASE_PATH . '/uploads');
        $thumbsBase = realpath(BASE_PATH . '/thumbs');

        if ($realFilePath === false || !is_file($realFilePath)) {
            Session::set('files_error', 'Die Datei wurde nicht gefunden.');
            $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
            return;
        }

        $normalizedFilePath = str_replace('\\', '/', $realFilePath);
        $isInUploads = false;
        $isInThumbs = false;

        if ($uploadsBase !== false) {
            $normalizedUploadsBase = str_replace('\\', '/', $uploadsBase);
            $isInUploads = strpos($normalizedFilePath, $normalizedUploadsBase . '/') === 0;
        }

        if ($thumbsBase !== false) {
            $normalizedThumbsBase = str_replace('\\', '/', $thumbsBase);
            $isInThumbs = strpos($normalizedFilePath, $normalizedThumbsBase . '/') === 0;
        }

        if (!$isInUploads && !$isInThumbs) {
            Session::set('files_error', 'Es dürfen nur Dateien aus uploads oder thumbs gelöscht werden.');
            $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
            return;
        }

        if (!@unlink($realFilePath)) {
            Session::set('files_error', 'Die Datei konnte nicht gelöscht werden.');
            $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
            return;
        }

        Session::set('files_message', 'Datei wurde gelöscht: ' . basename($realFilePath));
        $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
    }

    private function deleteAllOrphanFiles(): void
    {
        Auth::requireLogin();

        global $pdo;

        $stmt = $pdo->query("
            SELECT
                name,
                ordner
            FROM vup_dateien
        ");

        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();

        $dbUploadsMap = array();
        $dbThumbsMap = array();

        foreach ($rows as $row) {
            $filename = trim((string) ($row['name'] ?? ''));
            $folderFromDb = trim((string) ($row['ordner'] ?? ''));
            $folder = $folderFromDb !== '' ? trim($folderFromDb, '/\\') : 'uploads';

            if ($filename === '') {
                continue;
            }

            $cleanFilename = ltrim(str_replace('\\', '/', $filename), '/');

            $dbUploadsMap[strtolower($folder . '/' . $cleanFilename)] = true;
            $dbThumbsMap[strtolower('thumbs/' . $cleanFilename)] = true;
        }

        $orphanUploadsFiles = $this->findFilesWithoutDbEntry(BASE_PATH . '/uploads', $dbUploadsMap);
        $orphanThumbsFiles = $this->findFilesWithoutDbEntry(BASE_PATH . '/thumbs', $dbThumbsMap);
        $orphanServerFiles = array_merge($orphanUploadsFiles, $orphanThumbsFiles);

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($orphanServerFiles as $file) {
            $relativePath = ltrim(str_replace('\\', '/', (string) ($file['relative_path'] ?? '')), '/');

            if ($relativePath === '') {
                continue;
            }

            if (strtolower(basename($relativePath)) === 'trans.gif') {
                continue;
            }

            $absolutePath = BASE_PATH . '/' . $relativePath;
            $realFilePath = realpath($absolutePath);

            if ($realFilePath === false || !is_file($realFilePath)) {
                $failedCount++;
                continue;
            }

            $normalizedFilePath = str_replace('\\', '/', $realFilePath);
            $uploadsBase = realpath(BASE_PATH . '/uploads');
            $thumbsBase = realpath(BASE_PATH . '/thumbs');

            $isInUploads = false;
            $isInThumbs = false;

            if ($uploadsBase !== false) {
                $normalizedUploadsBase = str_replace('\\', '/', $uploadsBase);
                $isInUploads = strpos($normalizedFilePath, $normalizedUploadsBase . '/') === 0;
            }

            if ($thumbsBase !== false) {
                $normalizedThumbsBase = str_replace('\\', '/', $thumbsBase);
                $isInThumbs = strpos($normalizedFilePath, $normalizedThumbsBase . '/') === 0;
            }

            if (!$isInUploads && !$isInThumbs) {
                $failedCount++;
                continue;
            }

            if (@unlink($realFilePath)) {
                $deletedCount++;
            } else {
                $failedCount++;
            }
        }

        Session::set('files_message', $deletedCount . ' Datei(en) ohne Zuordnung gelöscht.' . ($failedCount > 0 ? ' Fehler: ' . $failedCount : ''));
        $this->redirect('index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1');
    }

    private function findFilesWithoutDbEntry(string $baseDir, array $dbFileMap): array
    {
        $result = array();

        if (!is_dir($baseDir)) {
            return $result;
        }

        $basePathNormalized = str_replace('\\', '/', BASE_PATH);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $absolutePath = str_replace('\\', '/', $fileInfo->getPathname());
            $relativePath = ltrim(substr($absolutePath, strlen($basePathNormalized)), '/');
            $key = strtolower($relativePath);

            if (strtolower($fileInfo->getFilename()) === 'trans.gif') {
                continue;
            }

            if (isset($dbFileMap[$key])) {
                continue;
            }

            $result[] = array(
                'name' => $fileInfo->getFilename(),
                'relative_path' => $relativePath,
                'absolute_path' => $absolutePath,
                'size' => (int) $fileInfo->getSize(),
                'modified' => date('Y-m-d H:i:s', (int) $fileInfo->getMTime()),
            );
        }

        return $result;
    }
}