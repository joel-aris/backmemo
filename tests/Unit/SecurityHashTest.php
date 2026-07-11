<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SecurityHashTest extends TestCase
{
    public function test_validika_hash_is_sha256(): void
    {
        self::assertSame(64, strlen(validika_hash('VALIDIKA', 'RDC')));
    }
}
