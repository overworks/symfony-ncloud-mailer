<?php

namespace Minhyung\Ncloud\Mailer;

use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractHttpTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NcloudHttpTransport extends AbstractHttpTransport
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

    protected function doSendHttp(SentMessage $message): ResponseInterface
    {
        throw new LogicException('This method is not implemented.');
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        throw new LogicException();
    }
}
