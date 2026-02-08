<?php

declare(strict_types=1);

use PhpSoftBox\Vite\Vite;

/** @var array $page */
/** @var string $rootId */
/** @var Vite|null $vite */
/** @var array|null $ssr */

$pageJson = htmlspecialchars(
    json_encode($page, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$rootId = htmlspecialchars($rootId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$props = is_array($page['props'] ?? null) ? $page['props'] : [];
$meta = is_array($props['meta'] ?? null) ? $props['meta'] : [];

$ssrData = is_array($ssr ?? null) ? $ssr : [];
$ssrHead = is_array($ssrData['head'] ?? null) ? $ssrData['head'] : [];
$ssrBody = is_string($ssrData['body'] ?? null) ? $ssrData['body'] : null;
$hasSsr = $ssrHead !== [] || $ssrBody !== null;

$title = is_string($meta['title'] ?? null) && $meta['title'] !== ''
    ? (string) $meta['title']
    : (is_string($props['title'] ?? null) ? (string) $props['title'] : null);

$description = is_string($meta['description'] ?? null) && $meta['description'] !== ''
    ? (string) $meta['description']
    : null;

$keywords = null;
if (isset($meta['keywords'])) {
    if (is_array($meta['keywords'])) {
        $keywords = implode(', ', array_values(array_filter($meta['keywords'], 'is_string')));
    } elseif (is_string($meta['keywords'])) {
        $keywords = $meta['keywords'];
    }
}

if ($title !== null) {
    $title = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if ($description !== null) {
    $description = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if ($keywords !== null) {
    $keywords = htmlspecialchars($keywords, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (!$hasSsr) : ?>
        <?php if ($title !== null) : ?>
            <title><?= $title ?></title>
            <meta property="og:title" content="<?= $title ?>">
        <?php endif; ?>
        <?php if ($description !== null) : ?>
            <meta name="description" content="<?= $description ?>">
            <meta property="og:description" content="<?= $description ?>">
        <?php endif; ?>
        <?php if ($keywords !== null) : ?>
            <meta name="keywords" content="<?= $keywords ?>">
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($ssrHead !== []) : ?>
        <?= implode("\n", array_filter($ssrHead, 'is_string')) ?>
    <?php endif; ?>
    <?php if (isset($vite)) : ?>
        <?= $vite->reactRefreshPreamble() ?>
        <?= $vite->tags('resources/js/app.tsx') ?>
    <?php endif; ?>
</head>
<body>
<div id="<?= $rootId ?>" data-page="<?= $pageJson ?>"><?= $ssrBody ?? '' ?></div>
</body>
</html>
