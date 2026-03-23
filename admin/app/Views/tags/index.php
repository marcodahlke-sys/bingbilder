<?php

declare(strict_types=1);
?>
<main class="p-3 p-lg-4">
    <style>
        .tags-grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            color: #fff;
        }

        .tags-grid-table thead th {
            background: rgba(70, 125, 185, 0.58);
            border: 0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }

        .tags-grid-table tbody td {
            background: rgba(24, 41, 58, 0.72);
            border: 0;
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            vertical-align: middle;
            backdrop-filter: blur(2px);
        }

        .tags-grid-table .tag-name-cell {
            min-width: 320px;
            word-break: break-word;
        }

        .tags-grid-table .tag-count-cell {
            min-width: 180px;
            white-space: nowrap;
        }
    </style>

    <div class="glass-panel p-3 p-lg-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
            <h1 class="h4 mb-0">Tags verwalten</h1>
            <div class="small text-white-50">Neueste Tags zuerst</div>
        </div>

        <?php if (empty($tags)): ?>
            <div class="alert alert-light mb-0">Keine Tags vorhanden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="tags-grid-table">
                    <thead>
                        <tr>
                            <th>Tagname</th>
                            <th>Verwendungen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tags as $tag): ?>
                            <?php
                            $rawTagName = (string)($tag['tag'] ?? '');
                            $cleanTagName = trim(html_entity_decode(strip_tags($rawTagName), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                            ?>
                            <tr>
                                <td class="tag-name-cell">
                                    <?= e($cleanTagName !== '' ? $cleanTagName : '—') ?>
                                </td>
                                <td class="tag-count-cell">
                                    <?= (int)$tag['anzahl'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>