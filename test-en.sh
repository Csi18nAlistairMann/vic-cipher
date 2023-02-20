#!/bin/bash

ALPHABET="ABCDEFGHIJKLMNOPQRSTUVWXYZ"
ALPHABET_IGNORE=""
TEST_POEM="IF YOU CAN KEEP YOUR HEAD WHEN ALL ABOUT YOU
    ARE LOSING THEIRS AND BLAMING IT ON YOU,
IF YOU CAN TRUST YOURSELF WHEN ALL MEN DOUBT YOU,
    BUT MAKE ALLOWANCE FOR THEIR DOUBTING TOO;
IF YOU CAN WAIT AND NOT BE TIRED BY WAITING,
    OR BEING LIED ABOUT, DON’T DEAL IN LIES,
OR BEING HATED, DON’T GIVE WAY TO HATING,
    AND YET DON’T LOOK TOO GOOD, NOR TALK TOO WISE:

IF YOU CAN DREAM—AND NOT MAKE DREAMS YOUR MASTER;
    IF YOU CAN THINK—AND NOT MAKE THOUGHTS YOUR AIM;
IF YOU CAN MEET WITH TRIUMPH AND DISASTER
    AND TREAT THOSE TWO IMPOSTORS JUST THE SAME;
IF YOU CAN BEAR TO HEAR THE TRUTH YOU’VE SPOKEN
    TWISTED BY KNAVES TO MAKE A TRAP FOR FOOLS,
OR WATCH THE THINGS YOU GAVE YOUR LIFE TO, BROKEN,
    AND STOOP AND BUILD ’EM UP WITH WORN-OUT TOOLS:

IF YOU CAN MAKE ONE HEAP OF ALL YOUR WINNINGS
    AND RISK IT ON ONE TURN OF PITCH-AND-TOSS,
AND LOSE, AND START AGAIN AT YOUR BEGINNINGS
    AND NEVER BREATHE A WORD ABOUT YOUR LOSS;
IF YOU CAN FORCE YOUR HEART AND NERVE AND SINEW
    TO SERVE YOUR TURN LONG AFTER THEY ARE GONE,
AND SO HOLD ON WHEN THERE IS NOTHING IN YOU
    EXCEPT THE WILL WHICH SAYS TO THEM: ‘HOLD ON!’

IF YOU CAN TALK WITH CROWDS AND KEEP YOUR VIRTUE,
    OR WALK WITH KINGS—NOR LOSE THE COMMON TOUCH,
IF NEITHER FOES NOR LOVING FRIENDS CAN HURT YOU,
    IF ALL MEN COUNT WITH YOU, BUT NONE TOO MUCH;
IF YOU CAN FILL THE UNFORGIVING MINUTE
    WITH SIXTY SECONDS’ WORTH OF DISTANCE RUN,
YOURS IS THE EARTH AND EVERYTHING THAT’S IN IT,
    AND—WHICH IS MORE—YOU’LL BE A MAN, MY SON!"
