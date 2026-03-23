<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class UploadController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $message = '';
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->isAjaxRequest()) {
                $this->handleAjaxUpload();
                return;
            }

            list($message, $errors) = $this->handleClassicUpload();
        }

        $this->view('upload/index', [
            'title'   => 'Upload',
            'message' => $message,
            'errors'  => $errors,
        ]);
    }

    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function handleAjaxUpload(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $result = $this->saveSingleUploadedFile('file');
        echo json_encode($result);
        exit;
    }

    private function handleClassicUpload(): array
    {
        if (!isset($_FILES['files']) || !is_array($_FILES['files']['name'])) {
            return array('', array('Es wurden keine Dateien übergeben.'));
        }

        $count = count($_FILES['files']['name']);
        if ($count < 1) {
            return array('', array('Bitte wähle mindestens eine Datei aus.'));
        }

        if ($count > 31) {
            return array('', array('Es dürfen maximal 31 Dateien gleichzeitig hochgeladen werden.'));
        }

        $uploadedCount = 0;
        $uploadErrors = array();

        for ($i = 0; $i < $count; $i++) {
            $_FILES['__single'] = array(
                'name'     => $_FILES['files']['name'][$i] ?? '',
                'type'     => $_FILES['files']['type'][$i] ?? '',
                'tmp_name' => $_FILES['files']['tmp_name'][$i] ?? '',
                'error'    => $_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES['files']['size'][$i] ?? 0,
            );

            $result = $this->saveSingleUploadedFile('__single');

            if (!empty($result['success'])) {
                $uploadedCount++;
            } else {
                $uploadErrors[] = (string) ($result['message'] ?? 'Upload fehlgeschlagen.');
            }
        }

        unset($_FILES['__single']);

        if ($uploadedCount > 0 && empty($uploadErrors)) {
            return array(sprintf('%d Datei(en) erfolgreich nach temp/ hochgeladen.', $uploadedCount), array());
        }

        if ($uploadedCount > 0 && !empty($uploadErrors)) {
            return array(
                sprintf('%d Datei(en) wurden hochgeladen, aber es gab Probleme bei anderen Dateien.', $uploadedCount),
                $uploadErrors
            );
        }

        return array('', $uploadErrors ?: array('Es konnten keine Dateien hochgeladen werden.'));
    }

    private function saveSingleUploadedFile(string $fieldName): array
    {
        if (!isset($_FILES[$fieldName])) {
            return array(
                'success' => false,
                'message' => 'Es wurde keine Datei übergeben.',
            );
        }

        $file = $_FILES[$fieldName];

        $originalName = trim((string) ($file['name'] ?? ''));
        $tmpFile = (string) ($file['tmp_name'] ?? '');
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $fileSize = (int) ($file['size'] ?? 0);

        if ($errorCode === UPLOAD_ERR_NO_FILE || $originalName === '') {
            return array(
                'success' => false,
                'message' => 'Es wurde keine Datei ausgewählt.',
            );
        }

        if ($errorCode !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'message' => $this->mapUploadError($errorCode, $originalName),
            );
        }

        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'webp');

        if (!in_array($extension, $allowedExtensions, true)) {
            return array(
                'success' => false,
                'message' => sprintf('Dateityp von "%s" ist nicht erlaubt.', $originalName),
            );
        }

        if ($fileSize <= 0) {
            return array(
                'success' => false,
                'message' => sprintf('"%s" ist leer oder ungültig.', $originalName),
            );
        }

        if (!is_uploaded_file($tmpFile)) {
            return array(
                'success' => false,
                'message' => sprintf('"%s" ist keine gültige Upload-Datei.', $originalName),
            );
        }

        $tempDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'temp';

        if (!is_dir($tempDir) && !mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            return array(
                'success' => false,
                'message' => 'Der Temp-Ordner konnte nicht erstellt werden.',
            );
        }

        $safeName = $this->sanitizeFileName($originalName);
        $targetPath = $tempDir . DIRECTORY_SEPARATOR . $safeName;
        $targetPath = $this->resolveUniquePath($targetPath);

        if (!move_uploaded_file($tmpFile, $targetPath)) {
            return array(
                'success' => false,
                'message' => sprintf('"%s" konnte nicht in den Temp-Ordner verschoben werden.', $originalName),
            );
        }

        return array(
            'success' => true,
            'message' => sprintf('"%s" wurde erfolgreich nach temp/ hochgeladen.', basename($targetPath)),
            'file' => array(
                'name' => basename($targetPath),
                'size' => $fileSize,
            ),
        );
    }

    private function mapUploadError(int $errorCode, string $originalName): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return sprintf('"%s" ist zu groß.', $originalName);
            case UPLOAD_ERR_PARTIAL:
                return sprintf('"%s" wurde nur teilweise hochgeladen.', $originalName);
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Temporärer Upload-Ordner fehlt.';
            case UPLOAD_ERR_CANT_WRITE:
                return sprintf('"%s" konnte nicht auf den Server geschrieben werden.', $originalName);
            case UPLOAD_ERR_EXTENSION:
                return sprintf('Der Upload von "%s" wurde durch eine PHP-Erweiterung gestoppt.', $originalName);
            default:
                return sprintf('Fehler beim Upload von "%s".', $originalName);
        }
    }

    private function sanitizeFileName(string $fileName): string
    {
        $fileName = basename(trim($fileName));

        $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
        $nameOnly = (string) pathinfo($fileName, PATHINFO_FILENAME);

        $nameOnly = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nameOnly);
        $nameOnly = preg_replace('/_+/', '_', (string) $nameOnly);
        $nameOnly = trim((string) $nameOnly, '._-');

        if ($nameOnly === '') {
            $nameOnly = 'upload_' . time();
        }

        return $extension !== '' ? $nameOnly . '.' . $extension : $nameOnly;
    }

    private function resolveUniquePath(string $targetPath): string
    {
        if (!file_exists($targetPath)) {
            return $targetPath;
        }

        $dir = dirname($targetPath);
        $extension = pathinfo($targetPath, PATHINFO_EXTENSION);
        $baseName = pathinfo($targetPath, PATHINFO_FILENAME);

        $counter = 1;

        do {
            $candidate = $dir . DIRECTORY_SEPARATOR . $baseName . '_' . $counter;
            if ($extension !== '') {
                $candidate .= '.' . $extension;
            }
            $counter++;
        } while (file_exists($candidate));

        return $candidate;
    }
}