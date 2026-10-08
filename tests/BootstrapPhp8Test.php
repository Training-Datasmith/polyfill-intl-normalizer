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

class BootstrapPhp8Test extends TestCase
{
    /**
     * @requires PHP 8
     */
    public function testPhp8NullStringBecomesEmpty()
    {
        $this->assertSame('', normalizer_normalize(null));
        $this->assertTrue(normalizer_is_normalized(null));

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('normalizer_normalize(): Argument #2 ($form) must be a a valid normalization form');
        normalizer_normalize('a', null);
    }
}
