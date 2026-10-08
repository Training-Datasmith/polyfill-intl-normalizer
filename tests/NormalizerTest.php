<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Intl\Normalizer\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Polyfill\Intl\Normalizer\Normalizer;

class NormalizerTest extends TestCase
{
    public function testEmptyAndAscii()
    {
        $ascii = "a\nB\t0";
        foreach ([Normalizer::NFC, Normalizer::NFD, Normalizer::NFKC, Normalizer::NFKD] as $form) {
            $this->assertSame('', Normalizer::normalize('', $form));
            $this->assertTrue(Normalizer::isNormalized('', $form));
            $this->assertSame($ascii, Normalizer::normalize($ascii, $form));
            $this->assertTrue(Normalizer::isNormalized($ascii, $form));
        }
    }

    public function testDefaultFormIsNfc()
    {
        $this->assertSame("\xC3\xA9", Normalizer::normalize("e\xCC\x81"));
    }

    public function testDejaRoundTrip()
    {
        $nfc = "d\xC3\xA9j\xC3\xA0";
        $nfd = "d\x65\xCC\x81j\x61\xCC\x80";

        $this->assertSame($nfc, Normalizer::normalize($nfc, Normalizer::NFC));
        $this->assertSame($nfc, Normalizer::normalize($nfc, Normalizer::FORM_C));
        $this->assertSame($nfd, Normalizer::normalize($nfc, Normalizer::NFD));
        $this->assertSame($nfd, Normalizer::normalize($nfc, Normalizer::NFKD));
        $this->assertSame($nfc, Normalizer::normalize($nfd, Normalizer::NFC));
        $this->assertSame($nfc, Normalizer::normalize($nfd, Normalizer::NFKC));

        $this->assertTrue(Normalizer::isNormalized($nfc, Normalizer::NFC));
        $this->assertFalse(Normalizer::isNormalized($nfd, Normalizer::NFC));
        $this->assertTrue(Normalizer::isNormalized($nfd, Normalizer::NFD));
        $this->assertFalse(Normalizer::isNormalized($nfc, Normalizer::NFD));
    }

    public function testCombiningClassOrder()
    {
        $input = "e\xCC\x81\xCC\xA7";
        $nfd = "e\xCC\xA7\xCC\x81";
        $nfc = "\xC8\xA9\xCC\x81";

        $this->assertSame($nfd, Normalizer::normalize($input, Normalizer::NFD));
        $this->assertSame($nfc, Normalizer::normalize($input, Normalizer::NFC));
    }

    public function testSameClassOrderIsStable()
    {
        $input = "a\xCC\x81\xCC\x80";
        $nfd = "a\xCC\x81\xCC\x80";
        $nfc = "\xC3\xA1\xCC\x80";

        $this->assertSame($nfd, Normalizer::normalize($input, Normalizer::NFD));
        $this->assertSame($nfc, Normalizer::normalize($input, Normalizer::NFC));
    }

    public function testDialytikaTonos()
    {
        $input = "A\xCD\x84";
        $nfc = "\xC3\x84\xCC\x81";
        $nfd = "A\xCC\x88\xCC\x81";

        $this->assertSame($nfc, Normalizer::normalize($input, Normalizer::NFC));
        $this->assertSame($nfd, Normalizer::normalize($input, Normalizer::NFD));
    }

    public function testBlockedComposition()
    {
        $input = "D\xCC\x9B\xCC\x84";

        $this->assertSame($input, Normalizer::normalize($input, Normalizer::NFC));
        $this->assertSame($input, Normalizer::normalize($input, Normalizer::NFD));
    }

    public function testCanonicalSingletons()
    {
        $angstrom = "\xE2\x84\xAB";
        $aring = "\xC3\x85";
        $angstromNfd = "A\xCC\x8A";
        $ohm = "\xCE\xA9";
        $omegaSign = "\xE2\x84\xA6";

        $this->assertSame($aring, Normalizer::normalize($angstrom, Normalizer::NFC));
        $this->assertSame($aring, Normalizer::normalize($angstrom, Normalizer::NFKC));
        $this->assertSame($angstromNfd, Normalizer::normalize($angstrom, Normalizer::NFD));
        $this->assertSame($angstromNfd, Normalizer::normalize($angstrom, Normalizer::NFKD));
        $this->assertFalse(Normalizer::isNormalized($angstrom, Normalizer::NFC));
        $this->assertTrue(Normalizer::isNormalized($aring, Normalizer::NFC));

        $this->assertSame($ohm, Normalizer::normalize($omegaSign, Normalizer::NFC));
        $this->assertSame($ohm, Normalizer::normalize($omegaSign, Normalizer::NFD));
        $this->assertFalse(Normalizer::isNormalized($omegaSign, Normalizer::NFD));
        $this->assertTrue(Normalizer::isNormalized($ohm, Normalizer::NFD));
    }

