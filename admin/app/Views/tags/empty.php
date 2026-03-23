<?php

declare(strict_types=1);
?>
<main class="p-3 p-lg-4">
    <style>
        .tags-grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            color: #fff;
        }

        .tags-grid-table thead th {
            background: rgba(70, 125, 185, 0.58);
            border: 0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }

        .tags-grid-table tbody td {
            background: rgba(24, 41, 58, 0.72);
            border: 0;
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            vertical-align: middle;
            backdrop-filter: blur(2px);
        }

        .tags-grid-table .tag-id-cell {
            min-width: 120px;
            white-space: nowrap;
        }

        .tags-grid-table .tag-name-cell {
            min-width: 320px;
            word-break: break-word;
        }

        .tags-grid-table .tag-delete-cell {
            white-space: nowrap;
        }

        .tags-grid-table .icon-btn {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            text-decoration: none;
            background: transparent;
            transition: 0.15s ease-in-out;
        }

        .tags-grid-table .icon-btn i {
            font-size: 0.9rem;
            line-height: 1;
        }

        .tags-grid-table .icon-btn-delete {
            border: 1px solid #ff4d6d;
            color: #ff4d6d;
        }

        .tags-grid-table .icon-btn-delete:hover {
            background: rgba(255, 77, 109, 0.16);
            color: #fff;
        }

        .tags-grid-table .delete-form {
            margin: 0;
            display: inline-flex;
        }

        .tags-grid-table .delete-form button {
            cursor: pointer;
        }
    </style>

    <div class="glass-panel p-3 p-lg-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
            <h1 class="h4 mb-0">Leere Tags</h1>
            <div class="small text-white-50">Tags ohne zugehöriges Bild</div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= e((string)$message) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <?php if (empty($tags)): ?>
            <div class="alert alert-light mb-0">Keine leeren Tags vorhanden.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="tags-grid-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tagname</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tags as $tag): ?>
                            <?php
                            $rawTagName = (string)($tag['tag'] ?? '');
                            $cleanTagName = trim(html_entity_decode(strip_tags($rawTagName), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                            ?>
                            <tr>
                                <td class="tag-id-cell">
                                    <?= (int)$tag['id'] ?>
                                </td>
                                <td class="tag-name-cell">
                                    <?= e($cleanTagName !== '' ? $cleanTagName : '—') ?>
                                </td>
                                <td class="tag-delete-cell">
                                    <form
                                        method="post"
                                        action="index.php?page=empty-tags"
                                        class="delete-form"
                                        onsubmit="return confirm('Soll dieser leere Tag wirklich gelöscht werden?');"
                                    >
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$tag['id'] ?>">
                                        <button type="submit" class="icon-btn icon-btn-delete" title="Löschen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>