#!/bin/bash

ALPHABET="АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ"
ALPHABET_IGNORE="ЁЙЪ"
TEST_POEM="СНОВА ЗАМЕРЛО ВСЕ ДО РАССВЕТА
ДВЕРЬ НЕ СКРИПНЕТ НЕ ВСПЫХНЕТ ОГОНЬ
ТОЛЬКО СЛЫШНО НА УЛИЦЕ ГДЕ-ТО
ОДИНОКАЯ БРОДИТ ГАРМОНЬ
ТОЛЬКО СЛЫШНО НА УЛИЦЕ ГДЕ-ТО
ОДИНОКАЯ БРОДИТ ГАРМОНЬ
ТО ПОЙДЕТ НА ПОЛЯ ЗА ВОРОТА
ТО ВЕРНЕТСЯ ОБРАТНО ОПЯТЬ
СЛОВНО ИЩЕТ В ПОТЕМКАХ КОГО-ТО
И НЕ МОЖЕТ НИКАК ОТЫСКАТЬ
СЛОВНО ИЩЕТ В ПОТЕМКАХ КОГО-ТО
И НЕ МОЖЕТ НИКАК ОТЫСКАТЬ
ВЕЕТ С ПОЛЯ НОЧНАЯ ПРОХЛАДА
С ЯБЛОНЬ ЦВЕТ ОБЛЕТАЕТ ГУСТОЙ
ТЫ ПРИЗНАЙСЯ КОГО ТЕБЕ НАДО
ТЫ СКАЖИ ГАРМОНИСТ МОЛОДОЙ
ТЫ ПРИЗНАЙСЯ КОГО ТЕБЕ НАДО
ТЫ СКАЖИ ГАРМОНИСТ МОЛОДОЙ
МОЖЕТ РАДОСТЬ ТВОЯ НЕДАЛЕКО
ДА НЕ ЗНАЕТ ЕЕ ЛИ ТЫ ЖДЕШЬ
ЧТО Ж ТЫ БРОДИШЬ ВСЮ НОЧЬ ОДИНОКО
ЧТО Ж ТЫ ДЕВУШКАМ СПАТЬ НЕ ДАЕШЬ
ЧТО Ж ТЫ БРОДИШЬ ВСЮ НОЧЬ ОДИНОКО
ЧТО Ж ТЫ ДЕВУШКАМ СПАТЬ НЕ ДАЕШЬ"
TEST_PLAINTEXT='1. ПОЗДРАВЛЯЕМ С БЛАГОПОЛУЧНЫМ ПРИБЫТИЕМ. ПОДТВЕРЖДАЕМ ПОЛУЧЕНИЕ ВАШЕГО ПИСЬМА В АДРЕС ,,В@В,, И ПРОЧТЕНИЕ ПИСЬМА №1.
2. ДЛЯ ОРГАНИЗАЦИИ ПРИКРЫТИЯ МЫ ДАЛИ УКАЗАНИЕ ПЕРЕДАТЬ ВАМ ТРИ ТЫСЯЧИ МЕСТНЫХ. ПЕРЕД ТЕМ КАК ИХ ВЛОЖИТЬ В КАКОЕ ЛИБО ДЕЛО ПОСОВЕТУИТЕСЬ С НАМИ, СООБЩИВ ХАРАКТЕРИСТИКУ ЭТОГО ДЕЛА.
3. ПО ВАШЕИ ПРОСЬБЕ РЕЦЕПТУРУ ИЗГОТОВЛЕНИЯ МЯГКОИ ПЛЕНКИ И НОВОСТЕИ ПЕРЕДАДИМ ОТДЕЛЬНО ВМЕСТЕ С ПИСЬМОМ МАТЕРИ.
4. ГАММЫ ВЫСЫЛАТЬ ВАМ РАНО. КОРОТКИЕ ПИСЬМА ШИФРУИТЕ, А ПОБОЛЬШЕТИРЕ ДЕЛАИТЕ СО ВСТАВКАМИ. ВСЕ ДАННЫЕ О СЕБЕ, МЕСТО РАБОТЫ, АДРЕС И Т.Д. В ОДНОИ ШИФРОВКЕ ПЕРЕДАВАТЬ НЕЛЬЗЯ. ВСТАВКИ ПЕРЕДАВАИТЕ ОТДЕЛЬНО.
5. ПОСЫЛКУ ЖЕНЕ ПЕРЕДАЛИ ЛИЧНО. С СЕМЬЕИ ВСЕ БЛАГОПОЛУЧНО. ЖЕЛАЕМ УСПЕХА. ПРИВЕТ ОТ ТОВАРИЩЕИ
№1 ДРОБЬО 3 ДЕКАБРЯ'
TEST_CIPHERTEXT="14546 36056 64211 08919 18710 71187 71215 02906 66036 10922
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
15764 96851 20818 22370 11391 83520 62297"

