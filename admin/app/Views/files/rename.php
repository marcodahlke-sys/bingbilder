<?php

declare(strict_types=1);

if (!function_exists('decodeCategoryLabelLocal')) {
    function decodeCategoryLabelLocal($value): string
    {
        $decoded = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = strip_tags($decoded);
        return trim($decoded);
    }
}
?>
<main class="p-3 p-lg-4">
    <div class="glass-panel p-4 text-dark">
        <h1 class="h4 mb-4">Datei umbenennen</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= e((string) $message) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e((string) $error) ?></div>
        <?php endif; ?>

        <?php if (empty($tempFiles)): ?>
            <div class="alert alert-light mb-0">Im Temp-Ordner wurden keine Dateien gefunden.</div>
        <?php else: ?>
            <div class="rename-stage">
                <div class="rename-thumb-grid" id="renameThumbGrid">
                    <?php foreach ($tempFiles as $index => $tempFile): ?>
                        <?php
                        $fileName = (string) $tempFile['name'];
                        $fileUrl = 'temp/' . rawurlencode($fileName);
                        ?>
                        <div
                            class="rename-card"
                            data-rename-card
                            data-file-name="<?= e($fileName) ?>"
                            data-file-src="<?= e($fileUrl) ?>"
                        >
                            <button
                                type="button"
                                class="rename-thumb-button"
                                data-rename-trigger
                                aria-label="Bild auswählen: <?= e($fileName) ?>"
                            >
                                <img
                                    src="<?= e($fileUrl) ?>"
                                    alt="<?= e($fileName) ?>"
                                    class="rename-thumb-image"
                                >
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <div id="renameEditorRow" class="rename-editor-row d-none">
                        <div class="rename-editor-inner">
                            <form method="post" action="index.php?page=rename" class="rename-editor-form">
                                <input type="hidden" name="temp_name" id="renameTempName" value="">

                                <div class="rename-editor-left">
                                    <div class="rename-editor-title">Bild speichern</div>

                                    <div class="mb-3">
                                        <label class="form-label">Bild</label>
                                        <input
                                            type="text"
                                            id="renameFileName"
                                            class="form-control"
                                            value=""
                                            readonly
                                        >
                                    </div>

                                    <div class="mb-3 position-relative">
                                        <label class="form-label">Datum</label>
                                        <input
                                            type="text"
                                            name="date"
                                            id="renameDateInput"
                                            class="form-control"
                                            placeholder="TT.MM.JJJJ"
                                            required
                                        >

                                        <div id="renameDatepicker" class="rename-datepicker d-none">
                                            <div class="rename-datepicker-toolbar">
                                                <select id="renameDatepickerMonth" class="form-select form-select-sm"></select>
                                                <select id="renameDatepickerYear" class="form-select form-select-sm"></select>
                                            </div>

                                            <div class="rename-datepicker-weekdays">
                                                <span>Mo</span>
                                                <span>Di</span>
                                                <span>Mi</span>
                                                <span>Do</span>
                                                <span>Fr</span>
                                                <span>Sa</span>
                                                <span>So</span>
                                            </div>

                                            <div id="renameDatepickerDays" class="rename-datepicker-days"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kategorie</label>
                                        <select name="to_kat" class="form-select" required>
                                            <option value="">Bitte wählen</option>
                                            <?php foreach ($mainCategories as $category): ?>
                                                <option value="<?= (int) $category['id'] ?>">
                                                    <?= e(decodeCategoryLabelLocal($category['name'] ?? '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="rename-editor-subtitle">Weitere Kategorien</div>

                                    <div class="row g-2">
                                        <?php foreach ($flagCategories as $category): ?>
                                            <div class="col-12 col-md-6">
                                                <label class="rename-switch-row">
                                                    <span><?= e(decodeCategoryLabelLocal($category['name'] ?? '')) ?></span>

                                                    <span class="rename-switch">
                                                        <input
                                                            class="rename-switch-input"
                                                            type="checkbox"
                                                            name="flag_<?= (int) $category['id'] ?>"
                                                            value="1"
                                                        >
                                                        <span class="rename-switch-slider"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="mt-4 d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary">Speichern</button>
                                        <button type="button" class="btn btn-secondary" id="renameCloseForm">Abbrechen</button>
                                    </div>
                                </div>

                                <div class="rename-editor-right">
                                    <div class="rename-preview-label">Vorschau</div>
                                    <div class="rename-preview-box">
                                        <img
                                            id="renamePreviewImage"
                                            src=""
                                            alt="Vorschau"
                                            class="rename-preview-image"
                                        >
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .rename-datepicker-day.is-today {
                    border-color: #0d6efd;
                    background: #eff6ff;
                    color: #0d6efd;
                    font-weight: 700;
                }

                .rename-datepicker-day.is-today.is-selected {
                    box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.85);
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const cards = Array.from(document.querySelectorAll('[data-rename-card]'));
                    const editorRow = document.getElementById('renameEditorRow');
                    const tempNameInput = document.getElementById('renameTempName');
                    const fileNameInput = document.getElementById('renameFileName');
                    const previewImage = document.getElementById('renamePreviewImage');
                    const closeButton = document.getElementById('renameCloseForm');

                    const dateInput = document.getElementById('renameDateInput');
                    const datepicker = document.getElementById('renameDatepicker');
                    const monthSelect = document.getElementById('renameDatepickerMonth');
                    const yearSelect = document.getElementById('renameDatepickerYear');
                    const daysContainer = document.getElementById('renameDatepickerDays');

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    let pickerMonth = today.getMonth();
                    let pickerYear = today.getFullYear();

                    const monthNames = [
                        'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
                        'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'
                    ];

                    function closeEditor() {
                        editorRow.classList.add('d-none');

                        cards.forEach(function (card) {
                            const trigger = card.querySelector('[data-rename-trigger]');
                            if (trigger) {
                                trigger.classList.remove('is-active');
                            }
                        });
                    }

                    function parseInputDate(value) {
                        const parts = value.split('.');
                        if (parts.length !== 3) {
                            return null;
                        }

                        const day = parseInt(parts[0], 10);
                        const month = parseInt(parts[1], 10) - 1;
                        const year = parseInt(parts[2], 10);

                        if (isNaN(day) || isNaN(month) || isNaN(year)) {
                            return null;
                        }

                        const date = new Date(year, month, day);
                        if (
                            date.getFullYear() !== year ||
                            date.getMonth() !== month ||
                            date.getDate() !== day
                        ) {
                            return null;
                        }

                        return date;
                    }

                    function formatDate(date) {
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = String(date.getFullYear());
                        return day + '.' + month + '.' + year;
                    }

                    function renderPicker() {
                        monthSelect.innerHTML = '';
                        yearSelect.innerHTML = '';

                        monthNames.forEach(function (monthName, index) {
                            const option = document.createElement('option');
                            option.value = String(index);
                            option.textContent = monthName;
                            if (index === pickerMonth) {
                                option.selected = true;
                            }
                            monthSelect.appendChild(option);
                        });

                        for (let year = 2009; year <= 2035; year++) {
                            const option = document.createElement('option');
                            option.value = String(year);
                            option.textContent = String(year);
                            if (year === pickerYear) {
                                option.selected = true;
                            }
                            yearSelect.appendChild(option);
                        }

                        daysContainer.innerHTML = '';

                        const firstDay = new Date(pickerYear, pickerMonth, 1);
                        const firstWeekday = (firstDay.getDay() + 6) % 7;
                        const daysInMonth = new Date(pickerYear, pickerMonth + 1, 0).getDate();
                        const selectedDate = parseInputDate(dateInput.value);

                        for (let i = 0; i < firstWeekday; i++) {
                            const empty = document.createElement('button');
                            empty.type = 'button';
                            empty.className = 'rename-datepicker-day is-empty';
                            empty.textContent = '';
                            daysContainer.appendChild(empty);
                        }

                        for (let day = 1; day <= daysInMonth; day++) {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'rename-datepicker-day';
                            button.textContent = String(day);

                            const currentDate = new Date(pickerYear, pickerMonth, day);

                            if (
                                currentDate.getFullYear() === today.getFullYear() &&
                                currentDate.getMonth() === today.getMonth() &&
                                currentDate.getDate() === today.getDate()
                            ) {
                                button.classList.add('is-today');
                            }

                            if (
                                selectedDate &&
                                currentDate.getFullYear() === selectedDate.getFullYear() &&
                                currentDate.getMonth() === selectedDate.getMonth() &&
                                currentDate.getDate() === selectedDate.getDate()
                            ) {
                                button.classList.add('is-selected');
                            }

                            button.addEventListener('click', function () {
                                dateInput.value = formatDate(currentDate);
                                renderPicker();
                                datepicker.classList.add('d-none');
                            });

                            daysContainer.appendChild(button);
                        }
                    }

                    function openPicker() {
                        const parsed = parseInputDate(dateInput.value);
                        if (parsed) {
                            pickerMonth = parsed.getMonth();
                            pickerYear = parsed.getFullYear();
                        } else {
                            const now = new Date();
                            pickerMonth = now.getMonth();
                            pickerYear = now.getFullYear();
                        }

                        renderPicker();
                        datepicker.classList.remove('d-none');
                    }

                    if (monthSelect) {
                        monthSelect.addEventListener('change', function () {
                            pickerMonth = parseInt(monthSelect.value, 10);
                            renderPicker();
                        });
                    }

                    if (yearSelect) {
                        yearSelect.addEventListener('change', function () {
                            pickerYear = parseInt(yearSelect.value, 10);
                            renderPicker();
                        });
                    }

                    if (dateInput) {
                        dateInput.addEventListener('focus', openPicker);
                        dateInput.addEventListener('click', openPicker);
                    }

                    document.addEventListener('click', function (event) {
                        if (
                            datepicker &&
                            !datepicker.classList.contains('d-none') &&
                            !datepicker.contains(event.target) &&
                            event.target !== dateInput
                        ) {
                            datepicker.classList.add('d-none');
                        }
                    });

                    cards.forEach(function (card) {
                        const trigger = card.querySelector('[data-rename-trigger]');
                        if (!trigger) {
                            return;
                        }

                        trigger.addEventListener('click', function () {
                            const isAlreadyOpen = trigger.classList.contains('is-active');
                            const fileName = card.getAttribute('data-file-name') || '';
                            const fileSrc = card.getAttribute('data-file-src') || '';

                            cards.forEach(function (otherCard) {
                                const otherTrigger = otherCard.querySelector('[data-rename-trigger]');
                                if (otherTrigger) {
                                    otherTrigger.classList.remove('is-active');
                                }
                            });

                            if (isAlreadyOpen) {
                                closeEditor();
                                return;
                            }

                            trigger.classList.add('is-active');

                            tempNameInput.value = fileName;
                            fileNameInput.value = fileName;
                            previewImage.src = fileSrc;
                            previewImage.alt = fileName;

                            card.insertAdjacentElement('afterend', editorRow);
                            editorRow.classList.remove('d-none');
                            dateInput.value = '';
                            renderPicker();
                            editorRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    });

                    if (closeButton) {
                        closeButton.addEventListener('click', function () {
                            closeEditor();
                            if (datepicker) {
                                datepicker.classList.add('d-none');
                            }
                            if (dateInput) {
                                dateInput.value = '';
                            }
                        });
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</main>