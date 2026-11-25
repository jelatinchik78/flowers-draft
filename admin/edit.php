<?php
defined('APP') or exit;

$isNew = $isNew ?? ($editItem['id'] === '');
$word = type_word($type);
$listUrl = 'admin.php?type=' . $type;
?>

<a class="back" href="<?= e($listUrl) ?>">← К списку</a>

<div class="page-head" style="padding-top:0">
  <div>
    <h1><?= $isNew ? 'Новый ' . e($word) : 'Редактирование' ?></h1>
    <p class="meta"><?= $isNew ? 'Заполните название и цену, фотографию можно добавить сразу или позже.' : e($editItem['name']) ?></p>
  </div>
</div>

<div class="edit-grid">

  <form class="form" method="post" action="admin.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <input type="hidden" name="id" value="<?= e($editItem['id']) ?>">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <label class="field">
      <span>Название</span>
      <input type="text" name="name" maxlength="80" required
             value="<?= e($editItem['name']) ?>" placeholder="<?= $type === 'flower' ? 'Роза, 60 см' : 'Букет «Классика»' ?>">
    </label>

    <?php if ($type === 'bouquet'): ?>
      <label class="field">
        <span>Описание <span style="text-transform:none;letter-spacing:0">— необязательно</span></span>
        <textarea name="desc" maxlength="160" placeholder="25 роз 60 см, крафт"><?= e($editItem['desc'] ?? '') ?></textarea>
      </label>
    <?php endif; ?>

    <div class="grid-2">
      <label class="field">
        <span>Цена, ₽</span>
        <input type="number" name="price" min="0" step="1" required
               value="<?= $editItem['price'] > 0 ? (int) $editItem['price'] : '' ?>" placeholder="1200">
      </label>

      <label class="field">
        <span>Цена оптом, ₽ <span style="text-transform:none;letter-spacing:0">— необязательно</span></span>
        <input type="number" name="priceWholesale" min="0" step="1"
               value="<?= $editItem['priceWholesale'] > 0 ? (int) $editItem['priceWholesale'] : '' ?>" placeholder="—">
      </label>
    </div>

    <label class="check">
      <input type="checkbox" name="available" value="1" <?= !empty($editItem['available']) ? 'checked' : '' ?>>
      В наличии
    </label>

    <label class="field">
      <span>Фотография</span>
      <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
      <p class="file-note">JPEG, PNG или WebP до 15 МБ. Повернём по EXIF и уменьшим до 1600 px по длинной стороне.</p>
    </label>

    <?php if (!empty($editItem['img'])): ?>
      <label class="check">
        <input type="checkbox" name="remove_img" value="1">
        Удалить текущее фото
      </label>
    <?php endif; ?>

    <div class="form-actions">
      <button class="btn" type="submit"><?= $isNew ? 'Добавить' : 'Сохранить' ?></button>
      <a class="btn btn-ghost" href="<?= e($listUrl) ?>">Отмена</a>
    </div>
  </form>

  <aside class="preview">
    <div class="pic">
      <?php if (!empty($editItem['img'])): ?>
        <img src="<?= e($editItem['img']) ?>" alt="<?= e($editItem['name']) ?>">
      <?php else: ?>
        <div class="ph">фото не загружено</div>
      <?php endif; ?>
    </div>
    <p>На сайте фотография показывается в пропорции 4:5 — по центру, без искажений. Лучше всего подходят вертикальные снимки.</p>

    <?php if (!$isNew): ?>
      <form method="post" action="admin.php" style="margin-top:16px"
            onsubmit="return confirm('Удалить эту позицию? Фото тоже будет удалено.');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="id" value="<?= e($editItem['id']) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button class="act danger" type="submit">Удалить позицию</button>
      </form>
    <?php endif; ?>
  </aside>

</div>
