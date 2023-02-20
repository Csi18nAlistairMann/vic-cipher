# Fully reimplement the VIC Cipher per "Two Soviet Spy Cyphers"

On 22 June 1953 a newspaper boy by chance discovered a nickel (American 5c coin) that was hollow, and contained an enciphered message. This set events in train that ended up with a senior Soviet spy being arrested, and the message itself decrypted when its intended recipient, Reino Häyhänen, defected to the West. The story can be found at [wikipedia](https://en.wikipedia.org/wiki/Hollow_Nickel_Case) but here I'm interested in Häyhänen's encryption, which has become known as the VIC Cipher. 

The cipher was laid out to the American Cryptogram Association on 3 September 1960 in a paper called "Two Soviet Spy Cyphers." Google doesn't appear to have a link to the paper, but the CIA has [this](https://www.cia.gov/static/7799f1d576b09d8bce07988c635725db/Number-One-From-Moscow.pdf) which mostly repeats it. However I've got a copy of the paper in an 1983 copy of "Kahn On Codes" where he goes into such detail as to make me think I could recreate the encryption system on a modern computer without needing assistance from any other source. This repository is the successful result of that effort.

vic.php is the main entry point. 

test.sh exercises vic.php with the original Cyrillic message and keygroups.

test-en.sh exercises vic.php with the original message in English and so in the Roman alphabet.

Investigation of VIC Cipher.md has much more detailed notes.

My code lacks exhaustive testing, and I've take a few guesses at choices that were not laid out in the book. I'm a programmer, not a cryptographer, so while the code should be usable, don't rely on it without an actual cryptographer going over it. The guesses were about: 
- The Message ID Keygroup is arbitrary, and
- a "0" Position for the same keygroup would be treated as the 10th position
- That the 5th digit of the date is used for placement. Now I think about it, the last digit makes more sense, as it would change more frquently
- The Padding was in fact characters chosen to be uncommon, that just happen to substitute to "2 1 4" in the paper
- That for this reason the padding is known at Derivation time when decrypting, so can be recognised as such in the last keygroup, even if the message isn't known
- The disruption table once complete is treated as wholly undisrupted for later rows 
- That coords that don't exist in the Checkerboard are immediate causes for termination rather than attempts at obfuscation that should be ignored

I've also encoded the control characters into Ascii rather than the strings in the example, simply to ease processing: as control characters the form doesn't matter as long as the symbol hasn't already been used

I'm disappointed to say I didn't find any other full reimplementations of the VIC Cipher so I can't report success having them decode my transmission and vice versa. I'm disappointed also that there are plenty of resources but none of them seem to offer a complete treatment, and the one that did, was wholly in (presumably) Russian. Not a problem as the paper itself was thorough, but it does make testing my choices that much harder.

This was a fun little challenge! - A
