<?php

declare(strict_types=1);
?>
<main class="p-3 p-lg-4">
    <div class="glass-panel p-4 p-lg-5">
        <h1 class="h3 mb-3">Bing-Download</h1>

        <p class="mb-4">
            Dieser Bereich dient als manueller Fallback. Der eigentliche Bing-Download wird normalerweise täglich
            automatisch per Cronjob ausgeführt. Dieser Link ist nur dafür gedacht, den Vorgang manuell anzustoßen,
            falls der automatische Lauf einmal nicht geklappt hat.
        </p>

        <div class="rounded-4 border border-white border-opacity-10 bg-dark bg-opacity-25 p-4 mb-4">
            <div class="fw-semibold mb-2">Status</div>
            <div class="small text-white-50">
                Automatischer Tageslauf aktiv · letzter manueller Start nicht erforderlich
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3">
            <a class="btn btn-primary" href="bing-download.php" target="_blank" rel="noopener">
                Download jetzt manuell starten
            </a>

        </div>
    </div>
</main>