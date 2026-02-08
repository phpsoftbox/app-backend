<?php

declare(strict_types=1);

use PhpSoftBox\Vite\Vite;

/** @var array $page */
/** @var string $rootId */
/** @var Vite|null $vite */

$pageJson = htmlspecialchars(
    json_encode($page, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$rootId = htmlspecialchars($rootId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (isset($vite)) : ?>
        <?= $vite->reactRefreshPreamble() ?>
        <?= $vite->tags('resources/js/app.tsx') ?>
    <?php endif; ?>
</head>
<body>
<div id="<?= $rootId ?>" data-page="<?= $pageJson ?>"></div>
</body>
</html>
