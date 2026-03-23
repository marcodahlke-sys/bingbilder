<div class="glass-panel p-4 text-dark">
    <h1 class="h4 mb-3">Datei-Upload (maximal 31 Dateien)</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="uploadForm" action="index.php?page=upload">
        <input
            type="file"
            id="fileInput"
            class="d-none"
            name="files[]"
            multiple
            accept="image/*"
        >

        <div id="dropzone" class="dropzone-panel mb-3" role="button" tabindex="0" aria-label="Dateien hochladen">
            <div class="dropzone-frame">
                <div class="dropzone-icon">
                    <i class="bi bi-cloud-arrow-up"></i>
                </div>

                <div class="dropzone-title">
                    Dateien hier hineinziehen <span>oder klicken zum Auswählen</span>
                </div>

                <div class="dropzone-subtitle">
                    Es können maximal 31 Dateien hochgeladen werden.
                </div>
            </div>
        </div>

        <div id="uploadSummary" class="upload-summary mb-3 d-none">
            <div class="upload-summary-head">
                <strong>Gesamtstatus</strong>
                <span id="uploadSummaryText">0 erfolgreich, 0 fehlerhaft, 0 gesamt</span>
            </div>
            <div class="upload-summary-bar">
                <div id="uploadSummaryBarInner" class="upload-summary-bar-inner" style="width: 0%;"></div>
            </div>
        </div>

        <div id="selectedFiles" class="upload-preview-grid mb-3">
            <div class="text-muted small">Noch keine Dateien ausgewählt</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary" type="button" id="startUpload">Hochladen</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('uploadForm');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const selectedFiles = document.getElementById('selectedFiles');
    const startUpload = document.getElementById('startUpload');
    const uploadSummary = document.getElementById('uploadSummary');
    const uploadSummaryText = document.getElementById('uploadSummaryText');
    const uploadSummaryBarInner = document.getElementById('uploadSummaryBarInner');
    const dt = new DataTransfer();
    let isUploading = false;

    function syncInputFiles() {
        fileInput.files = dt.files;
    }

    function revokePreviewUrls() {
        selectedFiles.querySelectorAll('[data-preview-url]').forEach((element) => {
            const url = element.getAttribute('data-preview-url');
            if (url) {
                URL.revokeObjectURL(url);
            }
        });
    }

    function resetSummary() {
        uploadSummary.classList.add('d-none');
        uploadSummaryText.textContent = '0 erfolgreich, 0 fehlerhaft, 0 gesamt';
        uploadSummaryBarInner.style.width = '0%';
    }

    function setSummary(total, uploaded, failed, currentPercent) {
        if (!total) {
            resetSummary();
            return;
        }

        uploadSummary.classList.remove('d-none');

        const overallPercent = Math.round((((uploaded + failed) + (currentPercent / 100)) / total) * 100);
        uploadSummaryText.textContent = uploaded + ' erfolgreich, ' + failed + ' fehlerhaft, ' + total + ' gesamt';
        uploadSummaryBarInner.style.width = Math.min(overallPercent, 100) + '%';
    }

    function renderFileList() {
        revokePreviewUrls();

        if (!dt.files.length) {
            selectedFiles.innerHTML = '<div class="text-muted small">Noch keine Dateien ausgewählt</div>';
            resetSummary();
            return;
        }

        const items = Array.from(dt.files).map((file, index) => {
            const previewUrl = URL.createObjectURL(file);
            const safeName = file.name.replace(/"/g, '&quot;');

            return `
                <div class="upload-preview-card" data-file-card="${index}">
                    <div class="upload-preview-thumb-wrap">
                        <div class="upload-item-overlay" data-overlay>
                            <div class="upload-item-overlay-top">
                                <span class="upload-item-status-text" data-status-text></span>
                                <span class="upload-item-percent" data-status-percent></span>
                            </div>
                            <div class="upload-item-progress">
                                <div class="upload-item-progress-inner" data-progress-inner style="width: 0%;"></div>
                            </div>
                        </div>

                        <img
                            src="${previewUrl}"
                            alt="${safeName}"
                            class="upload-preview-thumb"
                            data-preview-url="${previewUrl}"
                        >

                        <button
                            type="button"
                            class="upload-preview-remove"
                            data-remove-index="${index}"
                            aria-label="Bild entfernen"
                            title="Bild entfernen"
                        >&times;</button>
                    </div>

                    <div class="upload-item-message" data-status-message></div>
                </div>
            `;
        });

        selectedFiles.innerHTML = items.join('');
        resetSummary();

        selectedFiles.querySelectorAll('[data-remove-index]').forEach((button) => {
            button.addEventListener('click', function () {
                if (isUploading) {
                    return;
                }

                const removeIndex = Number(this.getAttribute('data-remove-index'));
                const files = Array.from(dt.files);

                dt.items.clear();

                files.forEach((file, index) => {
                    if (index !== removeIndex) {
                        dt.items.add(file);
                    }
                });

                syncInputFiles();
                renderFileList();
            });
        });
    }

    function setCardProgress(index, percent, statusText, message, stateClass) {
        const card = selectedFiles.querySelector('[data-file-card="' + index + '"]');
        if (!card) return;

        const overlay = card.querySelector('[data-overlay]');
        const statusTextNode = card.querySelector('[data-status-text]');
        const percentNode = card.querySelector('[data-status-percent]');
        const progressInner = card.querySelector('[data-progress-inner]');
        const messageNode = card.querySelector('[data-status-message]');

        if (overlay) {
            overlay.classList.add('is-visible');
        }

        if (statusTextNode) {
            statusTextNode.textContent = statusText || '';
        }

        if (percentNode) {
            percentNode.textContent = Math.round(percent) + '%';
        }

        if (progressInner) {
            progressInner.style.width = Math.max(0, Math.min(100, percent)) + '%';
            progressInner.classList.remove('is-success', 'is-error');

            if (stateClass === 'success') {
                progressInner.classList.add('is-success');
            } else if (stateClass === 'error') {
                progressInner.classList.add('is-error');
            }
        }

        if (messageNode) {
            if (stateClass === 'error') {
                messageNode.textContent = message || 'Upload fehlgeschlagen.';
            } else {
                messageNode.textContent = '';
            }
        }
    }

    function addFiles(fileList) {
        const existingNames = new Set(Array.from(dt.files).map((file) => file.name));
        const incoming = Array.from(fileList);

        for (const file of incoming) {
            if (dt.files.length >= 31) {
                break;
            }

            if (!existingNames.has(file.name)) {
                dt.items.add(file);
                existingNames.add(file.name);
            }
        }

        syncInputFiles();
        renderFileList();
    }

    function uploadSingleFile(file, index, total, state) {
        return new Promise(function (resolve) {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();

            formData.append('file', file);

            xhr.open('POST', uploadForm.getAttribute('action') || 'index.php?page=upload', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', function (event) {
                if (event.lengthComputable) {
                    const percent = (event.loaded / event.total) * 100;
                    setCardProgress(index, percent, 'Lädt', '', 'uploading');
                    setSummary(total, state.uploaded, state.failed, percent);
                }
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) {
                    return;
                }

                let response = null;

                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    response = null;
                }

                if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
                    state.uploaded++;
                    setCardProgress(index, 100, 'Fertig', '', 'success');
                } else {
                    state.failed++;

                    let serverMessage = 'Upload fehlgeschlagen.';
                    if (response && response.message) {
                        serverMessage = response.message;
                    } else if (xhr.responseText) {
                        serverMessage = xhr.responseText.substring(0, 200);
                    }

                    setCardProgress(index, 100, 'Fehler', serverMessage, 'error');
                }

                setSummary(total, state.uploaded, state.failed, 0);
                resolve();
            };

            setCardProgress(index, 0, 'Lädt', '', 'uploading');
            xhr.send(formData);
        });
    }

    async function startSequentialUpload() {
        if (isUploading || !dt.files.length) {
            return;
        }

        isUploading = true;
        startUpload.disabled = true;

        const files = Array.from(dt.files);
        const state = {
            uploaded: 0,
            failed: 0
        };

        setSummary(files.length, 0, 0, 0);

        for (let index = 0; index < files.length; index++) {
            await uploadSingleFile(files[index], index, files.length, state);
        }

        isUploading = false;
        startUpload.disabled = false;

        if (state.failed === 0 && state.uploaded === files.length) {
            window.location.href = 'index.php?page=rename';
        }
    }

    dropzone.addEventListener('click', function () {
        if (!isUploading) {
            fileInput.click();
        }
    });

    dropzone.addEventListener('keydown', function (event) {
        if ((event.key === 'Enter' || event.key === ' ') && !isUploading) {
            event.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function () {
        addFiles(fileInput.files);
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', function (event) {
        if (!isUploading) {
            addFiles(event.dataTransfer.files);
        }
    });

    startUpload.addEventListener('click', function () {
        startSequentialUpload();
    });

    renderFileList();
});
</script>