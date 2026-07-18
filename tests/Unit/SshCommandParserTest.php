<?php

namespace Tests\Unit;

use App\Services\SshCommandParser;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SshCommandParserTest extends TestCase
{
    #[DataProvider('validCommands')]
    public function test_it_extracts_ssh_connection_details(string $command, array $expected): void
    {
        $this->assertSame($expected, (new SshCommandParser)->parse($command));
    }

    public static function validCommands(): array
    {
        return [
            ['ssh -p 65002 u123456789@82.25.113.52', ['username' => 'u123456789', 'host' => '82.25.113.52', 'port' => 65002]],
            ['ssh admin@example.com', ['username' => 'admin', 'host' => 'example.com', 'port' => 22]],
        ];
    }

    public function test_it_rejects_an_invalid_command(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SshCommandParser)->parse('user@example.com');
    }
}
