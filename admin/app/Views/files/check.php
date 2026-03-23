<?php
declare(strict_types=1);
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Dateien abgleichen</h1>
            <p class="text-muted mb-0">
                Wähle aus, welche Treffer angezeigt werden sollen.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?page=file-check&show_missing=1&show_broken=1&show_orphans=1" class="btn btn-outline-primary">
                Alle
            </a>
            <a href="index.php?page=file-check&show_missing=1&show_broken=1&show_orphans=0" class="btn btn-outline-warning">
                Nur Probleme aus DB
            </a>
            <a href="index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1" class="btn btn-outline-info">
                Nur Server ohne DB
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= e((string) $message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <form method="get" class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <input type="hidden" name="page" value="file-check">

            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <input type="hidden" name="show_missing" value="0">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="show_missing"
                            name="show_missing"
                            value="1"
                            <?= !empty($showMissing) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="show_missing">
                            Fehlende DB-Dateien
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <input type="hidden" name="show_broken" value="0">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="show_broken"
                            name="show_broken"
                            value="1"
                            <?= !empty($showBroken) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="show_broken">
                            Defekte DB-Dateien
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <input type="hidden" name="show_orphans" value="0">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="show_orphans"
                            name="show_orphans"
                            value="1"
                            <?= !empty($showOrphans) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="show_orphans">
                            Serverdateien ohne DB-Eintrag
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Anzeigen
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Geprüfte DB-Einträge</div>
                    <div class="fs-3 fw-bold"><?= (int) $total ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Vorhandene Dateien</div>
                    <div class="fs-3 fw-bold text-success"><?= (int) $existing ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Fehlende Dateien</div>
                    <div class="fs-3 fw-bold text-danger"><?= (int) $missing ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Defekte Dateien</div>
                    <div class="fs-3 fw-bold text-warning"><?= (int) $broken ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Serverdateien ohne DB-Eintrag (uploads + thumbs)</div>
                    <div class="fs-3 fw-bold text-info"><?= (int) $orphanCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($showMissing)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <strong>Fehlende Dateien aus der DB</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Dateiname</th>
                                <th>Pfad</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($missingFiles)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Keine fehlenden Dateien gefunden.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($missingFiles as $file): ?>
                                <tr>
                                    <td><?= (int) $file['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e((string) $file['name']) ?></div>
                                        <div class="small text-muted">
                                            entrytime: <?= e((string) $file['entrytime']) ?> |
                                            DB-Größe: <?= e((string) $file['size']) ?> |
                                            Kategorie: <?= (int) $file['to_kat'] ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><code><?= e((string) $file['relative_path']) ?></code></div>
                                        <div class="small text-muted mt-1"><?= e((string) $file['absolute_path']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">fehlt</span>
                                        <div class="small text-muted mt-1"><?= e((string) $file['status_text']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($showBroken)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <strong>Defekte Dateien aus der DB</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Dateiname</th>
                                <th>Pfad</th>
                                <th>Fehler</th>
                                <th>Infos</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($brokenFiles)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Keine defekten Dateien gefunden.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($brokenFiles as $file): ?>
                                <tr>
                                    <td><?= (int) $file['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e((string) $file['name']) ?></div>
                                        <div class="small text-muted">
                                            entrytime: <?= e((string) $file['entrytime']) ?> |
                                            DB-Größe: <?= e((string) $file['size']) ?> |
                                            Kategorie: <?= (int) $file['to_kat'] ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><code><?= e((string) $file['relative_path']) ?></code></div>
                                        <div class="small text-muted mt-1"><?= e((string) $file['absolute_path']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">defekt</span>
                                        <div class="small text-muted mt-1"><?= e((string) $file['status_text']) ?></div>
                                    </td>
                                    <td class="small">
                                        MIME: <?= e((string) $file['mime_type']) ?><br>
                                        Dateigröße: <?= (int) $file['real_file_size'] ?> Byte<br>
                                        Maße: <?= (int) $file['width'] ?> × <?= (int) $file['height'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($showOrphans)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <strong>Dateien auf dem Server ohne Eintrag in der DB</strong>

                <?php if (!empty($orphanServerFiles)): ?>
                    <form
                        method="post"
                        action="index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1"
                        onsubmit="return confirm('Wirklich alle Dateien ohne Zuordnung löschen?');"
                    >
                        <input type="hidden" name="action" value="delete_all_orphans">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash me-1"></i>Alle ohne Zuordnung löschen
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Dateiname</th>
                                <th>Relativer Pfad</th>
                                <th>Größe</th>
                                <th>Geändert</th>
                                <th>Löschen</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($orphanServerFiles)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Keine Dateien ohne DB-Eintrag gefunden.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orphanServerFiles as $file): ?>
                                <tr>
                                    <td><?= e((string) $file['name']) ?></td>
                                    <td>
                                        <code><?= e((string) $file['relative_path']) ?></code>
                                        <div class="small text-muted mt-1"><?= e((string) $file['absolute_path']) ?></div>
                                    </td>
                                    <td><?= (int) $file['size'] ?> Byte</td>
                                    <td><?= e((string) $file['modified']) ?></td>
                                    <td>
                                        <form
                                            method="post"
                                            action="index.php?page=file-check&show_missing=0&show_broken=0&show_orphans=1"
                                            onsubmit="return confirm('Soll diese Datei wirklich gelöscht werden?');"
                                        >
                                            <input type="hidden" name="action" value="delete_orphan">
                                            <input type="hidden" name="relative_path" value="<?= e((string) $file['relative_path']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>