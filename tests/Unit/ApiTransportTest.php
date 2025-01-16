<?php

namespace Minhyung\Ncloud\Mailer\Tests\Unit;

use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use Minhyung\Ncloud\Mailer\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[CoversClass(NcloudApiTransport::class)]
class ApiTransportTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    public function testDsn(): void
    {
        $accessKey = $this->faker->userName();
        $secretKey = $this->faker->password();

        $transport = new NcloudApiTransport($accessKey, $secretKey);

        $dsn = "ncloud+api://{$accessKey}:{$secretKey}@default";
        $this->assertEquals($dsn, (string) $transport);
    }

    public function testSend(): void
    {
        $sender = new Address($this->faker->safeEmail());
        $receiver = new Address($this->faker->safeEmail());

        $email = new Email();
        $email->from($sender);
        $email->to($receiver);
        $email->subject($this->faker->sentence());
        $email->text($this->faker->paragraph());
        
        $envelope = new Envelope($sender, [$receiver]);

        $response = new MockResponse(json_encode([
            'responseId' => $this->faker->uuid(),
            'count' => count($envelope->getRecipients()),
        ]));
        $client = new MockHttpClient($response);
        $transport = new NcloudApiTransport($this->faker()->userName(), $this->faker()->password(), client: $client);
        $sentMessage = $transport->send($email, $envelope);
        $this->assertNotNull($sentMessage);
    }
}
