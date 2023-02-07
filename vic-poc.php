<?php
//
// Proof of concept that the VIC cipher process has been understood
// Alistair Mann, 2023

// Testing
// Force random swap to happen at given pos; null for off
define("TEST_RANDOM_SWAP_POS", 148);
// Plaintext must be uppercased first
define("TEST_PLAINTEXT", "1. ПОЗДРАВЛЯЕМ С БЛАГОПОЛУЧНЫМ ПРИБЫТИЕМ. ПОДТВЕРЖДАЕМ ПОЛУЧЕНИЕ ВАШЕГО ПИСЬМА В АДРЕС ,,В@В,, И ПРОЧТЕНИЕ ПИСЬМА №1.
2. ДЛЯ ОРГАНИЗАЦИИ ПРИКРЫТИЯ МЫ ДАЛИ УКАЗАНИЕ ПЕРЕДАТЬ ВАМ ТРИ ТЫСЯЧИ МЕСТНЫХ. ПЕРЕД ТЕМ КАК ИХ ВЛОЖИТЬ В КАКОЕ ЛИБО ДЕЛО ПОСОВЕТУИТЕСЬ С НАМИ, СООБЩИВ ХАРАКТЕРИСТИКУ ЭТОГО ДЕЛА.
3. ПО ВАШЕИ ПРОСЬБЕ РЕЦЕПТУРУ ИЗГОТОВЛЕНИЯ МЯГКОИ ПЛЕНКИ И НОВОСТЕИ ПЕРЕДАДИМ ОТДЕЛЬНО ВМЕСТЕ С ПИСЬМОМ МАТЕРИ.
4. ГАММЫ ВЫСЫЛАТЬ ВАМ РАНО. КОРОТКИЕ ПИСЬМА ШИФРУИТЕ, А ПОБОЛЬШЕТИРЕ ДЕЛАИТЕ СО ВСТАВКАМИ. ВСЕ ДАННЫЕ О СЕБЕ, МЕСТО РАБОТЫ, АДРЕС И Т.Д. В ОДНОИ ШИФРОВКЕ ПЕРЕДАВАТЬ НЕЛЬЗЯ. ВСТАВКИ ПЕРЕДАВАИТЕ ОТДЕЛЬНО.
5. ПОСЫЛКУ ЖЕНЕ ПЕРЕДАЛИ ЛИЧНО. С СЕМЬЕИ ВСЕ БЛАГОПОЛУЧНО. ЖЕЛАЕМ УСПЕХА. ПРИВЕТ ОТ ТОВАРИЩЕИ
№1 ДРОБЬО 3 ДЕКАБРЯ");

// Constants
// Placeholders are needed to get single character control codes
define("PLACEHOLDER_НЦ", '*'); // swap alpha to numeric or back again
define("PLACEHOLDER_НТ", '%'); // message starts here
define("PLACEHOLDER_ПВТ", '@'); // repeat
define("PLACEHOLDER_ПЛ", '#'); // undetermined
define("PLACEHOLDER_№", '&'); // Literally No.

define("RU_ALPHABET", "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ");
define("RU_ALPHABET_IGNORE", "ЁЙЪ");
define("VIC_CHECKERBOARD_WIDTH", 11);
define("VIC_CHECKERBOARD_HEIGHT", 5);
define("CHECKERBOARD_OTHERS", array(array(3, '.', ',', PLACEHOLDER_ПЛ),
				    array(5, PLACEHOLDER_№, PLACEHOLDER_НЦ,
					  PLACEHOLDER_НТ)));
define("CHECKERBOARD_DEFAULT_VAL", ' ');
define("ENCIPHER", true); // false for decipher, supercede on command line later
define('TABLEAUX_TYPE_1', 1);
define('TABLEAUX_TYPE_2', 2);
define('FIVEGROUP_NUM', 5); // There are five digits in each Group of 5
define('CIPHERTEXT_PAGEWIDTH', 10); // There are ten Groups of 5 per row
define('MYSTERY_KEYGROUP', 20818); // There are ten Groups of 5 per row

// Handle command line
// Forced for now
$key1 = "СНЕГОПА";
$key2 = keyFromPoem(3); // 3 for third line
$key3 = "3/9/1945"; // Not sure structure just yet
$key4 = 13;

$alphabet = RU_ALPHABET;
$alphabet_ignore = RU_ALPHABET_IGNORE;
$plaintext = TEST_PLAINTEXT;

//
// Main
$alphabet_usable = constructAcceptableAlphabet($alphabet, $alphabet_ignore);

// Set up tables
$cb = new Checkerboard();
$tt1 = new TranspositionTableaux(TABLEAUX_TYPE_1);
$tt2 = new TranspositionTableaux(TABLEAUX_TYPE_2);
$cb->initialise(VIC_CHECKERBOARD_HEIGHT, VIC_CHECKERBOARD_WIDTH, $key1,
		$alphabet_usable, CHECKERBOARD_OTHERS);

if (ENCIPHER === true) {
  // Encipher
  // Process plaintext
  $plaintext_numbers = encodeNumbers($plaintext);
  $plaintext_chopped = swapHalves($plaintext_numbers, TEST_RANDOM_SWAP_POS);
  $cb->skyhookNumbers(); // temporary code to add the coords not yet described
  $plaintext_checkerboarded = $cb->checkerboardSubstitution($plaintext_chopped);
  // At this point the character stream is short the final "214"
  $tt1->skyhookNumbers(); // temporary code to add the tableaux headers
  $tt1->fillTableaux($plaintext_checkerboarded);
  $tt2->skyhookNumbers(); // temporary code to add the tableaux headers
  $plaintext_transposed1 = $tt1->getTransposed();
  $tt2->fillTableaux($plaintext_transposed1);
  $plaintext_transposed2 = $tt2->getTransposed();
  $ciphertext = fiveGroups($plaintext_transposed2, MYSTERY_KEYGROUP);
  // At this point the ciphertext is based on skyhooked 2nd transposition
  var_dump($ciphertext);

} else {
  // Decipher

}
exit;

//
// Classes
//

//
// TranspositionTableux class
// VIC Cipher uses two - this class implements both
//  Type 1 is the straightforward Figure 3 table
//  Type 2 is the Figure 4 "disrupted areas" table
class TranspositionTableaux {
  private $type;
  private $tableaux;
  private $disruption;

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
  function generateDisruptionData() {
    $width = 14; // temporary code
    $col = 1;
    $row = 2;
    // Traverse the columns in order 1, 2, ... N
    do {
      $found = false;
      // Is this the column N?
      for($a = 0; $a < $width; $a++) {
	if ($this->tableaux[1][$a] === $col) {
	  // Yes it is. $a starts the disruption data
	  $found = true;
	  for ($b = $a; $b < $width + 1; $b++) {
	    $this->disruption[$row++] = $b;
	  }
	}
      }
      $col++;
    } while ($found === true);
  }

  //
  // The two transposition tablueax fill up in different ways - this method
  // implements both
  function fillTableaux($stream) {
    if ($this->type === TABLEAUX_TYPE_1) {
      // Straight forward left to right, top to bottom
      $width = 17; // Temporary bodge
      $stream_idx = 0;
      $row = 2;

      do {
	$line = substr($stream, $stream_idx, $width);
	for($a = 0; $a < strlen($line); $a++) {
	  $this->tableaux[$row][$a] = intval(substr($line, $a, 1));
	}
	$row++;
	$stream_idx += $width;
      } while ($stream_idx < strlen($stream));

    } elseif ($this->type === TABLEAUX_TYPE_2) {
      // Not so straightforward: left to right, top to bottom in the areas
      // without disruption, then repeat in the areas with disruption
      $width = 14; // Temporary bodge
      $height = 75; // Temporary bodge
      $stream_idx = 0;
      $row = 2;

      // Undisrupted areas first (always starts at left,
      do {
	$this->tableaux[$row] = array(); // Helpful to create unused empty row
	$line = substr($stream, $stream_idx, $this->disruption[$row]);
	for($a = 0; $a < strlen($line); $a++) {
	  $this->tableaux[$row][$a] = intval(substr($line, $a, 1));
	}
	$stream_idx += $this->disruption[$row];
	$row++;
      } while ($stream_idx < strlen($stream) && ($row <= $height));

      // Disrupted areas second
      $row = 2;
      do {
	$line = substr($stream, $stream_idx, $width - $this->disruption[$row]);
	for($a = 0; $a < strlen($line); $a++) {
	  $this->tableaux[$row][$this->disruption[$row] + $a] =
	    intval(substr($line, $a, 1));
	}
	$stream_idx += $width - $this->disruption[$row];
	$row++;
      } while ($stream_idx < strlen($stream) && ($row <= $height));
    }
  }

  function __construct($type) {
    $this->type = $type;
    $this->tableaux = array();
    $this->disruption = array();
  }

  function skyhookNumbers() {
    if ($this->type === TABLEAUX_TYPE_1) {
      $width = 17;
      $data = array(9, 6, 0, 3, 3, 1, 8, 3, 6, 6, 4, 6, 9, 0, 4, 7, 5,
		    14, 8, 16, 2, 3, 1, 13, 4, 9, 10, 5, 11, 15, 17, 6, 12, 7);

    } elseif ($this->type === TABLEAUX_TYPE_2) {
      $width = 14;
      $data = array(3, 0, 2, 7, 4, 3, 0, 4, 2, 8, 7, 7, 1, 2,
		    5, 13, 2, 9, 7, 6, 14, 8, 3, 12, 10, 11, 1, 4);
    } else {
      // unhandled error
    }

    // Create a multidimensional array to hold the above
    $idx = 0;
    while($idx < sizeof($data)) {
      $row = array_slice($data, $idx, $width);
      $this->tableaux[] = $row;
      $idx += $width;
    }

    if ($this->type === TABLEAUX_TYPE_2) {
      $this->generateDisruptionData();
    }
  }

  //
  // With the transposition tableaux filled in, retrieve the stream according
  // to the order kept in row [1]
  function getTransposed() {
    $stream = '';
    $keynumber = 1;
    // Traverse table
    do {

      // Traverse columns
      $coldone = false;
      // Find work to do for current keynumber
      for ($knidx = 0; $knidx < sizeof($this->tableaux[1]); $knidx++) {
	// Go in the order stated in row[1]
	if ($this->tableaux[1][$knidx] === $keynumber) {
	  $coldone = true;

	  // Traverse down the column
	  $idx = 2;
	  // do another row if there is one
	  while (array_key_exists($idx, $this->tableaux)) {
	    // Append the column value if it exists
	    if (array_key_exists($knidx, $this->tableaux[$idx])) {
	      $stream .= $this->tableaux[$idx][$knidx];
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
class Checkerboard {
  private $cb = null;
  private $editable = true;
  // Associative array such that it maps characters to their coords
  private $cb_aarr = array();

  //
  // fillBody() takes an array of characters and places them in the checkerboard
  // start at Row 2, in the given column, and filling down and right where there
  // are free spaces
  function fillBody($startx, $data) {
    // setup
    $starty = 2; // all entries start in second row
    $row = VIC_CHECKERBOARD_WIDTH * $starty;
    $pos = $row + $startx; // increment $pos until we find an empty cell

    //
    $donef = false;
    do {
      if ($data === array()) {
	// Finish if we run out of characters to add
	$donef = true;

      } else {
	// Convert $pos into co-ords
	$y = intval($pos / VIC_CHECKERBOARD_WIDTH);
	$x = $pos - ($y * VIC_CHECKERBOARD_WIDTH);

	if ($x >= VIC_CHECKERBOARD_WIDTH) {
	  // Finish if the next column is off the right of the checkerboard
	  $donef = true;

	} else {
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
      }
    } while (!$donef);
  }

  //
  // initialise() does the initial set up of the checkerboard in this order
  // processing chars / key / usable alphabet / 'repeat' symbol. By using this
  // order we can arrange later characters around earlier ones, per the book
  function initialise($width, $height, $key, $alphabet, $others = null) {
    // Unlock the class for editing (used to support a quick lookup)
    $this->editable = true;

    // PHP doesn't have constrained arrays, so we'll fake one
    $this->cb = array_fill(0, $width, array_fill(0, $height, CHECKERBOARD_DEFAULT_VAL));

    // Fill any "others" first: "message starts", "change to/from numeric", etc
    if ($others !== null) {
      for ($a = 0; $a < sizeof($others); $a++) {
	$data = $others[$a];
	$x = array_shift($data);
	$this->fillBody($x, $data);
      }
    }

    // Now do the characters in the key
    $x = 1;
    $y = 1;
    $idx = 0;
    while ($idx < mb_strlen($key)) {
      $char = mb_substr($key, $idx, 1);
      $this->cb[$y][$x] = $char;
      $x++;
      $idx++;
    }

    // Remove the key's characters from the usable alphabet
    $alphabet_nokey = constructAcceptableAlphabet($alphabet, $key);

    // Continue with the usable alphabet
    if ($alphabet_nokey !== null) {
      $alphabet_arr = array();
      for ($a = 0; $a < mb_strlen($alphabet_nokey); $a++) {
	$alphabet_arr[] = mb_substr($alphabet_nokey, $a, 1);
      }
      $x = 1;
      $this->fillBody($x, $alphabet_arr);
    }

    // And end with the final "repeat" symbol
    $this->cb[VIC_CHECKERBOARD_HEIGHT - 1][VIC_CHECKERBOARD_WIDTH - 1] =
      PLACEHOLDER_ПВТ;
  }

  //
  // Return the substitution for a single character
  function checkerboardSubstituteOne($char) {
    // Make lookups easier: first time through create an associative array and
    // use that
    if ($this->editable === true) {
      $this->editable = false;

      // Handle the top row with single digit coords
      for($a = 1; $a < VIC_CHECKERBOARD_WIDTH; $a++) {
	$c = $this->cb[1][$a];
	if ($c !== CHECKERBOARD_DEFAULT_VAL) {
	  $this->cb_aarr[$c] = $this->cb[0][$a];
	}
      }

      // Handle the remaining rows with double digit coords
      for ($row = 2; $row < VIC_CHECKERBOARD_HEIGHT; $row++) {
	for($a = 1; $a < VIC_CHECKERBOARD_WIDTH; $a++) {
	  $c = $this->cb[$row][$a];
	  if ($c !== CHECKERBOARD_DEFAULT_VAL)
	    $this->cb_aarr[$c] = $this->cb[$row][0] . $this->cb[0][$a];
	}
      }
    }

    // Digits are not substituted at all; otherwise the character is substituted
    // per the associative array. Any remaining characters are silently dropped
    $rv = '';
    if ($char >= "0" && $char <= "9") {
      $rv = $char;

    } else {
      if (array_key_exists($char, $this->cb_aarr))
	$rv = $this->cb_aarr[$char];
    }
    return ($rv);
  }

  //
  // Return the substitution for a stream of text
  // Note original ends on 2 1 4, labelled NULLS, but it isn't clear how this
  // is arrived at. I suspect 2 being empty in the original checkerboard is any
  // null ending processing, that the 1 and 4 are filler, and the length is
  // to make up the numbers
  function checkerboardSubstitution($text) {
    $output = '';
    for ($idx = 0; $idx <= mb_strlen($text); $idx++) {
      $c = mb_substr($text, $idx, 1);
      $d = $this->checkerboardSubstituteOne($c);

      $output .= $d;
    }
    $output .= '214'; // Bodge as book doesn't indicate how NULLS arrived at
    return $output;
  }

  //
  // Temporary until derivation code is available
  function skyhookNumbers() {
    $this->cb[0][1] = 5;
    $this->cb[0][2] = 0;
    $this->cb[0][3] = 7;
    $this->cb[0][4] = 3;
    $this->cb[0][5] = 8;
    $this->cb[0][6] = 9;
    $this->cb[0][7] = 4;
    $this->cb[0][8] = 6;
    $this->cb[0][9] = 1;
    $this->cb[0][10] = 2;

    $this->cb[2][0] = $this->cb[0][8];
    $this->cb[3][0] = $this->cb[0][9];
    $this->cb[4][0] = $this->cb[0][10];
  }
}

//
// Functions
//

//
// Prepare the final ciphertext for output by grouping into groups of five
// with ten such groups per row
function fiveGroups($stream, $keygroup) {
  // Construct the groups of five
  $page = array();
  for ($idx = 0; $idx < strlen($stream); $idx += FIVEGROUP_NUM) {
    $page[] = substr($stream, $idx, FIVEGROUP_NUM);
  }

  // Bodge in 20818 keygroup as the fifth from last group. Book is unclear
  // how this arrived at other than to say "A keygroup is inserted at a
  // prearranged point before the final message is sent."
  $sz = sizeof($page);
  $page = array_merge(array_slice($page, 0, $sz - 4),
		      array($keygroup),
		      array_slice($page, $sz - 4));

  // Construct the output - spaces follows each except last which has \n
  $output = '';
  for ($idx = 0; $idx < sizeof($page) - 1; $idx += CIPHERTEXT_PAGEWIDTH){
    for ($line = 0; $line < CIPHERTEXT_PAGEWIDTH; $line++) {
      if (array_key_exists($idx + $line, $page)) {
	$output .= $page[$idx + $line] . ' ';
      }
    }
    $output = trim($output) . "\n";
  }

  return $output;
}

//
// Remove given items from the original alphabet
// Cyrillic has three chars not used in the VIC Cipher
function constructAcceptableAlphabet($alphabet, $alphabet_ignore) {
  if (mb_strlen($alphabet_ignore) > 0) {
    // construct the acceptable parts of the alphabet by removing the known
    // ignorable entries;
    for ($a = 0; $a < mb_strlen($alphabet_ignore) - 1; $a++) {
      $alphabet = mb_ereg_replace("[" . mb_substr($alphabet_ignore, $a) . "]",
				  "", $alphabet);
    }
  }
  return $alphabet;
}

function swapHalves($text, $position = null) {
  // swap the second part with the first part of the text, with НТ used to mark
  // where that first part now starts
  if ($position === null) {
    $len = mb_strlen($text);
    $position = random_int(0, $len - 1);
  }
  return mb_substr($text, $position) . PLACEHOLDER_НТ . mb_substr($text, 0, $position);
}

function encodeNumbers($text) {
  // Change each occurence of a number such that " 3 " becomes " НЦ333НЦ "
  for($number = 0; $number <= 9; $number++) {
    $text = str_replace(strval($number), PLACEHOLDER_НЦ .
			strval($number) . strval($number) . strval($number) .
			PLACEHOLDER_НЦ, $text);
  }
  // Also change PLACEHOLDER_№
  $text = str_replace("№", PLACEHOLDER_№, $text);
  return $text;
}

function keyFromPoem($line) {
  // Poem must be capitalised
  $poem = array("СНОВА ЗАМЕРЛО ВСЕ ДО РАССВЕТА",
		"ДВЕРЬ НЕ СКРИПНЕТ НЕ ВСПЫХНЕТ ОГОНЬ",
		"ТОЛЬКО СЛЫШНО НА УЛИЦЕ ГДЕ-ТО",
		"ОДИНОКАЯ БРОДИТ ГАРМОНЬ",
		"ТОЛЬКО СЛЫШНО НА УЛИЦЕ ГДЕ-ТО",
		"ОДИНОКАЯ БРОДИТ ГАРМОНЬ",
		"ТО ПОЙДЕТ НА ПОЛЯ ЗА ВОРОТА",
		"ТО ВЕРНЕТСЯ ОБРАТНО ОПЯТЬ",
		"СЛОВНО ИЩЕТ В ПОТЕМКАХ КОГО-ТО",
		"И НЕ МОЖЕТ НИКАК ОТЫСКАТЬ",
		"СЛОВНО ИЩЕТ В ПОТЕМКАХ КОГО-ТО",
		"И НЕ МОЖЕТ НИКАК ОТЫСКАТЬ",
		"ВЕЕТ С ПОЛЯ НОЧНАЯ ПРОХЛАДА",
		"С ЯБЛОНЬ ЦВЕТ ОБЛЕТАЕТ ГУСТОЙ",
		"ТЫ ПРИЗНАЙСЯ КОГО ТЕБЕ НАДО",
		"ТЫ СКАЖИ ГАРМОНИСТ МОЛОДОЙ",
		"ТЫ ПРИЗНАЙСЯ КОГО ТЕБЕ НАДО",
		"ТЫ СКАЖИ ГАРМОНИСТ МОЛОДОЙ",
		"МОЖЕТ РАДОСТЬ ТВОЯ НЕДАЛЕКО",
		"ДА НЕ ЗНАЕТ ЕЕ ЛИ ТЫ ЖДЕШЬ",
		"ЧТО Ж ТЫ БРОДИШЬ ВСЮ НОЧЬ ОДИНОКО",
		"ЧТО Ж ТЫ ДЕВУШКАМ СПАТЬ НЕ ДАЕШЬ",
		"ЧТО Ж ТЫ БРОДИШЬ ВСЮ НОЧЬ ОДИНОКО",
		"ЧТО Ж ТЫ ДЕВУШКАМ СПАТЬ НЕ ДАЕШЬ");
  // Take nth line
  $sentence = $poem[$line - 1];
  // Remove spaces
  $sentence = str_replace(" ", "", $sentence);
  // Take first 20 characters
  $sentence = mb_substr($sentence, 0, 20);
  // That's the key
  return $sentence;
}

?>
