<?php

namespace Minhyung\Ncloud\Mailer;

use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NcloudApiTransport extends AbstractApiTransport
{
    public function __construct(
        private string $accessKey,
        private string $secretKey,
        string $region = 'KR',
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($client, $dispatcher, $logger);
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        // TODO: Implement doSendApi() method.
        throw new LogicException();
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        throw new LogicException();
    }
}
