<?php

namespace viccipher;

define("ENCIPHER", true);
define("DECIPHER", false);
define("CHAIN", 3);
define("RU_ALPHABET", "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ");
define("RU_ALPHABET_IGNORE", "ЁЙЪ");
define("BAD_UNSUBSTITUTIONS_ERROR", "Stream corrupt or derivation data wrong\n");

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
// Languages. This is about the alphabet so en-GB and en-US are identical
define('LANG_CYRILLIC', 'ru');
define('LANG_ROMAN', 'en');

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
                           (Default=148)
  -t, --poem=STRING        Use STRING to form poem part of key3. Lines marked
                           by \\n or \\r\\n

Examples:
  php ./vic-poc.php -");
