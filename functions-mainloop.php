<?php

namespace viccipher;

//
// Run the major tasks of this program

// Encipher
function encipher($plaintext, $swappos, $d, $cb, $tt1, $tt2, $msgnumKeygroup)
{
    // Encipher
    // Process plaintext
    $plaintextNumbers = encodeNumbers($plaintext);
    $plaintextChopped = swapHalves($plaintextNumbers, $swappos);
    $plaintextCheckerboarded =
        $cb->checkerboardSubstitution($plaintextChopped);
    // Bodged? Height is int(cipherLength / table width). Does that interact
    // with the disruption areas properly?
    $cipherLength = strlen($plaintextCheckerboarded);
    $tt2->setCipherLength($cipherLength);
    $tt1->fillTableauxDuringEncrypt($plaintextCheckerboarded);
    $plaintextTransposed1 = $tt1->getTransposed();
    $tt2->fillTableauxDuringEncrypt($plaintextTransposed1);
    $plaintextTransposed2 = $tt2->getTransposed();
    $ciphertext = FiveDigitGroups::fiveGroups($plaintextTransposed2,
                                              $msgnumKeygroup,
                                              $d->getMessageNumberPosition());
    return $ciphertext;
}

// Decipher
function decipher($padding, $cb, $tt1, $tt2, $ciphertextArr)
{
    // Decipher
    $ciphertextStr = FiveDigitGroups::fiveGroupsArr2Str($ciphertextArr);
    $tt2->fillTableauxDuringDecrypt($ciphertextStr);
    $undisruptedStream = $tt2->undisruptTableaux();
    $tt1->fillTableauxDuringDecrypt($undisruptedStream);
    $fig2stream = $tt1->readOutByRows();
    $plaintextStr1 = $cb->unsubstitutions($fig2stream, $padding);
    $plaintext = ($plaintextStr1 === false)
        ? false
        : unswapHalves($plaintextStr1);
    return $plaintext;
}

// With the command line dealt with, now get the proper work done
function mainloop($alphabet, $alphabetIgnore, $key1, $key2, $key3, $key4,
                  $msgnumKeygroup, $direction, $randomSwapPos, $padding,
                  $message)
{
    $alphabet_usable = constructUsableAlphabet($alphabet, $alphabetIgnore);

    // Set up tables
    $d = new Derivations($alphabet_usable, $key1);
    // If we're about to decipher, we need the message id before we get the
    // checkerboard populated. Enciphering doesn't have that limitation
    if ($direction === DECIPHER) {
        $fdgs = new FiveDigitGroups($message, $key3[7]);
        $msgnumKeygroup = $fdgs->getMsgNumKeygroup();
        $ciphertextArr = $fdgs->getCiphertextArr();
    }
    $d->doDerivations($alphabet_usable, $key1, $key2, $key3, $key4,
                      $msgnumKeygroup);
    $tt1 = new TranspositionTableaux(TABLEAUX_TYPE_1, $d);
    $tt2 = new TranspositionTableaux(TABLEAUX_TYPE_2, $d);
    $cb = new Checkerboard(VIC_CHECKERBOARD_HEIGHT, VIC_CHECKERBOARD_WIDTH,
                           CHECKERBOARD_CONTROL_CHARS, $d, $padding);

    // Process
    $rv = 0;
    if ($direction === ENCIPHER || $direction == CHAIN) {
        $enciphered = encipher($message, $randomSwapPos, $d, $cb, $tt1, $tt2,
                               $msgnumKeygroup);
        echo $enciphered . "\n";
    }

    if ($direction === DECIPHER || $direction === CHAIN) {
        if ($direction === CHAIN) {
            // While, dear reader, we already know the message ID and have the
            // derivations set up, we want to catch errors: so reobtain the
            // message ID from where we just encrypted it, and rerun the
            // derivations because that's what it would affect
            $fdgs = new FiveDigitGroups($enciphered, $key3[7]);
            $msgnumKeygroup = $fdgs->getMsgNumKeygroup();
            $ciphertextArr = $fdgs->getCiphertextArr();
            $d->doDerivations($alphabet_usable, $key1, $key2, $key3, $key4,
                              $msgnumKeygroup);
        }
        $deciphered = decipher($padding, $cb, $tt1, $tt2, $ciphertextArr);
        if ($deciphered === false) {
            echo BAD_UNSUBSTITUTIONS_ERROR;
        } else {
            echo "MsgID: $msgnumKeygroup\n$deciphered\n";
        }

        // Handle if a decryption error was seen
        if ($deciphered === false) {
            $rv = -1;
        } elseif ($direction === CHAIN) {
            // Otherwise consider if the decryption matches what it should
            $originalSquashed = mb_ereg_replace('[\s]', '', $message);
            if ($originalSquashed === $deciphered) {
                echo "Passed!\n";
                $rv = 0;
            } else {
                echo "Failed\nOriginal:\n";
                var_dump($message);
                echo "Deciphered:\n";
                var_dump($deciphered);
                $rv = 1;
            }
        }
    }
    return $rv;
}
