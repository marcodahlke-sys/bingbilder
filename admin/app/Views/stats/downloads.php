<?php

declare(strict_types=1);
?>
<main class="p-3 p-lg-4">
    <style>
        .downloads-grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            color: #fff;
        }

        .downloads-grid-table thead th {
            background: rgba(70, 125, 185, 0.58);
            border: 0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }

        .downloads-grid-table tbody td {
            background: rgba(24, 41, 58, 0.72);
            border: 0;
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            vertical-align: middle;
            backdrop-filter: blur(2px);
        }

        .downloads-grid-table .rank-cell,
        .downloads-grid-table .id-cell,
        .downloads-grid-table .size-cell,
        .downloads-grid-table .count-cell {
            white-space: nowrap;
        }

        .downloads-grid-table .thumb-cell {
            min-width: 320px;
        }

        .downloads-grid-table .thumb-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .downloads-grid-table .thumb-image {
            display: block;
            max-width: 260px;
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 14px;
            border: 4px solid rgba(255, 255, 255, 0.92);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
        }

        .downloads-grid-table .rank-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-weight: 700;
        }

        .downloads-grid-table .rank-trophy {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 102px;
            height: 102px;
            border-radius: 999px;
            font-size: 3rem;
            line-height: 1;
        }

        .downloads-grid-table .rank-trophy-gold {
            background: rgba(255, 215, 0, 0.22);
            border: 1px solid rgba(255, 215, 0, 0.75);
            color: #ffe082;
        }

        .downloads-grid-table .rank-trophy-silver {
            background: rgba(192, 192, 192, 0.20);
            border: 1px solid rgba(220, 220, 220, 0.75);
            color: #f1f3f5;
        }

        .downloads-grid-table .rank-trophy-bronze {
            background: rgba(205, 127, 50, 0.22);
            border: 1px solid rgba(205, 127, 50, 0.78);
            color: #f6c28b;
        }

        .downloads-grid-table .rank-number {
            font-weight: 700;
            font-size: 1.1rem;
        }
    </style>

    <div class="glass-panel p-3 p-lg-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
            <h1 class="h4 mb-0">🔥 Top 100 Downloads</h1>
            <div class="small text-white-50">Sortiert nach Downloads (absteigend)</div>
        </div>

        <?php if (empty($downloads)): ?>
            <div class="alert alert-light mb-0">Keine Download-Daten vorhanden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="downloads-grid-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Bild</th>
                            <th>Größe</th>
                            <th>Downloads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($downloads as $index => $item): ?>
                            <?php
                            $fileName = (string)($item['name'] ?? '');
                            $sizeValue = (string)($item['size'] ?? '');
                            $displaySize = $sizeValue !== '' && is_numeric($sizeValue)
                                ? number_format(((float)$sizeValue / 1024 / 1024), 2, '.', '') . ' MB'
                                : ($sizeValue !== '' ? $sizeValue : '—');

                            $rank = $index + 1;
                            ?>
                            <tr>
                                <td class="rank-cell">
                                    <span class="rank-wrap">
                                        <?php if ($rank === 1): ?>
                                            <span class="rank-trophy rank-trophy-gold" title="Platz 1">🏆</span>
                                        <?php elseif ($rank === 2): ?>
                                            <span class="rank-trophy rank-trophy-silver" title="Platz 2">🏆</span>
                                        <?php elseif ($rank === 3): ?>
                                            <span class="rank-trophy rank-trophy-bronze" title="Platz 3">🏆</span>
                                        <?php endif; ?>

                                        <span class="rank-number"><?= $rank ?></span>
                                    </span>
                                </td>

                                <td class="id-cell"><?= (int)$item['id'] ?></td>

                                <td class="thumb-cell">
                                    <div class="thumb-wrap">
                                        <img
                                            src="thumbs/<?= e($fileName) ?>"
                                            alt="<?= e($fileName) ?>"
                                            class="thumb-image"
                                        >
                                    </div>
                                </td>

                                <td class="size-cell"><?= e($displaySize) ?></td>
                                <td class="count-cell"><?= (int)($item['downloads'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>