TEST_PLAINTEXT="1. WE CONGRATULATE YOU ON [YOUR] SAFE ARRIVAL. WE CONFIRM THE RECEIPT OF YOUR LETTER TO THE ADDRESS \"V REPEAT V\" AND THE READING OF [YOUR] LETTER NO. 1.
2. FOR ORGANIZATION OF COVER WE HAVE GIVEN INSTRUCTIONS TO TRANSMIT TO YOU THREE THOUSAND IN LOCAL [CURRENCY]. CONSULT WITH US PRIOR TO INVESTING IT IN ANY KIND OF BUSINESS ADVISING THE CHARACTER OF THE BUSINESS.
3. ACCORDING TO YOUR REQUEST WE WILL TRANSMIT THE FORMULA FOR THE PREPARATION OF SOFT FILM AND THE NEWS SEPARATELY, TOGETHER WITH [YOUR] MOTHER'S LETTER.
4. [IT IS TOO] EARLY TO SEND YOU THE GAMMAS. ENCIPHER SHORT LETTERS, BUT DO THE LONGER ONES WITH INSERTIONS. ALL THE DATA ABOUT YOURSELF, PLACE OF WORK, ADDRESS, ETC., MUST NOT BE TRANSMITTED IN ONE CIPHER MESSAGE. TRANSMIT INSERTIONS SEPARATELY.
5. THE PACKAGE WAS DELIVERED TO [YOUR] WIFE PERSONALLY. EVERYTHING IS ALL RIGHT WITH [YOUR] FAMILY. WE WISH [YOU] SUCCESS. GREETINGS FROM THE COMRADES. NO. 1, 3 DECEMBER."
TEST_CIPHERTEXT="20187 56267 69548 76463 53423 61675 11661 31823 73858 87186
88864 77216 18762 93895 35921 86889 14259 15571 76111 42628
14238 06755 86128 91162 57825 58516 78867 62383 33256 51762
86795 78658 37639 49788 31396 10806 63699 90876 97617 61356
61776 18758 87875 86761 51866 29507 01528 92555 77817 21748
71659 62216 27577 52561 71377 87974 50766 97303 65262 68086
86428 50672 10171 71178 19376 25667 87532 58977 69218 80684
47486 73904 19868 10509 83256 86779 23358 65525 84691 67255
67939 87738 17072 56077 76096 69417 77341 61034 85190 15115
36575 11893 61597 74651 76461 85325 26600 01047 77676 72688
52565 59437 48466 42088 83381 66771 70111 76376 14689 85167
75555 57223 42248 64177 05314 10605 88910 63212 58688 28766
19312 58147 64121 92543 58501 08525 39774 01688 78402 37663
20550 57672 57123 58469 78483 91227 48727 66677 91678 28837
67788 92261 07926 84494 27750 13236 72574 97107 46271 53942
85072 23704 86561 16466 85237 13512 51637 05226 75975 26526
37722 27778 29381 48676 71466 82772 76286 66936 73651 56661
92745 39177 67766 62748 92246 90964 69012 37729 69665 87126
61307 57086 26906 58474 92126 59768 18326 96586 07385 09668
96675 29293 57534 69988 37894 96523 93684 75698 61271 05352
67388 67727 86683 78277 73696 62416 93122 19378 87661 29582
82871 26676 11796 63984 56490 34206 68368 89795 71275 82426
56472 85768 51257 48663 46510 99881 65728 75880 71786 95597
16487 87736 57499 75527 37268 65625 05156 56189 77569 89693
91316 96417"

# Instead of СНЕГОПА, CALORIE for being seven unique letters with four vowels
# Instead of 3, 4 for no other reason than Because
# Instead of 3/9/1945, 31/10/2008 the date of Satoshi Nakamoto's announcement
# Instead of 13, 7 after 007
# Instead of 20818, 57499 chosen through an online RNG
# Instead of padding=2142, padding left empty. Encryption to fill in with QGQG on seeing English

# Encipher
RVE=`/usr/bin/php ./vic.php --lang=en --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="CALORIE" --key2=4 --key3="31/10/2008" --key4=7 --msgnum="57499" --swappos=0 --poem="$TEST_POEM" --message="$TEST_PLAINTEXT"`
RV=$?
if [ "$RVE" = "$TEST_CIPHERTEXT" ]; then
    echo "Encipher passes"
else
    echo "Encipher fails"

    echo "$RVE" >/tmp/1
    echo "--"
    echo "$TEST_CIPHERTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi

# Decipher
RVD=`/usr/bin/php ./vic.php --lang=en --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="CALORIE" --key2=4 --key3="31/10/2008" --key4=7 --poem="$TEST_POEM" --message="$TEST_CIPHERTEXT" --decrypt`
SQUASHED_PLAINTEXT=`echo "$TEST_PLAINTEXT" | tr -d '[:space:]\[\]\"' | tr -d "'"`
SQUASHED_PLAINTEXT=`echo "MsgID: 57499"; echo "$SQUASHED_PLAINTEXT"`
if [ "$RVD" = "$SQUASHED_PLAINTEXT" ]; then
    echo "Decipher passes"
else
    echo "Decipher fails"

    echo "$RVD" >/tmp/1
    echo "--"
    echo "$SQUASHED_PLAINTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi

# Encipher, then decipher
CIPHERTEXT=`/usr/bin/php ./vic.php --lang=en --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="CALORIE" --key2=4 --key3="31/10/2008" --key4=7 --msgnum="57499" --swappos=0 --poem="$TEST_POEM" --message="$TEST_PLAINTEXT"`
RVC=`/usr/bin/php ./vic.php --lang=en --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="CALORIE" --key2=4 --key3="31/10/2008" --key4=7 --poem="$TEST_POEM" --message="$CIPHERTEXT" --decrypt`
SQUASHED_PLAINTEXT=`echo "$TEST_PLAINTEXT" | tr -d '[:space:]\[\]\"' | tr -d "'"`
SQUASHED_PLAINTEXT=`echo "MsgID: 57499"; echo "$SQUASHED_PLAINTEXT"`
if [ "$RVC" = "$SQUASHED_PLAINTEXT" ]; then
    echo "Chaining passes"
else
    echo "Chaining fails"

    echo "$RVC" >/tmp/1
    echo "--"
    echo "$SQUASHED_PLAINTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi