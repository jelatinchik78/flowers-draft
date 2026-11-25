<?php defined('APP') or exit; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Вход — Flowers</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <a class="brand" href="index.html">Flowers</a>
    <h1>Панель управления</h1>
    <p class="sub">Цены, наличие и фотографии каталога</p>

    <?php foreach ($flash as $item): ?>
      <div class="flash <?= e($item['type']) ?>"><?= e($item['text']) ?></div>
    <?php endforeach; ?>

    <form class="form" method="post" action="admin.php">
      <input type="hidden" name="action" value="login">
      <label class="field">
        <span>Пароль</span>
        <input type="password" name="password" required autofocus autocomplete="current-password">
      </label>
      <button class="btn" type="submit">Войти</button>
    </form>
  </div>
</div>
</body>
</html>
