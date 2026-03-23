<?php

normalizeCalendarParams();

$month = (int)$_GET['amonat'];
$year = (int)$_GET['ajahr'];
$selectedDat = $_GET['dat'] ?? '';

$prevYear = $year - 1;
$nextYear = $year + 1;

if ($month === 1) {
    $prevMonth = 12;
    $prevMonthYear = $year - 1;
} else {
    $prevMonth = $month - 1;
    $prevMonthYear = $year;
}

if ($month === 12) {
    $nextMonth = 1;
    $nextMonthYear = $year + 1;
} else {
    $nextMonth = $month + 1;
    $nextMonthYear = $year;
}

$calendarHtml = getCalendarHtml($month, $year);
$image = getCalendarImage();

renderHeader(title('Home'));
?>

<section class="section">
  <div class="box box-pad" style="background:transparent;border:none;box-shadow:none;padding:0;">
    <div class="home-calendar-layout">
      <aside class="cal-wrap">
        <div class="cal-nav">
          <a class="cal-arrow" href="?page=home&amonat=<?= $month ?>&ajahr=<?= $prevYear ?>&dat=<?= e((string)$selectedDat) ?>" title="Jahr zurück">&laquo;</a>
          <a class="cal-arrow" href="?page=home&amonat=<?= $prevMonth ?>&ajahr=<?= $prevMonthYear ?>&dat=<?= e((string)$selectedDat) ?>" title="Monat zurück">&lsaquo;</a>
          <span class="cal-title"><?= e(monthNameGerman($month)) ?> <?= $year ?></span>
          <a class="cal-arrow" href="?page=home&amonat=<?= $nextMonth ?>&ajahr=<?= $nextMonthYear ?>&dat=<?= e((string)$selectedDat) ?>" title="Monat weiter">&rsaquo;</a>
          <a class="cal-arrow" href="?page=home&amonat=<?= $month ?>&ajahr=<?= $nextYear ?>&dat=<?= e((string)$selectedDat) ?>" title="Jahr weiter">&raquo;</a>
        </div>

        <?= $calendarHtml ?>
      </aside>

      <div class="home-calendar-image">
        <?php if ($image): ?>
          <a href="?page=detail&id=<?= (int)$image['id'] ?>">
            <img src="<?= e(imagePath($image)) ?>" alt="<?= displayText($image['name']) ?>">
          </a>
        <?php else: ?>
          <div>Kein Bild gefunden.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php renderFooter(); ?>