# Encipher
RVE=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/9/1945" --key4=13 --msgnum="20818" --padding="2142" --swappos=148 --poem="$TEST_POEM" --message="$TEST_PLAINTEXT"`
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

#
# These tests should pass

# Decipher
RVD=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/9/1945" --key4=13 --padding="2142" --poem="$TEST_POEM" --message="$TEST_CIPHERTEXT" --decrypt`
SQUASHED_PLAINTEXT=`echo "$TEST_PLAINTEXT" | tr -d '[:space:]'`
SQUASHED_PLAINTEXT=`echo "MsgID: 20818"; echo "$SQUASHED_PLAINTEXT"`
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
CIPHERTEXT=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/9/1945" --key4=13 --msgnum="20818" --padding="2142" --swappos=148 --poem="$TEST_POEM" --message="$TEST_PLAINTEXT"`
RVC=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/9/1945" --key4=13 --padding="2142" --poem="$TEST_POEM" --message="$CIPHERTEXT" --decrypt`
SQUASHED_PLAINTEXT=`echo "$TEST_PLAINTEXT" | tr -d '[:space:]'`
SQUASHED_PLAINTEXT=`echo "MsgID: 20818"; echo "$SQUASHED_PLAINTEXT"`
if [ "$RVC" = "$SQUASHED_PLAINTEXT" ]; then
    echo "Chaining passes"
else
    echo "Chaining fails"

    echo "$RVC" >/tmp/1
    echo "--"
    echo "$SQUASHED_PLAINTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi

#
# These tests should fail

# Encipher
RVE=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=2 --key3="3/9/1945" --key4=13 --msgnum="20818" --padding="2142" --swappos=148 --poem="$TEST_POEM" --message="$TEST_PLAINTEXT"`
RV=$?
if [ "$RVE" != "$TEST_CIPHERTEXT" ]; then
    echo "Encipher fails successfully"
else
    echo "Encipher succeeds when it shouldn't"

    echo "$RVE" >/tmp/1
    echo "--"
    echo "$TEST_CIPHERTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi

# Decipher
RVD=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/1/1945" --key4=13 --padding="2142" --poem="$TEST_POEM" --message="$TEST_CIPHERTEXT" --decrypt`
SQUASHED_PLAINTEXT=`echo "$TEST_PLAINTEXT" | tr -d '[:space:]'`
SQUASHED_PLAINTEXT=`echo "MsgID: 20818"; echo "$SQUASHED_PLAINTEXT"`
if [ "$RVD" != "$SQUASHED_PLAINTEXT" ]; then
    echo "Decipher fails successfully"
else
    echo "Decipher succeeds when it shouldn't"

    echo "$RVD" >/tmp/1
    echo "--"
    echo "$SQUASHED_PLAINTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi

# Encipher, then decipher
CIPHERTEXT=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/9/1945" --key4=12 --msgnum="20818" --padding="2142" --swappos=148 --poem="$TEST_POEM" --message="$TEST_PLAINTEXT"`
RVC=`/usr/bin/php ./vic-poc.php --alphabet="$ALPHABET" --alphabet-ignore="$ALPHABET_IGNORE" --key1="СНЕГОПА" --key2=3 --key3="3/9/1945" --key4=14 --padding="2142" --poem="$TEST_POEM" --message="$CIPHERTEXT" --decrypt`
SQUASHED_PLAINTEXT=`echo "$TEST_PLAINTEXT" | tr -d '[:space:]'`
SQUASHED_PLAINTEXT=`echo "MsgID: 20818"; echo "$SQUASHED_PLAINTEXT"`
if [ "$RVC" != "$SQUASHED_PLAINTEXT" ]; then
    echo "Chaining fails successfully"
else
    echo "Chaining succeeds when it shouldn't"

    echo "$RVC" >/tmp/1
    echo "--"
    echo "$SQUASHED_PLAINTEXT" >/tmp/2

    diff /tmp/1 /tmp/2
fi
