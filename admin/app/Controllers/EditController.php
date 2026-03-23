<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use DateTime;
use PDO;

class EditController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = trim((string) ($_POST['action'] ?? ''));

            if ($action === 'load_temp_description') {
                $this->loadTempDescription();
                return;
            }

            if ($action === 'save_description') {
                $this->saveDescription($pdo);
                return;
            }

            if ($action === 'save_file_data') {
                $this->saveFileData($pdo);
                return;
            }

            if ($action === 'add_tags') {
                $this->addTags($pdo);
                return;
            }

            if ($action === 'update_tag') {
                $this->updateTag($pdo);
                return;
            }

            if ($action === 'delete_tag') {
                $this->deleteTag($pdo);
                return;
            }

            if ($action === 'analyze_tags_with_gemini') {
                $this->analyzeTagsWithGemini($pdo);
                return;
            }
        }

        $pid = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;

        if ($pid <= 0) {
            http_response_code(404);
            exit('Keine gültige Bild-ID übergeben.');
        }

        $fileStmt = $pdo->prepare('SELECT * FROM vup_dateien WHERE id = :id LIMIT 1');
        $fileStmt->execute(array('id' => $pid));
        $file = $fileStmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            http_response_code(404);
            exit('Bild nicht gefunden.');
        }

        $descriptionStmt = $pdo->prepare('SELECT * FROM beschreibung WHERE id = :id LIMIT 1');
        $descriptionStmt->execute(array('id' => $pid));
        $description = $descriptionStmt->fetch(PDO::FETCH_ASSOC);

        if (!$description) {
            $description = array(
                'id' => $pid,
                'beschreibung' => '',
                'html' => '0',
            );
        }

        $tagsStmt = $pdo->prepare('SELECT * FROM tags WHERE bid = :bid ORDER BY id ASC');
        $tagsStmt->execute(array('bid' => $pid));
        $tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);

        $categoriesStmt = $pdo->query('SELECT id, name FROM vup_kategorien ORDER BY id ASC');
        $categories = $categoriesStmt ? $categoriesStmt->fetchAll(PDO::FETCH_ASSOC) : array();

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

        $nextStmt = $pdo->prepare('SELECT id FROM vup_dateien WHERE id > :id ORDER BY id ASC LIMIT 1');
        $nextStmt->execute(array('id' => $pid));
        $nextId = $nextStmt->fetchColumn();
        $hasNextId = $nextId !== false && $nextId !== null;

        $prevStmt = $pdo->prepare('SELECT id FROM vup_dateien WHERE id < :id ORDER BY id DESC LIMIT 1');
        $prevStmt->execute(array('id' => $pid));
        $prevId = $prevStmt->fetchColumn();
        $hasPrevId = $prevId !== false && $prevId !== null;

        $this->view('edit/index', array(
            'title' => 'Bild bearbeiten',
            'file' => $file,
            'description' => $description,
            'tags' => $tags,
            'categories' => $categories,
            'flagCategories' => $flagCategories,
            'flagFieldMap' => $flagFieldMap,
            'nextId' => $hasNextId ? (int) $nextId : null,
            'hasNextId' => $hasNextId,
            'prevId' => $hasPrevId ? (int) $prevId : null,
            'hasPrevId' => $hasPrevId,
        ));
    }

    private function normalizeTag(string $tag): string
    {
        $tag = html_entity_decode($tag, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $tag = trim($tag);

        $tag = str_replace(
            array('(Â©', 'Ã¼', 'Ã¶', 'Ã¤', 'ÃŸ', 'Ãœ', 'Ã–', 'Ã„', 'Ã¨', 'â€“', 'Ã¡', 'â€œ', 'â€', 'Ã', 'â€ž', 'í´', 'í¥', 'í³'),
            array('(©', 'ü', 'ö', 'ä', 'ß', 'Ü', 'Ö', 'Ä', 'è', '–', 'á', '"', '"', 'í', '"', 'ô', 'å', 'ó'),
            $tag
        );

        $tag = preg_replace('/\s+/u', ' ', $tag);

        if ($tag === null) {
            $tag = trim($tag);
        }

        if ($tag === '') {
            return '';
        }

        return mb_strtolower($tag, 'UTF-8');
    }

    private function fetchTagsForImage(PDO $pdo, int $pid): array
    {
        $stmt = $pdo->prepare('SELECT * FROM tags WHERE bid = :bid ORDER BY id ASC');
        $stmt->execute(array('bid' => $pid));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function loadTempDescription(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $tempFile = BASE_PATH . '/temp/temp.txt';

        if (!is_file($tempFile)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die Datei temp/temp.txt wurde nicht gefunden.',
            ));
            exit;
        }

        $content = file_get_contents($tempFile);

        if ($content === false) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die Datei temp/temp.txt konnte nicht gelesen werden.',
            ));
            exit;
        }

        if (!unlink($tempFile)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die Datei temp/temp.txt konnte nach dem Lesen nicht gelöscht werden.',
            ));
            exit;
        }

        echo json_encode(array(
            'success' => true,
            'content' => $content,
        ));
        exit;
    }

    private function saveDescription(PDO $pdo): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        $descriptionText = trim((string) ($_POST['beschreibung'] ?? ''));

        if ($pid <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Keine gültige Bild-ID übergeben.',
            ));
            exit;
        }

        $fileStmt = $pdo->prepare('SELECT id FROM vup_dateien WHERE id = :id LIMIT 1');
        $fileStmt->execute(array('id' => $pid));
        $fileExists = $fileStmt->fetchColumn();

        if (!$fileExists) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Bild nicht gefunden.',
            ));
            exit;
        }

        $descriptionText = str_replace(
            array('(Â©', 'Ã¼', 'Ã¶', 'Ã¤', 'ÃŸ', 'Ãœ', 'Ã–', 'Ã„', 'Ã¨', 'â€“', 'Ã¡', 'â€œ', 'â€', 'Ã', 'â€ž', 'í´', 'í¥', 'í³'),
            array('(©', 'ü', 'ö', 'ä', 'ß', 'Ü', 'Ö', 'Ä', 'è', '–', 'á', '"', '"', 'í', '"', 'ô', 'å', 'ó'),
            $descriptionText
        );

        $descriptionText = preg_replace('/[ \t]*\R?[ \t]*\((?:Â©|©)/u', PHP_EOL . '(©', $descriptionText);

        if ($descriptionText === null) {
            $descriptionText = trim((string) ($_POST['beschreibung'] ?? ''));
            $descriptionText = str_replace(
                array('(Â©', 'Ã¼', 'Ã¶', 'Ã¤', 'ÃŸ', 'Ãœ', 'Ã–', 'Ã„', 'Ã¨', 'â€“', 'Ã¡', 'â€œ', 'â€', 'Ã', 'â€ž', 'í´', 'í¥', 'í³'),
                array('(©', 'ü', 'ö', 'ä', 'ß', 'Ü', 'Ö', 'Ä', 'è', '–', 'á', '"', '"', 'í', '"', 'ô', 'å', 'ó'),
                $descriptionText
            );
        }

        $stmt = $pdo->prepare("
            INSERT INTO beschreibung (id, beschreibung, html)
            VALUES (:id, :beschreibung, '0')
            ON DUPLICATE KEY UPDATE
                beschreibung = VALUES(beschreibung),
                html = '0'
        ");

        $ok = $stmt->execute(array(
            'id' => $pid,
            'beschreibung' => $descriptionText,
        ));

        if (!$ok) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Beschreibung konnte nicht gespeichert werden.',
            ));
            exit;
        }

        $resetTagsStmt = $pdo->prepare("UPDATE tags SET aktiv = '0' WHERE bid = :bid");
        $resetTagsStmt->execute(array(
            'bid' => $pid,
        ));

        echo json_encode(array(
            'success' => true,
            'message' => 'Beschreibung wurde gespeichert.',
            'content' => $descriptionText,
        ));
        exit;
    }

    private function saveFileData(PDO $pdo): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        $date = trim((string) ($_POST['date'] ?? ''));
        $categoryId = isset($_POST['to_kat']) ? (int) $_POST['to_kat'] : 0;

        if ($pid <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Keine gültige Bild-ID übergeben.',
            ));
            exit;
        }

        if ($date === '' || $categoryId <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Bitte Datum und Kategorie angeben.',
            ));
            exit;
        }

        $fileStmt = $pdo->prepare('SELECT * FROM vup_dateien WHERE id = :id LIMIT 1');
        $fileStmt->execute(array('id' => $pid));
        $file = $fileStmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Bild nicht gefunden.',
            ));
            exit;
        }

        $categoryStmt = $pdo->prepare('SELECT id FROM vup_kategorien WHERE id = :id LIMIT 1');
        $categoryStmt->execute(array('id' => $categoryId));
        $categoryExists = $categoryStmt->fetchColumn();

        if (!$categoryExists) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die gewählte Kategorie existiert nicht.',
            ));
            exit;
        }

        $dateObject = DateTime::createFromFormat('d.m.Y', $date);
        if (!$dateObject || $dateObject->format('d.m.Y') !== $date) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Das Datum ist ungültig.',
            ));
            exit;
        }

        $newTimestamp = $dateObject->setTime(0, 0, 0)->getTimestamp();

        $oldName = (string) ($file['name'] ?? '');
        $extension = strtolower((string) pathinfo($oldName, PATHINFO_EXTENSION));
        $newName = $extension !== '' ? $newTimestamp . '.' . $extension : (string) $newTimestamp;

        $oldFilePath = BASE_PATH . '/uploads/' . $oldName;
        $newFilePath = BASE_PATH . '/uploads/' . $newName;

        $oldThumbPath = BASE_PATH . '/thumbs/' . $oldName;
        $newThumbPath = BASE_PATH . '/thumbs/' . $newName;

        if ($newName !== $oldName) {
            $duplicateStmt = $pdo->prepare('SELECT id FROM vup_dateien WHERE name = :name AND id <> :id LIMIT 1');
            $duplicateStmt->execute(array(
                'name' => $newName,
                'id' => $pid,
            ));
            $duplicate = $duplicateStmt->fetchColumn();

            if ($duplicate) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Es existiert bereits ein Bild mit diesem Datum.',
                ));
                exit;
            }

            if (is_file($newFilePath)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Im Upload-Ordner existiert bereits eine Datei mit diesem Namen.',
                ));
                exit;
            }

            if (!is_file($oldFilePath)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Die Bilddatei im Upload-Ordner wurde nicht gefunden.',
                ));
                exit;
            }
        }

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

        if ($newName !== $oldName) {
            if (!rename($oldFilePath, $newFilePath)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Die Bilddatei konnte nicht umbenannt werden.',
                ));
                exit;
            }

            if (is_file($oldThumbPath)) {
                @rename($oldThumbPath, $newThumbPath);
            }
        }

        $updateStmt = $pdo->prepare("
            UPDATE vup_dateien
            SET
                entrytime = :entrytime,
                name = :name,
                to_kat = :to_kat,
                k1 = :k1,
                k3 = :k3,
                k4 = :k4,
                k7 = :k7,
                k9 = :k9,
                k10 = :k10,
                k13 = :k13,
                k15 = :k15
            WHERE id = :id
            LIMIT 1
        ");

        $ok = $updateStmt->execute(array(
            'entrytime' => (string) $newTimestamp,
            'name' => $newName,
            'to_kat' => $categoryId,
            'k1' => $flagValues['k1'],
            'k3' => $flagValues['k3'],
            'k4' => $flagValues['k4'],
            'k7' => $flagValues['k7'],
            'k9' => $flagValues['k9'],
            'k10' => $flagValues['k10'],
            'k13' => $flagValues['k13'],
            'k15' => $flagValues['k15'],
            'id' => $pid,
        ));

        if (!$ok) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die allgemeinen Bilddaten konnten nicht gespeichert werden.',
            ));
            exit;
        }

        echo json_encode(array(
            'success' => true,
            'message' => 'Die allgemeinen Bilddaten wurden gespeichert.',
            'date' => $date,
            'name' => $newName,
            'entrytime' => (string) $newTimestamp,
        ));
        exit;
    }

    private function addTags(PDO $pdo): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        $rawTags = trim((string) ($_POST['tags'] ?? ''));

        if ($pid <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Keine gültige Bild-ID übergeben.',
            ));
            exit;
        }

        $fileStmt = $pdo->prepare('SELECT id FROM vup_dateien WHERE id = :id LIMIT 1');
        $fileStmt->execute(array('id' => $pid));
        $fileExists = $fileStmt->fetchColumn();

        if (!$fileExists) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Bild nicht gefunden.',
            ));
            exit;
        }

        if ($rawTags === '') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Bitte mindestens ein Tag eingeben.',
            ));
            exit;
        }

        $parts = explode(',', $rawTags);
        $cleanTags = array();
        $seen = array();

        foreach ($parts as $part) {
            $tag = $this->normalizeTag((string) $part);

            if ($tag === '') {
                continue;
            }

            if (isset($seen[$tag])) {
                continue;
            }

            $seen[$tag] = true;
            $cleanTags[] = $tag;
        }

        if (empty($cleanTags)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Es konnten keine gültigen Tags erkannt werden.',
            ));
            exit;
        }

        $existingStmt = $pdo->prepare('SELECT tag FROM tags WHERE bid = :bid');
        $existingStmt->execute(array('bid' => $pid));
        $existingRows = $existingStmt->fetchAll(PDO::FETCH_ASSOC);

        $existingMap = array();
        foreach ($existingRows as $row) {
            $existingTag = mb_strtolower(trim((string) ($row['tag'] ?? '')), 'UTF-8');
            if ($existingTag !== '') {
                $existingMap[$existingTag] = true;
            }
        }

        $insertStmt = $pdo->prepare("INSERT INTO tags (bid, tag, aktiv) VALUES (:bid, :tag, '1')");
        $addedCount = 0;

        foreach ($cleanTags as $tag) {
            if (isset($existingMap[$tag])) {
                continue;
            }

            $ok = $insertStmt->execute(array(
                'bid' => $pid,
                'tag' => $tag,
            ));

            if ($ok) {
                $existingMap[$tag] = true;
                $addedCount++;
            }
        }

        $tags = $this->fetchTagsForImage($pdo, $pid);

        echo json_encode(array(
            'success' => true,
            'message' => $addedCount > 0 ? 'Tags wurden hinzugefügt.' : 'Keine neuen Tags hinzugefügt.',
            'tags' => $tags,
        ));
        exit;
    }

    private function updateTag(PDO $pdo): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        $tagId = isset($_POST['tag_id']) ? (int) $_POST['tag_id'] : 0;
        $tag = $this->normalizeTag((string) ($_POST['tag'] ?? ''));

        if ($pid <= 0 || $tagId <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Ungültige Anfrage.',
            ));
            exit;
        }

        if ($tag === '') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Das Tag darf nicht leer sein.',
            ));
            exit;
        }

        $tagStmt = $pdo->prepare('SELECT id, bid FROM tags WHERE id = :id LIMIT 1');
        $tagStmt->execute(array('id' => $tagId));
        $tagRow = $tagStmt->fetch(PDO::FETCH_ASSOC);

        if (!$tagRow || (int) $tagRow['bid'] !== $pid) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Tag nicht gefunden.',
            ));
            exit;
        }

        $duplicateStmt = $pdo->prepare('SELECT id FROM tags WHERE bid = :bid AND LOWER(tag) = LOWER(:tag) AND id <> :id LIMIT 1');
        $duplicateStmt->execute(array(
            'bid' => $pid,
            'tag' => $tag,
            'id' => $tagId,
        ));
        $duplicateExists = $duplicateStmt->fetchColumn();

        if ($duplicateExists) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Dieses Tag ist bereits vorhanden.',
            ));
            exit;
        }

        $updateStmt = $pdo->prepare("UPDATE tags SET tag = :tag, aktiv = '0' WHERE id = :id AND bid = :bid LIMIT 1");
        $ok = $updateStmt->execute(array(
            'tag' => $tag,
            'id' => $tagId,
            'bid' => $pid,
        ));

        if (!$ok) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Tag konnte nicht gespeichert werden.',
            ));
            exit;
        }

        $tags = $this->fetchTagsForImage($pdo, $pid);

        echo json_encode(array(
            'success' => true,
            'message' => 'Tag wurde gespeichert.',
            'tags' => $tags,
        ));
        exit;
    }

    private function analyzeTagsWithGemini(PDO $pdo): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        global $appConfig;

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;

        if ($pid <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Keine gültige Bild-ID übergeben.',
            ));
            exit;
        }

        $apiKey = trim((string) ($appConfig['gemini_api_key'] ?? ''));

        if ($apiKey === '' || $apiKey === 'DEIN_API_SCHLUESSEL_HIER') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Der Gemini API-Key ist nicht konfiguriert.',
            ));
            exit;
        }

        $fileStmt = $pdo->prepare('SELECT id, name FROM vup_dateien WHERE id = :id LIMIT 1');
        $fileStmt->execute(array('id' => $pid));
        $file = $fileStmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Bild nicht gefunden.',
            ));
            exit;
        }

        $filename = trim((string) ($file['name'] ?? ''));

        if ($filename === '') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Der Dateiname des Bildes ist leer.',
            ));
            exit;
        }

        $imagePath = BASE_PATH . '/uploads/' . $filename;

        if (!is_file($imagePath)) {
            $thumbPath = BASE_PATH . '/thumbs/' . $filename;
            if (is_file($thumbPath)) {
                $imagePath = $thumbPath;
            }
        }

        if (!is_file($imagePath) || !is_readable($imagePath)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die Bilddatei konnte nicht gelesen werden.',
            ));
            exit;
        }

        $imageContent = file_get_contents($imagePath);

        if ($imageContent === false) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die Bilddatei konnte nicht geladen werden.',
            ));
            exit;
        }

        $mimeType = mime_content_type($imagePath);
        if (!is_string($mimeType) || $mimeType === '') {
            $mimeType = 'image/jpeg';
        }

        $payload = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => 'Analysiere dieses Bild und generiere relevante, beschreibende Tags. Antworte AUSSCHLIESSLICH mit einer kommagetrennten Liste dieser Tags in deutscher Sprache (z.B.: Baum, Natur, Himmel). Schreibe keinen weiteren Text.'
                        ),
                        array(
                            'inlineData' => array(
                                'mimeType' => $mimeType,
                                'data' => base64_encode($imageContent),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . rawurlencode($apiKey);

        $ch = curl_init($url);

        if ($ch === false) {
            echo json_encode(array(
                'success' => false,
                'message' => 'cURL konnte nicht initialisiert werden.',
            ));
            exit;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(array(
                'success' => false,
                'message' => 'cURL-Fehler: ' . $curlError,
            ));
            exit;
        }

        $result = json_decode((string) $response, true);

        if (!is_array($result)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Die Antwort von Gemini war ungültig.',
            ));
            exit;
        }

        if ($httpCode >= 400) {
            $apiMessage = '';
            if (isset($result['error']['message']) && is_string($result['error']['message'])) {
                $apiMessage = trim($result['error']['message']);
            }

            echo json_encode(array(
                'success' => false,
                'message' => $apiMessage !== '' ? $apiMessage : 'Gemini hat einen Fehler zurückgegeben.',
            ));
            exit;
        }

        $tags = '';

        if (isset($result['candidates'][0]['content']['parts'][0]['text']) && is_string($result['candidates'][0]['content']['parts'][0]['text'])) {
            $tags = trim($result['candidates'][0]['content']['parts'][0]['text']);
        }

        $tags = str_replace(array("\r", "\n"), '', $tags);

        if ($tags === '') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Es konnten keine Tags generiert werden.',
            ));
            exit;
        }

        echo json_encode(array(
            'success' => true,
            'message' => 'Tags wurden mit Gemini generiert.',
            'tags' => $tags,
        ));
        exit;
    }

    private function deleteTag(PDO $pdo): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        $tagId = isset($_POST['tag_id']) ? (int) $_POST['tag_id'] : 0;

        if ($pid <= 0 || $tagId <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Ungültige Anfrage.',
            ));
            exit;
        }

        $deleteStmt = $pdo->prepare('DELETE FROM tags WHERE id = :id AND bid = :bid LIMIT 1');
        $ok = $deleteStmt->execute(array(
            'id' => $tagId,
            'bid' => $pid,
        ));

        if (!$ok) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Tag konnte nicht gelöscht werden.',
            ));
            exit;
        }

        $tags = $this->fetchTagsForImage($pdo, $pid);

        echo json_encode(array(
            'success' => true,
            'message' => 'Tag wurde gelöscht.',
            'tags' => $tags,
        ));
        exit;
    }
}