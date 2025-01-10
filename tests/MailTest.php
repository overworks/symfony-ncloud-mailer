<?php

namespace Minhyung\Ncloud\Mailer\Tests;

use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\File;

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
            $file = new File($path);
            $email->attach($file);
        }
        $envelope = new Envelope($sender, [$receiver]);

        $transport = new NcloudApiTransport($this->accessKey, $this->secretKey);
        $sentMessage = $transport->send($email, $envelope);
        $this->assertNotNull($sentMessage);
    }
}
