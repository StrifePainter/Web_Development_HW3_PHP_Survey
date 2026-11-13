<?php
//<?php is the tag that tells the web server to start running PHP code instead of rendering plain HTML.

session_start(); // session_start() is put before the HTML so it reads $_SESSION, which is how it reads errors on the page. Has to be first

// these are the option inputs
$options = [
    // picking single checkbox answers
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

    // These are checkboxes for multiple selections
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

// It retrieves temporary form data like errors, inputs, and success statuses from the session and clears it so it only shows once.
$errors  = $_SESSION['form_errors']  ?? [];
$old     = $_SESSION['form_input']   ?? [];
$success = $_SESSION['form_success'] ?? false;
 
$_SESSION['form_errors']  = [];
$_SESSION['form_input']   = [];
$_SESSION['form_success'] = false;

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <title>PHP Survey: Food</title>
</head>
<body>
    <header>
        <h1>Survey on Food</h1>
    </header>

    <main>
        <h2>How much do you like food?</h2>
        <?php if ($success): ?>
            <p class="success" role="status">Thanks! Your answers were submitted.</p> <!-- Checks if the last submission was successful. If true, it displays a success message. role="status" is for screen readers -->
        <?php elseif (count($errors) > 0): ?>
            <p class="error-summary" role="alert">
                Please fix the <?= count($errors) ?> highlighted question(s) below.
            </p>
        <?php endif; ?>

        <!-- this line links to the process php form-->
        <form id="survey-form" method="POST" action="includes/scripts/process_food_survey.php">
            <section aria-labelledby="about-you">
                <h3 id="about-you">About you</h3>

                <div class="field">
                    <label for="name">What is your name?</label> <!-- "name" is the key this value will be stored in on $_POST (the id is for the page, the name is for the server). looked up how to add accessibility aria labels for screen readers and whatnot in order for screenreaders to say if something is valid or invalid-->
                    <input type="text" id="name" name="name" autocomplete="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" <?php if (isset($errors['name'])): ?>aria-invalid="true" aria-describedby="name-error"<?php endif; ?>>
                    <!-- the name of ?? '' means “if there’s no old value (like on a first visit), use an empty string. htmlspecialchars() makes the text safe. If someone types <script>, it shows up as plain text instead of running as code. -->
                    <?php if (isset($errors['name'])): ?>
                        <p class="error" id="name-error"><?= htmlspecialchars($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="city">What city do you live in?</label>
                    <input type="text" id="city" name="city" autocomplete="address-level2"
                        value="<?= htmlspecialchars($old['city'] ?? '') ?>"
                        <?php if (isset($errors['city'])): ?>aria-invalid="true" aria-describedby="city-error"<?php endif; ?>>
                    <?php if (isset($errors['city'])): ?>
                        <p class="error" id="city-error"><?= htmlspecialchars($errors['city']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="fav_food">What is your favourite food?</label>
                    <input type="text" id="fav_food" name="fav_food"
                        value="<?= htmlspecialchars($old['fav_food'] ?? '') ?>"
                        <?php if (isset($errors['fav_food'])): ?>aria-invalid="true" aria-describedby="fav_food-error"<?php endif; ?>>
                    <?php if (isset($errors['fav_food'])): ?>
                        <p class="error" id="fav_food-error"><?= htmlspecialchars($errors['fav_food']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="fav_restaurant">What is your favourite restaurant?</label>
                    <input type="text" id="fav_restaurant" name="fav_restaurant"
                        value="<?= htmlspecialchars($old['fav_restaurant'] ?? '') ?>"
                        <?php if (isset($errors['fav_restaurant'])): ?>aria-invalid="true" aria-describedby="fav_restaurant-error"<?php endif; ?>>
                    <?php if (isset($errors['fav_restaurant'])): ?>
                        <p class="error" id="fav_restaurant-error"><?= htmlspecialchars($errors['fav_restaurant']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="fav_snack">What is your go-to snack?</label>
                    <input type="text" id="fav_snack" name="fav_snack"
                        value="<?= htmlspecialchars($old['fav_snack'] ?? '') ?>"
                        <?php if (isset($errors['fav_snack'])): ?>aria-invalid="true" aria-describedby="fav_snack-error"<?php endif; ?>>
                    <?php if (isset($errors['fav_snack'])): ?>
                        <p class="error" id="fav_snack-error"><?= htmlspecialchars($errors['fav_snack']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="never_eat">What food would you never eat?</label>
                    <input type="text" id="never_eat" name="never_eat"
                        value="<?= htmlspecialchars($old['never_eat'] ?? '') ?>"
                        <?php if (isset($errors['never_eat'])): ?>aria-invalid="true" aria-describedby="never_eat-error"<?php endif; ?>>
                    <?php if (isset($errors['never_eat'])): ?>
                        <p class="error" id="never_eat-error"><?= htmlspecialchars($errors['never_eat']) ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <section aria-labelledby="preferences">
                <h3 id="preferences">Your preferences</h3>

                <!-- using fieldset to group inputs together as part of the same question;-->
                <fieldset <?php if (isset($errors['allergies'])): ?>aria-invalid="true" aria-describedby="allergies-error"<?php endif; ?>>
                    <legend>Do you have any allergies or food restrictions that prevent you from eating certain foods?</legend>
                    <!-- foreach is a loop: "for each option in this list, build one radio button". $value is the short code that gets submitted, $label is the words the user reads. All radios in a group share the same name so only one can be picked -->
                    <?php foreach ($options['allergies'] as $value => $label): ?>
                        <div class="choice">
                            <!-- id is unique per option so its label can attach to it. The last line adds "checked" if this option was the user's old answer. -->
                            <input type="radio" id="allergies-<?= $value ?>" name="allergies"
                                value="<?= $value ?>"
                                <?= ($old['allergies'] ?? '') === (string) $value ? 'checked' : '' ?>>
                            <label for="allergies-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['allergies'])): ?>
                        <p class="error" id="allergies-error"><?= htmlspecialchars($errors['allergies']) ?></p>
                    <?php endif; ?>
                </fieldset>

                <fieldset <?php if (isset($errors['meal'])): ?>aria-invalid="true" aria-describedby="meal-error"<?php endif; ?>>
                    <legend>Which meal is your favourite?</legend>
                    <?php foreach ($options['meal'] as $value => $label): ?>
                        <div class="choice">
                            <input type="radio" id="meal-<?= $value ?>" name="meal"
                                value="<?= $value ?>"
                                <?= ($old['meal'] ?? '') === (string) $value ? 'checked' : '' ?>>
                            <label for="meal-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['meal'])): ?>
                        <p class="error" id="meal-error"><?= htmlspecialchars($errors['meal']) ?></p>
                    <?php endif; ?>
                </fieldset>

                <fieldset <?php if (isset($errors['cooking'])): ?>aria-invalid="true" aria-describedby="cooking-error"<?php endif; ?>>
                    <legend>Do you prefer cooking at home or eating out?</legend>
                    <?php foreach ($options['cooking'] as $value => $label): ?>
                        <div class="choice">
                            <input type="radio" id="cooking-<?= $value ?>" name="cooking"
                                value="<?= $value ?>"
                                <?= ($old['cooking'] ?? '') === (string) $value ? 'checked' : '' ?>>
                            <label for="cooking-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['cooking'])): ?>
                        <p class="error" id="cooking-error"><?= htmlspecialchars($errors['cooking']) ?></p>
                    <?php endif; ?>
                </fieldset>

                <fieldset <?php if (isset($errors['spice'])): ?>aria-invalid="true" aria-describedby="spice-error"<?php endif; ?>>
                    <legend>How spicy do you like your food?</legend>
                    <?php foreach ($options['spice'] as $value => $label): ?>
                        <div class="choice">
                            <input type="radio" id="spice-<?= $value ?>" name="spice"
                                value="<?= $value ?>"
                                <?= ($old['spice'] ?? '') === (string) $value ? 'checked' : '' ?>>
                            <label for="spice-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['spice'])): ?>
                        <p class="error" id="spice-error"><?= htmlspecialchars($errors['spice']) ?></p>
                    <?php endif; ?>
                </fieldset>
            </section>

            <section aria-labelledby="favourites">
                <h3 id="favourites">Pick all that apply</h3>

                <fieldset <?php if (isset($errors['foods'])): ?>aria-invalid="true" aria-describedby="foods-error"<?php endif; ?>>
                    <legend>Which of the following foods do you enjoy?</legend>
                    <?php foreach ($options['foods'] as $value => $label): ?>
                        <div class="choice">
                            <!-- Adding brackets [] to the name tells PHP to collect all checked items as a list, so in_array can check which ones were previously selected. -->
                            <input type="checkbox" id="foods-<?= $value ?>" name="foods[]"
                                value="<?= $value ?>"
                                <?= in_array((string) $value, $old['foods'] ?? [], true) ? 'checked' : '' ?>>
                            <label for="foods-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['foods'])): ?>
                        <p class="error" id="foods-error"><?= htmlspecialchars($errors['foods']) ?></p>
                    <?php endif; ?>
                </fieldset>

                <fieldset <?php if (isset($errors['cuisines'])): ?>aria-invalid="true" aria-describedby="cuisines-error"<?php endif; ?>>
                    <legend>Which cuisines would you like to try more often?</legend>
                    <?php foreach ($options['cuisines'] as $value => $label): ?>
                        <div class="choice">
                            <input type="checkbox" id="cuisines-<?= $value ?>" name="cuisines[]"
                                value="<?= $value ?>"
                                <?= in_array((string) $value, $old['cuisines'] ?? [], true) ? 'checked' : '' ?>>
                            <label for="cuisines-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['cuisines'])): ?>
                        <p class="error" id="cuisines-error"><?= htmlspecialchars($errors['cuisines']) ?></p>
                    <?php endif; ?>
                </fieldset>

                <fieldset <?php if (isset($errors['drinks'])): ?>aria-invalid="true" aria-describedby="drinks-error"<?php endif; ?>>
                    <legend>Which drinks do you have in a typical week?</legend>
                    <?php foreach ($options['drinks'] as $value => $label): ?>
                        <div class="choice">
                            <input type="checkbox" id="drinks-<?= $value ?>" name="drinks[]"
                                value="<?= $value ?>"
                                <?= in_array((string) $value, $old['drinks'] ?? [], true) ? 'checked' : '' ?>>
                            <label for="drinks-<?= $value ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($errors['drinks'])): ?>
                        <p class="error" id="drinks-error"><?= htmlspecialchars($errors['drinks']) ?></p>
                    <?php endif; ?>
                </fieldset>
            </section>

            <section aria-labelledby="stories">
                <h3 id="stories">Tell us more</h3>

                <div class="field">
                    <label for="recipe">Could you share the recipe for your favourite meal?</label>
                    <!-- A textarea stores previous text between its opening and closing tags instead of a value attribute, while rows="5" sets its starting height. It'll let the user write more in the box for long term answers -->
                    <textarea id="recipe" name="recipe" rows="5"
                        <?php if (isset($errors['recipe'])): ?>aria-invalid="true" aria-describedby="recipe-error"<?php endif; ?>><?= htmlspecialchars($old['recipe'] ?? '') ?></textarea>
                    <?php if (isset($errors['recipe'])): ?>
                        <p class="error" id="recipe-error"><?= htmlspecialchars($errors['recipe']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="memory">Describe your most memorable meal and why it stuck with you.</label>
                    <textarea id="memory" name="memory" rows="5"
                        <?php if (isset($errors['memory'])): ?>aria-invalid="true" aria-describedby="memory-error"<?php endif; ?>><?= htmlspecialchars($old['memory'] ?? '') ?></textarea>
                    <?php if (isset($errors['memory'])): ?>
                        <p class="error" id="memory-error"><?= htmlspecialchars($errors['memory']) ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <section aria-labelledby="extras">
                <h3 id="extras">A few extras</h3>

                <div class="field">
                    <label for="last_new_food">When did you last try a new food?</label>
                    <!-- type="date" shows a calendar picker. max = today's date so the picker greys out the
                        future, but that's only a browser hint (someone could still send a future date
                        by tampering with the form), so the PHP validator also has to reject it -->
                    <input type="date" id="last_new_food" name="last_new_food"
                        max="<?= date('Y-m-d') ?>"
                        value="<?= htmlspecialchars($old['last_new_food'] ?? '') ?>"
                        <?php if (isset($errors['last_new_food'])): ?>aria-invalid="true" aria-describedby="last_new_food-error"<?php endif; ?>>
                    <?php if (isset($errors['last_new_food'])): ?>
                        <p class="error" id="last_new_food-error"><?= htmlspecialchars($errors['last_new_food']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="food_colour">What colour best matches your favourite food?</label>
                    <!-- type="color" shows a colour picker. It always has a colour, so if there's no old
                         answer we start it at orange. Googled how to input with colour and these examples -->
                    <input type="color" id="food_colour" name="food_colour"
                        value="<?= htmlspecialchars($old['food_colour'] ?? '#ff6600') ?>"
                        <?php if (isset($errors['food_colour'])): ?>aria-invalid="true" aria-describedby="food_colour-error"<?php endif; ?>>
                    <?php if (isset($errors['food_colour'])): ?>
                        <p class="error" id="food_colour-error"><?= htmlspecialchars($errors['food_colour']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="recipe_site">What is your favourite recipe website?</label>
                    <!-- type="url" is a text box meant for web addresses; placeholder is the example texet that will be shown -->
                    <input type="url" id="recipe_site" name="recipe_site" placeholder="https://example.com"
                        value="<?= htmlspecialchars($old['recipe_site'] ?? '') ?>"
                        <?php if (isset($errors['recipe_site'])): ?>aria-invalid="true" aria-describedby="recipe_site-error"<?php endif; ?>>
                    <?php if (isset($errors['recipe_site'])): ?>
                        <p class="error" id="recipe_site-error"><?= htmlspecialchars($errors['recipe_site']) ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <button type="submit">Submit survey</button>
            <!-- submission button to send to the scripts -->
        </form>
    </main>
</body>
</html>