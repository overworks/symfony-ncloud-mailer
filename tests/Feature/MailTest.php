<?php

namespace Minhyung\Ncloud\Mailer\Tests\Feature;

use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use Minhyung\Ncloud\Mailer\Tests\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\File;

class MailTest extends TestCase
{
    protected string $accessKey = '';
    protected string $secretKey = '';
    protected string $senderAddress = '';
    protected string $receiverAddress = '';

    protected function setUp(): void
    {
        $this->accessKey = $_ENV['NCLOUD_ACCESS_KEY'];
        $this->secretKey = $_ENV['NCLOUD_SECRET_KEY'];
        $this->senderAddress = $_ENV['SENDER_ADDRESS'];
        $this->receiverAddress = $_ENV['RECEIVER_ADDRESS'];

        $this->setUpFaker();
    }

    public function testSend(): void
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
        if ($path = $this->faker->image()) {
            $email->attachFromPath($path);
        }
        $envelope = new Envelope($sender, [$receiver]);

        $transport = new NcloudApiTransport($this->accessKey, $this->secretKey);
        $sentMessage = $transport->send($email, $envelope);
        $this->assertNotNull($sentMessage);
    }
}
