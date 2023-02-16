<?php
//
// Proof of concept that the VIC cipher process has been understood
// Alistair Mann, 2023

define("ENCIPHER", true);
define("DECIPHER", false);
define("CHAIN", 3);
define("RU_ALPHABET", "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ");
define("RU_ALPHABET_IGNORE", "ЁЙЪ");
define("BAD_UNSUBSTITUTIONS_ERROR", "Stream corrupt or derivation data wrong\n");

// Testing
if (1) {
    // Used when calling direct from command line

    // Force random swap to happen at given pos; null for off
    // Plaintext must be uppercased first
    define("TEST_PLAINTEXT", "1. ПОЗДРАВЛЯЕМ С БЛАГОПОЛУЧНЫМ ПРИБЫТИЕМ. ПОДТВЕРЖДАЕМ ПОЛУЧЕНИЕ ВАШЕГО ПИСЬМА В АДРЕС ,,В@В,, И ПРОЧТЕНИЕ ПИСЬМА №1.
2. ДЛЯ ОРГАНИЗАЦИИ ПРИКРЫТИЯ МЫ ДАЛИ УКАЗАНИЕ ПЕРЕДАТЬ ВАМ ТРИ ТЫСЯЧИ МЕСТНЫХ. ПЕРЕД ТЕМ КАК ИХ ВЛОЖИТЬ В КАКОЕ ЛИБО ДЕЛО ПОСОВЕТУИТЕСЬ С НАМИ, СООБЩИВ ХАРАКТЕРИСТИКУ ЭТОГО ДЕЛА.
3. ПО ВАШЕИ ПРОСЬБЕ РЕЦЕПТУРУ ИЗГОТОВЛЕНИЯ МЯГКОИ ПЛЕНКИ И НОВОСТЕИ ПЕРЕДАДИМ ОТДЕЛЬНО ВМЕСТЕ С ПИСЬМОМ МАТЕРИ.
4. ГАММЫ ВЫСЫЛАТЬ ВАМ РАНО. КОРОТКИЕ ПИСЬМА ШИФРУИТЕ, А ПОБОЛЬШЕТИРЕ ДЕЛАИТЕ СО ВСТАВКАМИ. ВСЕ ДАННЫЕ О СЕБЕ, МЕСТО РАБОТЫ, АДРЕС И Т.Д. В ОДНОИ ШИФРОВКЕ ПЕРЕДАВАТЬ НЕЛЬЗЯ. ВСТАВКИ ПЕРЕДАВАИТЕ ОТДЕЛЬНО.
5. ПОСЫЛКУ ЖЕНЕ ПЕРЕДАЛИ ЛИЧНО. С СЕМЬЕИ ВСЕ БЛАГОПОЛУЧНО. ЖЕЛАЕМ УСПЕХА. ПРИВЕТ ОТ ТОВАРИЩЕИ
№1 ДРОБЬО 3 ДЕКАБРЯ");
    // 2 is null, idk how 1 4 arrived at, repeat to maximum five chars
    define('TEST_PADDING_MATERIAL', '2142');
    // The "214" problem: padding to a five group boundary but there's no
    // explanation as to how that sequence was arrived at.
    define("TEST_CIPHERTEXT", "14546 36056 64211 08919 18710 71187 71215 02906 66036 10922
11375 61238 65634 39175 37378 31013 22596 19291 17463 23551
88527 10130 01767 12366 16669 97846 76559 50062 91171 72332
19262 69849 90251 11576 46121 24666 05902 19229 56150 23521
51911 78912 32939 31966 12096 12060 89748 25362 43167 99841
76271 31154 26838 77221 58343 61164 14349 01241 26269 71578
31734 27562 51236 12982 18089 66218 22577 09454 81216 71953
26986 89779 54197 11990 23881 48884 22165 62992 36449 41742
30267 77614 31565 30902 85812 16112 93312 71220 60369 12872
12458 19081 97117 70107 06391 71114 19459 59586 80317 07522
76509 11111 36990 32666 04411 51532 91184 23162 82011 19185
56110 28876 76718 03563 28222 31674 39023 07623 93513 97175
29816 95761 69483 32951 97686 34992 61109 95090 24092 71008
90061 14790 15154 14655 29011 57206 77195 01256 69250 62901
39179 71229 23299 84164 45900 42227 65853 17591 60182 06315
65812 01378 14566 87719 92507 79517 99651 82155 58118 67197
30015 70687 36201 56531 56721 26306 87185 91796 51341 07796
76655 62716 33588 21932 16224 87721 85519 23191 20665 45140
66098 60959 71521 02334 21212 51110 85227 98768 11125 05321
53152 14191 12166 12715 03116 43041 74822 72759 29130 21947
15764 96851 20818 22370 11391 83520 62297");
    // Poem must be capitalised and in the usable alphabet already, and with one
    // line per element
    define("TEST_POEM", "СНОВА ЗАМЕРЛО ВСЕ ДО РАССВЕТА\nДВЕРЬ НЕ СКРИПНЕТ НЕ ВСПЫХНЕТ ОГОНЬ\nТОЛЬКО СЛЫШНО НА УЛИЦЕ ГДЕ-ТО\nОДИНОКАЯ БРОДИТ ГАРМОНЬ\nТОЛЬКО СЛЫШНО НА УЛИЦЕ ГДЕ-ТО\nОДИНОКАЯ БРОДИТ ГАРМОНЬ\nТО ПОЙДЕТ НА ПОЛЯ ЗА ВОРОТА\nТО ВЕРНЕТСЯ ОБРАТНО ОПЯТЬ\nСЛОВНО ИЩЕТ В ПОТЕМКАХ КОГО-ТО\nИ НЕ МОЖЕТ НИКАК ОТЫСКАТЬ\nСЛОВНО ИЩЕТ В ПОТЕМКАХ КОГО-ТО\nИ НЕ МОЖЕТ НИКАК ОТЫСКАТЬ\nВЕЕТ С ПОЛЯ НОЧНАЯ ПРОХЛАДА\nС ЯБЛОНЬ ЦВЕТ ОБЛЕТАЕТ ГУСТОЙ\nТЫ ПРИЗНАЙСЯ КОГО ТЕБЕ НАДО\nТЫ СКАЖИ ГАРМОНИСТ МОЛОДОЙ\nТЫ ПРИЗНАЙСЯ КОГО ТЕБЕ НАДО\nТЫ СКАЖИ ГАРМОНИСТ МОЛОДОЙ\nМОЖЕТ РАДОСТЬ ТВОЯ НЕДАЛЕКО\nДА НЕ ЗНАЕТ ЕЕ ЛИ ТЫ ЖДЕШЬ\nЧТО Ж ТЫ БРОДИШЬ ВСЮ НОЧЬ ОДИНОКО\nЧТО Ж ТЫ ДЕВУШКАМ СПАТЬ НЕ ДАЕШЬ\nЧТО Ж ТЫ БРОДИШЬ ВСЮ НОЧЬ ОДИНОКО\nЧТО Ж ТЫ ДЕВУШКАМ СПАТЬ НЕ ДАЕШЬ");
    define('MESSAGE_NUMBER_KEYGROUP', "20818"); // Different group each message
    define("TEST_RANDOM_SWAP_POS", 148); // null for random, when needed

    $randomSwapPos = TEST_RANDOM_SWAP_POS;
    $alphabet = RU_ALPHABET;
    $alphabetIgnore = RU_ALPHABET_IGNORE;
    $key1 = "СНЕГОПА";
    $key2 = 3; // keyFromPoem(TEST_POEM, 3); // 3 for third line
    $key3 = "3/9/1945"; // Just the digits are used
    $key4 = 13; // Agent's personal identifier
    $msgnumKeygroup = MESSAGE_NUMBER_KEYGROUP;
    $padding = TEST_PADDING_MATERIAL;
    $ciphertext = TEST_CIPHERTEXT;
    $plaintext = TEST_PLAINTEXT;
    $direction = CHAIN; //ENCIPHER;
    if ($direction !== DECIPHER) {
        $message = $plaintext;
    } else {
        $message = $ciphertext;
    }

} else {
    // Used when run from batch file

    define('TEST_PLAINTEXT', 'EMPTY');
    define('TEST_POEM', 'EMPTY');
    define('TEST_PADDING_MATERIAL', '6767');
    define("TEST_RANDOM_SWAP_POS", null); // null for random, when needed
    define('MESSAGE_NUMBER_KEYGROUP', "00000"); // Different group each message
    $key1 = '';
    $key2 = '';
    $key3 = '';
    $key4 = '';
    $message = '';
    $direction = ENCIPHER;
}

// Constants
// Placeholders are needed to get single character control codes
define("PLACEHOLDER_НЦ", '*'); // swap alpha to numeric or back again
define("PLACEHOLDER_НТ", '%'); // message starts here
define("PLACEHOLDER_ПВТ", '@'); // repeat
define("PLACEHOLDER_ПЛ", '#'); // undetermined
define("PLACEHOLDER_№", '&'); // Literally No.

define("NUMERIC_ALPHABET", "1234567890"); // Single digit conversion sequences
define("VIC_CHECKERBOARD_WIDTH", 11);
define("VIC_CHECKERBOARD_HEIGHT", 5);
define("CHECKERBOARD_CONTROL_CHARS", array(array(3, '.', ',', PLACEHOLDER_ПЛ),
                                           array(5, PLACEHOLDER_№, PLACEHOLDER_НЦ,
                                                 PLACEHOLDER_НТ)));
define("CHECKERBOARD_DEFAULT_VAL", ' ');
// true=encipher, false=decipher, 3=enc then dec. Supercede on command line later
define('TABLEAUX_TYPE_1', 1);
define('TABLEAUX_TYPE_2', 2);
define('FIVEGROUP_NUM', 5); // There are five digits in each Group of 5
define('CIPHERTEXT_PAGEWIDTH', 10); // There are ten Groups of 5 per row

// Handle command line
// Forced for now

define('HELP_MSG', "Usage: php vic-poc.php [OPTION]...
Encipher or decipher a message using the VIC Cipher

With no FILE, or when FILE is -, read standard input.

  -1, --key1=WORD          Use WORD for first key
  -2, --key2=NUMBER        Use NUMBER'th line of poem for second key
  -3, --key3=DATE          Use DATE for third key
  -4, --key4=NUMBER        Use NUMBER for Agent's identifier
  -a, --alphabet=STRING    Use STRING as alphabet (Default=Cyrillic)
  -b, --ignore-alphabet=STRING
                           Remove from alphabet any character in STRING. Used
                           to remove diacritics (Default=Cyrillic diacritics)
  -d, --decrypt=VALUE      Encrypt if missing, Decrypt if no value, Encrypt
                           then Decrypt otherwise
  -h, --help               This message
  -m, --message=STRING     Message to encrypt or decrypt
  -n, --msgnum=STRING      Five numberic characters used to identify this
                           message
  -p, --padding=STRING     Four numeric characters used to pad last keygroup.
                           (Default=2727)
  -s, --swappos=NUMBER     Swap start and end of message at position NUMBER.
                           (Default=" . TEST_RANDOM_SWAP_POS . ")
  -t, --poem=STRING        Use STRING to form poem part of key3. Lines marked
                           by \\n or \\r\\n

Examples:
  php ./vic-poc.php -");

//
// Merge command line and defines, then process
$a_opt = handleArgument('', 'alphabet', ':', RU_ALPHABET);
$b_opt = handleArgument('', 'ignore-alphabet', ':', RU_ALPHABET_IGNORE);
$d_opt = handleArgument('', 'decrypt', '::', $direction);
$d_opt = ($d_opt !== ENCIPHER && $d_opt !== DECIPHER) ? 3 : $d_opt;
$h_opt = handleArgument('h', 'help', '', true);
$k1_opt = handleArgument('', 'key1', ':', $key1);
$k2_opt = handleArgument('', 'key2', ':', $key2);
$k2_opt = intval($k2_opt);
$k3_opt = handleArgument('', 'key3', ':', $key3);
$k4_opt = handleArgument('', 'key4', ':', $key4);
$k4_opt = intval($k4_opt);
$m_opt = handleArgument('', 'message', ':', $message);
$n_opt = handleArgument('', 'msgnum', ':', '');
$n_opt = substr($n_opt . MESSAGE_NUMBER_KEYGROUP, 0, 5);
$p_opt = handleArgument('', 'padding', ':', TEST_PADDING_MATERIAL);
$p_opt = substr($p_opt, 0, 4);
$s_opt = handleArgument('', 'swappos', ':', TEST_RANDOM_SWAP_POS);
$s_opt = intval($s_opt);
$t_opt = handleArgument('', 'poem', ':', TEST_POEM);
$t_opt = mb_ereg_replace("\\\\r", '', $t_opt);
$t_opt = mb_split('\\n', $t_opt);
$k2_opt = keyFromPoem($t_opt, $k2_opt);

mainloop($a_opt, $b_opt,
         $k1_opt, $k2_opt, $k3_opt, $k4_opt, $n_opt, $d_opt, $s_opt, $p_opt,
         $m_opt);
exit;

//
// -----------------------------------------------------------------------------

//
// Classes
//

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
        $idx = sizeof($cta) - $index;
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
    public function doDerivations($alphabet, $word, $poem, $date, $id, $msgNum) {
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
        } while ($found === true && $row <= $this->height);
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

            // Undisrupted areas first (always starts at left)
            do {
                $this->tableaux[$row] = array(); // Helpful to create unused empty row
                $line = substr($stream, $streamIdx, $this->disruption[$row]);
                for ($a = 0; $a < strlen($line); $a++) {
                    $this->tableaux[$row][$a] = intval(substr($line, $a, 1));
                }
                $streamIdx += $this->disruption[$row];
                $row++;
            } while ($streamIdx < strlen($stream) && $row <= $this->height);

            // Disrupted areas second
            $row = 2;
            do {
                $line = substr($stream, $streamIdx, $this->width - $this->disruption[$row]);
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
        $stream = '';
        $row = 2;
        // Undisrupted areas first (always starts at left,
        for ($row = 2; $row < $this->height + 1; $row++) {
            for ($col = 0; $col < $this->disruption[$row]; $col++) {
                $stream .= $this->tableaux[$row][$col];
            }
        }
        // Remove blank chars from short row at end
        $stream = trim($stream);

        // Now disrupted areas
        for ($row = 2; $row < $this->height + 1; $row++) {
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
        for ($row = 2; $row < $this->height + 1; $row++) {
            for ($col = 0; $col < $this->width; $col++) {
                // CHECKERBOARD_DEFAULT_VAL addresses shorter columns: they're padded
                // empty
                if (array_key_exists($col, $this->tableaux[$row])) {
                    if ($this->tableaux[$row][$col] !== CHECKERBOARD_DEFAULT_VAL) {
                        $stream .= $this->tableaux[$row][$col];
                    }
                }
            }
        }
        return $stream;
    }

    //
    // Debug: print out what the table looks like
    public function debug_readOutByRows()
    {
        // The first table type will not normally have a height
        $height = sizeof($this->tableaux) - 1;

        for ($row = 0; $row < $height + 1; $row++) {
            $stream = '';
            for ($col = 0; $col < $this->width; $col++) {
                // CHECKERBOARD_DEFAULT_VAL addresses shorter columns: they're padded
                // empty
                if (array_key_exists($col, $this->tableaux[$row])) {
                    if ($this->tableaux[$row][$col] !== CHECKERBOARD_DEFAULT_VAL) {
                        $stream .= substr($this->tableaux[$row][$col] . "  ", 0, 3);
                    }
                }
            }
            echo $stream . "\n";
        }
    }

    //
    // Take the enciphered stream and fill the tableaux according to the sequence
    // given in row[1]
    public function fillTableauxDuringDecrypt($stream)
    {
        // Use length to derive table dimensions
        $ciphertextLen = strlen($stream);
        $this->setCipherLength($ciphertextLen);
        $shortRowSz = $ciphertextLen - ($this->height - 2) * $this->width;
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
                    // If so, fill the tableaux using the stream & accounting that some
                    // columns are longer than others
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
        // +2 to accomodate breeder lines
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

//
// Checkerboard class
class Checkerboard
{
    private $cb = null;
    private $editable = true;
    // Associative array such that it maps characters to their coords
    private $cbAarr = array();
    private $padding;

    //
    // fillBody() takes an array of characters and places them in the checkerboard
    // start at Row 2, in the given column, and filling down and right where there
    // are free spaces
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
                // If down a row is off the bottom, go back up and right one column
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
    public function __construct($width, $height, $controlChars, $derivations, $padding)
    {
        // Unlock the class for editing (used to support a quick lookup)
        $this->editable = true;

        // Store the padding to be used later
        $this->padding = $padding;

        // PHP doesn't have constrained arrays, so we'll fake one
        $this->cb = array_fill(0, $width, array_fill(0, $height, CHECKERBOARD_DEFAULT_VAL));

        // Fill in the control chars: "message starts", "change to/from numeric", etc
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
        $alphabetNokey = constructUsableAlphabet($derivations->getAlphabetUsable(),
                                                 $derivations->getKey1());

        // Continue with the usable alphabet
        if ($alphabetNokey !== null) {
            $alphabetArr = array();
            for ($a = 0; $a < mb_strlen($alphabetNokey); $a++) {
                $alphabetArr[] = mb_substr($alphabetNokey, $a, 1);
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
        // Make lookups easier: first time through create an associative array and
        // use that thereafter
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
                        $this->cbAarr[$c] = $this->cb[$row][0] . $this->cb[0][$a];
                    }
                }
            }
        }

        // Digits are not substituted at all; otherwise the character is substituted
        // per the associative array. Any remaining characters are silently dropped
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
    // the first "2" is actioned for being a null, and the remainder are ignored.
    // It seems to me that cryptographers might choose to vary the content rather
    // than repeat it, so "2142" would be the maximum padding used
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
        $output .= substr($this->padding, 0,
                          FIVEGROUP_NUM - (strlen($output) % FIVEGROUP_NUM));
        return $output;
    }

    // Turn the coords back into plaintext
    public function unsubstitutions($stream, $padding)
    {
        // Make up an associative array of the coords with their various plaintext
        // equivalents
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
                    $unsub[($lcoord * 10) + $this->cb[0][$a]] = $this->cb[$row][$a];
                }
            }
        }

        //
        // There's no nice way to do this: this bodge removes the padding text,
        // try to remove successively shorter matches. It's possible there's
        // no padding, but not possible for more than four
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
            // Each time through we take whatever the CB says exists. If we come
            // across the НЦ marker, we change to handling numbers until such
            // time as we see another НЦ.
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
}

//
// Functions
//

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
    // On occasion the text arrives in array format - convert it to string first
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
function bigConvert2Sequential($numArr) {
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
    return mb_substr($text, $position) . PLACEHOLDER_НТ . mb_substr($text, 0, $position);
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
// Run the major tasks of this program

// Encipher
function encipher($plaintext, $swappos, $d, $cb, $tt1, $tt2, $msgnumKeygroup)
{
    // Encipher
    // Process plaintext
    $plaintextNumbers = encodeNumbers($plaintext);
    $plaintextChopped = swapHalves($plaintextNumbers, $swappos);
    $plaintextCheckerboarded = $cb->checkerboardSubstitution($plaintextChopped);
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

//
// Misc

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
            $alphabet = mb_ereg_replace("[" . mb_substr($alphabetIgnore, $a) . "]",
                                        "", $alphabet);
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
                            strval($number) . strval($number) . strval($number) .
                            PLACEHOLDER_НЦ, $text);
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

//
// Mainloop

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
