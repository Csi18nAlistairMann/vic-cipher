<?php

namespace viccipher;

//
// Checkerboard class
class Checkerboard
{
    private $cb = null;
    private $editable = true;
    // Associative array such that it maps characters to their coords
    private $cbAarr = array();
    private $padding;
    private $language;

    //
    // The padding can be numeric eg "2142", or alphabetic eg "QGQG" such that
    // the code needs to convert them first. Doing this ensures an unlikely
    // combo of letters forms the padding even though the Checkerboard isn't
    // known
    public function getPadding()
    {
        // If the padding is wholly numeric, return it.
        // If not convert the alphabetic form to numeric according to the
        // current checkerboard, store it for next time, AND then return it
        if (strval(intval($this->padding)) !== $this->padding) {
            $padding = '';
            for ($a = 0; $a < mb_strlen($this->padding); $a++) {
                $padding .= $this->checkerBoardSubstituteOne($this->padding[$a]);
            }
            $this->padding = $padding;
        }
        return $this->padding;
    }

    //
    // Language refers to the name of the alphabet. "en" ends up as English
    // whether user uses British English or not.
    public function setPadding($val, $language)
    {
        // If no padding given ...
        if ($val === "") {
            // "2 1 4" seemed to have been chosen to be unlikely. The following
            // seeks to name the unlikely character combinations in each
            // language
            switch ($language) {
            case LANG_ROMAN:
                $val = 'QGQG';
                break;
            case LANG_CYRILLIC:
                $val = 'ЭАЭА';
                break;
            default:
            }
        }
        $this->padding = $val;
        $this->language = $language;
    }

    //
    // fillBody() takes an array of characters and places them in the
    // checkerboard start at Row 2, in the given column, and filling down and
    //  right where there are free spaces
    public function fillBody($startx, $data)
    {
        // setup
        $starty = 2; // all entries start in second row
        $row = VIC_CHECKERBOARD_WIDTH * $starty;
        $pos = $row + $startx; // increment $pos until we find an empty cell

        // Keep going until no work is done
        $donef = false;
        do {
            if ($data === array()) {
                // Finish if we run out of characters to add
                $donef = true;
            } else {
                // Convert $pos into co-ords
                $y = intval($pos / VIC_CHECKERBOARD_WIDTH);
                $x = $pos % VIC_CHECKERBOARD_WIDTH;

                if ($this->cb[$y][$x] === CHECKERBOARD_DEFAULT_VAL) {
                    // Place next character only if the cell is free
                    $this->cb[$y][$x] = array_shift($data);
                }
                // Next character goes down a row
                $pos += VIC_CHECKERBOARD_WIDTH;
                // If down a row is off the bottom, go back up and right one
                //  column
                if ($pos >= VIC_CHECKERBOARD_WIDTH * VIC_CHECKERBOARD_HEIGHT) {
                    $pos -= (3 * VIC_CHECKERBOARD_WIDTH);
                    $pos++;
                }
            }
        } while (!$donef);
    }

    //
    // initialise() does the initial set up of the checkerboard in this order:
    // processing chars / key / usable alphabet / 'repeat' symbol. By using this
    // order we can arrange later characters around earlier ones, per the book
    public function __construct($width, $height, $controlChars, $derivations,
                                $padding, $language)
    {
        // Unlock the class for editing (used to support a quick lookup)
        $this->editable = true;

        // Store the padding to be used later
        $this->setPadding($padding, $language);

        // PHP doesn't have constrained arrays, so we'll fake one
        $this->cb = array_fill(0, $width,
                               array_fill(0, $height,
                                          CHECKERBOARD_DEFAULT_VAL));

        // Fill in the control chars: "message starts", "change to/from
        // numeric", etc
        if ($controlChars !== null) {
            for ($a = 0; $a < sizeof($controlChars); $a++) {
                $data = $controlChars[$a];
                $x = array_shift($data);
                $this->fillBody($x, $data);
            }
        }

        // Now do the characters in the key
        $x = 1;
        $y = 1;
        $idx = 0;
        while ($idx < mb_strlen($derivations->getKey1())) {
            $char = mb_substr($derivations->getKey1(), $idx, 1);
            $this->cb[$y][$x] = $char;
            $x++;
            $idx++;
        }

        // Remove the key's characters from the usable alphabet
        $smlAlphab = constructUsableAlphabet($derivations->getAlphabetUsable(),
                                             $derivations->getKey1());

        // Continue with the usable alphabet
        if ($smlAlphab !== null) {
            $alphabetArr = array();
            for ($a = 0; $a < mb_strlen($smlAlphab); $a++) {
                $alphabetArr[] = mb_substr($smlAlphab, $a, 1);
            }
            $x = 1;
            $this->fillBody($x, $alphabetArr);
        }

        // Add the "repeat" symbol in the bottom right most cell
        $this->cb[VIC_CHECKERBOARD_HEIGHT - 1][VIC_CHECKERBOARD_WIDTH - 1] =
            PLACEHOLDER_ПВТ;

        // Now use the breeder to populate the top and left
        $breeder = $derivations->getCheckerboardBreeder();
        for ($a = 1; $a <= 10; $a++) {
            $this->cb[0][$a] = $breeder[$a - 1];
        }
        $this->cb[2][0] = $this->cb[0][8];
        $this->cb[3][0] = $this->cb[0][9];
        $this->cb[4][0] = $this->cb[0][10];
    }

