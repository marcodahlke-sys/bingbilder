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

$descriptionText = (string) ($description['beschreibung'] ?? '');
$mainCategoryId = (int) ($file['to_kat'] ?? 0);

$thumbPath = 'thumbs/' . (string) $file['name'];
$imagePath = file_exists(BASE_PATH . '/' . $thumbPath) ? $thumbPath : 'uploads/' . (string) $file['name'];
$backgroundImagePath = 'uploads/' . (string) $file['name'];

$hasNextId = !empty($hasNextId);
$nextId = isset($nextId) ? (int) $nextId : null;

$hasPrevId = !empty($hasPrevId);
$prevId = isset($prevId) ? (int) $prevId : null;
?>
<style>
:root {
    --edit-top-offset: 71px;
}

body {
    background:
        linear-gradient(rgba(9, 22, 35, 0.48), rgba(9, 22, 35, 0.48)),
        url("<?= e($backgroundImagePath) ?>") center center / cover no-repeat fixed !important;
    overflow-anchor: none;
}

.topbar {
    position: sticky;
    top: 0;
    z-index: 1050;
}

.sidebar {
    top: var(--edit-top-offset) !important;
    height: calc(100vh - var(--edit-top-offset)) !important;
}

.content-shell {
    overflow: visible !important;
}

.glass-panel {
    color: #ffffff;
}

.glass-panel > *,
.glass-panel .h5,
.glass-panel .small,
.glass-panel .form-label,
.glass-panel .fw-semibold,
.glass-panel .text-muted {
    color: #ffffff;
}

.glass-panel input,
.glass-panel textarea,
.glass-panel select,
.glass-panel option {
    color: #212529 !important;
}

.glass-panel input::placeholder,
.glass-panel textarea::placeholder {
    color: #6c757d !important;
}

#fileDataMessageBox,
#descriptionMessageBox,
#tagsMessageBox {
    color: #212529;
}

#fileDataMessageBox.alert,
#descriptionMessageBox.alert,
#tagsMessageBox.alert {
    color: #212529 !important;
}

.edit-floating-nav {
    position: sticky;
    top: var(--edit-top-offset);
    z-index: 1040;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 18px;
    padding-top: 4px;
}

.edit-floating-nav-left {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.edit-floating-nav-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.edit-page-content {
    padding-top: 4px;
}

.edit-switch-row {
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border: 1px solid #d7d7d7;
    background: #f7f7f7;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
    width: 100%;
}

.edit-switch-label {
    color: #000000 !important;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.2;
}

.edit-switch {
    position: relative;
    width: 42px;
    min-width: 42px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    flex-shrink: 0;
}

.edit-switch-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    cursor: pointer;
    z-index: 2;
}

.edit-switch-slider {
    position: relative;
    width: 42px;
    height: 24px;
    border-radius: 999px;
    background: #cbd5e1;
    transition: background .18s ease;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.12);
}

.edit-switch-slider::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 999px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    transition: transform .18s ease;
}

.edit-switch-input:checked + .edit-switch-slider {
    background: #0d6efd;
}

.edit-switch-input:checked + .edit-switch-slider::after {
    transform: translateX(18px);
}

.edit-switch-input:focus + .edit-switch-slider {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.18);
}

.tags-inline-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tag-inline-item {
    border: 1px solid #d7d7d7;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 6px;
    padding: 10px 12px;
    color: #212529;
}

.tag-inline-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 999px;
    background: #0d6efd;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    padding: 7px 12px;
    cursor: pointer;
}

.tag-inline-editor {
    margin-top: 10px;
}

.tag-inline-editor-inner {
    border-top: 1px solid #d7d7d7;
    padding-top: 10px;
}

@media (max-width: 767.98px) {
    :root {
        --edit-top-offset: 70px;
    }

    .edit-floating-nav {
        flex-direction: column;
        align-items: stretch;
    }

    .edit-floating-nav-left {
        justify-content: flex-start;
    }

    .edit-floating-nav-right {
        justify-content: flex-start;
    }

    .sidebar {
        top: var(--edit-top-offset) !important;
        height: calc(100vh - var(--edit-top-offset)) !important;
    }
}
</style>

