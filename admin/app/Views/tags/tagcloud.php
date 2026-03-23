<?php

declare(strict_types=1);

$minCount = null;
$maxCount = null;

foreach ($tags as $tagItem) {
    $count = (int)($tagItem['anzahl'] ?? 0);

    if ($minCount === null || $count < $minCount) {
        $minCount = $count;
    }

    if ($maxCount === null || $count > $maxCount) {
        $maxCount = $count;
    }
}

if ($minCount === null) {
    $minCount = 1;
}

if ($maxCount === null) {
    $maxCount = 1;
}

$minFont = 0.95;
$maxFont = 3.2;
?>
<main class="p-3 p-lg-4">
    <style>
        .tag-cloud-panel {
            background: rgba(24, 41, 58, 0.72);
            border-radius: 18px;
            padding: 24px;
            backdrop-filter: blur(2px);
        }

        .tag-cloud-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 14px 18px;
            align-items: center;
            justify-content: center;
        }

        .tag-cloud-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            text-decoration: none;
            color: #ffffff;
            background: rgba(70, 125, 185, 0.24);
            border: 1px solid rgba(255, 255, 255, 0.14);
            transition: 0.15s ease-in-out;
            line-height: 1.1;
        }

        .tag-cloud-item:hover {
            background: rgba(70, 125, 185, 0.38);
            color: #ffffff;
            transform: translateY(-1px) scale(1.03);
        }

        .tag-cloud-name {
            font-weight: 700;
            word-break: break-word;
        }

        .tag-cloud-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 0.78rem;
            font-weight: 700;
            color: #ffffff;
        }
    </style>

    <div class="glass-panel p-3 p-lg-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-start justify-content-between gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Tag-Cloud</h1>
                <div style="font-size: 1.05rem; color: #ffffff;">
                    Eindeutige Tags: <strong><?= count($tags) ?></strong>
                </div>
            </div>
            <div class="small text-white-50 text-sm-end">Klick auf ein Tag zum Suchen</div>
        </div>

        <?php if (empty($tags)): ?>
            <div class="alert alert-light mb-0">Keine Tags vorhanden.</div>
        <?php else: ?>
            <div class="tag-cloud-panel">
                <div class="tag-cloud-wrap">
                    <?php foreach ($tags as $tag): ?>
                        <?php
                        $rawTagName = (string)($tag['tag'] ?? '');
                        $cleanTagName = trim(html_entity_decode(strip_tags($rawTagName), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        $count = (int)($tag['anzahl'] ?? 0);

                        if ($cleanTagName === '') {
                            continue;
                        }

                        if ($maxCount === $minCount) {
                            $fontSize = ($minFont + $maxFont) / 2;
                        } else {
                            $ratio = ($count - $minCount) / ($maxCount - $minCount);
                            $fontSize = $minFont + (($maxFont - $minFont) * $ratio);
                        }
                        ?>
                        <a
                            href="index.php?page=search&tag=<?= urlencode($cleanTagName) ?>"
                            class="tag-cloud-item"
                            title="<?= e($cleanTagName) ?> (<?= $count ?>)"
                            style="font-size: <?= number_format($fontSize, 2, '.', '') ?>rem;"
                        >
                            <span class="tag-cloud-name"><?= e($cleanTagName) ?></span>
                            <span class="tag-cloud-count"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>