    //
    // Return the substitution for a single character
    public function checkerboardSubstituteOne($char)
    {
        // Make lookups easier: first time through create an associative array
        // and use that thereafter
        if ($this->editable === true) {
            $this->editable = false;

            // Handle the top row with single digit coords
            for ($a = 1; $a < VIC_CHECKERBOARD_WIDTH; $a++) {
                $c = $this->cb[1][$a];
                if ($c !== CHECKERBOARD_DEFAULT_VAL) {
                    $this->cbAarr[$c] = $this->cb[0][$a];
                }
            }

            // Handle the remaining rows with double digit coords
            for ($row = 2; $row < VIC_CHECKERBOARD_HEIGHT; $row++) {
                for ($a = 1; $a < VIC_CHECKERBOARD_WIDTH; $a++) {
                    $c = $this->cb[$row][$a];
                    if ($c !== CHECKERBOARD_DEFAULT_VAL) {
                        $this->cbAarr[$c] = $this->cb[$row][0] .
                            $this->cb[0][$a];
                    }
                }
            }
        }

        // Digits are not substituted at all; otherwise the character is
        // substituted per the associative array. Any remaining characters are
        //  silently dropped
        $rv = '';
        if ($char >= "0" && $char <= "9") {
            $rv = $char;
        } else {
            if (array_key_exists($char, $this->cbAarr)) {
                $rv = $this->cbAarr[$char];
            }
        }
        return ($rv);
    }

    //
    // Return the substitution for a stream of text having padded it out. The
    // choices behind "2 1 4" are not clear from the book, so I've assumed only
    // the first "2" is actioned for being a null, and the remainder are
    // ignored. It seems to me that cryptographers might choose to vary the
    //  content rather than repeat it, so "2142" would be the maximum padding
    //  used
    public function checkerboardSubstitution($text)
    {
        // Loop the string substituting one at a time
        $output = '';
        for ($idx = 0; $idx <= mb_strlen($text); $idx++) {
            $c = mb_substr($text, $idx, 1);
            $d = $this->checkerboardSubstituteOne($c);
            $output .= $d;
        }

        // We now know the final ciphertext length, so pad it out to a fit the
        // five groups scheme
        $output .= substr($this->getPadding(), 0,
                          FIVEGROUP_NUM - (strlen($output) % FIVEGROUP_NUM));
        return $output;
    }

