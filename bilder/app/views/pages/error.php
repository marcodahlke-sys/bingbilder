<?php renderHeader(title('Fehler')); ?>
<section class="section"><div class="box box-pad">Fehler: <?= e($errorMessage ?? 'Unbekannter Fehler') ?></div></section>
<?php renderFooter(); ?>