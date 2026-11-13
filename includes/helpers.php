<?php

declare(strict_types=1);


// declare a file full of helper functions so that we can keep our code DRY:
//
// D: DO NOT
// R: REPEAT
// Y: YOURSELF

/**
 * Nicely formatted var_dump for WAMP / MAMP 
 * installations without nicely formatted var_dump functions.
 *
 * @param mixed $something
 * @return void - 'void' is like 'null', but literally means "does not return anything at all"
 */
function prettyDump($something)
{
    echo '<pre><code>';
    var_dump($something);
    echo '</code></pre>';
}

/**
 * Nicely formatted var_dump for WAMP / MAMP 
 * installations without nicely formatted var_dump functions.
 * Exits after running
 *
 * @param mixed $something
 * @return void
 */
function prettyDumpAndExit($something)
{
    prettyDump($something);
    exit;
}
