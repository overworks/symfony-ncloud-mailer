<?php

namespace Minhyung\Ncloud\Mailer\Tests;

use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NcloudApiTransport::class)]
class ApiTransportTest extends TestCase
{
    public function testDsn(): void
    {
        $accessKey = $this->faker()->userName();
        $secretKey = $this->faker()->password();

        $transport = new NcloudApiTransport($accessKey, $secretKey);

        $dsn = "ncloud+api://{$accessKey}:{$secretKey}@default";
        $this->assertEquals($dsn, (string) $transport);
    }
}