    public function testHangulModernSyllables()
    {
        $ga = "\xEA\xB0\x80";
        $gaNfd = "\xE1\x84\x80\xE1\x85\xA1";
        $gak = "\xEA\xB0\x81";
        $gakNfd = "\xE1\x84\x80\xE1\x85\xA1\xE1\x86\xA8";
        $gaks = "\xEA\xB0\x9B";
        $gaksNfd = "\xE1\x84\x80\xE1\x85\xA1\xE1\x87\x82";
        $gaPlus11c3 = "\xEA\xB0\x80\xE1\x87\x83";
        $gaPlus11c3Nfd = "\xE1\x84\x80\xE1\x85\xA1\xE1\x87\x83";
        $lPlus11a8 = "\xE1\x84\x80\xE1\x86\xA8";

        $this->assertSame($ga, Normalizer::normalize($ga, Normalizer::NFC));
        $this->assertSame($gaNfd, Normalizer::normalize($ga, Normalizer::NFD));
        $this->assertSame($gak, Normalizer::normalize($gakNfd, Normalizer::NFC));
        $this->assertSame($gakNfd, Normalizer::normalize($gak, Normalizer::NFD));
        $this->assertSame($gaks, Normalizer::normalize($gaksNfd, Normalizer::NFC));
        $this->assertSame($gaksNfd, Normalizer::normalize($gaks, Normalizer::NFD));
        $this->assertSame($gaPlus11c3, Normalizer::normalize($gaPlus11c3Nfd, Normalizer::NFC));
        $this->assertSame($gaPlus11c3Nfd, Normalizer::normalize($gaPlus11c3, Normalizer::NFD));
        $this->assertSame($lPlus11a8, Normalizer::normalize($lPlus11a8, Normalizer::NFC));
        $this->assertSame($lPlus11a8, Normalizer::normalize($lPlus11a8, Normalizer::NFD));
    }

    public function testU11A7IsPreserved()
    {
        $lv11a7 = "\xE1\x84\x80\xE1\x85\xA1\xE1\x86\xA7";
        $ga11a7 = "\xEA\xB0\x80\xE1\x86\xA7";
        $lv11a7Nfd = $lv11a7;

        $this->assertSame($ga11a7, Normalizer::normalize($lv11a7, Normalizer::NFC));
        $this->assertSame($ga11a7, Normalizer::normalize($ga11a7, Normalizer::NFC));
        $this->assertTrue(Normalizer::isNormalized($ga11a7, Normalizer::NFC));
        $this->assertSame($lv11a7Nfd, Normalizer::normalize($ga11a7, Normalizer::NFD));
    }

    public function testCompatibilityVsCanonical()
    {
        $fiLig = "\xEF\xAC\x81";
        $fi = 'fi';
        $tm = "\xE2\x84\xA2";
        $tmAscii = 'TM';
        $sup2 = "\xC2\xB2";
        $micro = "\xC2\xB5";
        $mu = "\xCE\xBC";
        $quarter = "\xC2\xBC";
        $quarterNfkc = "1\xE2\x81\x84\x34";

        $this->assertSame($fiLig, Normalizer::normalize($fiLig, Normalizer::NFC));
        $this->assertSame($fiLig, Normalizer::normalize($fiLig, Normalizer::NFD));
        $this->assertSame($fi, Normalizer::normalize($fiLig, Normalizer::NFKC));
        $this->assertSame($fi, Normalizer::normalize($fiLig, Normalizer::NFKD));
        $this->assertSame($tmAscii, Normalizer::normalize($tm, Normalizer::NFKC));
        $this->assertSame('2', Normalizer::normalize($sup2, Normalizer::NFKC));
        $this->assertSame($mu, Normalizer::normalize($micro, Normalizer::NFKC));
        $this->assertSame($micro, Normalizer::normalize($micro, Normalizer::NFC));
        $this->assertSame($quarterNfkc, Normalizer::normalize($quarter, Normalizer::NFKC));
        $this->assertTrue(Normalizer::isNormalized($sup2, Normalizer::NFC));
        $this->assertFalse(Normalizer::isNormalized($sup2, Normalizer::NFKC));
    }

    public function testNfcQuickCheckDoesNotHideCombiningMarks()
    {
        $eAcute = "\xC3\xA9";
        $eCombining = "e\xCC\x81";
        $ga = "\xEA\xB0\x80";
        $gaNfd = "\xE1\x84\x80\xE1\x85\xA1";

        $this->assertTrue(Normalizer::isNormalized($eAcute, Normalizer::NFC));
        $this->assertFalse(Normalizer::isNormalized($eCombining, Normalizer::NFC));
        $this->assertTrue(Normalizer::isNormalized($ga, Normalizer::NFC));
        $this->assertFalse(Normalizer::isNormalized($gaNfd, Normalizer::NFC));
    }

