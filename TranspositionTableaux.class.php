<?php

namespace viccipher;

//
// TranspositionTableux class
// VIC Cipher uses two - this class implements both
//  Type 1 is the straightforward Figure 3 table
//  Type 2 is the Figure 4 "disrupted areas" table
class TranspositionTableaux
{
    private $type;
    private $tableaux;
    private $disruption;
    private $width;
    private $height;

    //
    // Type 2 tableaux have disruption areas: calculate where they should be and
    // store them as index into the row where found. As stands, code generates
    // all disruption areas for each column, however in the book example only
    // the first nine disruption areas are used
    //
    // Disruption areas can be calculated from row[1]
    // - start with column 1
    // - On row[2], the disrupted area starts immediately under the 1 above
    // - that disrupted area always moves right one column for each
    //   additional row
    // - when 0 disrupted area is seen in a row, the next disruption starts
    //   where row[2] has the value 2
    public function generateDisruptionData()
    {
        $col = 1;
        $row = 2;
        // Traverse the columns in order 1, 2, ... N
        do {
            $found = false;
            // Is this the column N?
            for ($a = 0; $a < $this->width; $a++) {
                if ($this->tableaux[1][$a] === $col) {
                    // Yes it is. $a starts the disruption data
                    $found = true;
                    for ($b = $a; $b < $this->width + 1; $b++) {
                        $this->disruption[$row++] = $b;
                    }
                }
            }
            $col++;
            // <= and + 1 to guarantee an edge case doesn't matter
        } while ($found === true && $row <= $this->height + 1);
    }

    //
    // I'm sure it isn't ideal but the disruption table could be shorter than
    // the tableaux: extend the former to match "as if" there's no disruption
    private function disruptionTableExpand() {
        $r = ($this->height - sizeof($this->disruption)) + 2;
        if ($r > 0) {
            $this->disruption = array_merge($this->disruption,
                                            array_fill(0, $r, $this->width));
        }
    }

    //
    // The two transposition tableaux fill up in different ways - this method
    // implements both
    public function fillTableauxDuringEncrypt($stream)
    {
        // Both start at top left, with first character in stream
        $streamIdx = 0;
        $row = 2;
        $this->tableaux = array_slice($this->tableaux, 0, 2);
        if ($this->type === TABLEAUX_TYPE_1) {
            // Straight forward left to right, top to bottom
            do {
                $line = substr($stream, $streamIdx, $this->width);
                for ($a = 0; $a < strlen($line); $a++) {
                    $this->tableaux[$row][$a] = intval(substr($line, $a, 1));
                }
                $row++;
                $streamIdx += $this->width;
            } while ($streamIdx < strlen($stream));
        } elseif ($this->type === TABLEAUX_TYPE_2) {
            // Not so straightforward: left to right, top to bottom in the areas
            // without disruption, then repeat in the areas with disruption

	    // Disruption table should be at least as long as tableaux
            $this->disruptionTableExpand();

            // The last line or "short row" is handled differently. Calculate
            // how much data it holds
            $shortRowSz =
                strlen($stream) - (($this->height - 2) * $this->width);

            // Undisrupted areas first (always starts at left), full lines only
            do {
                $this->tableaux[$row] = array(); // Create unused empty row
                $line = substr($stream, $streamIdx, $this->disruption[$row]);
                for ($a = 0; $a < strlen($line); $a++) {
                    $this->tableaux[$row][$a] = intval(substr($line, $a, 1));
                }
                $streamIdx += $this->disruption[$row];
                $row++;
            } while ($streamIdx < strlen($stream) && $row < $this->height);

            // Undisrupted area, short row. There are multiple ways short rows
            // could be handled, here I'm sticking them on the last line to the
            // left. Even if we've not reached the disruption area, don't put
            // more than max. If we don't use all the chars up, that's okay -
            // the process will fill the remainder in the disrupted area later.
            $this->tableaux[$row] = array();
            $sz = $this->disruption[$row];
            if ($shortRowSz < $this->disruption[$row]) {
                $sz = $shortRowSz;
            }
            $line = substr($stream, $streamIdx, $sz);
            for ($a = 0; $a < strlen($line); $a++) {
                $this->tableaux[$row][$a] = intval(substr($line, $a, 1));
            }
            $streamIdx += $sz;

            // Disrupted areas second
            $row = 2;
            do {
                $line = substr($stream, $streamIdx,
                               $this->width - $this->disruption[$row]);
                for ($a = 0; $a < strlen($line); $a++) {
                    $this->tableaux[$row][$this->disruption[$row] + $a]
                        = intval(substr($line, $a, 1));
                }
                $streamIdx += $this->width - $this->disruption[$row];
                $row++;
            } while ($streamIdx < strlen($stream) && $row <= $this->height);
        }
    }

    //
    // To get a stream back from a disrupted tableaux we need to address the
    // undisrupted areas before the disrupted. Both start at top left most
    // available cell in row[2]
    public function undisruptTableaux()
    {
	// Disruption table should be at least as long as tableaux
        $this->disruptionTableExpand();

        $stream = '';
        $row = 2;
        // Undisrupted areas first (always starts at left,
        for ($row = 2; $row <= $this->height; $row++) {
            for ($col = 0; $col < $this->disruption[$row]; $col++) {
                $stream .= $this->tableaux[$row][$col];
            }
        }
        // Remove blank chars from short row at end
        $stream = trim($stream);

        // Now disrupted areas
        for ($row = 2; $row <= $this->height; $row++) {
            for ($col = $this->disruption[$row]; $col < $this->width; $col++) {
                if (array_key_exists($col, $this->tableaux[$row])) {
                    $stream .= $this->tableaux[$row][$col];
                }
            }
        }
        return trim($stream);
    }

