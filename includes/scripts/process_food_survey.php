<?php

declare(strict_types=1);

/**
 * Survey processing script.
 *  
 * The form in index.php posts here. This script never shows anything itself: it
 * validates the answers, stores the result in the session, optionally emails the
 * answers, and redirects back to the form (Post-Redirect-Get). Redirecting means
 * refreshing the form page can't accidentally re-submit the survey.
 */

// session_start() must run before any output; $_SESSION is how we pass errors and
// the user's answers back to index.php
session_start();

// The allowed values for every radio / checkbox question, as 'value' => 'label'.
//   - the key (left) is the short code that gets submitted and validated
//   - the value (right) is the words the user reads on the page
// The outer key ('meal', 'foods', ...) matches the name of the input in the form.
// The form builds its inputs from this list, and the processing script checks submitted
// values against the same list so a tampered value that was never offered gets rejected.
// !! The same array is at the top of index.php. If you change an option here,
// !! change it there too.
$options = [

    // ===== Radio buttons (single-select: the user picks ONE) =====
    'allergies' => [
        'yes'     => 'Yes',
        'no'      => 'No',
        'yes_but' => "Yes, but that won't stop me!",
    ],
    'meal' => [
        'breakfast' => 'Breakfast',
        'lunch'     => 'Lunch',
        'dinner'    => 'Dinner',
        'snacks'    => 'Snacks',
    ],
    'cooking' => [
        'home'    => 'Cooking at home',
        'out'     => 'Eating out',
        'takeout' => 'Takeout / delivery',
        'mix'     => 'A bit of everything',
    ],
    'spice' => [
        'none'   => 'No spice, please',
        'mild'   => 'Mild',
        'medium' => 'Medium',
        'hot'    => 'Hot',
    ],

    // ===== Checkboxes (multi-select: the user can pick SEVERAL) =====
    'foods' => [
        'pizza'   => 'Pizza',
        'sushi'   => 'Sushi',
        'tacos'   => 'Tacos',
        'pasta'   => 'Pasta',
        'burgers' => 'Burgers',
        'salad'   => 'Salad',
    ],
    'cuisines' => [
        'italian'  => 'Italian',
        'mexican'  => 'Mexican',
        'japanese' => 'Japanese',
        'indian'   => 'Indian',
        'chinese'  => 'Chinese',
        'greek'    => 'Greek',
    ],
    'drinks' => [
        'water'  => 'Water',
        'juice'  => 'Juice',
        'coffee' => 'Coffee',
        'tea'    => 'Tea',
        'soda'   => 'Soda',
        'milk'   => 'Milk',
    ],
];

/* ------------------------------------------------------------------ */
/* Reading input safely                                               */
/* ------------------------------------------------------------------ */

/**
 * Reads one text value from the submitted data and trims it.
 *
 * Why not just use $_POST['name']: a missing key causes a warning, and a
 * tampered request (e.g. name[]=x) sends an ARRAY where we expect text, which
 * would crash our string-typed validators under strict_types. Returning '' for
 * anything that isn't a string means the validator then reports a normal
 * "required" error instead. trim() runs first so a value that is only spaces
 * counts as empty.
 */
