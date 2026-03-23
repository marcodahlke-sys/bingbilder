<?php

declare(strict_types=1);

$monthNames = [
    1 => 'Januar',
    2 => 'Februar',
    3 => 'März',
    4 => 'April',
    5 => 'Mai',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'August',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Dezember',
];

$firstWeekday = (int)date('N', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
$dayNames = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
$currentYear = (int)date('Y');

$getThumbPath = static function (string $fileName): string {
    $fileName = ltrim($fileName, '/');
    return thumbUrl($fileName);
};
?>
<?php if (empty($images)): ?>
    <main class="p-3 p-lg-4">
        <div class="glass-panel p-4">
            <div class="alert alert-light mb-0">Es wurden keine Bilder gefunden.</div>
        </div>
    </main>
<?php else: ?>
    <main class="p-3 p-lg-4">
        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-5">
                <section class="glass-panel p-3 p-lg-4">
                    <form method="get" action="index.php" class="mb-3">
                        <input type="hidden" name="page" value="home">

                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-sm-6">
                                <label for="amonat" class="form-label mb-1">Monat</label>
                                <select name="amonat" id="amonat" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($monthNames as $monthNumber => $monthLabel): ?>
                                        <option value="<?= $monthNumber ?>" <?= $monthNumber === (int)$month ? 'selected' : '' ?>>
                                            <?= e($monthLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-sm-4">
                                <label for="ajahr" class="form-label mb-1">Jahr</label>
                                <select name="ajahr" id="ajahr" class="form-select" onchange="this.form.submit()">
                                    <?php for ($yearOption = $currentYear; $yearOption >= 2009; $yearOption--): ?>
                                        <option value="<?= $yearOption ?>" <?= $yearOption === (int)$year ? 'selected' : '' ?>>
                                            <?= $yearOption ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-12 col-sm-2">
                                <button type="submit" class="btn btn-primary w-100">OK</button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center px-3 py-2 rounded-3 bg-white bg-opacity-25 fw-bold fs-4 mb-3">
                        <?= e($monthNames[$month] . ' ' . $year) ?>
                    </div>

                    <div class="calendar-grid">
                        <?php foreach ($dayNames as $dayName): ?>
                            <div class="calendar-head"><?= e($dayName) ?></div>
                        <?php endforeach; ?>

                        <?php for ($i = 1; $i < $firstWeekday; $i++): ?>
                            <div class="calendar-cell calendar-empty"></div>
                        <?php endfor; ?>

                        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                            <?php
                            $hasImages = !empty($imagesByDay[$day]);
                            $timestamp = mktime(0, 0, 0, $month, $day, $year);
                            $isSelected = $selectedTimestamp && ((int)date('j', $selectedTimestamp) === $day);

                            $dayHasDescription = false;
                            $dayHasTags = false;
                            $dayPreviewPath = null;
                            $dayPreviewAlt = '';

                            if ($hasImages) {
                                $firstDayImage = $imagesByDay[$day][0] ?? null;

                                if (is_array($firstDayImage) && !empty($firstDayImage['name'])) {
                                    $dayPreviewPath = $getThumbPath((string)$firstDayImage['name']);
                                    $dayPreviewAlt = (string)$firstDayImage['name'];
                                }

                                foreach ($imagesByDay[$day] as $dayImage) {
                                    if (!empty($dayImage['has_description'])) {
                                        $dayHasDescription = true;
                                    }
                                    if (!empty($dayImage['has_tags'])) {
                                        $dayHasTags = true;
                                    }
                                }
                            }
                            ?>
                            <div class="calendar-cell<?= $isSelected ? ' is-selected' : '' ?><?= $hasImages ? ' has-images' : '' ?>">
                                <?php if ($hasImages): ?>
                                    <a class="calendar-link" href="index.php?page=home&amonat=<?= $month ?>&ajahr=<?= $year ?>&dat=<?= $timestamp ?>">
                                        <span class="calendar-day-number"><?= sprintf('%02d', $day) ?></span>

                                        <?php if ($dayPreviewPath !== null): ?>
                                            <span
                                                style="
                                                    display:block;
                                                    width:100%;
                                                    height:42px;
                                                    border-radius:8px;
                                                    overflow:hidden;
                                                    border:1px solid rgba(255,255,255,0.18);
                                                    background:rgba(255,255,255,0.08);
                                                    margin-top:8px;
                                                "
                                            >
                                                <img
                                                    src="<?= e($dayPreviewPath) ?>"
                                                    alt="<?= e($dayPreviewAlt) ?>"
                                                    style="
                                                        display:block;
                                                        width:100%;
                                                        height:100%;
                                                        object-fit:cover;
                                                    "
                                                >
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($dayHasDescription || $dayHasTags): ?>
                                            <span class="d-flex justify-content-start align-items-center mt-1 small" style="gap: 8px;">
                                                <?php if ($dayHasDescription): ?>
                                                    <span title="Beschreibung vorhanden">
                                                        <i class="bi bi-card-text"></i>
                                                    </span>
                                                <?php endif; ?>

                                                <?php if ($dayHasTags): ?>
                                                    <span title="Tags vorhanden">
                                                        <i class="bi bi-tags-fill"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <div class="calendar-link is-disabled">
                                        <span class="calendar-day-number"><?= sprintf('%02d', $day) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-7">
                <section class="glass-panel p-3 p-lg-4">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                        <h1 class="h4 mb-0">
                            <?= $selectedTimestamp ? 'Bilder vom ' . e(date('d.m.Y', $selectedTimestamp)) : 'Neueste Bilder' ?>
                        </h1>
                        <div class="small text-white-50"><?= count($images) ?> Eintrag<?= count($images) === 1 ? '' : 'e' ?></div>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($images as $image): ?>
                            <?php
                            $imageTs = (int)($image['entrytime'] ?? 0);
                            $rawCategoryName = (string)($image['kategorie_name'] ?? '');
                            $categoryName = trim(html_entity_decode(strip_tags($rawCategoryName), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                            $imageName = (string)($image['name'] ?? '');
                            $thumbPath = $getThumbPath($imageName);
                            ?>
                            <div class="col-12 col-md-6">
                                <article class="image-card h-100">
                                    <img src="<?= e($thumbPath) ?>" alt="<?= e($imageName) ?>">
                                    <div class="p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                            <div class="fw-bold text-break"><?= e($imageName) ?></div>
                                            <a class="btn btn-sm btn-primary" href="index.php?page=edit&pid=<?= (int)$image['id'] ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </div>

                                        <div class="meta-line">
                                            <span>ID</span>
                                            <span><?= (int)$image['id'] ?></span>
                                        </div>
                                        <div class="meta-line">
                                            <span>Kategorie</span>
                                            <span><?= e($categoryName !== '' ? $categoryName : '—') ?></span>
                                        </div>
                                        <div class="meta-line">
                                            <span>Größe</span>
                                            <span><?= e((string)($image['size'] ?? '—')) ?></span>
                                        </div>
                                        <div class="meta-line">
                                            <span>Datum</span>
                                            <span><?= $imageTs > 0 ? e(date('d.m.Y', $imageTs)) : '—' ?></span>
                                        </div>
                                        <div class="meta-line">
                                            <span>Beschreibung</span>
                                            <span><?= !empty($image['has_description']) ? 'Ja' : 'Nein' ?></span>
                                        </div>
                                        <div class="meta-line">
                                            <span>Tags</span>
                                            <span><?= !empty($image['has_tags']) ? 'Ja' : 'Nein' ?></span>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
<?php endif; ?>