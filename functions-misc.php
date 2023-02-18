<?php

namespace viccipher;

//
// Sequence Conversion and Chain Addition as described in the article

//
// Using array X to create a sequence containing 1..N
// but where "1" appears at the position the 'earliest' element of
// X appears at. Examples:
//
// Simple examples (simple because can be done with ascii)
// BABY = 2134
//  A is the first to appear in the alphabet so gets 1
//  B is the second to appear in the alphabet so gets 2
//   second B gets 3
//  Y is the fourth to appear in the alphabet so gets 4
//
// 7181 = 3142
//  1 is the first to appear in 1234567890, so gets 1
//  1 is the second to appear, so gets 2
//  7 is the third to appear, so gets 3
//  8 is the last to appear so gets 4
//
// Complex examples (Potentially requires more than ascii)
// 1792 2 18 2 = 3142
//  2 is the first to appear, so gets 1
//  2 is the second to appear, so gets 2
//  1792 is the third to appear, so gets 3
//  18 is the last to appear so gets 4
//
// Most uses of convering to sequences works conveniently on elements
// from 0 - 9 or on no more than ten alphabetic characters.  This first
// function handles these
function simpleConvert2Sequential($alphabet, $origString)
{
    // On occasion the text arrives in array format - convert it to string
    // first
    $string = $origString;
    if (is_array($origString)) {
        $string = '';
        for ($a = 0; $a < sizeof($origString); $a++) {
            $string .= $origString[$a];
        }
    }

    // And convert to a sequence
    $rv = array_fill(0, 10, 0);
    $nextnum = 1;
    // Loop through standard alphabet
    for ($a = 0; $a < mb_strlen($alphabet); $a++) {
        // Loop through string.
        for ($b = 0; $b < mb_strlen($string); $b++) {
            // If there's a match, assign next number % 10
            if (mb_substr($string, $b, 1) === mb_substr($alphabet, $a, 1)) {
                $rv[$b] = $nextnum % 10;
                $nextnum++;
            }
        }
    }
    return $rv;
}

//
// The tableaux being wider than ten characters requires an approach that can
// handle sequence members > 9. This code accomplishes same using arrays
function bigConvert2Sequential($numArr)
{
    // Find the maximum value used
    $sz = sizeof($numArr);
    $max = 0;
    for ($a = 1; $a < $sz; $a++) {
        if ($numArr[$a] === 0) {
            // Max digit 0 treated as 10, so gets placed last
            $numArr[$a] = 10;
        }
        if ($numArr[$a] > $max) {
            $max = $numArr[$a];
        }
    }

    // Brute forced, could probably be improved for genuinely large numbers
    $rv = array_fill(0, $sz, 0);
    $nextnum = 1;
    // Loop up from 0 to max
    for ($a = 0; $a <= $max; $a++) {
        // Loop through numArr for anyone matching.
        for ($b = 0; $b < $sz; $b++) {
            // If there's a match, assign next number
            if ($numArr[$b] === $a) {
                $rv[$b] = $nextnum;
                $nextnum++;
            }
        }
    }
    return $rv;
}

//
// Derive an arbitrarily long number from a shorter one
function chainAddition($digitsArr, $length)
{
    $idx = 0;
    do {
        $digitsArr[] = ($digitsArr[$idx] + $digitsArr[$idx + 1]) % 10;
        $idx++;
    } while (sizeof($digitsArr) < $length);

    return $digitsArr;
}

//
// Manipulating the halves of messages

//
// Swap the second half with the first half of the text, with НТ used to mark
// where that first half now starts
function swapHalves($text, $position = null)
{
    if ($position === null) {
        $len = mb_strlen($text);
        $position = random_int(0, $len - 1);
    }
    return mb_substr($text, $position) . PLACEHOLDER_НТ .
        mb_substr($text, 0, $position);
}

//
// НТ marks where the message should start: discard it and swap the halves back
function unswapHalves($stream)
{
    $startpos = mb_strpos($stream, PLACEHOLDER_НТ);
    $half2 = mb_substr($stream, 0, $startpos);
    $half1 = mb_substr($stream, $startpos + 1); // + 1 to skip marker
    return ($half1 . $half2);
}

//
// Remove given items from the original alphabet
// Cyrillic has three chars not used in the VIC Cipher
function constructUsableAlphabet($alphabet, $alphabetIgnore)
{
    if (mb_strlen($alphabetIgnore) > 0) {
        // Construct the acceptable parts of the alphabet by removing the known
        // ignorable entries. Yes, could have done that directly, but trying to
        // show process not shortest method
        for ($a = 0; $a < mb_strlen($alphabetIgnore) - 1; $a++) {
            $alphabet = mb_ereg_replace("[" . mb_substr($alphabetIgnore, $a) .
                                        "]", "", $alphabet);
        }
    }
    return $alphabet;
}

//
// Change each occurence of a number such that " 3 " becomes " НЦ333НЦ "
function encodeNumbers($text)
{
    for ($number = 0; $number <= 9; $number++) {
        $text = str_replace(strval($number), PLACEHOLDER_НЦ .
                            strval($number) . strval($number) .
                            strval($number) . PLACEHOLDER_НЦ, $text);
    }
    // Also change PLACEHOLDER_№
    $text = str_replace("№", PLACEHOLDER_№, $text);
    return $text;
}

//
// Extract a particular line from the poem, remove the white space, and return
// first 20 chars
function keyFromPoem($poem, $line)
{
    // Poem must be capitalised already
    // Take nth line
    $sentence = $poem[$line - 1];
    // Remove spaces
    $sentence = str_replace(" ", "", $sentence);
    // Take first 20 characters
    $sentence = mb_substr($sentence, 0, 20);
    // That's the key
    return $sentence;
}

//
// Modulo arithmetic forcing values to be positive
function modulo($a, $modulo)
{
    $rv = $a % $modulo;
    if ($rv < 0) {
        $rv += $modulo;
    }
    return $rv;
}

//
// Retrieve a particular command line option
function handleArgument($short, $long, $val, $default = null)
{
    $options = getopt($short, [$long . $val]);
    $opt = $default;
    $opt = (isset($options[$short])) ? $options[$short] : $opt;
    $opt = (isset($options[$long])) ? $options[$long] : $opt;
    return $opt;
}
