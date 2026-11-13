
<?php
/**
 * Survey form page.
 *
 * This page only DISPLAYS the form. Submissions are posted to
 * includes/process_survey.php, which validates them and redirects back here
 * (Post-Redirect-Get). Errors and the user's previous answers travel through
 * the session so that refreshing this page never re-submits the form.
 */
session_start(); // session_start() is put before the HTML so it reads $_SESSION, which is how it reads errors on the page

$options = require __DIR__ . '/includes/survey_options.php'; //

// It retrieves temporary form data like errors, inputs, and success statuses from the session and clears it so it only shows once.
$errors  = $_SESSION['form_errors']  ?? [];
$old     = $_SESSION['form_input']   ?? [];
$success = $_SESSION['form_success'] ?? false;
 
$_SESSION['form_errors']  = [];
$_SESSION['form_input']   = [];
$_SESSION['form_success'] = false;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <title>PHP Survey: Food</title>
</head>
<body>
    <a class="skip-link" href="#survey-form">Skip to the survey</a>
 
    <header>
        <h1>Survey on Food</h1>
    </header>
 
    <main>
        <h2>How much do you like food?</h2>
 
        <?php if ($success): ?>
            <p class="success" role="status">Thanks! Your answers were submitted.</p>
        <?php elseif (count($errors) > 0): ?>
            <p class="error-summary" role="alert">
                Please fix the <?= count($errors) ?> highlighted question(s) below.
            </p>
        <?php endif; ?>
 
        <form id="survey-form" method="post" action="includes/process_survey.php" novalidate>
 
            <section aria-labelledby="about-you">
                <h3 id="about-you">About you</h3>
                <?php
                renderInput($errors, $old, 'name', 'What is your name?', 'text', ['autocomplete' => 'name']);
                renderInput($errors, $old, 'city', 'What city do you live in?', 'text', ['autocomplete' => 'address-level2']);
                renderInput($errors, $old, 'fav_food', 'What is your favourite food?');
                renderInput($errors, $old, 'fav_restaurant', 'What is your favourite restaurant?');
                renderInput($errors, $old, 'fav_snack', 'What is your go-to snack?');
                renderInput($errors, $old, 'never_eat', 'What food would you never eat?');
                ?>
            </section>
 
            <button type="submit">Submit survey</button>
        </form>
    </main>
</body>
</html>
 