    //
    // To get a stream that's not disrupted is rather simpler
    public function readOutByRows()
    {
        // The first table type will not normally have a height
        if ($this->height === null) {
            $this->height = sizeof($this->tableaux) - 1;
        }

        // Create a stream of characters in table from top left to bottom right
        $stream = '';
        for ($row = 2; $row <= $this->height; $row++) {
            for ($col = 0; $col < $this->width; $col++) {
                // CHECKERBOARD_DEFAULT_VAL addresses shorter columns:
                // empty they're padded
                if (array_key_exists($col, $this->tableaux[$row])) {
                    if ($this->tableaux[$row][$col] !==
                        CHECKERBOARD_DEFAULT_VAL) {
                        $stream .= $this->tableaux[$row][$col];
                    }
                }
            }
        }
        return $stream;
    }

    //
    // Debug: print out what the table looks like
    public function debugReadOutByRows()
    {
        // The first table type will not normally have a height
        $height = sizeof($this->tableaux) - 1;

        for ($row = 0; $row < $height + 1; $row++) {
            $stream = '';
            for ($col = 0; $col < $this->width; $col++) {
                // CHECKERBOARD_DEFAULT_VAL addresses shorter columns:
                // they're padded empty
                if (array_key_exists($col, $this->tableaux[$row])) {
                    if ($this->tableaux[$row][$col] !==
                        CHECKERBOARD_DEFAULT_VAL) {
                        $stream .= substr($this->tableaux[$row][$col] .
                                          "  ", 0, 3);
                    }
                } else {
                    $stream .= "   ";
                }
            }
            echo $stream . "\n";
        }
    }

    //
    // Take the enciphered stream and fill the tableaux according to the
    // sequence given in row[1]
    public function fillTableauxDuringDecrypt($stream)
    {
        // Use length to derive table dimensions
        $ciphertextLen = strlen($stream);
        $this->setCipherLength($ciphertextLen);
        $shortRowSz = $ciphertextLen - (($this->height - 2) * $this->width);
        $emptyArr = array_fill(0, $this->height - 1,
                               array_fill(0, $this->width,
                                          CHECKERBOARD_DEFAULT_VAL));
        $this->tableaux = array_slice($this->tableaux, 0, 2);
        $this->tableaux = array_merge($this->tableaux, $emptyArr);

        // Loop through the sequenced columns
        $streamIdx = 0;
        for ($column = 1; $column <= $this->width; $column++) {
            // Find that column
            $idx = 0;
            $donef = false;
            do {
                // Check if column matches the sequence number
                if ($this->tableaux[1][$idx] === $column) {
                    // If so, fill the tableaux using the stream & accounting
                    //  that some columns are longer than others
                    $colHeight = ($idx < $shortRowSz) ?
                        $this->height - 1 :
                        $this->height - 2;
                    // + 2 for breeder lines
                    for ($row = 2; $row < $colHeight + 2; $row++) {
                        $this->tableaux[$row][$idx] = $stream[$streamIdx++];
                    }
                    $donef = true;
                }
                $idx++;
            } while ($donef === false);
        }
    }

    public function __construct($type, $derivations)
    {
        $this->type = $type;
        $this->disruption = array();

        if ($this->type === TABLEAUX_TYPE_1) {
            $this->width = intval($derivations->getWidthTableaux1());
            $this->tableaux = array($derivations->getTableaux1Breeder());
        } elseif ($this->type === TABLEAUX_TYPE_2) {
            $this->width = intval($derivations->getWidthTableaux2());
            $this->tableaux = array($derivations->getTableaux2Breeder());
        }
        $this->tableaux[1] = bigConvert2Sequential($this->tableaux[0]);
    }

    // The length is used to determine the height. For the second tableaux its
    // availability allows generation of the disruption data
    public function setCipherLength($length)
    {
        // +2 to accomodate breeder lines but not short row
        $this->height = intval($length / $this->width) + 2;
        if ($this->type === TABLEAUX_TYPE_2) {
            $this->generateDisruptionData();
        }
    }

    //
    // With the transposition tableaux filled in, retrieve the stream according
    // to the order kept in row [1]
    public function getTransposed()
    {
        $stream = '';
        $keynumber = 1;

        // Traverse table
        do {
            // Traverse columns
            $coldone = false;
            // Find work to do for current keynumber
            for ($knIdx = 0; $knIdx < sizeof($this->tableaux[1]); $knIdx++) {
                // Go in the order stated in row[1]
                if ($this->tableaux[1][$knIdx] === $keynumber) {
                    $coldone = true;

                    // Traverse down the column
                    $idx = 2;
                    // do another row if there is one
                    while (array_key_exists($idx, $this->tableaux)) {
                        // Append the column value if it exists
                        if (array_key_exists($knIdx, $this->tableaux[$idx])) {
                            $stream .= $this->tableaux[$idx][$knIdx];
                        }
                        $idx++;
                    }
                }
            }
            $keynumber++;
        } while ($coldone === true);
        return $stream;
    }
}
