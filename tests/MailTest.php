<?php

namespace Minhyung\Ncloud\Mailer\Tests;

use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailTest extends TestCase
{
    protected $accessKey;
    protected $secretKey;
    protected $senderAddress;
    protected $receiverAddress;

    protected function setUp(): void
    {
        $this->accessKey = $_ENV['NCLOUD_ACCESS_KEY'];
        $this->secretKey = $_ENV['NCLOUD_SECRET_KEY'];
        $this->senderAddress = $_ENV['SENDER_ADDRESS'];
        $this->receiverAddress = $_ENV['RECEIVER_ADDRESS'];
    }

    public function testMockMail()
    {
        $sender = new Address($this->faker()->safeEmail());
        $receiver = new Address($this->faker()->safeEmail());

        $email = new Email();
        $email->from($sender);
        $email->to($receiver);
        $email->subject($this->faker()->sentence());
        $email->text($this->faker()->paragraph());
        
        $envelope = new Envelope($sender, [$receiver]);

        $response = new MockResponse(json_encode([
            'responseId' => $this->faker()->uuid(),
            'count' => count($envelope->getRecipients()),
        ]));
        $client = new MockHttpClient($response);
        $transport = new NcloudApiTransport($this->faker()->userName(), $this->faker()->password(), client: $client);
        $sentMessage = $transport->send($email, $envelope);
        $this->assertNotNull($sentMessage);
    }

    public function testRealMail()
    {
        if (! $this->accessKey || ! $this->secretKey || ! $this->senderAddress || ! $this->receiverAddress) {
            $this->markTestSkipped();
        }

        $sender = new Address($this->senderAddress);
        $receiver = new Address($this->receiverAddress);

        $email = new Email();
        $email->from($sender);
        $email->to($receiver);
        $email->subject($this->faker()->sentence());
        $email->text($this->faker()->paragraph());
        $email->html($this->faker()->randomHtml());
        
        $envelope = new Envelope($sender, [$receiver]);

        $transport = new NcloudApiTransport($this->accessKey, $this->secretKey);
        $sentMessage = $transport->send($email, $envelope);
        $this->assertNotNull($sentMessage);
    }
}
