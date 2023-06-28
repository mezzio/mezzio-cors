<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Fig\Http\Message\RequestMethodInterface;
use Generator;
use Laminas\Diactoros\Uri;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Service\CorsMetadata;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

final class CorsMetadataTest extends TestCase
{
    private const DOCUMENTATION_ALLOWED_ORIGINS = [
        '*//example.com',
        '*.example.com',
    ];

    /**
     * @psalm-param list<non-empty-string> $allowedOrigins
     * @dataProvider allowedOrigins
     */
    public function testAllowsOrigin(UriInterface $origin, array $allowedOrigins): void
    {
        $metadata = new CorsMetadata(
            $origin,
            $this->createMock(UriInterface::class),
            RequestMethodInterface::METHOD_GET
        );

        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration
            ->expects(self::atMost(1))
            ->method('allowedOrigins')
            ->willReturn($allowedOrigins);

        $this->assertSame((string) $origin, $metadata->origin($configuration));
    }

    /**
     * @psalm-return Generator<non-empty-string,array{0:UriInterface,1:list<non-empty-string>}>
     */
    public static function allowedOrigins(): Generator
    {
        yield 'documentation example with subdomain' => [
            new Uri('https://www.example.com'),
            self::DOCUMENTATION_ALLOWED_ORIGINS,
        ];

        yield 'documentation example with sub subdomain' => [
            new Uri('https://subsubdomain.www.example.com'),
            self::DOCUMENTATION_ALLOWED_ORIGINS,
        ];

        yield 'documentation example without subdomain' => [
            new Uri('https://example.com'),
            self::DOCUMENTATION_ALLOWED_ORIGINS,
        ];
    }
}
