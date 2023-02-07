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
  $tt1->skyhookNumbers(); // temporary code to add the tableaux contents
  $tt2->skyhookNumbers(); // temporary code to add the tableaux contents
  $plaintext_transposed1 = $tt1->getTransposed();
  $plaintext_transposed2 = $tt2->getTransposed();
  $ciphertext = fiveGroups($plaintext_transposed2);
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

  function __construct($type) {
    $this->type = $type;
    $this->tableaux = array();
  }

  function skyhookNumbers() {
    if ($this->type === TABLEAUX_TYPE_1) {
      $width = 17;
      $data = array(9, 6, 0, 3, 3, 1, 8, 3, 6, 6, 4, 6, 9, 0, 4, 7, 5,
		    14, 8, 16, 2, 3, 1, 13, 4, 9, 10, 5, 11, 15, 17, 6, 12, 7,
		    9, 6, 9, 2, 0, 6, 3, 6, 9, 6, 1, 1, 9, 2, 0, 1, 2,
		    2, 3, 6, 1, 2, 5, 4, 1, 3, 2, 0, 2, 9, 6, 3, 4, 1,
		    0, 4, 0, 2, 0, 7, 9, 7, 6, 9, 7, 2, 5, 4, 1, 9, 1,
		    1, 1, 5, 4, 2, 3, 1, 9, 6, 9, 2, 0, 1, 9, 6, 1, 5,
		    1, 2, 6, 6, 2, 0, 2, 3, 7, 5, 1, 9, 0, 6, 1, 1, 4,
		    6, 7, 9, 7, 6, 9, 7, 2, 5, 1, 9, 7, 2, 3, 6, 3, 4,
		    6, 3, 2, 0, 1, 4, 1, 5, 1, 3, 8, 6, 0, 2, 0, 1, 9,
		    1, 1, 1, 5, 6, 3, 4, 6, 3, 8, 7, 1, 3, 2, 0, 6, 5,
		    8, 2, 5, 7, 1, 3, 8, 9, 8, 5, 8, 1, 5, 7, 1, 9, 2,
		    9, 2, 0, 1, 9, 7, 5, 1, 1, 5, 0, 4, 2, 3, 2, 0, 1,
		    7, 5, 8, 8, 6, 5, 2, 6, 2, 0, 1, 5, 1, 4, 4, 6, 9,
		    4, 6, 3, 1, 9, 7, 6, 9, 2, 0, 5, 1, 9, 2, 0, 6, 3,
		    2, 9, 2, 1, 1, 9, 8, 3, 8, 2, 5, 7, 1, 3, 4, 6, 7,
		    1, 8, 3, 3, 3, 1, 8, 6, 7, 9, 8, 1, 5, 4, 1, 6, 7,
		    2, 0, 9, 6, 9, 8, 5, 1, 1, 6, 5, 7, 6, 9, 7, 2, 4,
		    7, 9, 1, 9, 2, 9, 6, 9, 2, 9, 2, 0, 1, 0, 3, 8, 1,
		    9, 8, 1, 5, 1, 3, 7, 0, 2, 0, 1, 2, 2, 3, 1, 2, 3,
		    6, 3, 8, 2, 0, 9, 1, 3, 7, 0, 6, 3, 2, 0, 2, 0, 0,
		    8, 1, 5, 8, 5, 1, 9, 7, 2, 0, 9, 7, 6, 9, 7, 2, 5,
		    4, 2, 5, 2, 0, 2, 3, 8, 1, 9, 2, 5, 7, 1, 3, 1, 1,
		    0, 8, 1, 5, 2, 3, 7, 5, 1, 9, 7, 5, 9, 2, 0, 5, 1,
		    1, 2, 3, 8, 2, 3, 2, 3, 4, 1, 9, 7, 6, 9, 2, 0, 6,
		    7, 1, 8, 4, 4, 4, 1, 8, 6, 7, 3, 4, 2, 3, 2, 3, 6,
		    1, 1, 5, 6, 1, 5, 6, 1, 1, 3, 4, 1, 9, 1, 1, 1, 5,
		    4, 2, 3, 6, 9, 4, 0, 8, 6, 7, 6, 3, 8, 6, 9, 8, 1,
		    9, 6, 3, 2, 0, 7, 9, 2, 0, 5, 1, 1, 2, 3, 4, 1, 6,
		    2, 0, 6, 4, 6, 9, 2, 9, 2, 0, 1, 9, 7, 1, 7, 4, 9,
		    8, 6, 5, 8, 1, 3, 1, 1, 1, 6, 7, 1, 9, 2, 0, 6, 9,
		    7, 2, 5, 7, 1, 3, 4, 2, 0, 1, 9, 7, 5, 8, 1, 5, 5,
		    1, 9, 4, 1, 5, 6, 3, 4, 2, 3, 2, 0, 6, 7, 1, 5, 5,
		    7, 2, 5, 4, 0, 0, 6, 1, 7, 8, 5, 7, 6, 5, 7, 1, 7,
		    2, 3, 7, 5, 1, 9, 8, 6, 9, 4, 6, 5, 8, 1, 9, 6, 1,
		    1, 7, 4, 2, 5, 6, 9, 7, 5, 2, 0, 1, 9, 6, 7, 2, 5,
		    6, 7, 1, 5, 8, 2, 5, 0, 8, 2, 0, 1, 6, 2, 0, 6, 4,
		    6, 9, 8, 1, 5, 6, 3, 7, 9, 7, 6, 9, 7, 2, 5, 4, 1,
		    5, 4, 1, 9, 1, 1, 0, 7, 1, 3, 1, 1, 1, 0, 1, 2, 6,
		    7, 1, 5, 5, 1, 9, 4, 1, 5, 6, 3, 2, 0, 9, 7, 6, 9,
		    7, 2, 5, 4, 1, 5, 4, 2, 0, 1, 9, 7, 8, 1, 9, 2, 5,
		    7, 1, 3, 1, 1, 0, 8, 6, 7, 1, 8, 5, 5, 5, 1, 8, 6,
		    7, 9, 8, 5, 6, 1, 1, 3, 6, 3, 2, 9, 6, 0, 7, 0, 7,
		    9, 7, 6, 9, 7, 2, 5, 4, 1, 3, 2, 0, 1, 3, 2, 0, 6,
		    6, 0, 8, 6, 7, 5, 5, 7, 2, 3, 1, 1, 7, 2, 0, 1, 5,
		    5, 7, 6, 5, 1, 3, 4, 3, 8, 9, 8, 1, 3, 2, 9, 6, 6,
		    0, 8, 6, 7, 6, 0, 7, 1, 3, 4, 7, 2, 3, 2, 9, 5, 9,
		    7, 1, 4, 4, 6, 7, 9, 6, 9, 2, 0, 1, 5, 7, 1, 9, 8,
		    1, 9, 1, 9, 8, 1, 5, 4, 6, 9, 2, 0, 2, 6, 7, 2, 0,
		    6, 8, 1, 8, 1, 1, 1, 1, 8, 2, 5, 6, 9, 8, 6, 5, 1,
		    1, 8, 1, 8, 3, 3, 3, 1, 8, 2, 5, 7, 6, 3, 4, 6, 5,
		    6, 9, 1, 2, 2, 8, 1, 8, 1, 1, 1, 1, 8, 6, 7, 9, 8,
		    1, 0, 2, 5, 6, 9, 4, 1, 5, 1, 3, 1, 2, 7, 2, 3, 5,
		    6, 5, 1, 3, 4, 3, 8, 9, 8, 1, 3, 2, 9, 6, 6, 0, 6,
		    1, 2, 3, 9, 6, 9, 2, 0, 5, 6, 5, 1, 1, 9, 2, 0, 7,
		    2, 3, 6, 7, 9, 8, 2, 5, 1, 9, 1, 5, 7, 6, 9, 6, 0,
		    2, 5, 4, 7, 2, 3, 9, 8, 1, 3, 2, 9, 6, 6, 7, 0, 2,
		    0, 7, 1, 5, 4, 1, 6, 7, 3, 8, 9, 2, 0, 5, 1, 1, 2,
		    3, 4, 1, 5, 4, 2, 5, 6, 9, 7, 5, 1, 7, 1, 7, 1, 5,
		    2, 2, 1, 5, 1, 7, 1, 7, 2, 0, 9, 6, 9, 8, 6, 6, 1,
		    9, 7, 0, 2, 0, 7, 9, 2, 0, 5, 1, 1, 2, 3, 4, 6, 8,
		    1, 8, 1, 1, 1, 1, 8, 6, 7, 1, 8, 2, 2, 2, 1, 8, 6,
		    7, 2, 5, 1, 3, 1, 2, 8, 6, 9, 3, 4, 0, 2, 0, 1, 0,
		    4, 2, 4, 2, 0, 2, 0, 2, 1, 4);

    } elseif ($this->type === TABLEAUX_TYPE_2) {
      $width = 14;
      // With disruption pattern
      $data = array(3, 0, 2, 7, 4, 3, 0, 4, 2, 8, 7, 7, 1, 2,
		    5, 13, 2, 9, 7, 6, 14, 8, 3, 12, 10, 11, 1, 4,
		    6, 5, 7, 3, 0, 9, 4, 3, 3, 7, 5, 7, '*', 1, 1,
		    9, 1, 8, 9, 3, 9, 1, 2, 3, 3, 4, 5, 4, '*', 2,
		    7, 9, 3, 3, 6, 0, 9, 6, 2, 6, 1, 9, 5, 0,
		    1, 2, '*', 1, 5, 9, 2, 1, 6, 1, 2, 4, 1, 4, 9,
		    5, 3, 0, '*', 1, 1, 3, 1, 6, 9, 0, 6, 6, 6, 6,
		    7, 1, 1, 3, '*', 2, 8, 2, 0, 2, 1, 5, 0, 3, 1,
		    8, 9, 3, 9, 8, '*', 8, 1, 4, 6, 5, 5, 1, 6, 2,
		    3, 1, 2, 7, 7, 1, '*', 6, 4, 2, 6, 2, 8, 0, 0,
		    1, 2, 2, 1, 2, 4, 6, '*', 1, 6, 5, 9, 2, 5, 6,
		    7, 0, 5, 7, 1, 8, 1, 1, '*', 9, 3, 0, 0, 6, 0,
		    3, 6, 9, 5, 2, 8, 2, 5, 8, '*', 1, 1, 6, 6, 8,
		    4, 6, 6, 2, 4, 8, 7, 1, 4, 5, '*', 1, 3, 4, 9,
		    2, 5, 1, 9, 5, 4, 1, 5, 9, 6, 5, '*', 1, 2, 7,
		    7, 4, 9, 8, 8, 2, 5, 3, 9, 7, 7, 5, '*', 1, 4,
		    5, 5, 2, 1, 1, 2, 0, 2, 0, 2, 2, 6, 1, '*', 8,
		    6, 1, 9, 6, 9, 1, 3, 9, 2, 1, 0, 5, 0, 2,
		    2, 4, 1, 9, 0, 6, 1, 1, '*', 5, 2, 6, 8, 8, 5,
		    5, 0, 1, 5, 8, 5, 1, 1, 1, '*', 6, 7, 1, 9, 3,
		    1, 6, 7, 7, 1, 6, 6, 8, 1, 3, '*', 7, 2, 1, 6,
		    2, 6, 4, 6, 9, 2, 4, 4, 1, 0, 1, '*', 0, 9, 2,
		    3, 0, 6, 1, 7, 9, 3, 2, 5, 6, 9, 1, '*', 1, 4,
		    6, 9, 3, 6, 1, 9, 0, 3, 7, 8, 5, 3, 8, '*', 3,
		    1, 8, 2, 9, 1, 2, 4, 1, 6, 7, 0, 7, 7, 1,
		    2, 6, 3, 4, 7, 3, 1, 6, 4, 1, 1, 8, 1, '*', 6,
		    9, 0, 5, 8, 7, 6, 7, 2, 6, 8, 2, 1, 0, 7,
		    '*', 8, 9, 5, 3, 0, 4, 4, 8, 1, 5, 5, 4, 7, 9,
		    2, '*', 5, 1, 3, 1, 4, 8, 2, 2, 9, 6, 5, 1, 9,
		    1, 9, '*', 8, 2, 0, 9, 2, 0, 1, 1, 6, 6, 1, 8,
		    8, 7, 8, '*', 9, 7, 4, 2, 1, 2, 7, 9, 6, 8, 4,
		    0, 1, 5, 5, '*', 0, 1, 7, 1, 4, 9, 2, 8, 7, 1,
		    8, 5, 2, 1, 6, '*', 7, 2, 1, 6, 6, 5, 7, 7, 7,
		    9, 2, 7, 9, 3, 4, '*', 7, 9, 6, 5, 0, 7, 1, 8,
		    6, 1, 1, 7, 9, 2, 5, '*', 1, 6, 1, 6, 1, 2, 2,
		    6, 0, 0, 6, 1, 3, 9, 8, '*', 0, 3, 2, 9, 1, 7,
		    2, 2, 1, 8, 7, 0, 2, 5, 5, '*', 4, 9, 9, 5, 1,
		    1, 3, 3, 6, 1, 2, 9, 5, 9, 1, '*', 0, 2, 0, 3,
		    8, 3, 0, 3, 1, 6, 1, 6, 0, 0, 1, '*', 5, 2, 1,
		    2, 4, 0, 4, 1, 7, 3, 1, 2, 7, 3, 0, '*', 9, 1,
		    2, 2, 1, 9, 4, 7, 0, 1, 1, 7, 9, 7, 0, '*', [9],
		    5, 1, 7, 9, 1, 7, 2, 0, 9, 9, 1, 7, 6, 4,
		    7, 2, 6, 2, 9, '*', 6, 1, 2, 2, 6, 7, 9, 6, 2,
		    7, 1, 7, 6, 4, 1, '*', 9, 8, 2, 7, 9, 5, 6, 6,
		    0, 2, 1, 1, 5, 4, 4, '*', 8, 9, 6, 7, 1, 0, 8,
		    9, 5, 2, 1, 9, 3, 7, 7, '*', 5, 6, 1, 7, 3, 3,
		    4, 1, 3, 0, 5, 1, 1, 6, 6, '*', 5, 2, 9, 6, 8,
		    5, 1, 6, 9, 9, 5, 5, 7, 1, 5, '*', 2, 9, 1, 7,
		    4, 1, 6, 9, 5, 6, 7, 6, 5, 6, 9, '*', 6, 0, 7,
		    8, 0, 1, 5, 8, 5, 6, 7, 0, 2, 2, 5, '*', 9, 2,
		    1, 8, 6, 0, 6, 3, 4, 1, 2, 7, 3, 1, 2, '*', 2,
		    2, 5, 6, 9, 8, 0, 9, 8, 3, 1, 2, 8, 2, 1,
		    1, 2, 6, 0, '*', 0, 9, 6, 0, 5, 6, 9, 2, 1, 5,
		    6, 2, 9, 2, 3, '*', 0, 8, 3, 2, 3, 9, 1, 1, 8,
		    7, 7, 9, 4, 1, 2, '*', 5, 5, 1, 3, 8, 5, 3, 3,
		    1, 9, 7, 0, 7, 8, 1, '*', 6, 5, 5, 4, 5, 7, 4,
		    9, 8, 8, 9, 0, 5, 2, 3, '*', 1, 8, 1, 5, 5, 3,
		    5, 7, 4, 2, 7, 8, 2, 2, 9, '*', 8, 6, 8, 6, 6,
		    3, 6, 6, 7, 5, 1, 3, 8, 1, 2, '*', 4, 1, 1, 1,
		    2, 8, 7, 1, 2, 2, 7, 2, 1, 1, 4, '*', 1, 2, 1,
		    6, 1, 6, 0, 2, 1, 0, 2, 7, 9, 5, 8, '*', 3, 6,
		    9, 1, 5, 0, 7, 6, 1, 2, 8, 3, 9, 6, 8, '*', 4,
		    8, 1, 5, 8, 6, 1, 1, 3, 9, 2, 0, 7, 6, 1,
		    6, 2, 9, 9, 5, 1, 3, '*', 1, 1, 1, 0, 1, 5, 4,
		    8, 5, 5, 0, 0, 2, 9, 6, '*', 2, 6, 4, 9, 6, 3,
		    9, 0, 0, 0, 9, 9, 1, 7, 3, '*', 2, 2, 7, 3, 4,
		    7, 5, 0, 6, 1, 3, 8, 4, 2, 2, '*', 2, 3, 4, 9,
		    7, 3, 6, 1, 1, 3, 3, 3, 9, 4, 2, '*', 0, 3, 0,
		    9, 2, 2, 1, 1, 1, 5, 9, 3, 8, 7, 0, '*', 9, 1,
		    5, 1, 9, 4, 1, 2, 2, 0, 9, 7, 6, 1, 1, '*', 2,
		    4, 5, 1, 7, 1, 7, 0, 2, 3, 7, 5, 5, 7, 4,
		    1, 3, 1, '*', 9, 3, 1, 6, 3, 1, 2, 8, 7, 5, 1,
		    9, 1, 7, 0, '*', 6, 2, 2, 0, 9, 1, 5, 0, 3, 2,
		    7, 5, 1, 1, 9, '*', 2, 2, 7, 6, 8, 3, 6, 7, 6,
		    1, 2, 7, 5, 9, 0, '*', 9, 6, 6, 5, 1, 8, 3, 2,
		    1, 1, 2, 1, 0, 6, 7, '*', 2);
      // Without disruption pattern
      $data = array(3, 0, 2, 7, 4, 3, 0, 4, 2, 8, 7, 7, 1, 2,
		    5, 13, 2, 9, 7, 6, 14, 8, 3, 12, 10, 11, 1, 4,
		    6, 5, 7, 3, 0, 9, 4, 3, 3, 7, 5, 7, 1, 1,
		    9, 1, 8, 9, 3, 9, 1, 2, 3, 3, 4, 5, 4, 2,
		    7, 9, 3, 3, 6, 0, 9, 6, 2, 6, 1, 9, 5, 0,
		    1, 2, 1, 5, 9, 2, 1, 6, 1, 2, 4, 1, 4, 9,
		    5, 3, 0, 1, 1, 3, 1, 6, 9, 0, 6, 6, 6, 6,
		    7, 1, 1, 3, 2, 8, 2, 0, 2, 1, 5, 0, 3, 1,
		    8, 9, 3, 9, 8, 8, 1, 4, 6, 5, 5, 1, 6, 2,
		    3, 1, 2, 7, 7, 1, 6, 4, 2, 6, 2, 8, 0, 0,
		    1, 2, 2, 1, 2, 4, 6, 1, 6, 5, 9, 2, 5, 6,
		    7, 0, 5, 7, 1, 8, 1, 1, 9, 3, 0, 0, 6, 0,
		    3, 6, 9, 5, 2, 8, 2, 5, 8, 1, 1, 6, 6, 8,
		    4, 6, 6, 2, 4, 8, 7, 1, 4, 5, 1, 3, 4, 9,
		    2, 5, 1, 9, 5, 4, 1, 5, 9, 6, 5, 1, 2, 7,
		    7, 4, 9, 8, 8, 2, 5, 3, 9, 7, 7, 5, 1, 4,
		    5, 5, 2, 1, 1, 2, 0, 2, 0, 2, 2, 6, 1, 8,
		    6, 1, 9, 6, 9, 1, 3, 9, 2, 1, 0, 5, 0, 2,
		    2, 4, 1, 9, 0, 6, 1, 1, 5, 2, 6, 8, 8, 5,
		    5, 0, 1, 5, 8, 5, 1, 1, 1, 6, 7, 1, 9, 3,
		    1, 6, 7, 7, 1, 6, 6, 8, 1, 3, 7, 2, 1, 6,
		    2, 6, 4, 6, 9, 2, 4, 4, 1, 0, 1, 0, 9, 2,
		    3, 0, 6, 1, 7, 9, 3, 2, 5, 6, 9, 1, 1, 4,
		    6, 9, 3, 6, 1, 9, 0, 3, 7, 8, 5, 3, 8, 3,
		    1, 8, 2, 9, 1, 2, 4, 1, 6, 7, 0, 7, 7, 1,
		    2, 6, 3, 4, 7, 3, 1, 6, 4, 1, 1, 8, 1, 6,
		    9, 0, 5, 8, 7, 6, 7, 2, 6, 8, 2, 1, 0, 7,
		    8, 9, 5, 3, 0, 4, 4, 8, 1, 5, 5, 4, 7, 9,
		    2, 5, 1, 3, 1, 4, 8, 2, 2, 9, 6, 5, 1, 9,
		    1, 9, 8, 2, 0, 9, 2, 0, 1, 1, 6, 6, 1, 8,
		    8, 7, 8, 9, 7, 4, 2, 1, 2, 7, 9, 6, 8, 4,
		    0, 1, 5, 5, 0, 1, 7, 1, 4, 9, 2, 8, 7, 1,
		    8, 5, 2, 1, 6, 7, 2, 1, 6, 6, 5, 7, 7, 7,
		    9, 2, 7, 9, 3, 4, 7, 9, 6, 5, 0, 7, 1, 8,
		    6, 1, 1, 7, 9, 2, 5, 1, 6, 1, 6, 1, 2, 2,
		    6, 0, 0, 6, 1, 3, 9, 8, 0, 3, 2, 9, 1, 7,
		    2, 2, 1, 8, 7, 0, 2, 5, 5, 4, 9, 9, 5, 1,
		    1, 3, 3, 6, 1, 2, 9, 5, 9, 1, 0, 2, 0, 3,
		    8, 3, 0, 3, 1, 6, 1, 6, 0, 0, 1, 5, 2, 1,
		    2, 4, 0, 4, 1, 7, 3, 1, 2, 7, 3, 0, 9, 1,
		    2, 2, 1, 9, 4, 7, 0, 1, 1, 7, 9, 7, 0, 9,
		    5, 1, 7, 9, 1, 7, 2, 0, 9, 9, 1, 7, 6, 4,
		    7, 2, 6, 2, 9, 6, 1, 2, 2, 6, 7, 9, 6, 2,
		    7, 1, 7, 6, 4, 1, 9, 8, 2, 7, 9, 5, 6, 6,
		    0, 2, 1, 1, 5, 4, 4, 8, 9, 6, 7, 1, 0, 8,
		    9, 5, 2, 1, 9, 3, 7, 7, 5, 6, 1, 7, 3, 3,
		    4, 1, 3, 0, 5, 1, 1, 6, 6, 5, 2, 9, 6, 8,
		    5, 1, 6, 9, 9, 5, 5, 7, 1, 5, 2, 9, 1, 7,
		    4, 1, 6, 9, 5, 6, 7, 6, 5, 6, 9, 6, 0, 7,
		    8, 0, 1, 5, 8, 5, 6, 7, 0, 2, 2, 5, 9, 2,
		    1, 8, 6, 0, 6, 3, 4, 1, 2, 7, 3, 1, 2, 2,
		    2, 5, 6, 9, 8, 0, 9, 8, 3, 1, 2, 8, 2, 1,
		    1, 2, 6, 0, 0, 9, 6, 0, 5, 6, 9, 2, 1, 5,
		    6, 2, 9, 2, 3, 0, 8, 3, 2, 3, 9, 1, 1, 8,
		    7, 7, 9, 4, 1, 2, 5, 5, 1, 3, 8, 5, 3, 3,
		    1, 9, 7, 0, 7, 8, 1, 6, 5, 5, 4, 5, 7, 4,
		    9, 8, 8, 9, 0, 5, 2, 3, 1, 8, 1, 5, 5, 3,
		    5, 7, 4, 2, 7, 8, 2, 2, 9, 8, 6, 8, 6, 6,
		    3, 6, 6, 7, 5, 1, 3, 8, 1, 2, 4, 1, 1, 1,
		    2, 8, 7, 1, 2, 2, 7, 2, 1, 1, 4, 1, 2, 1,
		    6, 1, 6, 0, 2, 1, 0, 2, 7, 9, 5, 8, 3, 6,
		    9, 1, 5, 0, 7, 6, 1, 2, 8, 3, 9, 6, 8, 4,
		    8, 1, 5, 8, 6, 1, 1, 3, 9, 2, 0, 7, 6, 1,
		    6, 2, 9, 9, 5, 1, 3, 1, 1, 1, 0, 1, 5, 4,
		    8, 5, 5, 0, 0, 2, 9, 6, 2, 6, 4, 9, 6, 3,
		    9, 0, 0, 0, 9, 9, 1, 7, 3, 2, 2, 7, 3, 4,
		    7, 5, 0, 6, 1, 3, 8, 4, 2, 2, 2, 3, 4, 9,
		    7, 3, 6, 1, 1, 3, 3, 3, 9, 4, 2, 0, 3, 0,
		    9, 2, 2, 1, 1, 1, 5, 9, 3, 8, 7, 0, 9, 1,
		    5, 1, 9, 4, 1, 2, 2, 0, 9, 7, 6, 1, 1, 2,
		    4, 5, 1, 7, 1, 7, 0, 2, 3, 7, 5, 5, 7, 4,
		    1, 3, 1, 9, 3, 1, 6, 3, 1, 2, 8, 7, 5, 1,
		    9, 1, 7, 0, 6, 2, 2, 0, 9, 1, 5, 0, 3, 2,
		    7, 5, 1, 1, 9, 2, 2, 7, 6, 8, 3, 6, 7, 6,
		    1, 2, 7, 5, 9, 0, 9, 6, 6, 5, 1, 8, 3, 2,
		    1, 1, 2, 1, 0, 6, 7, 2);
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
function fiveGroups($stream) {
  // Construct the groups of five
  $page = array();
  for ($idx = 0; $idx < strlen($stream); $idx += FIVEGROUP_NUM) {
    $page[] = substr($stream, $idx, FIVEGROUP_NUM);
  }

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