function readString(array $input, string $key): string
{
    $value = $input[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

/**
 * Reads a checkbox group (an array of values) from the submitted data.
 *
 * Unticked checkboxes send nothing at all, so a missing key means "nothing
 * ticked" and becomes an empty array. Anything in the array that isn't a string
 * is dropped, because a real browser only ever sends strings; this keeps later
 * code (including the sticky-value check in index.php) from crashing on junk.
 *
 * @return string[]
 */
function readStringList(array $input, string $key): array
{
    $value = $input[$key] ?? [];

    if (!is_array($value)) {
        return [];
    }

    return array_values(array_filter($value, 'is_string'));
}

/* ------------------------------------------------------------------ */
/* Validators (return null if valid, otherwise an error message)      */
/* ------------------------------------------------------------------ */

/**
 * Checks that a text answer is present and between $min and $max characters.
 *
 * Gives a different message for empty, too short and too long, so the user
 * knows exactly what to fix. mb_strlen() is used instead of strlen() because
 * strlen() counts bytes, so a name like "Zoë" would be counted as 4 characters
 * instead of 3 and could be wrongly rejected. The $max also protects the
 * email / server from huge submissions.
 */
function validateTextLength(string $value, int $min, int $max, string $label): ?string
{
    $length = mb_strlen($value);

    if ($length === 0) {
        return "{$label} is required.";
    }
    if ($length < $min) {
        return "{$label} is too short (minimum {$min} characters).";
    }
    if ($length > $max) {
        return "{$label} is too long (maximum {$max} characters).";
    }

    return null;
}

/**
 * Checks that a single-choice answer (radio buttons) was picked AND is one of
 * the options we actually offered.
 *
 * $allowedOptions is one question's 'value' => 'label' array from $options
 * above, so we check against its keys. Checking only "is it not empty" would let someone
 * edit the form in their browser and submit a value we never offered, which is
 * what this prevents. An unticked radio group sends nothing, so '' means
 * "not answered".
 */
function validateOneOf(string $value, array $allowedOptions, string $label): ?string
{
    if ($value === '') {
        return "Please choose an answer for {$label}.";
    }
    if (!array_key_exists($value, $allowedOptions)) {
        return "{$label}: that option isn't available.";
    }

    return null;
}

/**
 * Checks a multiple-choice answer (checkboxes): every ticked value must be a
 * real option, and optionally at least one must be ticked.
 *
 * $required lets one function cover both kinds of question. The
 * "more values than options" check catches someone sending the same valid value
 * many times, and each remaining value is matched against the allowed list just
 * like validateOneOf() does for a single value.
 *
 * @param string[] $values
 */
function validateAllOf(array $values, array $allowedOptions, string $label, bool $required): ?string
{
    if ($required && count($values) === 0) {
        return "Please choose at least one answer for {$label}.";
    }
    if (count($values) > count($allowedOptions)) {
        return "{$label}: too many answers were submitted.";
    }
    foreach ($values as $value) {
        if (!array_key_exists($value, $allowedOptions)) {
            return "{$label}: one of the options isn't available.";
        }
    }

    return null;
}

/**
 * Checks that a date is real, correctly formatted (YYYY-MM-DD), and not in the
 * future or absurdly far in the past.
 *
 * This is for "when did you LAST do something" questions, so a future date can't
 * be true. The browser's date picker already sets max=today, but that is only a
 * hint and can be bypassed, so the server has to check too. createFromFormat()
 * alone happily "fixes" impossible dates (2026-02-31 becomes March 3), so we
 * format the result back to text and compare it to the input; if they differ,
 * the date wasn't real. The leading "!" resets the time to midnight so today
 * itself is allowed.
 */
function validatePastDate(string $value, string $label): ?string
{
    if ($value === '') {
        return "{$label} is required.";
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    if ($date === false || $date->format('Y-m-d') !== $value) {
        return "{$label} must be a real date (YYYY-MM-DD).";
    }
    if ($date > new DateTimeImmutable('today')) {
        return "{$label} can't be in the future.";
    }
    if ($date < new DateTimeImmutable('1900-01-01')) {
        return "{$label} is too far in the past.";
    }

    return null;
}

/**
 * Checks that a colour is a 6-digit hex code like #ff6600.
 *
 * That is exactly what <input type="color"> submits, so anything else means the
 * form was tampered with. The pattern allows only "#" followed by six
 * hexadecimal characters, which also guarantees no markup can sneak through.
 */
function validateHexColour(string $value, string $label): ?string
{
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        return "{$label} must be a valid colour.";
    }

    return null;
}

/**
 * Checks that a web address is present, not too long, and a real http(s) URL.
 *
 * filter_var() checks the overall shape of the URL, but it also accepts things
 * like "javascript:alert(1)" or "ftp://...". Those aren't websites, and a
 * javascript: link is dangerous if the address is ever turned into a clickable
 * link, so we additionally require the scheme to be http or https.
 */
function validateUrl(string $value, int $max, string $label): ?string
{
    if ($value === '') {
        return "{$label} is required.";
    }
    if (mb_strlen($value) > $max) {
        return "{$label} is too long (maximum {$max} characters).";
    }

    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

    if (filter_var($value, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
        return "{$label} must be a valid web address starting with http:// or https://.";
    }

    return null;
}

// we only want to accept POST requests to this page, so block everything else
// (for example someone typing the address into the browser)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 = "method not allowed"
    exit('Must be a POST request!');
}

/* ------------------------------------------------------------------ */
/* What we expect from the form                                       */
/* ------------------------------------------------------------------ */

// Every text-style question, with its label (used in error messages) and its
// length limits. The two long-answer textareas live here too, since they're
// checked the same way, just with a bigger maximum.
$textFields = [
    'name'           => ['label' => 'Name',                  'min' => 2,  'max' => 50],
    'city'           => ['label' => 'City',                  'min' => 2,  'max' => 50],
    'fav_food'       => ['label' => 'Favourite food',        'min' => 2,  'max' => 50],
    'fav_restaurant' => ['label' => 'Favourite restaurant',  'min' => 2,  'max' => 60],
    'fav_snack'      => ['label' => 'Go-to snack',           'min' => 2,  'max' => 50],
    'never_eat'      => ['label' => 'Food you would never eat', 'min' => 2, 'max' => 50],
    'recipe'         => ['label' => 'Recipe',                'min' => 10, 'max' => 1000],
    'memory'         => ['label' => 'Memorable meal',        'min' => 10, 'max' => 1000],
];

// Radio questions (exactly one answer): input name => label for error messages
$radioFields = [
    'allergies' => 'Allergies and restrictions',
    'meal'      => 'Favourite meal',
    'cooking'   => 'Cooking preference',
    'spice'     => 'Spice level',
];

// Checkbox questions (any number of answers): input name => label + whether at
// least one box must be ticked. Drinks is optional to show both cases.
$checkboxFields = [
    'foods'    => ['label' => 'Foods you enjoy',  'required' => true],
    'cuisines' => ['label' => 'Cuisines to try',  'required' => true],
    'drinks'   => ['label' => 'Weekly drinks',    'required' => false],
];

// $input holds the cleaned answers (stored in the session so the form can refill);
// $errors holds one message per field that failed
$input  = [];
$errors = [];

foreach ($textFields as $key => $field) {
    $input[$key] = readString($_POST, $key);
    $error = validateTextLength($input[$key], $field['min'], $field['max'], $field['label']);
    if ($error !== null) {
        $errors[$key] = $error;
    }
}

foreach ($radioFields as $key => $label) {
    $input[$key] = readString($_POST, $key);
    $error = validateOneOf($input[$key], $options[$key], $label);
    if ($error !== null) {
        $errors[$key] = $error;
    }
}

foreach ($checkboxFields as $key => $field) {
    $input[$key] = readStringList($_POST, $key);
    $error = validateAllOf($input[$key], $options[$key], $field['label'], $field['required']);
    if ($error !== null) {
        $errors[$key] = $error;
    }
}

// the 3 atypical fields, checked to its retrospective question
$input['last_new_food'] = readString($_POST, 'last_new_food');
if (($error = validatePastDate($input['last_new_food'], 'Last new food date')) !== null) {
    $errors['last_new_food'] = $error;
}

$input['food_colour'] = readString($_POST, 'food_colour');
if (($error = validateHexColour($input['food_colour'], 'Food colour')) !== null) {
    $errors['food_colour'] = $error;
}

$input['recipe_site'] = readString($_POST, 'recipe_site');
if (($error = validateUrl($input['recipe_site'], 200, 'Recipe website')) !== null) {
    $errors['recipe_site'] = $error;
}

if (count($errors) > 0) {
    // $_SESSION persists between pages, so index.php can read these. We store the cleaned $input, not the raw $_POST, so only values we expect get printed back
    $_SESSION['form_errors']  = $errors;
    $_SESSION['form_input']   = $input;
    $_SESSION['form_success'] = false;

    // header() sets a response header; "Location:" makes the browser redirect.
    // exit stops the script right here. Without it PHP keeps running after the
    // redirect header and would carry on to the email code below
    header('Location: ../../index.php');
    exit;
}

$to      = 'hello@email.com'; // TODO: change to your own email address
$subject = 'New Food Survey Submission';
$message = "New survey submission\n\n";

foreach ($textFields as $key => $field) {
    $message .= $field['label'] . ': ' . $input[$key] . "\n";
}

foreach ($radioFields as $key => $label) {
    // swap the submitted code (e.g. "yes_but") for the readable label
    $message .= $label . ': ' . $options[$key][$input[$key]] . "\n";
}

foreach ($checkboxFields as $key => $field) {
    $chosen = array_map(fn(string $value): string => $options[$key][$value], $input[$key]);
    $message .= $field['label'] . ': ' . (count($chosen) > 0 ? implode(', ', $chosen) : 'None') . "\n";
}

$message .= 'Last new food date: ' . $input['last_new_food'] . "\n";
$message .= 'Food colour: ' . $input['food_colour'] . "\n";
$message .= 'Recipe website: ' . $input['recipe_site'] . "\n";

// log it instead of showing them an error; on a hosting server with a mail server, this call works as-is
$mailSent = mail($to, $subject, $message, 'Content-Type: text/plain; charset=UTF-8');

if (!$mailSent) {
    error_log('Survey email could not be sent (expected on a local setup).');
}

// success: clear the old answers so the form comes back empty, and flag the success
$_SESSION['form_errors']  = [];
$_SESSION['form_input']   = [];
$_SESSION['form_success'] = true;

header('Location: ../../index.php');
exit;