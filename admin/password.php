<?php defined('APP') or exit; ?>

<a class="back" href="admin.php">← К каталогу</a>

<div class="page-head" style="padding-top:0">
  <div>
    <h1>Пароль</h1>
    <p class="meta">Пароль нужен для входа в панель. Смените стартовый пароль на свой.</p>
  </div>
</div>

<form class="form" method="post" action="admin.php" style="max-width:420px;padding-bottom:60px">
  <input type="hidden" name="action" value="password">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <label class="field">
    <span>Текущий пароль</span>
    <input type="password" name="current" required autocomplete="current-password">
  </label>

  <label class="field">
    <span>Новый пароль</span>
    <input type="password" name="new" required minlength="8" autocomplete="new-password">
    <p class="file-note">Минимум 8 символов.</p>
  </label>

  <label class="field">
    <span>Повторите новый пароль</span>
    <input type="password" name="repeat" required minlength="8" autocomplete="new-password">
  </label>

  <div class="form-actions">
    <button class="btn" type="submit">Сохранить пароль</button>
    <a class="btn btn-ghost" href="admin.php">Отмена</a>
  </div>
</form>
