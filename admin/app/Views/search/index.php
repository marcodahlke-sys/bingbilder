<?php

declare(strict_types=1);
?>
<main class="p-3 p-lg-4">
    <style>
        .search-grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            color: #fff;
        }

        .search-grid-table thead th {
            background: rgba(70, 125, 185, 0.58);
            border: 0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }

        .search-grid-table tbody td {
            background: rgba(24, 41, 58, 0.72);
            border: 0;
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            vertical-align: middle;
            backdrop-filter: blur(2px);
        }

        .search-grid-table .thumb-cell {
            min-width: 320px;
        }

        .search-grid-table .thumb-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .search-grid-table .thumb-image {
            display: block;
            max-width: 260px;
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 14px;
            border: 4px solid rgba(255, 255, 255, 0.92);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
        }

        .search-grid-table .category-cell {
            min-width: 240px;
            word-break: break-word;
        }

        .search-grid-table .size-cell,
        .search-grid-table .date-cell,
        .search-grid-table .tags-cell,
        .search-grid-table .description-cell,
        .search-grid-table .edit-cell {
            white-space: nowrap;
        }

        .status-badge-yes {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 7px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            background: #1f9d63;
            color: #fff;
        }

        .status-badge-no {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 7px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            background: rgba(255, 255, 255, 0.20);
            color: #fff;
        }

        .icon-btn {
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

        .icon-btn i {
            font-size: 0.9rem;
            line-height: 1;
        }

        .icon-btn-edit {
            border: 1px solid #0d6efd;
            color: #0d6efd;
        }

        .icon-btn-edit:hover {
            background: rgba(13, 110, 253, 0.16);
            color: #fff;
        }

        .search-info-box {
            background: rgba(24, 41, 58, 0.72);
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
        }
    </style>

    <div class="glass-panel p-3 p-lg-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Suchergebnisse</h1>
                <div class="small text-white-50">Tag-Suche</div>
            </div>
            <a href="index.php?page=tagcloud" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Zurück zur Tag-Cloud
            </a>
        </div>

        <div class="search-info-box mb-3">
            <?php if ($searchTag !== ''): ?>
                Suche nach Tag: <strong><?= e($searchTag) ?></strong><br>
                Treffer: <strong><?= count($results) ?></strong>
            <?php else: ?>
                Es wurde kein Suchbegriff übergeben.
            <?php endif; ?>
        </div>

        <?php if ($searchTag === ''): ?>
            <div class="alert alert-light mb-0">Bitte ein Tag aus der Tag-Cloud auswählen.</div>
        <?php elseif (empty($results)): ?>
            <div class="alert alert-light mb-0">Zu diesem Tag wurden keine Bilder gefunden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="search-grid-table">
                    <thead>
                        <tr>
                            <th>Bild</th>
                            <th>Kategorie</th>
                            <th>Dateigröße</th>
                            <th>Hochgeladen am</th>
                            <th>Tags vorhanden</th>
                            <th>Beschreibung vorhanden</th>
                            <th>Bearbeiten</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $file): ?>
                            <?php
                            $rawCategoryName = (string)($file['kategorie_name'] ?? '');
                            $categoryName = trim(html_entity_decode(strip_tags($rawCategoryName), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                            $sizeValue = (string)($file['size'] ?? '');
                            $displaySize = $sizeValue !== '' && is_numeric($sizeValue)
                                ? number_format(((float)$sizeValue / 1024 / 1024), 2, '.', '') . ' MB'
                                : ($sizeValue !== '' ? $sizeValue : '—');

                            $fileName = (string)($file['name'] ?? '');
                            ?>
                            <tr>
                                <td class="thumb-cell">
                                    <div class="thumb-wrap">
                                        <img
                                            src="thumbs/<?= e($fileName) ?>"
                                            alt="<?= e($fileName) ?>"
                                            class="thumb-image"
                                        >
                                    </div>
                                </td>

                                <td class="category-cell">
                                    <?= e($categoryName !== '' ? $categoryName : '—') ?>
                                </td>

                                <td class="size-cell">
                                    <?= e($displaySize) ?>
                                </td>

                                <td class="date-cell">
                                    <?= !empty($file['entrytime']) ? e(date('d.m.Y', (int)$file['entrytime'])) : '—' ?>
                                </td>

                                <td class="tags-cell">
                                    <?php if (!empty($file['has_tags'])): ?>
                                        <span class="status-badge-yes">Ja</span>
                                    <?php else: ?>
                                        <span class="status-badge-no">Nein</span>
                                    <?php endif; ?>
                                </td>

                                <td class="description-cell">
                                    <?php if (!empty($file['has_description'])): ?>
                                        <span class="status-badge-yes">Ja</span>
                                    <?php else: ?>
                                        <span class="status-badge-no">Nein</span>
                                    <?php endif; ?>
                                </td>

                                <td class="edit-cell">
                                    <a class="icon-btn icon-btn-edit" href="index.php?page=edit&pid=<?= (int)$file['id'] ?>" title="Bearbeiten">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>