    // Turn the coords back into plaintext
    public function unsubstitutions($stream)
    {
        // Make up an associative array of the coords with their various
        // plaintext equivalents
        // Single digit row first
        $unsub = array();
        for ($a = 1; $a < VIC_CHECKERBOARD_WIDTH; $a++) {
            $unsub[$this->cb[0][$a]] = $this->cb[1][$a];
        }

        // Doubles after
        $lcoordStr = '';
        for ($row = 2; $row < VIC_CHECKERBOARD_HEIGHT; $row++) {
            $lcoord = $this->cb[$row][0];
            $lcoordStr .= "$lcoord";
            for ($a = 1; $a < VIC_CHECKERBOARD_WIDTH; $a++) {
                if ($this->cb[$row][$a] !== CHECKERBOARD_DEFAULT_VAL) {
                    $unsub[($lcoord * 10) + $this->cb[0][$a]] =
                        $this->cb[$row][$a];
                }
            }
        }

        //
        // There's no nice way to do this: this bodge removes the padding text,
        // try to remove successively shorter matches. It's possible there's
        // no padding, but not possible for more than four
        $padding = $this->getPadding();
        if (substr($stream, -4) === $padding) {
            $stream = substr($stream, 0, -4);
        } elseif (substr($stream, -3) === substr($padding, 0, 3)) {
            $stream = substr($stream, 0, -3);
        } elseif (substr($stream, -2) === substr($padding, 0, 2)) {
            $stream = substr($stream, 0, -2);
        } elseif (substr($stream, -1) === substr($padding, 0, 1)) {
            $stream = substr($stream, 0, -1);
        }

        // Now convert the stream to coords
        $idx = 0;
        $coordStream = array();
        $doingNumerics = false;
        $allLegal = true;
        do {
            // Each time through we take whatever the CB says exists. If we
            // come across the НЦ marker, we change to handling numbers until
            // such time as we see another НЦ.
            if (strpos($lcoordStr, $stream[$idx]) !== false) {
                // a double digit coord
                $coordLen = 2;
                $c = substr($stream, $idx, $coordLen);
                $lcoord = $c[0] * 10;
                $tcoord = $c[1];
                // If the coord isn't in the associative array, the stream is
                // corrupt and/or the credentials are wrong
                if (!array_key_exists($lcoord + $tcoord, $unsub)) {
                    $allLegal = false;
                    break 1;
                } elseif ($unsub[$lcoord + $tcoord] === PLACEHOLDER_НЦ) {
                    $doingNumerics = !$doingNumerics;
                }
            } else {
                // this is a single digit coord
                $coordLen = 1;
                $c = substr($stream, $idx, $coordLen);
                $lcoord = 0;
                $tcoord = $c[0];
            }

            // Record the plain text and look to the next coord
            $coordStream[] = $c;
            $idx += $coordLen;

            // If the next coord will be a numeric, handle them separately
            while ($doingNumerics) {
                $coordLen = 3;
                $c = substr($stream, $idx, $coordLen);
                $coordStream[] = $c[0]; // three chars should match
                $idx += 3;

                // Keep doing more numerics until we see another НЦ
                $c = substr($stream, $idx, 2);
                $lcoord = $c[0] * 10;
                $tcoord = $c[1];
                // Again check against legal but invalid cooords
                if (!array_key_exists($lcoord + $tcoord, $unsub)) {
                    $allLegal = false;
                    break 2;
                } elseif ($unsub[$lcoord + $tcoord] === PLACEHOLDER_НЦ) {
                    break;
                }
            }
        } while (($idx < strlen($stream)) && ($allLegal === true));

        // If we previously discovered legal but invalid chars, skip the
        // remainder of the substitutions
        if ($allLegal === false) {
            $rv = false;
        } else {
            // Now convert the coords back to plaintext
            $idx = 0;
            $text = '';
            do {
                $coord = $coordStream[$idx];
                // Again check the coord actually exists before access.
                if (!array_key_exists($coord, $unsub)) {
                    $allLegal = false;
                    break 1;
                } elseif ($unsub[$coord] !== PLACEHOLDER_НЦ) {
                    $char = $unsub[$coord];
                    $text .= $char;
                } else {
                    // On НЦ we record the numbers until we see the next НЦ
                    $idx++;
                    do {
                        $char = $coordStream[$idx];
                        $text .= $char;
                        $idx++;
                        // Last check for legal but invalid characters
                        if (!array_key_exists($coordStream[$idx], $unsub)) {
                            $allLegal = false;
                            break 2;
                        } else {
                            $char = $unsub[$coordStream[$idx]];
                        }
                    } while ($char !== PLACEHOLDER_НЦ);
                }
                $idx++;
            } while ($idx < sizeof($coordStream));

            // If we found cause to terminate early deal with that first
            // otherwise return the substituted coords
            if ($allLegal === false) {
                $rv = false;
            } else {
                //
                // While we're here, we can swap No. too
                $text = mb_ereg_replace('[' . PLACEHOLDER_№ . "]", "№", $text);
                $rv = $text;
            }
        }
        return $rv;
    }

    //
    // Display the completed checkerboard
    private function debugShowCheckerboard()
    {
        for ($row = 0; $row < sizeof($this->cb); $row++) {
            for ($col = 0; $col < sizeof($this->cb[$row]); $col++) {
                if ($this->cb[$row][$col] === CHECKERBOARD_DEFAULT_VAL) {
                    $char = ' ';
                } else {
                    $char = $this->cb[$row][$col];
                }
                $char = mb_substr($char . '   ', 0, 3);
                echo $char;
            }
            echo "\n";
        }
        exit;
    }
}
