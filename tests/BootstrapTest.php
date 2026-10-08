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
use Symfony\Polyfill\Intl\Normalizer\Normalizer as PolyfillNormalizer;

class BootstrapTest extends TestCase
{
    public function testGlobalClassExtendsThePolyfillAndIntlIsAbsent()
    {
        $this->assertFalse(extension_loaded('intl'));
        $this->assertTrue(is_subclass_of(\Normalizer::class, PolyfillNormalizer::class));
    }

    public function testConstants()
    {
        $this->assertSame(4, \Normalizer::FORM_D);
        $this->assertSame(8, \Normalizer::FORM_KD);
        $this->assertSame(16, \Normalizer::FORM_C);
        $this->assertSame(32, \Normalizer::FORM_KC);
        $this->assertSame(4, \Normalizer::NFD);
        $this->assertSame(8, \Normalizer::NFKD);
        $this->assertSame(16, \Normalizer::NFC);
        $this->assertSame(32, \Normalizer::NFKC);
        $this->assertSame(2, \Normalizer::NONE);

        $this->assertSame(4, PolyfillNormalizer::FORM_D);
        $this->assertSame(8, PolyfillNormalizer::FORM_KD);
        $this->assertSame(16, PolyfillNormalizer::FORM_C);
        $this->assertSame(32, PolyfillNormalizer::FORM_KC);
        $this->assertSame(4, PolyfillNormalizer::NFD);
        $this->assertSame(8, PolyfillNormalizer::NFKD);
        $this->assertSame(16, PolyfillNormalizer::NFC);
        $this->assertSame(32, PolyfillNormalizer::NFKC);

        $this->assertFalse(defined(PolyfillNormalizer::class.'::NONE'));
    }

    public function testFunctionsComeFromTheVersionedBootstrap()
    {
        $normalize = new \ReflectionFunction('normalizer_normalize');
        $isNormalized = new \ReflectionFunction('normalizer_is_normalized');

        if (\PHP_VERSION_ID >= 80000) {
            $this->assertSame(realpath(__DIR__.'/../bootstrap80.php'), $normalize->getFileName());
            $this->assertSame(realpath(__DIR__.'/../bootstrap80.php'), $isNormalized->getFileName());

            $this->assertTrue($normalize->getParameters()[0]->allowsNull());
            $this->assertSame('?string', (string) $normalize->getParameters()[0]->getType());
            $this->assertSame('string', $normalize->getParameters()[0]->getName());
            $this->assertTrue($normalize->getParameters()[1]->allowsNull());
            $this->assertSame('?int', (string) $normalize->getParameters()[1]->getType());
            $this->assertSame('form', $normalize->getParameters()[1]->getName());
            $this->assertSame('string|false', (string) $normalize->getReturnType());
            $this->assertSame('bool', (string) $isNormalized->getReturnType());
        } else {
            $this->assertSame(realpath(__DIR__.'/../bootstrap.php'), $normalize->getFileName());
            $this->assertSame(realpath(__DIR__.'/../bootstrap.php'), $isNormalized->getFileName());
        }
    }

    public function testGlobalFunctionsMatchTheClassForDeja()
    {
        $nfc = "d\xC3\xA9j\xC3\xA0";
        $nfd = "d\x65\xCC\x81j\x61\xCC\x80";

        $this->assertSame($nfc, normalizer_normalize('déjà'));
        $this->assertSame($nfc, \Normalizer::normalize('déjà'));
        $this->assertTrue(normalizer_is_normalized($nfc));
        $this->assertTrue(\Normalizer::isNormalized($nfc));
        $this->assertFalse(normalizer_is_normalized($nfd, \Normalizer::NFC));
        $this->assertFalse(\Normalizer::isNormalized($nfd, \Normalizer::NFC));
    }

    public function testClassMethodRejectsNull()
    {
        $this->expectException(\TypeError::class);
        \Normalizer::normalize(null);
    }
}
