# Introduction

Cryptonomicon (Stephenson 1999) mentions in a passage by Schneier that there's an interesting explanation of a Soviet system elsewhere. That elsewhere appears to be the chapter "Two Soviet Spy Ciphers" - a reprint of an 1960 article in Kahn On Codes (Kahn 1983). Of the two Ciphers mentioned one ("Abel") appears to be a One-time Pad cipher and so less interesting. The first though - the VIC Cipher - is described in such detail that I wonder if I could recreate it.

Alas I can't find an online copy of the chapter. While there are voluminous notes online I'm challenging myself to use just the article's contents - now 63 years old. I'll permit cheating beyond this in the case of reusing typed-up figures and that suggests my first task: getting computer-readable copies of the data within. Such as I found [is below](#figures).

# Measuring success
- Can encipher the book's test message ([See from "1. ПОЗДРАВЛЯЕМ" here](#russian))
- Can decipher the book's ciphertext ([See from "14546" here](#figure-1))
- Can complete above from command line
- Optional: if I find other reimplementations, can exchange messages with them
I'm aware that there may be errors in the data I've used, so I'd like also to determine which if any are correct.

# Process
## Capture computer readable sections
Presented [below under "Figures"](#figures). Much were copy/pasted from articles found online and edited into shape. There appears occasional disagreement between the book and a source - any such highlighted as they arise.

## Capture keys
[Shown here](#keys).

## Process to encipher
Book specifies that encipherment will be described.
1. Create the checkerboard and transposition tables?
1. Start with the message to encipher [given below](#russian) below.
1. For each number in the text, replace it with Н/Ц, that number repeated three times, and Н/Ц again. So " 3 " would be replaced with " Н/Ц333Н/Ц ". I've changed "Н/Ц" and other such tokens to single character tokens such as "#" to assist in processing, but that may need changing in order to interact with others.
1. Randomly chop the plaintext in two, take the second side first, append 'Н/Т', then append the first side
1. Substitution via checkerboard [given here](#Checkerboard)
    1. Row 0 and Column 0 are both reserved for the coordinates to be added in later
    1. To get the coord of a character in row 1 `СНЕГOПА`, only the top title is used. So E has the coordinate 7.
    1. In the remaining rows, use the left title before the top title. So B has the co-ordinate 15
    1. This gives us a stream starting 9 69 20 63 ... (See [Figure 2](#figure-2))
1. Pass through first transposition table 17 cols by N (See [Figure 3](#figure-3))
    1. First two rows how?
    1. Second row indicates which order to access for next step
    1. Take the stream from step 5 and fill in the first table from the third row moving left to right, top to bottom
1. Pass through second transposition table (See [Figure 4](#figure-4))
    1. Construct the second transposition table
	- 14 cols by N. How?
    1. Fill in top two rows how?
    1. Create disruption areas based on "1" in second row, with area extending to right, and on following row starting one character to right, and so on. Once there are no more to move to right, skip a row, and repeat with "2" in the second row
	- Disruption starts right of * in each row
    3. Now read columns starting with "1" in second row of first transposition table and work down; when the column ends, continue with "2" and so on.
    4. Enter the stream at the first undisrupted space at top left, and continue along row until no more undisrupted spaces are available. When that happens, continue on next row at left side. Continue until there are no more undisrupted spaces available in the second transposition table
    5. Now return to the highest row with a disrupted space, and at its leftmost available slot continue to paste the stream, from left to right. With the row filled up, repeat this step at the new highest row with empty disrupted space.
1. Construct the output stream, reading down the "1" column from the second transposition table, ignoring disruptor space, and taking five digits at a time.

# Writing code
- First commit has many of the sections I'll be using (constants, command line, classes, functions, main, etc)
- Also does preprocessing not directly mentioned by the book
- And gets as far as swapping the halves of the message
- Next implements the Checkerboard
- Next implements the Transposition tables initial work, and some presentation

# Keys
Book states four keys:
1. The Russian word for snowfall: СНЕГOПА
1. Part of a folk song: The first 20 letters of the third line of ["The Lone Accordion" by Korovyeff](https://lyricstranslate.com/en/odinokaya-garmon039-odinokaya-garmon-lonely-accordion.html): "ТОЛЬКО СЛЫШНО НА УЛИЦЕ Г"
1. A patriotic date: 3/9/1945 ([3rd Sept 1945 Russian VoJ date](https://ru.wikisource.org/wiki/%D0%A3%D0%BA%D0%B0%D0%B7_%D0%9F%D1%80%D0%B5%D0%B7%D0%B8%D0%B4%D0%B8%D1%83%D0%BC%D0%B0_%D0%92%D0%A1_%D0%A1%D0%A1%D0%A1%D0%A0_%D0%BE%D1%82_2.09.1945_%D0%BE%D0%B1_%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B8_3_%D1%81%D0%B5%D0%BD%D1%82%D1%8F%D0%B1%D1%80%D1%8F_%D0%BF%D1%80%D0%B0%D0%B7%D0%B4%D0%BD%D0%B8%D0%BA%D0%BE%D0%BC_%D0%BF%D0%BE%D0%B1%D0%B5%D0%B4%D1%8B_%D0%BD%D0%B0%D0%B4_%D0%AF%D0%BF%D0%BE%D0%BD%D0%B8%D0%B5%D0%B9))
1. A number: 13

# Cyrillic alphabet
It's not stated but the book assumes the agent would know the order of the standard Russian alphabet
```
А Б В Г Д Е Ё Ж З И Й К Л М Н О П Р С Т У Ф Х Ц Ч Ш Щ Ъ Ы Ь Э Ю Я
```
For the purposes of the VIC Cipher, we're told to skip diacritics:
```
А Б В Г Д Е   Ж З И   К Л М Н О П Р С Т У Ф Х Ц Ч Ш Щ   Ы Ь Э Ю Я
```

# Figures
These attenpt to reconstruct the figures used in the article  in computer readable form.
## Figure 1
The message discovered in the hollow nickel
```
14546 36056 64211 08919 18710 71187 71215 02906 66036 10927
11375 61233 65634 39175 37378 31013 22596 19291 17463 23551
88527 10130 01767 12366 16669 97846 76559 50062 91171 72332
19262 69849 90251 11576 46121 24666 05902 19229 56150 23521
51911 78912 32939 31966 12096 12060 89748 25362 43167 99841
76271 31154 26938 77221 58343 61164 14349 01241 26269 71578
31734 27562 51236 12982 13089 66218 22577 09454 01216 71958
26948 89779 54197 11990 23881 48884 22165 62994 35449 41742
30267 77614 31565 30902 65812 16112 93312 71220 62369 12872
12458 19081 97117 70107 06391 71114 19459 59586 80317 07522
76509 11111 35990 32666 04411 51532 91184 23162 82011 19185
56110 28876 76716 03563 28222 31674 39023 07623 93513 97175
29816 95761 69483 32591 97696 34992 61105 95090 24092 71008
90061 14790 15154 14655 29011 57206 77195 01256 69250 62901
39179 71229 23299 84164 45900 42227 65853 17591 60182 06315
65812 01378 14566 87719 92507 79517 99551 82155 58118 67197
30015 70687 36201 56531 56721 26306 57135 91796 51341 07796
76655 62718 33588 91902 16224 87721 23519 23191 20665 45140
66093 60959 71521 02334 21212 51110 85227 98768 11125 05321
53152 14191 12166 12715 03116 43041 74827 72759 29130 21947
15764 96851 20618 22370 11391 43520 62297
```
https://www.prc68.com/I/NickelSpy.shtml
https://wikimedia.org/api/rest_v1/media/math/render/svg/b5ae818af4e893926550762289221c998ec301e9

## Origin of Figure 1
### English
Edited from https://cryptome.org/2013/05/kahn-moscow-cipher.pdf to match book. The VIC Cipher does not use English at any point, so it's reprinted here for interest.
```
1. We congratulate you on [your] safe arrival. We confirm the receipt of your letter to the address "V repeat V" and the reading of [your] letter No. 1.
2. For organization of cover we have given instructions to transmit to you three thousand in local [currency]. Consult with us prior to investing it in any kind of business advising the character of the business.
3. According to your request we will transmit the formula for the preparation of soft film and the news separately, together with [your] mother's letter.
4. [It is too] early to send you the gammas. Encipher short letters, but do the longer ones with insertions. All the data about yourself, place of work, address, etc., must not be transmitted in one cipher message. Transmit insertions separately.
5. The package was delivered to [your] wife personally. Everything is all right with [your] family. We wish [you] success. Greetings from the comrades. No. 1, 3 December.
```

### Russian
No online source already found so I pasted the above English into translate.google.com then copied the Russian and edited to match the book, double-checking that it reasonably translated back into English. Also passed through https://www.russiantools.com/en/convert-russian-case-uppercase.
* The following is the text in Figure 2 rather than the message written out in the article, then checked again at Google translate
* In No. 1, '@' is used as the `ПВТ` code in order to match Figure 2
* `ДРОБЬО` in No. 5 should possibly have been `ДРОБЬ` meaning 'Fraction' which does fit the book stating "№1/03"
```
1. ПОЗДРАВЛЯЕМ С БЛАГОПОЛУЧНЫМ ПРИБЫТИЕМ. ПОДТВЕРЖДАЕМ ПОЛУЧЕНИЕ ВАШЕГО ПИСЬМА В АДРЕС ,,В@В,, И ПРОЧТЕНИЕ ПИСЬМА №1.
2. ДЛЯ ОРГАНИЗАЦИИ ПРИКРЫТИЯ МЫ ДАЛИ УКАЗАНИЕ ПЕРЕДАТЬ ВАМ ТРИ ТЫСЯЧИ МЕСТНЫХ. ПЕРЕД ТЕМ КАК ИХ ВЛОЖИТЬ В КАКОЕ ЛИБО ДЕЛО ПОСОВЕТУИТЕСЬ С НАМИ, СООБЩИВ ХАРАКТЕРИСТИКУ ЭТОГО ДЕЛА.
3. ПО ВАШЕИ ПРОСЬБЕ РЕЦЕПТУРУ ИЗГОТОВЛЕНИЯ МЯГКОИ ПЛЕНКИ И НОВОСТЕИ ПЕРЕДАДИМ ОТДЕЛЬНО ВМЕСТЕ С ПИСЬМОМ МАТЕРИ.
4. ГАММЫ ВЫСЫЛАТЬ ВАМ РАНО. КОРОТКИЕ ПИСЬМА ШИФРУИТЕ, А ПОБОЛЬШЕТИРЕ ДЕЛАИТЕ СО ВСТАВКАМИ. ВСЕ ДАННЫЕ О СЕБЕ, МЕСТО РАБОТЫ, АДРЕС И Т.Д. В ОДНОИ ШИФРОВКЕ ПЕРЕДАВАТЬ НЕЛЬЗЯ. ВСТАВКИ ПЕРЕДАВАИТЕ ОТДЕЛЬНО.
5. ПОСЫЛКУ ЖЕНЕ ПЕРЕДАЛИ ЛИЧНО. С СЕМЬЕИ ВСЕ БЛАГОПОЛУЧНО. ЖЕЛАЕМ УСПЕХА. ПРИВЕТ ОТ ТОВАРИЩЕИ
№1 ДРОБЬО 3 ДЕКАБРЯ
```

#### Russian back to English
That the above is accurate were confirmed at translate.google.com and bing.com/translator.
* Note at 1: "B" translates to English as "V"
* At 3: the second sentence is dramatically different when guessed from the English. Spelling out the original Russian comes back with the expected translation, so no problem
* At 4: The "gammas" seem to translate as scales
* At 5: `DROBIO` is a mistranslation as noted in the Russian above

```
1. CONGRATULATIONS ON A SAFE ARRIVAL. WE CONFIRM THE RECEIVING OF YOUR LETTER TO THE ADDRESS ,,В@В, AND READING LETTERS #1.
2. TO ORGANIZE THE COVER WE INSTRUCTED TO TRANSFER YOU THREE THOUSAND LOCAL. BEFORE INVESTING THEM IN ANY BUSINESS ADVICE
BE WITH US, INFORMING THE CHARACTERISTIC OF THIS CASE.
3. AT YOUR REQUEST, THE RECIPE FOR PRODUCING SOFT FILM AND THE NEWS WILL BE GIVEN SEPARATELY TOGETHER WITH A LETTER TO MOTHER.
4. GAMMA SEND YOU EARLY. SHORT LETTERS ENCRYPT, AND MORE LARGE DO WITH INSERT. ALL DATA ABOUT YOURSELF, PLACE OF WORK, ADDRESS AND T.D. IT IS NOT POSSIBLE TO TRANSMIT IN ONE ENCRYPTION. PLEASE TRANSMIT THE INSERTS SEPARATELY.
5. THE PARCEL IS PASSED TO THE WIFE PERSONALLY. EVERYTHING IS GOOD WITH THE FAMILY. WE WISH YOU SUCCESS. GREETINGS FROM COMRADES
№1 DROBIO 3 DECEMBER
```

## Checkerboard
The checkerboard allows individual characters to be converted to coords
* If the character appears along the top row (`СНЕГОПА`) the coord used is the number directly above, so `С` has coord 5
* Otherwise the coord is the lefthand number THEN the number above, so `К` has coord 63
Created by hand from book copy/pasting cyrillic from https://www.russianlessons.net/lessons/lesson1_alphabet.php
* Start with a 11x5 grid, with top row and left column reserved for later
* Fill the third column top to bottom with full stop, comma, П/Л ('pl')
* Fill the fifth column top to bottom with №, Н/Ц ('nits' - switch alpha to numeric or back again), Н/Т ('nit' - message starts here)
* Take the first key - СНЕГOПА - and fill in the cells from [1][1] and right, leaving the last three on the row blank
* Remove the letters in that first key from the modified Cyrillic alphabet above, and fill in the remaining cells top to bottom, then left to right skipping the third and fifth columns
* Fill the bottom right cell with ПВТ ('повторить' - repeat)
```
    С   Н   Е   Г   О   П   А
    Б   Ж   .   К   №   Р   Ф   Ч   Ы   Ю
    В   З   ,   Л   Н/Ц Т   Х   Ш   Ь   Я
    Д   И   П/Л М   Н/Т У   Ц   Щ   Э   ПВТ
```
Will eventually use titles, where the last three numbers along the top row are used in the same order down the left
```
    5   0   7   3   8   9   4   6   1   2
    С   Н   Е   Г   О   П   А
6   Б   Ж   .   К   №   Р   Ф   Ч   Ы   Ю
1   В   З   ,   Л   Н/Ц Т   Х   Ш   Ь   Я
2   Д   И   П/Л М   Н/Т У   Ц   Щ   Э   ПВТ
```

## Figure 2
States the stream of coords obtained by passing the plaintext through the checkerboard
Retrieved from https://libmonster.ru/m/files/get_file/3407.pdf
Itself found from https://ru.wikipedia.org/wiki/%D0%A8%D0%B8%D1%84%D1%80_%D0%92%D0%98%D0%9A
Itself found from https://commons.wikimedia.org/wiki/File:Vic_step9.png
* note that the book says the last line "2 1 4" has the meaning "N U L L S"
* note also book has error in line `23 69 4 0 8 67 63 8 69 8 19 63 20 7` instead reading `23 69 4 0 8 67 63 8 19 8 19 63 20 7`
* copied using Okular and a search/replace to add lines back in
```
9 69 20 63 69 61 19 20 12 23 61 25 4 13
п р и к р ы т и я м ы д а л
20 29 63 4 10 4 0 20 7 9 7 69 7 25
и у к а з а н и е п е р е д
4 19 11 15 4 23 19 69 20 19 61 5 12 66
а т ь в а м т р и т ы с я ч
20 23 7 5 19 0 61 14 67 9 7 69 7 25
и м е с т н ы х . п е р е д
19 7 23 63 4 63 20 14 15 13 8 60 20 19
т е м к а к и х в л о ж и т
11 15 63 4 63 8 7 13 20 65 8 25 7 13
ь в к а к о е л и б о д Е л
8 9 8 5 8 15 7 19 29 20 19 7 5 11
о п о с о в е т у и т е с ь
5 0 4 23 20 17 5 8 8 65 26 20 15 14
с н а м и  с о о б щ и в х
4 69 4 63 19 7 69 20 5 19 20 63 29 21
а р а к т е р и с т и к у э
19 8 3 8 25 7 13 4 67 18 333 18 67 9
т о г о д е л а . н/ц 333 н/ц . п
8 15 4 16 7 20 9 69 8 5 11 65 7 69
о в а ш е и п р о с ь б е р
7 24 7 9 19 29 69 29 20 10 3 8 19 8
е ц е п т у р у и з г о т о
15 13 7 0 20 12 23 12 3 63 8 20 9 13
в л е н и я м я г к о и п л
7 0 63 20 20 0 8 15 8 5 19 7 20 9
е н к и и н о в о с т е и п
7 69 7 25 4 25 20 23 8 19 25 7 13 11
е р е д а д и м о т д е л ь
0 8 15 23 7 5 19 7 5 9 20 5 11 23
н о в м е с т е с п и с ь м
8 23 23 4 19 7 69 20 67 18 444 18 67 3
о м м а т е р и . н/ц 444 н/ц . г
4 23 23 61 15 61 5 61 13 4 19 11 15 4
а м м ы в ы с ы л а т ь в а
23 69 4 0 8 67 63 8 69 8 19 63 20 7
м р а н о . к о р о т к и е
9 20 5 11 23 4 16 20 64 69 29 20 19 7
п и с ь м а ш и ф р у и т е
17 4 9 8 65 8 13 11 16 7 19 20 69 7
 а п о б о л ь ш е т и р е
25 7 13 4 20 19 7 5 8 15 5 19 4 15
д е л а и т е с о в с т а в
63 4 23 20 67 15 5 7 25 4 0 0 61 7
к а м и . в с е д а н н ы е
8 5 7 65 7 17 23 7 5 19 8 69 4 65
о с е б Е  м е с т о р а б
8 19 61 17 4 25 69 7 5 20 19 67 25 67
о т ы  а д р е с и т . д .
15 8 25 0 8 20 16 20 64 69 8 15 63 7
в о д н о и ш и ф р о в к е
9 7 69 7 25 4 15 4 19 11 0 7 13 11
п е р е д а в а т ь н е Л ь
10 12 67 15 5 19 4 15 63 20 9 7 69 7
з я . в с т а в к и п Е р е
25 4 15 4 20 19 7 8 19 25 7 13 11 0
д а в а и т е о т д е л ь н
8 67 18 555 18 67 9 8 5 61 13 63 29 60
о . н/ц 555 н/ц . п о с ы л к у ж
7 0 7 9 7 69 7 25 4 13 20 13 20 66
е н е п е р е д а л и л и ч
0 8 67 5 5 7 23 11 7 20 15 5 7 65
н о . с с е м ь е и в с е б
13 4 3 8 9 8 13 29 66 0 8 67 60 7
л а г о п о л у ч н о . ж е
13 4 7 23 29 5 9 7 14 4 67 9 69 20
л а е м у с п е х а . п р и
15 7 19 8 19 19 8 15 4 69 20 26 7 20
в е т о т т о в а р и щ е и
68 18 111 18 25 69 8 65 11 8 18 333 18 25
No н/ц 111 н/ц д р о б ь 0 н/ц 333 н/ц д
7 63 4 65 69 12 28 18 111 18 67 9 8 10
е к а б р я н/т н/ц 111 н/ц . п о з
25 69 4 15 13 12 7 23 5 65 13 4 3 8
д р а в л я е м с б л а г о
9 8 13 29 66 0 61 23 9 69 20 65 61 19
п о л у ч н ы м п р и б ы т
20 7 23 67 9 8 25 19 15 7 69 60 25 4
и е м . п о д т в е р ж д а
7 23 9 8 13 29 66 7 0 20 7 15 4 16
е м п о л у ч е н и е в а ш
7 3 8 9 20 5 11 23 4 15 4 25 69 7
е г о п и с ь м а в а д р е
5 17 17 15 22 15 17 17 20 9 69 8 66 19
с   в пвт в   и п р о ч т
7 0 20 7 9 20 5 11 23 4 68 18 111 18
е н и е п и с ь м а No н/ц 111 н/ц
67 18 222 18 67 25 13 12 8 69 3 4 0 20
. н/ц 222 н/ц . д л я о р г а н и
10 4 24 20 20 2 1 4
з а ц и и
```

## Figure 3
First transposition tableau
- same source as figure 2
```
9 6 0 3 3 1 8 3 6 6 4 6 9 0 4 7 5
14 8 16 2 3 1 13 4 9 10 5 11 15 17 6 12 7
-----------------------------------------
9 6 9 2 0 6 3 6 9 6 1 1 9 2 0 1 2
2 3 6 1 2 5 4 1 3 2 0 2 9 6 3 4 1
0 4 0 2 0 7 9 7 6 9 7 2 5 4 1 9 1
1 1 5 4 2 3 1 9 6 9 2 0 1 9 6 1 5
1 2 6 6 2 0 2 3 7 5 1 9 0 6 1 1 4
6 7 9 7 6 9 7 2 5 1 9 7 2 3 6 3 4
6 3 2 0 1 4 1 5 1 3 8 6 0 2 0 1 9
1 1 1 5 6 3 4 6 3 8 7 1 3 2 0 6 5
8 2 5 7 1 3 8 9 8 5 8 1 5 7 1 9 2
9 2 0 1 9 7 5 1 1 5 0 4 2 3 2 0 1
7 5 8 8 6 5 2 6 2 0 1 5 1 4 4 6 9
4 6 3 1 9 7 6 9 2 0 5 1 9 2 0 6 3
2 9 2 1 1 9 8 3 8 2 5 7 1 3 4 6 7
1 8 3 3 3 1 8 6 7 9 8 1 5 4 1 6 7
2 0 9 6 9 8 5 1 1 6 5 7 6 9 7 2 4
7 9 1 9 2 9 6 9 2 9 2 0 1 0 3 8 1
9 8 1 5 1 3 7 0 2 0 1 2 2 3 1 2 3
6 3 8 2 0 9 1 3 7 0 6 3 2 0 2 0 0
8 1 5 8 5 1 9 7 2 0 9 7 6 9 7 2 5
4 2 5 2 0 2 3 8 1 9 2 5 7 1 3 1 1
0 8 1 5 2 3 7 5 1 9 7 5 9 2 0 5 1
1 2 3 8 2 3 2 3 4 1 9 7 6 9 2 0 6
7 1 8 4 4 4 1 8 6 7 3 4 2 3 2 3 6
1 1 5 6 1 5 6 1 1 3 4 1 9 1 1 1 5
4 2 3 6 9 4 0 8 6 7 6 3 8 6 9 8 1
9 6 3 2 0 7 9 2 0 5 1 1 2 3 4 1 6
2 0 6 4 6 9 2 9 2 0 1 9 7 1 7 4 9
8 6 5 8 1 3 1 1 1 6 7 1 9 2 0 6 9
7 2 5 7 1 3 4 2 0 1 9 7 5 8 1 5 5
1 9 4 1 5 6 3 4 2 3 2 0 6 7 1 5 5
7 2 5 4 0 0 6 1 7 8 5 7 6 5 7 1 7
2 3 7 5 1 9 8 6 9 4 6 5 8 1 9 6 1
1 7 4 2 5 6 9 7 5 2 0 1 9 6 7 2 5
6 7 1 5 8 2 5 0 8 2 0 1 6 2 0 6 4
6 9 8 1 5 6 3 7 9 7 6 9 7 2 5 4 1
5 4 1 9 1 1 0 7 1 3 1 1 1 0 1 2 6
7 1 5 5 1 9 4 1 5 6 3 2 0 9 7 6 9
7 2 5 4 1 5 4 2 0 1 9 7 8 1 9 2 5
7 1 3 1 1 0 8 6 7 1 8 5 5 5 1 8 6
7 9 8 5 6 1 1 3 6 3 2 9 6 0 7 0 7
9 7 6 9 7 2 5 4 1 3 2 0 1 3 2 0 6
6 0 8 6 7 5 5 7 2 3 1 1 7 2 0 1 5
5 7 6 5 1 3 4 3 8 9 8 1 3 2 9 6 6
0 8 6 7 6 0 7 1 3 4 7 2 3 2 9 5 9
7 1 4 4 6 7 9 6 9 2 0 1 5 7 1 9 8
1 9 1 9 8 1 5 4 6 9 2 0 2 6 7 2 0
6 8 1 8 1 1 1 1 8 2 5 6 9 8 6 5 1
1 8 1 8 3 3 3 1 8 2 5 7 6 3 4 6 5
6 9 1 2 2 8 1 8 1 1 1 1 8 6 7 9 8
1 0 2 5 6 9 4 1 5 1 3 1 2 7 2 3 5
6 5 1 3 4 3 8 9 8 1 3 2 9 6 6 0 6
1 2 3 9 6 9 2 0 5 6 5 1 1 9 2 0 7
2 3 6 7 9 8 2 5 1 9 1 5 7 6 9 6 0
2 5 4 7 2 3 9 8 1 3 2 9 6 6 7 0 2
0 7 1 5 4 1 6 7 3 8 9 2 0 5 1 1 2
3 4 1 5 4 2 5 6 9 7 5 1 7 1 7 1 5
2 2 1 5 1 7 1 7 2 0 9 6 9 8 6 6 1
9 7 0 2 0 7 9 2 0 5 1 1 2 3 4 6 8
1 8 1 1 1 1 8 6 7 1 8 2 2 2 1 8 6
7 2 5 1 3 1 2 8 6 9 3 4 0 2 0 1 0
4 2 4 2 0 2 0 2 1 4
```

## Figure 4
Second transposition tableau
- same source as figure 2
- the disruption areas are marked with a " * " before they start
- note [9] halfway down. In the book that's a 5. There may be others
```
3 0 2 7 4 3 0 4 2 8 7 7 1 2
5 13 2 9 7 6 14 8 3 12 10 11 1 4
--------------------------------
6 5 7 3 0 9 4 3 3 7 5 7 * 1 1
9 1 8 9 3 9 1 2 3 3 4 5 4 * 2
7 9 3 3 6 0 9 6 2 6 1 9 5 0
1 2 * 1 5 9 2 1 6 1 2 4 1 4 9
5 3 0 * 1 1 3 1 6 9 0 6 6 6 6
7 1 1 3 * 2 8 2 0 2 1 5 0 3 1
8 9 3 9 8 * 8 1 4 6 5 5 1 6 2
3 1 2 7 7 1 * 6 4 2 6 2 8 0 0
1 2 2 1 2 4 6 * 1 6 5 9 2 5 6
7 0 5 7 1 8 1 1 * 9 3 0 0 6 0
3 6 9 5 2 8 2 5 8 * 1 1 6 6 8
4 6 6 2 4 8 7 1 4 5 * 1 3 4 9
2 5 1 9 5 4 1 5 9 6 5 * 1 2 7
7 4 9 8 8 2 5 3 9 7 7 5 * 1 4
5 5 2 1 1 2 0 2 0 2 2 6 1 * 8
6 1 9 6 9 1 3 9 2 1 0 5 0 2
2 4 1 9 0 6 1 1 * 5 2 6 8 8 5
5 0 1 5 8 5 1 1 1 * 6 7 1 9 3
1 6 7 7 1 6 6 8 1 3 * 7 2 1 6
2 6 4 6 9 2 4 4 1 0 1 * 0 9 2
3 0 6 1 7 9 3 2 5 6 9 1 * 1 4
6 9 3 6 1 9 0 3 7 8 5 3 8 * 3
1 8 2 9 1 2 4 1 6 7 0 7 7 1
2 6 3 4 7 3 1 6 4 1 1 8 1 * 6
9 0 5 8 7 6 7 2 6 8 2 1 0 7
* 8 9 5 3 0 4 4 8 1 5 5 4 7 9
2 * 5 1 3 1 4 8 2 2 9 6 5 1 9
1 9 * 8 2 0 9 2 0 1 1 6 6 1 8
8 7 8 * 9 7 4 2 1 2 7 9 6 8 4
0 1 5 5 * 0 1 7 1 4 9 2 8 7 1
8 5 2 1 6 * 7 2 1 6 6 5 7 7 7
9 2 7 9 3 4 * 7 9 6 5 0 7 1 8
6 1 1 7 9 2 5 * 1 6 1 6 1 2 2
6 0 0 6 1 3 9 8 * 0 3 2 9 1 7
2 2 1 8 7 0 2 5 5 * 4 9 9 5 1
1 3 3 6 1 2 9 5 9 1 * 0 2 0 3
8 3 0 3 1 6 1 6 0 0 1 * 5 2 1
2 4 0 4 1 7 3 1 2 7 3 0 * 9 1
2 2 1 9 4 7 0 1 1 7 9 7 0 * [9]
5 1 7 9 1 7 2 0 9 9 1 7 6 4
7 2 6 2 9 * 6 1 2 2 6 7 9 6 2
7 1 7 6 4 1 * 9 8 2 7 9 5 6 6
0 2 1 1 5 4 4 * 8 9 6 7 1 0 8
9 5 2 1 9 3 7 7 * 5 6 1 7 3 3
4 1 3 0 5 1 1 6 6 * 5 2 9 6 8
5 1 6 9 9 5 5 7 1 5 * 2 9 1 7
4 1 6 9 5 6 7 6 5 6 9 * 6 0 7
8 0 1 5 8 5 6 7 0 2 2 5 * 9 2
1 8 6 0 6 3 4 1 2 7 3 1 2 * 2
2 5 6 9 8 0 9 8 3 1 2 8 2 1
1 2 6 0 * 0 9 6 0 5 6 9 2 1 5
6 2 9 2 3 * 0 8 3 2 3 9 1 1 8
7 7 9 4 1 2 * 5 5 1 3 8 5 3 3
1 9 7 0 7 8 1 * 6 5 5 4 5 7 4
9 8 8 9 0 5 2 3 * 1 8 1 5 5 3
5 7 4 2 7 8 2 2 9 * 8 6 8 6 6
3 6 6 7 5 1 3 8 1 2 * 4 1 1 1
2 8 7 1 2 2 7 2 1 1 4 * 1 2 1
6 1 6 0 2 1 0 2 7 9 5 8 * 3 6
9 1 5 0 7 6 1 2 8 3 9 6 8 * 4
8 1 5 8 6 1 1 3 9 2 0 7 6 1
6 2 9 9 5 1 3 * 1 1 1 0 1 5 4
8 5 5 0 0 2 9 6 * 2 6 4 9 6 3
9 0 0 0 9 9 1 7 3 * 2 2 7 3 4
7 5 0 6 1 3 8 4 2 2 * 2 3 4 9
7 3 6 1 1 3 3 3 9 4 2 * 0 3 0
9 2 2 1 1 1 5 9 3 8 7 0 * 9 1
5 1 9 4 1 2 2 0 9 7 6 1 1 * 2
4 5 1 7 1 7 0 2 3 7 5 5 7 4
1 3 1 * 9 3 1 6 3 1 2 8 7 5 1
9 1 7 0 * 6 2 2 0 9 1 5 0 3 2
7 5 1 1 9 * 2 2 7 6 8 3 6 7 6
1 2 7 5 9 0 * 9 6 6 5 1 8 3 2
1 1 2 1 0 6 7 * 2
```

## Line C
Copied by hand from book
```
Line A  2 0 8 1 8
Line B  3 9 1 9 4
	---------
Line C  9 1 7 2 4
```

## Line G
Same source as figure 2
Edited to match book
```
Line D  Т О Л Ь К О С Л Ы Ш | Н О Н А У Л И Ц Е Г
Line E  7 4 2 0 1 5 6 3 9 8 | 6 8 7 1 9 5 4 0 3 2
Line G  6 5 9 2 5 5 4 2 5 2
```

## Line P
Same source as figure 2
Edited to match book
- note Line J doesn't appear in source but does in book
```
Line H  5 9 3 8 9 9 1 8 9 8
Line J  3 7 2 4 8 9 1 5 0 6

Line K  4 2 1 7 8 0 9 7 7 2
Line L  6 3 8 5 8 9 6 4 9 8
Line M  9 1 3 3 7 5 0 3 7 7
Line N  0 4 6 0 2 5 3 0 4 7
Line P  4 0 6 2 7 8 3 4 1 1
```

## Line R
Same source as figure 2
Edited to match book
```
Line Q  9 6 0 3 3 1 8 3 6 6 4 6 9 0 4 7 5
Line R  3 0 2 7 4 3 0 4 2 8 7 7 1 2
```

## Line S
Same source as figure 2
Edited to match book
```
Line P  4 0 6 2 7 8 3 4 1 1
Line S  5 0 7 3 8 9 4 6 1 2
```

# Links
- During research I did find https://www.cia.gov/static/7799f1d576b09d8bce07988c635725db/Number-One-From-Moscow.pdf which specifies at the bottom "This article is based on the author's booklet Two Soviet Spy Ciphers".
