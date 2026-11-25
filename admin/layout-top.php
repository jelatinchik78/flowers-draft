<?php defined('APP') or exit; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($pageTitle) ?> — Flowers</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="top">
  <div class="wrap top-inner">
    <a class="brand" href="admin.php">Flowers</a>
    <div class="top-right">
      <a class="link" href="index.html" target="_blank" rel="noopener">Открыть сайт</a>
      <a class="link" href="admin.php?view=password">Пароль</a>
      <form method="post" action="admin.php">
        <input type="hidden" name="action" value="logout">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button class="link" type="submit">Выйти</button>
      </form>
    </div>
  </div>
</header>

<main class="wrap">

  <?php foreach ($flash as $item): ?>
    <div class="flash <?= e($item['type']) ?>"><?= e($item['text']) ?></div>
  <?php endforeach; ?>

  <?php foreach ($errors as $message): ?>
    <div class="flash error"><?= e($message) ?></div>
  <?php endforeach; ?>
