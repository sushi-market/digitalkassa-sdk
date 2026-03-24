<?php

declare(strict_types=1);

namespace Tests\Unit;

use DF\DigitalKassa\Exceptions\InvalidCredentialsException;
use DF\DigitalKassa\V2\ValueObjects\Credentials;
use PHPUnit\Framework\TestCase;

final class CredentialsTest extends TestCase
{
    /** Пустой `actorId` должен приводить к `InvalidCredentialsException`. */
    public function test_it_validates_empty_credentials(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        new Credentials(
            actorId: '',
            actorToken: 'token',
            cGroupId: '1',
        );
    }

    /** Нечисловой `cGroupId` не должен проходить валидацию. */
    public function test_it_validates_c_group_id_format(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        new Credentials(
            actorId: '123',
            actorToken: 'token',
            cGroupId: 'group-1',
        );
    }

    /** Пустой `actorToken` должен отклоняться при создании credentials. */
    public function test_it_validates_empty_actor_token(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        new Credentials(
            actorId: '123',
            actorToken: '',
            cGroupId: 1,
        );
    }

    /** `cGroupId` должен быть больше нуля. */
    public function test_it_validates_non_positive_c_group_id(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        new Credentials(
            actorId: '123',
            actorToken: 'token',
            cGroupId: 0,
        );
    }

    /** Строковый числовой `cGroupId` должен приводиться к `int`. */
    public function test_it_normalizes_numeric_string_c_group_id_to_integer(): void
    {
        $credentials = new Credentials(
            actorId: '123',
            actorToken: 'token',
            cGroupId: '42',
        );

        self::assertSame(42, $credentials->cGroupId);
    }
}