    public function testCompatibilityHangulRecomposesOnlyInNfKc()
    {
        $enc = "\xE3\x88\x9D";
        $nfkd = "(\xE1\x84\x8B\xE1\x85\xA9\xE1\x84\x8C\xE1\x85\xA5\xE1\x86\xAB)";
        $nfkc = "(\xEC\x98\xA4\xEC\xA0\x84)";

        $this->assertSame($enc, Normalizer::normalize($enc, Normalizer::NFC));
        $this->assertSame($enc, Normalizer::normalize($enc, Normalizer::NFD));
        $this->assertSame($nfkd, Normalizer::normalize($enc, Normalizer::NFKD));
        $this->assertSame($nfkc, Normalizer::normalize($enc, Normalizer::NFKC));
    }

    public function testDakutenComposesOnlyInNfKc()
    {
        $enc = "\xE3\x8C\x87";
        $nfkc = "\xE3\x82\xA8\xE3\x82\xB9\xE3\x82\xAF\xE3\x83\xBC\xE3\x83\x89";
        $nfkd = "\xE3\x82\xA8\xE3\x82\xB9\xE3\x82\xAF\xE3\x83\xBC\xE3\x83\x88\xE3\x82\x99";

        $this->assertSame($nfkc, Normalizer::normalize($enc, Normalizer::NFKC));
        $this->assertSame($nfkd, Normalizer::normalize($enc, Normalizer::NFKD));
    }

    public function testLongCompatibilityExpansion()
    {
        $lig = "\xEF\xB7\xBA";
        $expanded = "\xD8\xB5\xD9\x84\xD9\x89\x20\xD8\xA7\xD9\x84\xD9\x84\xD9\x87\x20\xD8\xB9\xD9\x84\xD9\x8A\xD9\x87\x20\xD9\x88\xD8\xB3\xD9\x84\xD9\x85";

        $this->assertSame($lig, Normalizer::normalize($lig, Normalizer::NFC));
        $this->assertSame($lig, Normalizer::normalize($lig, Normalizer::NFD));
        $this->assertSame($expanded, Normalizer::normalize($lig, Normalizer::NFKC));
        $this->assertSame($expanded, Normalizer::normalize($lig, Normalizer::NFKD));
    }

    public function testSupplementaryPlane()
    {
        $chak = "\xF0\x91\x82\x9A";
        $chakNfd = "\xF0\x91\x82\x99\xF0\x91\x82\xBA";
        $mathA = "\xF0\x9D\x90\x80";

        $this->assertSame($chak, Normalizer::normalize($chak, Normalizer::NFC));
        $this->assertSame($chakNfd, Normalizer::normalize($chak, Normalizer::NFD));
        $this->assertSame($mathA, Normalizer::normalize($mathA, Normalizer::NFC));
        $this->assertSame($mathA, Normalizer::normalize($mathA, Normalizer::NFD));
        $this->assertSame('A', Normalizer::normalize($mathA, Normalizer::NFKC));
        $this->assertSame('A', Normalizer::normalize($mathA, Normalizer::NFKD));
    }

    public function testInvalidUtf8()
    {
        foreach (["\xFF", "\xE1\x84", "\xC0\x80", "\xED\xA0\x80"] as $invalid) {
            $this->assertSame(false, Normalizer::normalize($invalid));
            $this->assertSame(false, Normalizer::isNormalized($invalid));
        }
    }

    /**
     * @dataProvider invalidFormProvider
     */
    public function testInvalidForm($form)
    {
        if (\PHP_VERSION_ID >= 80000) {
            $this->expectException(\ValueError::class);
            $this->expectExceptionMessage('normalizer_normalize(): Argument #2 ($form) must be a a valid normalization form');
            Normalizer::normalize('foo', $form);
        } else {
            $this->assertSame(false, Normalizer::normalize('foo', $form));
        }
    }

    public function invalidFormProvider()
    {
        return [
            'negative' => [-1],
            'zero' => [0],
            'one' => [1],
            'three' => [3],
            'sixty-four' => [64],
        ];
    }

    public function testNone()
    {
        $eAcute = "\xC3\xA9";
        $ascii = 'abc';

        $this->assertSame($eAcute, Normalizer::normalize($eAcute, \Normalizer::NONE));
        $this->assertFalse(Normalizer::isNormalized($eAcute, \Normalizer::NONE));
        $this->assertFalse(Normalizer::isNormalized($ascii, \Normalizer::NONE));
    }

    public function testStaticCachesDoNotLeakAcrossForms()
    {
        $fiLig = "\xEF\xAC\x81";
        $fi = 'fi';

        $this->assertSame($fi, Normalizer::normalize($fiLig, Normalizer::NFKD));
        $this->assertSame($fiLig, Normalizer::normalize($fiLig, Normalizer::NFC));

        $this->assertSame($fiLig, Normalizer::normalize($fiLig, Normalizer::NFC));
        $this->assertSame($fi, Normalizer::normalize($fiLig, Normalizer::NFKD));
    }
}
