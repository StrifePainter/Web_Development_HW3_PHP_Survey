<?php

declare(strict_types=1);

//Survey processing script. The declare makes PHP check data types, shows errors if the wrong type of data instead of guessing.

session_start();

// for some reason it crashes if I don't have this repeated; googled solution and it is because it can't be on two separated pages
// the other possible solution would be to add the third php like in week 9 example with $options = require __DIR__
$options = [

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


function readString(array $input, string $key): string // It takes the array input and a string key to look up.
{  
    $value = $input[$key] ?? '';
    return is_string($value) ? trim($value) : ''; //This will then try to get the input key and then breaks down the function safely  to retrieve a value using a fallback, verifying that it is a string, and trims extra whitespace (googled solution how to get rid of white spaces)
}

function readStringList(array $input, string $key): array // This function will look up a key in an array, verifying it is an array to make sure it's a clean list of the string elements.
{
    $value = $input[$key] ?? [];
    if (!is_array($value)) {
        return [];
    }
    return array_values(array_filter($value, 'is_string'));
}

// these are validators; this specific is for creating minimum and max for typing answers
function validateTextLength(string $value, int $min, int $max, string $label): ?string
{
    $length = mb_strlen($value);

    if ($length === 0) { // if statements to showcase message when it reaches these conditions
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

function validateOneOf(string $value, array $allowedOptions, string $label): ?string
{
    if ($value === '') { // more if statements. If the value is completely empty, it stops and returns an error message telling the user they must make a selection for that specific label.
        return "Please choose an answer for {$label}.";
    }
    if (!array_key_exists($value, $allowedOptions)) {
        return "{$label}: that option isn't available.";
    }

    return null;
}

function validateAllOf(array $values, array $allowedOptions, string $label, bool $required): ?string
{
    if ($required && count($values) === 0) { // If the field is marked as required ($required = true) and the user didn't select anything (count($values) === 0), it will stop and returns an error message.
        return "Please choose at least one answer for {$label}."; 
    }
    if (count($values) > count($allowedOptions)) { // using greater and lesser signs to have a range of submission 
        return "{$label}: too many answers were submitted.";
    }
    foreach ($values as $value) {
        if (!array_key_exists($value, $allowedOptions)) {
            return "{$label}: one of the options isn't available.";
        }
    }

    return null;
}

function validatePastDate(string $value, string $label): ?string // had to google how to input a proper date in order to check that a date is real, correctly formatted, and isn't beyond the current date.
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

function validateHexColour(string $value, string $label): ?string // googled how to have the atypical question. This format lets only # and 6 numbers through.
{
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        return "{$label} must be a valid colour.";
    }

    return null;
}

function validateUrl(string $value, int $max, string $label): ?string // googled how to check for adding a website for atypical question. This gives if statements when a web address is present, not too long, and a real http(s) URL.
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


if ($_SERVER['REQUEST_METHOD'] !== 'POST') { //only want to accept POST requests to this page, so block everything else
    http_response_code(405); // 405 means that it's not allowed! Short number code
    exit('Must be a POST request!');
}

// These are the text related questions
$textFields = [
    'name' => ['label' => 'Name', 'min' => 2,  'max' => 50],
    'city' => ['label' => 'City', 'min' => 2,  'max' => 50],
    'fav_food' => ['label' => 'Favourite food', 'min' => 2,  'max' => 50],
    'fav_restaurant' => ['label' => 'Favourite restaurant', 'min' => 2,'max' => 60],
    'fav_snack' => ['label' => 'Go-to snack', 'min' => 2, 'max' => 50],
    'never_eat' => ['label' => 'Food you would never eat', 'min' => 2, 'max' => 50],
    'recipe' => ['label' => 'Recipe', 'min' => 10, 'max' => 1000],
    'memory' => ['label' => 'Memorable meal', 'min' => 10, 'max' => 1000],
];

$radioFields = [
    'allergies' => 'Allergies and restrictions',
    'meal'      => 'Favourite meal',
    'cooking'   => 'Cooking preference',
    'spice'     => 'Spice level',
];

// Checkbox questions for having at least one picked
$checkboxFields = [
    'foods'    => ['label' => 'Foods you enjoy',  'required' => true],
    'cuisines' => ['label' => 'Cuisines to try',  'required' => true],
    'drinks'   => ['label' => 'Weekly drinks',    'required' => false],
];

// $input holds the cleaned answers and thee errors hold one message per field that failed
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

// the 3 atypical fields, this is where it becomes validated to its retrospective question
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
    // $_SESSION persists between pages, so index.php can read these.
    $_SESSION['form_errors']  = $errors;
    $_SESSION['form_input']   = $input;
    $_SESSION['form_success'] = false;
    header('Location: ../../index.php'); // googled this; The header('Location: ../../index.php') line sends the browser back to the form page after the script finishes checking the answers, and exit; stops the script from running any further
    exit;
}

$to      = 'hello@email.com'; // this is for sending the email that gets sent when the survey is good
$subject = 'New Food Survey Submission';
$message = "New survey submission\n\n";

foreach ($textFields as $key => $field) {
    $message .= $field['label'] . ': ' . $input[$key] . "\n";
}

foreach ($radioFields as $key => $label) {
    $message .= $label . ': ' . $options[$key][$input[$key]] . "\n";
}

foreach ($checkboxFields as $key => $field) {
    $chosen = array_map(fn(string $value): string => $options[$key][$value], $input[$key]);
    $message .= $field['label'] . ': ' . (count($chosen) > 0 ? implode(', ', $chosen) : 'None') . "\n";
}

$message .= 'Last new food date: ' . $input['last_new_food'] . "\n";
$message .= 'Food colour: ' . $input['food_colour'] . "\n";
$message .= 'Recipe website: ' . $input['recipe_site'] . "\n";

// This will log it instead of showing them an error
$mailSent = mail($to, $subject, $message, 'Content-Type: text/plain; charset=UTF-8');

if (!$mailSent) {
    error_log('Survey email could not be sent (expected on a local setup).');
}

// clearing the old answers so the form comes back empty and when it's a success
$_SESSION['form_errors']  = [];
$_SESSION['form_input']   = [];
$_SESSION['form_success'] = true;

header('Location: ../../index.php');
exit;