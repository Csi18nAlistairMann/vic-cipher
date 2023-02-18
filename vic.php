<?php

namespace viccipher;

//
// Proof of concept that the VIC cipher process has been understood
// Have a look through accompanying test.sh for example uses
// Alistair Mann, 2023

// Classes
require_once('FiveDigitGroups.class.php');
require_once('Derivations.class.php');
require_once('TranspositionTableaux.class.php');
require_once('Checkerboard.class.php');
// Defines in common
require_once('defines-common.php');
// Functions
require_once('functions-misc.php');
require_once('functions-mainloop.php');

// Used when run from command line
require_once('defines-with-commandline.php');

$key1 = '';
$key2 = '';
$key3 = '';
$key4 = '';
$message = '';
$direction = ENCIPHER;

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
// Merge in any piped message, overiding what's on the command line
stream_set_blocking(STDIN, false);
$pipedin = stream_get_contents(fopen("php://stdin", "r"));
$m_opt = ($pipedin === "") ? $m_opt : $pipedin;

mainloop($a_opt, $b_opt, $k1_opt, $k2_opt, $k3_opt, $k4_opt, $n_opt, $d_opt,
         $s_opt, $p_opt, $m_opt);
exit;
