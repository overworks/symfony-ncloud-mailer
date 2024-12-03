<?php

namespace Minhyung\Ncloud\Mailer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NcloudApiTransport extends AbstractApiTransport
{
    private const HOST = 'mail.apigw.ntruss.com';
    private const URIS = [
        'KR' => '/api/v1',
        'SGN' => '/api/v1-sgn',
        'JPN' => '/api/v1-jpn',
    ];

    private string $uri;

    public function __construct(
        private string $accessKey,
        private string $secretKey,
        string $region = 'KR',
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($client, $dispatcher, $logger);

        $this->uri = self::URIS[$region];
    }

    public function __toString(): string
    {
        return 'ncloud+api://'.self::HOST;
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $response = $this->client->request('POST', 'https://'.self::HOST.$this->uri.'/mails', [
            'headers' => array_merge($this->getRequestHeaders('POST', $this->uri.'/mails'), [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]),
            'json' => $this->getPayload($email, $envelope),
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $result = $response->toArray(false);
        } catch (DecodingExceptionInterface) {
            throw new HttpTransportException('Unable to send an email: '.$response->getContent(false).\sprintf(' (code %d).', $statusCode), $response);
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Could not reach the remote Postmark server.', $response, 0, $e);
        }

        if ($statusCode >= 400) {
            throw new HttpTransportException('Unable to send an email: '.$result['Message'].\sprintf(' (code %d).', $result['ErrorCode']), $response);
        }
        
        return $response;
    }

    private function getRequestHeaders(string $method, string $uri)
    {
        $timestamp = ((int) microtime(true)) / 1000;
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;

        $hmac = "{$method} {$uri}\n{$timestamp}\n{$accessKey}";
        $signature = base64_encode(hash_hmac('sha256', $hmac, $secretKey, true));

        return [
            'x-ncp-apigw-timestamp' => $timestamp,
            'x-ncp-iam-access-key' => $accessKey,
            'x-ncp-apigw-signature-v2' => $signature,
        ];
    }

    private function getPayload(Email $email, Envelope $envelope): array
    {
        // TODO

        // https://api.ncloud-docs.com/docs/ai-application-service-cloudoutboundmailer-createmailrequest
        $payload = [];
        $payload['senderAddress'] = $envelope->getSender()->getAddress();
        if ($senderName = $envelope->getSender()->getName()) {
            $payload['senderName'] = $senderName;
        }
        $payload['title'] = $email->getSubject();
        $payload['recipients'] = $this->getRecipientsPayload($email, $envelope);
        $payload['body'] = $this->getBody($email);

        // 첨부파일이 있는 경우 먼저 파일을 올리고 ID를 가져온다.
        if ($attachments = $email->getAttachments()) {
            $payload['attachFileIds'] = $this->createFile($attachments);
        }

        return $payload;
    }

    private function getRecipientsPayload(Email $email, Envelope $envelope): array
    {
        $recipients = [];

        foreach ($this->getRecipients($email, $envelope) as $address) {
            $recipient = [
                'type' => 'R',
                'address' => $address->getAddress(),
            ];
            if ($name = $address->getName()) {
                $recipient['name'] = $name;
            }
            $recipients[] = $recipient;
        }

        foreach ($email->getCc() as $address) {
            $recipient = [
                'type' => 'C',
                'address' => $address->getAddress(),
            ];
            if ($name = $address->getName()) {
                $recipient['name'] = $name;
            }
            $recipients[] = $recipient;
        }

        foreach ($email->getBcc() as $address) {
            $recipient = [
                'type' => 'B',
                'address' => $address->getAddress(),
            ];
            if ($name = $address->getName()) {
                $recipient['name'] = $name;
            }
            $recipients[] = $recipient;
        }

        return $recipients;
    }

    private function getBody(Email $email): string
    {
        // TODO: 첨부파일은 빼고 text 혹은 html 파트만 보내줘야한다...

        return $email->getTextBody();
    }

    /**
     * @param  \Symfony\Component\Mime\Part\DataPart[]  $attachments
     * @return array
     */
    private function createFile($attachments): array
    {
        foreach ($attachments as $attachment) {
            
        }
        return [];
    }

}
