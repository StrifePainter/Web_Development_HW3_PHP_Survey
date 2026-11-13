<?php

declare(strict_types=1);

session_start();

// special keyword: __DIR__ is the directory of the file we are currently in.
// __DIR__ = /Applications/MAMP/htdocs/week_nine_inclass/includes/scripts
// ../ = go up a directory

// include optionally includes the code, does not throw errors (just warnings) if it's not found
//include __DIR__ . '/../hfsadshhghghghghgelpers.php';


// require REQUIRES that the file exists and can be imported, blocking your code from running
// if it cannot find that file / script or if it cannot execute that script.
//require __DIR__ . '/../helpers.php';

// if we 'require' a file multiple times
// and there is a method or class, etc. defined in that file
// we will get "cannot redeclare xyz" errors

// use 'require_once' instead of 'require' to enforce that a file is only imported one time.
require_once __DIR__ . '/../helpers.php';
// when in doubt, use require_once

// we only want to accept POST requests to this page; so we should block all request that are not post requests

$requestType = $_SERVER['REQUEST_METHOD'];

if ($requestType !== 'POST') {
    http_response_code(500);
    exit('Must be a POST request!');
}

$formInput = $_POST;

// store the submitted ($_POST) data in the session, so we can refer to the last
// submission on our form page.
$_SESSION['form_input'] = $formInput;

// let's validate
// name
// phone_number
// reason
// which_cat
// other_cats

$errors = [];

if (!array_key_exists('name', $formInput)) {
    $errors['name'] = 'Name must be filled out!';
}

$name = $formInput['name'];
// || = OR
if (strlen($name) < 3 || strlen($name) > 30) {
    $errors['name'] = 'Name must be more than 3 characters and less than 30';
}

if (!array_key_exists('phone_number', $formInput)) {
    $errors['phone_number'] = 'Phone number must be filled out!';
}
$phoneNumber = $formInput['phone_number'];


// email validation is a bit different from other types of validation

if (!array_key_exists('email', $formInput)) {
    $errors['email'] = 'Email is required!';
}

$email = $formInput['email'];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors['email'] = 'Email must be a correct format!';
}

// try to think about your form validation
// as if someone is trying to abuse your submit script
// with bad data. You should guard against
// using only values that are valid in your form,
// and throw errors if values passed are invalid

// if our other_cats was required, we could do this:
//if (!array_key_exists('other_cats', $formInput)) {
    //$errors['other_cats'] = 'Other cats interest is required!';
//}
//
// otherwise, if our other_cats is optional, we can get the value if it's there,
// or default to something sensible
$otherCats = $formInput['other_cats'] ?? [];

if (!is_array($otherCats)) {
    $errors['other_cats'] = 'Other cats must be an array!';
}

$validCats = ['bobby', 'sally', 'jimmy'];
if (count($otherCats) > count($validCats)) {
    $errors['other_cats'] = 'Other cats is invalid!';
}

foreach ($otherCats as $cat) {
    if (!in_array($cat, $validCats)) {
        $errors['other_cats'] = $cat . ' is not a valid cat!';
    }
}

// let say we're now done all of our validation
// let's communicate this back to the other page using the session


// $_SESSION is a special array variable that persists between pages if session_start()
// is called at the top of the page
$_SESSION['form_errors'] = $errors;


if (count($errors) === 0) {
    // if there are no errors, let's email the contents of the form.
    $to      = 'hello@email.com';
    $subject = 'New Adoption Request';
    $message = '';

    $message .= '<h1>New Adoption Request</h1>';
    $message .= '<ul>';
    $message .= '<li>Name: ' . $name . '</li>';
    $message .= '<li>Email: ' . $email . '</li>';
    $message .= '<li>Phone Number: ' . $phoneNumber . '</li>';
    $message .= '</ul>';
    $message .= '<p>Other Cats:</p>';
    $message .= '<ul>';

    foreach ($otherCats as $cat) {
        $message .= '<li>' . $cat . '</li>';
    }
    $message .= '</ul>';

    // on local setups (MAMP/WAMP, etc.) mail() typically does NOT actually
    // send email; because this requires a local mail server to be set up.
    // WAMP / MAMP DO NOT include a local mail server;
    // and setting one up is outside the scope of this class.
    //
    // Hosting servers will typically include a mail server, allowing this method to work!
    mail($to, $subject, $message);
}

// header() sets a header in the response
// the "Location:" header causes the browser to redirect
header('Location: /week_nine_inclass/adopt-form.php');

//prettyDump($formInput);


// Typically, when writing code in any programming language,
// I like to try and stick to the "four line function" """rule"""
//
// Which means - most functionality can be described in a function that is four lines or less.
// If your code is growing beyond four lines, you should often separate 
// out pieces of it into smaller functions
