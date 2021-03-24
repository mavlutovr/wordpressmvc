<div id="js-update-content-by-table">

  <h2>Обновление контента по таблице</h2>

  <?php if (isset($data['errors'][0])): ?>
    <?php foreach($data['errors'] as $error): ?>
    <p style="color: red;"><?= $error ?></p>
    <?php endforeach; ?>
  <?php endif; ?>

  <?= $data['collsText'] ?>

  <?= $data['form'] ?>

</div>