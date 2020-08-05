<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// define('IN_NYOS_PROJECT', true);

final class Email2Test extends TestCase {

    public function testCanBeCreatedFromValidEmailAddress(): void {

        $this->assertInstanceOf(
                \Email2::class,
                \Email2::fromString('user@example.com')
        );

    }

//
//    public function testCannotBeCreatedFromInvalidEmailAddress(): void
//    {
//        $this->expectException(InvalidArgumentException::class);
//
//        Email2::fromString('invalid');
//    }
//    public function testCanBeUsedAsString(): void
//    {
//        $this->assertEquals(
//            'user@example.com',
//            \Email2::fromString('user@example.com')
//        );
//    }
}
