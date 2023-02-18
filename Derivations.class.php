<?php

namespace viccipher;

//
// Derivations class uses the factors known to the agent to reconstruct the
// constants used in creating the cipher system: positions, widths, and
// breeders.
class Derivations
{
    private $messageNumberPosition;
    private $widthTableux1;
    private $widthTableux2;
    private $tableaux1Breeder;
    private $tableaux2Breeder;
    private $checkerboardBreeder;
    private $alphabetUsable;
    private $key1;

    //
    // Elective point to derive the various data
    public function doDerivations($alphabet, $word, $poem, $date, $id, $msgNum)
    {
        // The "date" key is used twice, first to indicate the position from the
        // end at which the message number is to be inserted, and then develop
        // to Line C
        $this->date = '';
        for ($a = 0; $a < mb_strlen($date); $a++) {
            if ($date[$a] >= "0" && $date[$a] <= "9") {
                $this->date .= $date[$a];
            }
        }
        $this->messageNumberPosition = $this->date[5];

        // Upto Line C
        // 20818 - 39194 = 91724
        // Message number + first part of date = 91724
        // chain addition 91724 out to ten digits = 9172408964
        $linec = array();
        for ($a = 0; $a < 5; $a++) {
            $linec[$a] = modulo(($msgNum[$a] - $this->date[$a]), 10);
            if ($linec[$a] < 0) {
                $linec[$a] += 10;
            }
        }
        $lineCca = chainAddition($linec, 10);

        // Upto Line H
        // Divide poem in two & obtain each's sequence conversion
        $poeml = mb_substr($poem, 0, 10);
        $poemr = mb_substr($poem, 10, 10);
        $lineEl = simpleConvert2Sequential($alphabet, $poeml);
        $lineEr = simpleConvert2Sequential($alphabet, $poemr);

        // G = (C + El) % 10
        $lineG = array_fill(0, 10, '');
        for ($a = 0; $a < 10; $a++) {
            $lineG[$a] = ($lineCca[$a] + $lineEl[$a]) % 10;
        }

        // H = G mapped to Er - poem right side seq.conv.
        $this->lineH = array_fill(0, 10, '');
        for ($a = 0; $a < 10; $a++) {
            $this->lineH[$a] = $lineEr[modulo(($lineG[$a] - 1), 10)];
        }

        // Upto Line J
        // J = sequential key to H
        $lineJ = simpleConvert2Sequential(NUMERIC_ALPHABET, $this->lineH);

        // Upto Lines K-P
        // Use Line H to get a temporary table I've called k2pCube,
        $k2pStream = chainAddition($this->lineH, 60);
        $this->k2pCube = array();
        for ($a = 0; $a < 6; $a++) {
            $this->k2pCube[] = array_slice($k2pStream, $a * 10, 10);
        }

        // Use the k2pCube with the agent's ID to determine the widths
        $this->widthTableaux1 = ($id + $this->k2pCube[5][8 - 1]);
        $this->widthTableaux2 = ($id + $this->k2pCube[5][9 - 1]);
        $bothWidths = $this->widthTableaux1 + $this->widthTableaux2;

        // Upto Lines Q, R
        // Obtain a stream using the columns in sequence order
        $qnr = array();
        $idx = 1;
        do {
            for ($a = 0; $a < 10; $a++) {
                if ($lineJ[$a] === $idx) {
                    for ($b = 1; $b < 6; $b++) {
                        $qnr[] = $this->k2pCube[$b][$a];
                    }
                }
            }
            $idx++;
        } while (sizeof($qnr) < $bothWidths);

        // Use that stream to create the breeders for both tableaux
        $this->tableaux1Breeder = array_slice($qnr, 0, $this->widthTableaux1);
        $this->tableaux2Breeder = array_slice($qnr, $this->widthTableaux1,
                                              $this->widthTableaux2);

        // Upto Line S
        // And finally use the last row of the k2pCube to form a conversion
        // sequence which will populate the checkerboard's breeder
        $this->checkerboardBreeder = simpleConvert2Sequential(NUMERIC_ALPHABET,
                                                              $this->k2pCube[5]);
    }

    // As the message ID is not available without processing, do the lightest
    // of work here
    public function __construct($alphabet, $word)
    {
        // Store some for later availability to the Checkerboard
        $this->alphabetUsable = $alphabet;
        $this->key1 = $word;
    }

    // 20818 gets placed where
    public function getMessageNumberPosition()
    {
        return $this->messageNumberPosition;
    }

    // 96033...
    public function getTableaux1Breeder()
    {
        return $this->tableaux1Breeder;
    }

    // 30274...
    public function getTableaux2Breeder()
    {
        return $this->tableaux2Breeder;
    }

    // 17
    public function getWidthTableaux1()
    {
        return $this->widthTableaux1;
    }

    // 14
    public function getWidthTableaux2()
    {
        return $this->widthTableaux2;
    }

    // 50738...
    public function getCheckerboardBreeder()
    {
        return $this->checkerboardBreeder;
    }

    // АБВГДЕЖЗИКЛМНОПРСТУФХЦЧШЩЫЬЭЮЯ
    public function getAlphabetUsable()
    {
        return $this->alphabetUsable;
    }

    // СНЕГОПА
    public function getKey1()
    {
        return $this->key1;
    }
}
