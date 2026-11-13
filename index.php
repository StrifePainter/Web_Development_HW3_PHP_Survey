<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>PHP Survey</title>
</head>
<body>
    <header>
        <h1>Survey on Food</h1>
    </header>

    <main>
        <h2>How much do you like food?</h2>
    <form method="post" action="" novalidate>
      <div class="field">
        <label for="name">What is your name?</label>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($old['name'] ?? '') ?>"
               aria-describedby="name-error"
               <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
        <?php if (isset($errors['name'])): ?>
          <p class="error" id="name-error" role="alert"><?= $errors['name'] ?></p>
        <?php endif; ?>
      </div>
    </main>
</body>
</html>