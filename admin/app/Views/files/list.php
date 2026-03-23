<?php

declare(strict_types=1);
?>
<main class="p-3 p-lg-4">
    <style>
        .files-grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            color: #fff;
        }

        .files-grid-table thead th {
            background: rgba(70, 125, 185, 0.58);
            border: 0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }

        .files-grid-table tbody td {
            background: rgba(24, 41, 58, 0.72);
            border: 0;
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            vertical-align: middle;
            backdrop-filter: blur(2px);
        }

        .files-grid-table .file-thumb-cell {
            min-width: 320px;
        }

        .files-grid-table .file-thumb-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .files-grid-table .file-thumb {
            display: block;
            max-width: 260px;
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 14px;
            border: 4px solid rgba(255, 255, 255, 0.92);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
        }

        .files-grid-table .file-category {
            min-width: 260px;
            word-break: break-word;
        }

        .files-grid-table .file-size,
        .files-grid-table .file-date,
        .files-grid-table .file-tags,
        .files-grid-table .file-description,
        .files-grid-table .file-edit,
        .files-grid-table .file-delete {
            white-space: nowrap;
        }

        .files-grid-table .status-badge-yes {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 7px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            background: #1f9d63;
            color: #fff;
        }

        .files-grid-table .status-badge-no {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 7px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            background: rgba(255, 255, 255, 0.20);
            color: #fff;
        }

        .files-grid-table .icon-btn {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            text-decoration: none;
            background: transparent;
            transition: 0.15s ease-in-out;
        }

        .files-grid-table .icon-btn i {
            font-size: 0.9rem;
            line-height: 1;
        }

        .files-grid-table .icon-btn-edit {
            border: 1px solid #0d6efd;
            color: #0d6efd;
        }

        .files-grid-table .icon-btn-edit:hover {
            background: rgba(13, 110, 253, 0.16);
            color: #fff;
        }

        .files-grid-table .icon-btn-delete {
            border: 1px solid #ff4d6d;
            color: #ff4d6d;
        }

        .files-grid-table .icon-btn-delete:hover {
            background: rgba(255, 77, 109, 0.16);
            color: #fff;
        }

        .files-grid-table .delete-form {
            margin: 0;
            display: inline-flex;
        }

        .files-grid-table .delete-form button {
            cursor: pointer;
        }
    </style>

    <div class="glass-panel p-3 p-lg-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
            <h1 class="h4 mb-0">Bilderübersicht</h1>
            <div class="small text-white-50">Zeige bis zu 100 Einträge</div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= e((string) $message) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e((string) $error) ?></div>
        <?php endif; ?>

        <?php if (empty($files)): ?>
            <div class="alert alert-light mb-0">Keine Dateien vorhanden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="files-grid-table">
                    <thead>
                        <tr>
                            <th>Bild</th>
                            <th>Kategorie</th>
                            <th>Dateigröße</th>
                            <th>Hochgeladen am</th>
                            <th>Tags vorhanden</th>
                            <th>Beschreibung vorhanden</th>
                            <th>Bearbeiten</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <?php
                            $rawCategoryName = (string) ($file['kategorie_name'] ?? '');
                            $categoryName = trim(html_entity_decode(strip_tags($rawCategoryName), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                            $sizeValue = (string) ($file['size'] ?? '');
                            $displaySize = $sizeValue !== '' && is_numeric($sizeValue)
                                ? number_format(((float) $sizeValue / 1024 / 1024), 2, '.', '') . ' MB'
                                : ($sizeValue !== '' ? $sizeValue : '—');

                            $fileName = (string) ($file['name'] ?? '');
                            ?>
                            <tr>
                                <td class="file-thumb-cell">
                                    <div class="file-thumb-wrap">
                                        <img
                                            src="thumbs/<?= e($fileName) ?>"
                                            alt="<?= e($fileName) ?>"
                                            class="file-thumb"
                                        >
                                    </div>
                                </td>

                                <td class="file-category">
                                    <?= e($categoryName !== '' ? $categoryName : '—') ?>
                                </td>

                                <td class="file-size">
                                    <?= e($displaySize) ?>
                                </td>

                                <td class="file-date">
                                    <?= !empty($file['entrytime']) ? e(date('d.m.Y', (int) $file['entrytime'])) : '—' ?>
                                </td>

                                <td class="file-tags">
                                    <?php if (!empty($file['has_tags'])): ?>
                                        <span class="status-badge-yes">Ja</span>
                                    <?php else: ?>
                                        <span class="status-badge-no">Nein</span>
                                    <?php endif; ?>
                                </td>

                                <td class="file-description">
                                    <?php if (!empty($file['has_description'])): ?>
                                        <span class="status-badge-yes">Ja</span>
                                    <?php else: ?>
                                        <span class="status-badge-no">Nein</span>
                                    <?php endif; ?>
                                </td>

                                <td class="file-edit">
                                    <a class="icon-btn icon-btn-edit" href="index.php?page=edit&pid=<?= (int) $file['id'] ?>" title="Bearbeiten">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>

                                <td class="file-delete">
                                    <form
                                        method="post"
                                        action="index.php?page=files"
                                        class="delete-form"
                                        onsubmit="return confirm('Soll diese Datei wirklich gelöscht werden? Dabei werden auch Tags, Beschreibung, Upload und Thumbnail entfernt.');"
                                    >
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $file['id'] ?>">
                                        <button type="submit" class="icon-btn icon-btn-delete" title="Löschen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>