<main class="p-3 p-lg-4">
    <div class="edit-floating-nav">
        <div class="edit-floating-nav-left">
            <?php if ($hasPrevId && $prevId !== null): ?>
                <a href="index.php?page=edit&pid=<?= $prevId ?>" class="btn btn-light">← Zurück</a>
            <?php else: ?>
                <button type="button" class="btn btn-light" disabled aria-disabled="true">← Zurück</button>
            <?php endif; ?>

            <?php if ($hasNextId && $nextId !== null): ?>
                <a href="index.php?page=edit&pid=<?= $nextId ?>" class="btn btn-light">Nächste ID →</a>
            <?php else: ?>
                <button type="button" class="btn btn-light" disabled aria-disabled="true">Nächste ID →</button>
            <?php endif; ?>
        </div>

        <div class="edit-floating-nav-right">
            <div class="badge text-bg-dark d-flex align-items-center px-3 py-2">
                Bild-ID: <?= (int) $file['id'] ?>
            </div>
        </div>
    </div>

    <div class="edit-page-content mx-auto" style="max-width: 760px;">
        <div class="glass-panel p-4 mb-4">
            <div class="text-center mb-4">
                <div class="rounded-4 border border-light bg-white p-1 shadow-lg d-inline-block">
                    <img src="<?= e($imagePath) ?>" alt="<?= e((string) $file['name']) ?>" class="img-fluid rounded-4" style="max-width: 500px;" id="editPreviewImage">
                </div>
            </div>

            <div id="fileDataMessageBox" class="mb-3 d-none"></div>

            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-4">
                    <label for="fileDateInput" class="form-label fw-semibold">Datum</label>
                    <input type="text" class="form-control" id="fileDateInput" value="<?= e(!empty($file['entrytime']) ? date('d.m.Y', (int) $file['entrytime']) : '') ?>">
                </div>

                <div class="col-md-8">
                    <label for="fileNameInput" class="form-label fw-semibold">Dateiname</label>
                    <input type="text" class="form-control" id="fileNameInput" value="<?= e((string) $file['name']) ?>" disabled>
                </div>
            </div>

            <div class="mb-4">
                <label for="fileCategorySelect" class="form-label fw-semibold">Kategorie</label>
                <select class="form-select" id="fileCategorySelect">
                    <option value="">Bitte auswählen</option>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $categoryId = (int) $category['id'];
                        $categoryName = decodeCategoryLabelLocal($category['name'] ?? '');
                        ?>
                        <option value="<?= $categoryId ?>" <?= $categoryId === $mainCategoryId ? 'selected' : '' ?>>
                            <?= e($categoryName !== '' ? $categoryName : ('Kategorie ' . $categoryId)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($flagCategories)): ?>
                <div class="mb-4">
                    <div class="form-label fw-semibold mb-2">Zusätzliche Kategorien</div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($flagCategories as $flagCategory): ?>
                            <?php
                            $flagCategoryId = (int) $flagCategory['id'];
                            $flagFieldName = $flagFieldMap[$flagCategoryId] ?? null;
                            $isChecked = $flagFieldName !== null
                                && !empty($file[$flagFieldName])
                                && (string) $file[$flagFieldName] === '1';
                            $flagInputId = 'flagCategory' . $flagCategoryId;
                            $flagLabel = decodeCategoryLabelLocal($flagCategory['name'] ?? '');
                            ?>
                            <label class="edit-switch-row" for="<?= e($flagInputId) ?>">
                                <span class="edit-switch-label"><?= e($flagLabel !== '' ? $flagLabel : ('Kategorie ' . $flagCategoryId)) ?></span>
                                <span class="edit-switch">
                                    <input
                                        type="checkbox"
                                        class="edit-switch-input file-flag-input"
                                        id="<?= e($flagInputId) ?>"
                                        data-category-id="<?= $flagCategoryId ?>"
                                        <?= $isChecked ? 'checked' : '' ?>
                                    >
                                    <span class="edit-switch-slider"></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" type="button" id="saveFileDataButton">💾 Speichern</button>
                <button class="btn btn-secondary" type="button" id="reloadFileDataButton">↺ Neu laden</button>
            </div>
        </div>

        <div class="glass-panel p-4 mb-4">
            <div class="h5 mb-3">📝 Beschreibung</div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button class="btn btn-primary" type="button" id="saveDescriptionButton">💾 Beschreibung speichern</button>
                <button class="btn btn-secondary" type="button" id="loadTempDescriptionButton">🪄 Beschreibung laden</button>
                <button class="btn btn-secondary" type="button" id="reloadDescriptionButton">↺ Neu laden</button>
            </div>

            <div id="descriptionMessageBox" class="mb-3 d-none"></div>

            <textarea class="form-control" rows="10" id="descriptionTextarea"><?= e($descriptionText) ?></textarea>
        </div>

        <div class="glass-panel p-4">
            <div class="h5 mb-3">🏷 Tags dieses Bildes</div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button class="btn btn-secondary" type="button" id="extractTagsButton">🪄 Tags aus Beschreibung extrahieren</button>
                <button class="btn btn-info" type="button" id="analyzeImageTagsButton">🤖 Tags mit KI analysieren</button>
            </div>

            <div id="tagsMessageBox" class="mb-3 d-none"></div>

            <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                <input type="text" class="form-control" placeholder="Neue Tags, mit Komma getrennt" id="tagsInput">
                <button class="btn btn-primary" type="button" id="addTagsButton">＋ Tags hinzufügen</button>
            </div>

            <div id="tagsListWrap">
                <div class="alert alert-light <?= empty($tags) ? '' : 'd-none' ?>" id="emptyTagsNotice">Für dieses Bild sind keine Tags vorhanden.</div>

                <div class="tags-inline-list" id="tagsList">
                    <?php foreach ($tags as $tag): ?>
                        <div class="tag-inline-item" data-tag-item data-tag-id="<?= (int) $tag['id'] ?>" data-tag-text="<?= e((string) $tag['tag']) ?>">
                            <button type="button" class="tag-inline-chip" data-tag-open><?= e((string) $tag['tag']) ?></button>

                            <div class="tag-inline-editor d-none" data-tag-editor>
                                <div class="tag-inline-editor-inner">
                                    <input type="text" class="form-control tag-lowercase-input" value="<?= e((string) $tag['tag']) ?>" data-tag-edit-input>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <button type="button" class="btn btn-primary btn-sm" data-tag-save>Speichern</button>
                                        <button type="button" class="btn btn-danger btn-sm" data-tag-delete>Löschen</button>
                                        <button type="button" class="btn btn-secondary btn-sm" data-tag-cancel>Abbrechen</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3 small" id="tagsCount"><?= count($tags) ?> Tag(s) für dieses Bild</div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pid = <?= (int) $file['id'] ?>;
    let initialDescription = <?= json_encode($descriptionText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialDate = <?= json_encode(!empty($file['entrytime']) ? date('d.m.Y', (int) $file['entrytime']) : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialName = <?= json_encode((string) $file['name'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialCategoryId = <?= (int) $mainCategoryId ?>;
    const initialImagePath = <?= json_encode($imagePath, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialFlags = <?= json_encode(array_map(function ($category) use ($flagFieldMap, $file) {
        $categoryId = (int) $category['id'];
        $fieldName = $flagFieldMap[$categoryId] ?? null;
        return $fieldName !== null && !empty($file[$fieldName]) && (string) $file[$fieldName] === '1';
    }, $flagCategories), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const loadButton = document.getElementById('loadTempDescriptionButton');
    const saveDescriptionButton = document.getElementById('saveDescriptionButton');
    const descriptionTextarea = document.getElementById('descriptionTextarea');
    const descriptionMessageBox = document.getElementById('descriptionMessageBox');
    const reloadDescriptionButton = document.getElementById('reloadDescriptionButton');

    const saveFileDataButton = document.getElementById('saveFileDataButton');
    const reloadFileDataButton = document.getElementById('reloadFileDataButton');
    const fileDateInput = document.getElementById('fileDateInput');
    const fileNameInput = document.getElementById('fileNameInput');
    const fileCategorySelect = document.getElementById('fileCategorySelect');
    const fileDataMessageBox = document.getElementById('fileDataMessageBox');
    const fileFlagInputs = Array.from(document.querySelectorAll('.file-flag-input'));
    const editPreviewImage = document.getElementById('editPreviewImage');

    const addTagsButton = document.getElementById('addTagsButton');
    const tagsInput = document.getElementById('tagsInput');
    const tagsMessageBox = document.getElementById('tagsMessageBox');
    const tagsList = document.getElementById('tagsList');
    const emptyTagsNotice = document.getElementById('emptyTagsNotice');
    const tagsCount = document.getElementById('tagsCount');
    const extractTagsButton = document.getElementById('extractTagsButton');
    const analyzeImageTagsButton = document.getElementById('analyzeImageTagsButton');

    function showMessage(element, message, type) {
        if (!element) {
            return;
        }

        if (element.messageTimeout) {
            window.clearTimeout(element.messageTimeout);
            element.messageTimeout = null;
        }

        element.className = 'alert alert-' + type;
        element.textContent = message;
        element.classList.remove('d-none');

        element.messageTimeout = window.setTimeout(function () {
            hideMessage(element);
        }, 5000);
    }

    function hideMessage(element) {
        if (!element) {
            return;
        }

        if (element.messageTimeout) {
            window.clearTimeout(element.messageTimeout);
            element.messageTimeout = null;
        }

        element.classList.add('d-none');
        element.textContent = '';
        element.className = 'mb-3 d-none';
    }

    function setButtonLoading(button, isLoading, loadingText, defaultHtml) {
        if (!button) {
            return;
        }

        if (isLoading) {
            button.disabled = true;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = loadingText;
            return;
        }

        button.disabled = false;
        button.innerHTML = button.dataset.originalHtml || defaultHtml;
    }

    function resetFileDataForm() {
        fileDateInput.value = initialDate;
        fileNameInput.value = initialName;
        fileCategorySelect.value = String(initialCategoryId);

        fileFlagInputs.forEach(function (input, index) {
            input.checked = !!initialFlags[index];
        });

        if (editPreviewImage) {
            editPreviewImage.src = initialImagePath;
        }

        hideMessage(fileDataMessageBox);
    }

    function resetDescriptionForm() {
        descriptionTextarea.value = initialDescription;
        hideMessage(descriptionMessageBox);
    }

    function saveDescription() {
        hideMessage(descriptionMessageBox);
        setButtonLoading(saveDescriptionButton, true, 'Speichere ...', '💾 Beschreibung speichern');

        const formData = new FormData();
        formData.append('action', 'save_description');
        formData.append('pid', String(pid));
        formData.append('beschreibung', descriptionTextarea.value);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            showMessage(descriptionMessageBox, data.message || 'Beschreibung gespeichert.', data.success ? 'success' : 'danger');

            if (data.success) {
                initialDescription = typeof data.content === 'string' ? data.content : descriptionTextarea.value;
                descriptionTextarea.value = initialDescription;
            }
        })
        .catch(function () {
            showMessage(descriptionMessageBox, 'Beim Speichern ist ein Fehler aufgetreten.', 'danger');
        })
        .finally(function () {
            setButtonLoading(saveDescriptionButton, false, 'Speichere ...', '💾 Beschreibung speichern');
        });
    }

    function saveFileData() {
        hideMessage(fileDataMessageBox);
        setButtonLoading(saveFileDataButton, true, 'Speichere ...', '💾 Speichern');

        const formData = new FormData();
        formData.append('action', 'save_file_data');
        formData.append('pid', String(pid));
        formData.append('date', fileDateInput.value);
        formData.append('to_kat', fileCategorySelect.value);

        fileFlagInputs.forEach(function (input) {
            if (input.checked) {
                formData.append('flag_' + input.dataset.categoryId, '1');
            }
        });

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            showMessage(fileDataMessageBox, data.message || 'Dateidaten gespeichert.', data.success ? 'success' : 'danger');

            if (data.success && data.name) {
                fileNameInput.value = data.name;
            }
        })
        .catch(function () {
            showMessage(fileDataMessageBox, 'Beim Speichern ist ein Fehler aufgetreten.', 'danger');
        })
        .finally(function () {
            setButtonLoading(saveFileDataButton, false, 'Speichere ...', '💾 Speichern');
        });
    }

    function loadTempDescription() {
        hideMessage(descriptionMessageBox);
        setButtonLoading(loadButton, true, 'Lade ...', '🪄 Beschreibung laden');

        const formData = new FormData();
        formData.append('action', 'load_temp_description');

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                descriptionTextarea.value = data.content || '';
                showMessage(descriptionMessageBox, 'Beschreibung wurde geladen.', 'success');
            } else {
                showMessage(descriptionMessageBox, data.message || 'Beschreibung konnte nicht geladen werden.', 'danger');
            }
        })
        .catch(function () {
            showMessage(descriptionMessageBox, 'Beim Laden ist ein Fehler aufgetreten.', 'danger');
        })
        .finally(function () {
            setButtonLoading(loadButton, false, 'Lade ...', '🪄 Beschreibung laden');
        });
    }

    function renderTags(tags) {
        tagsList.innerHTML = '';

        if (!Array.isArray(tags) || tags.length === 0) {
            emptyTagsNotice.classList.remove('d-none');
            tagsCount.textContent = '0 Tag(s) für dieses Bild';
            return;
        }

        emptyTagsNotice.classList.add('d-none');
        tagsCount.textContent = tags.length + ' Tag(s) für dieses Bild';

        tags.forEach(function (tag) {
            const wrapper = document.createElement('div');
            wrapper.className = 'tag-inline-item';
            wrapper.setAttribute('data-tag-item', '');
            wrapper.setAttribute('data-tag-id', String(tag.id));
            wrapper.setAttribute('data-tag-text', tag.tag);

            wrapper.innerHTML = ''
                + '<button type="button" class="tag-inline-chip" data-tag-open></button>'
                + '<div class="tag-inline-editor d-none" data-tag-editor>'
                + '    <div class="tag-inline-editor-inner">'
                + '        <input type="text" class="form-control tag-lowercase-input" data-tag-edit-input>'
                + '        <div class="d-flex flex-wrap gap-2 mt-2">'
                + '            <button type="button" class="btn btn-primary btn-sm" data-tag-save>Speichern</button>'
                + '            <button type="button" class="btn btn-danger btn-sm" data-tag-delete>Löschen</button>'
                + '            <button type="button" class="btn btn-secondary btn-sm" data-tag-cancel>Abbrechen</button>'
                + '        </div>'
                + '    </div>'
                + '</div>';

            const chip = wrapper.querySelector('[data-tag-open]');
            const input = wrapper.querySelector('[data-tag-edit-input]');

            chip.textContent = tag.tag;
            input.value = tag.tag;

            tagsList.appendChild(wrapper);
        });

        bindTagEvents();
    }

    function addTags() {
        hideMessage(tagsMessageBox);

        const rawTags = tagsInput.value.trim();

        if (rawTags === '') {
            showMessage(tagsMessageBox, 'Bitte mindestens ein Tag eingeben.', 'danger');
            return;
        }

        setButtonLoading(addTagsButton, true, 'Füge hinzu ...', '＋ Tags hinzufügen');

        const formData = new FormData();
        formData.append('action', 'add_tags');
        formData.append('pid', String(pid));
        formData.append('tags', rawTags);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            showMessage(tagsMessageBox, data.message || 'Tags wurden verarbeitet.', data.success ? 'success' : 'danger');

            if (data.success && Array.isArray(data.tags)) {
                renderTags(data.tags);
                tagsInput.value = '';
            }
        })
        .catch(function () {
            showMessage(tagsMessageBox, 'Beim Hinzufügen ist ein Fehler aufgetreten.', 'danger');
        })
        .finally(function () {
            setButtonLoading(addTagsButton, false, 'Füge hinzu ...', '＋ Tags hinzufügen');
        });
    }

    function updateTag(tagId, tagValue) {
        hideMessage(tagsMessageBox);

        const formData = new FormData();
        formData.append('action', 'update_tag');
        formData.append('pid', String(pid));
        formData.append('tag_id', String(tagId));
        formData.append('tag', tagValue);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            showMessage(tagsMessageBox, data.message || 'Tag wurde gespeichert.', data.success ? 'success' : 'danger');

            if (data.success && Array.isArray(data.tags)) {
                renderTags(data.tags);
            }
        })
        .catch(function () {
            showMessage(tagsMessageBox, 'Beim Speichern des Tags ist ein Fehler aufgetreten.', 'danger');
        });
    }

    function deleteTag(tagId) {
        hideMessage(tagsMessageBox);

        const formData = new FormData();
        formData.append('action', 'delete_tag');
        formData.append('pid', String(pid));
        formData.append('tag_id', String(tagId));

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            showMessage(tagsMessageBox, data.message || 'Tag wurde gelöscht.', data.success ? 'success' : 'danger');

            if (data.success && Array.isArray(data.tags)) {
                renderTags(data.tags);
            }
        })
        .catch(function () {
            showMessage(tagsMessageBox, 'Beim Löschen des Tags ist ein Fehler aufgetreten.', 'danger');
        });
    }

    function closeAllTagEditors() {
        document.querySelectorAll('[data-tag-item]').forEach(function (item) {
            const editor = item.querySelector('[data-tag-editor]');
            if (editor) {
                editor.classList.add('d-none');
            }
        });
    }

    function openTagEditor(item) {
        closeAllTagEditors();

        const editor = item.querySelector('[data-tag-editor]');
        const input = item.querySelector('[data-tag-edit-input]');

        if (editor) {
            editor.classList.remove('d-none');
        }

        if (input) {
            input.focus();
            input.select();
        }
    }

    function bindTagEvents() {
        document.querySelectorAll('[data-tag-item]').forEach(function (item) {
            const tagId = item.getAttribute('data-tag-id');
            const openButton = item.querySelector('[data-tag-open]');
            const saveButton = item.querySelector('[data-tag-save]');
            const deleteButton = item.querySelector('[data-tag-delete]');
            const cancelButton = item.querySelector('[data-tag-cancel]');
            const input = item.querySelector('[data-tag-edit-input]');

            if (openButton) {
                openButton.addEventListener('click', function () {
                    openTagEditor(item);
                });
            }

            if (saveButton) {
                saveButton.addEventListener('click', function () {
                    updateTag(tagId, input.value);
                });
            }

            if (deleteButton) {
                deleteButton.addEventListener('click', function () {
                    deleteTag(tagId);
                });
            }

            if (cancelButton) {
                cancelButton.addEventListener('click', function () {
                    const editor = item.querySelector('[data-tag-editor]');
                    if (editor) {
                        editor.classList.add('d-none');
                    }
                });
            }

            if (input) {
                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        updateTag(tagId, input.value);
                    }
                });
            }
        });
    }

    if (loadButton) {
        loadButton.addEventListener('click', loadTempDescription);
    }

    if (saveDescriptionButton) {
        saveDescriptionButton.addEventListener('click', saveDescription);
    }

    if (reloadDescriptionButton) {
        reloadDescriptionButton.addEventListener('click', resetDescriptionForm);
    }

    if (saveFileDataButton) {
        saveFileDataButton.addEventListener('click', saveFileData);
    }

    if (reloadFileDataButton) {
        reloadFileDataButton.addEventListener('click', resetFileDataForm);
    }

    if (addTagsButton) {
        addTagsButton.addEventListener('click', addTags);
    }

    if (analyzeImageTagsButton) {
        analyzeImageTagsButton.addEventListener('click', function () {
            hideMessage(tagsMessageBox);
            setButtonLoading(analyzeImageTagsButton, true, 'Analysiere ...', '🤖 Tags mit Gemini analysieren');
            tagsInput.value = 'Warte auf Gemini ...';

            const formData = new FormData();
            formData.append('action', 'analyze_tags_with_gemini');
            formData.append('pid', String(pid));

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    tagsInput.value = typeof data.tags === 'string' ? data.tags : '';
                    showMessage(tagsMessageBox, data.message || 'Tags wurden generiert.', 'success');
                } else {
                    tagsInput.value = '';
                    showMessage(tagsMessageBox, data.message || 'Die Bildanalyse ist fehlgeschlagen.', 'danger');
                }
            })
            .catch(function () {
                tagsInput.value = '';
                showMessage(tagsMessageBox, 'Bei der Bildanalyse ist ein Fehler aufgetreten.', 'danger');
            })
            .finally(function () {
                setButtonLoading(analyzeImageTagsButton, false, 'Analysiere ...', '🤖 Tags mit Gemini analysieren');
            });
        });
    }

    if (extractTagsButton) {
        extractTagsButton.addEventListener('click', function () {
            const stopWords = [
                'und', 'oder', 'der', 'die', 'das', 'den', 'dem', 'des',
                'ein', 'eine', 'einer', 'einem', 'einen', 'eines',
                'ist', 'sind', 'war', 'waren', 'wird', 'werden',
                'mit', 'ohne', 'für', 'von', 'vom', 'zu', 'zum', 'zur',
                'im', 'in', 'am', 'an', 'auf', 'aus', 'bei', 'nach',
                'über', 'unter', 'vor', 'hinter', 'neben', 'durch',
                'the', 'a', 'an', 'and', 'or', 'of', 'to', 'from', 'by', 'with',
                'photo', 'foto', 'copyright', 'copyrighted'
            ];

            let text = descriptionTextarea.value || '';

            text = text.split('(©')[0];
            text = text.split('©')[0];

             text = text
                .toLowerCase()
                .replace(/[\r\n]+/g, ' ')
                .replace(/[\/|]+/g, ' ')
                .replace(/[“”„"‚‘’'`´]+/g, ' ')
                .replace(/[(){}\[\]]+/g, ' ')
                .replace(/[.,:;!?]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            const words = text
                .split(' ')
                .map(function (word) {
                    return word.trim();
                })
                .filter(function (word) {
                    return word.length > 1
                        && !stopWords.includes(word)
                        && !/^\d+$/.test(word);
                });

            const uniqueWords = [];

            words.forEach(function (word) {
                if (!uniqueWords.includes(word)) {
                    uniqueWords.push(word);
                }
            });

            tagsInput.value = uniqueWords.join(', ');
        });
    }

    bindTagEvents();
});
</script>