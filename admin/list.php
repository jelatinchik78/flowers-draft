<?php
defined('APP') or exit;

$counts = [
    'bouquet' => count($data['bouquets']),
    'flower'  => count($data['flowers']),
];
$updatedTs = strtotime((string) $data['updated']);
$updatedText = $updatedTs ? date('d.m.Y H:i', $updatedTs) : '—';
$addLabel = $type === 'flower' ? 'Добавить цветок' : 'Добавить букет';
$typeLabel = $type === 'flower' ? 'цветы поштучно' : 'букеты';
?>

<div class="page-head">
  <div>
    <h1>Каталог</h1>
    <p class="meta">Обновлено: <?= e($updatedText) ?>. Изменения сразу видны на сайте.</p>
  </div>
  <a class="btn" href="admin.php?view=edit&amp;type=<?= e($type) ?>"><?= e($addLabel) ?></a>
</div>

<nav class="tabs" aria-label="Разделы каталога">
  <a class="<?= $type === 'bouquet' ? 'active' : '' ?>" href="admin.php?type=bouquet">
    Букеты <span class="n"><?= (int) $counts['bouquet'] ?></span>
  </a>
  <a class="<?= $type === 'flower' ? 'active' : '' ?>" href="admin.php?type=flower">
    Цветы поштучно <span class="n"><?= (int) $counts['flower'] ?></span>
  </a>
</nav>

<p class="hint" style="margin-bottom:18px">
  <?= $type === 'flower'
      ? 'Цена за штуку. «Под заказ» скрывает цену и показывает пометку на сайте.'
      : 'Цена — розничная, за готовый букет. «Оптом» — необязательное поле, на сайт не выводится.' ?>
</p>

<?php if (!$items): ?>
  <div class="empty">Пока пусто. Нажмите «<?= e($addLabel) ?>», чтобы добавить первую позицию.</div>
<?php else: ?>
  <div class="list">
    <div class="row head">
      <span>Фото</span>
      <span>Название</span>
      <span>Цена</span>
      <span>Наличие</span>
      <span style="text-align:right">Действия</span>
    </div>

    <?php foreach ($items as $i => $item): ?>
      <div class="row">
        <?php if (!empty($item['img'])): ?>
          <img class="thumb" src="<?= e($item['img']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
        <?php else: ?>
          <div class="thumb ph">фото</div>
        <?php endif; ?>

        <div class="name">
          <b><?= e($item['name']) ?></b>
          <?php if ($type === 'bouquet' && !empty($item['desc'])): ?>
            <span><?= e($item['desc']) ?></span>
          <?php endif; ?>
        </div>

        <div class="price">
          <?= e(money((int) $item['price'])) ?>
          <?php if (!empty($item['priceWholesale'])): ?>
            <span>опт <?= e(money((int) $item['priceWholesale'])) ?></span>
          <?php endif; ?>
        </div>

        <div class="avail">
          <form method="post" action="admin.php">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="badge <?= !empty($item['available']) ? 'on' : 'off' ?>" type="submit"
                    title="Переключить наличие">
              <?= !empty($item['available']) ? 'в наличии' : 'под заказ' ?>
            </button>
          </form>
        </div>

        <div class="actions">
          <a class="act" href="admin.php?view=edit&amp;type=<?= e($type) ?>&amp;id=<?= e($item['id']) ?>">Изменить</a>

          <form method="post" action="admin.php">
            <input type="hidden" name="action" value="move">
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="act act-icon" type="submit" name="dir" value="up" title="Выше"
                    <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
            <button class="act act-icon" type="submit" name="dir" value="down" title="Ниже"
                    <?= $i === count($items) - 1 ? 'disabled' : '' ?>>↓</button>
          </form>

          <form method="post" action="admin.php"
                onsubmit="return confirm('Удалить эту позицию? Фото тоже будет удалено.');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="act act-icon danger" type="submit" title="Удалить">✕</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
