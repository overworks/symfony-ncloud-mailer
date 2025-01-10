<?php

namespace Minhyung\Ncloud\Mailer\Tests\Unit;

use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use Minhyung\Ncloud\Mailer\NcloudTransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Mailer\Test\AbstractTransportFactoryTestCase;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

#[CoversClass(NcloudApiTransport::class)]
class TransportFactoryTest extends AbstractTransportFactoryTestCase
{
	public function getFactory(): TransportFactoryInterface
	{
		return new NcloudTransportFactory();
	}

	public static function supportsProvider(): iterable
	{
		return [
			[Dsn::fromString("ncloud+api://default"), true],
		];
	}

	public static function createProvider(): iterable
	{
		$accessKey = static::USER;
		$secretKey = static::PASSWORD;
		$region = 'KR';
		return [
			[Dsn::fromString("ncloud+api://{$accessKey}:{$secretKey}@default"), new NcloudApiTransport($accessKey, $secretKey)],
			[Dsn::fromString("ncloud+api://{$accessKey}:{$secretKey}@default?region={$region}"), new NcloudApiTransport($accessKey, $secretKey, $region)],
		];
	}

	public static function unsupportedSchemeProvider(): iterable
	{
		return [
			[Dsn::fromString('smtp://default')],
			[Dsn::fromString('http://default')],
			[Dsn::fromString('https://default')],
		];
	}
}
