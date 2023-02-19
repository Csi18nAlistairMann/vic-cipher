<?php

namespace viccipher;

//
// Handling of 5 digit keygroups
class FiveDigitGroups
{
    private $msgnumKeygroup;
    private $ciphertextArr;

    // We want to make available the message ID keygroup (which we know to be
    // in position $index), and also make the stream available as an array
    // without that keygroup
    public function __construct($message, $index)
    {
        $cta = $this->stream2Arr($message);
        if ($index === "0") {
            $idx = sizeof($cta) - 10;
        } else {
            $idx = sizeof($cta) - $index;
        }
        $this->msgnumKeygroup = $cta[$idx];
        $this->ciphertextArr = array_merge(array_slice($cta, 0, $idx),
                                           array_slice($cta, $idx + 1));
    }

    //
    // Convert inbound five groups - now missing the message number - into a
    // stream
    public static function fiveGroupsArr2Str($dataArr)
    {
        $stream = '';
        for ($a = 0; $a < sizeof($dataArr); $a++) {
            $stream .= $dataArr[$a];
        }
        return $stream;
    }

    //
    // Prepare the final ciphertext for output by grouping into groups of five
    // with ten such groups per row
    public static function fiveGroups($stream, $keygroup, $position)
    {
        // Construct the groups of five
        $page = array();
        for ($idx = 0; $idx < strlen($stream); $idx += FIVEGROUP_NUM) {
            $page[] = substr($stream, $idx, FIVEGROUP_NUM);
        }

        // Insert message number
        $sz = sizeof($page);
        $position = intval($position);
        if ($position === 0)
            $position = 10;
        $position--;
        $page = array_merge(array_slice($page, 0, $sz - $position),
                            array($keygroup),
                            array_slice($page, $sz - $position));

        // Construct the output - spaces follows each except last which has \n
        $output = '';
        for ($idx = 0; $idx < sizeof($page) - 1; $idx += CIPHERTEXT_PAGEWIDTH) {
            for ($line = 0; $line < CIPHERTEXT_PAGEWIDTH; $line++) {
                if (array_key_exists($idx + $line, $page)) {
                    $output .= $page[$idx + $line] . ' ';
                }
            }
            $output = trim($output) . "\n";
        }

        // Remove final \n
        return trim($output);
    }

    //
    // Take the message enciphered into a text stream of groups of five and turn
    // it into an array
    private function stream2Arr($stream)
    {
        $stream = str_replace("\n", ' ', $stream);
        $rv = explode(' ', $stream);
        return $rv;
    }

    public function getMsgNumKeygroup()
    {
        return $this->msgnumKeygroup;
    }

    public function getCiphertextArr()
    {
        return $this->ciphertextArr;
    }
}
