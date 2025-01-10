<?php

namespace Minhyung\Ncloud\Mailer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\Exception\LogicException;
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

    public function __construct(
        protected string $accessKey,
        protected string $secretKey,
        protected string $region = '',
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($client, $dispatcher, $logger);
    }

    public function __toString(): string
    {
        return "ncloud+api://{$this->accessKey}:{$this->secretKey}@default".($this->region ? "?region={$this->region}" : '');
    }

    /**
     * @param  \Symfony\Component\Mailer\SentMessage  $sentMessage
     * @param  \Symfony\Component\Mime\Email          $email
     * @param  \Symfony\Component\Mailer\Envelope     $envelope
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * 
     * @throws \Symfony\Component\Mailer\Exception\LogicException
     * @throws \Symfony\Component\Mailer\Exception\HttpTransportException
     */
    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $attachments = $email->getAttachments();
        if (! empty($attachments)) {
            // TODO: Not implemented
            throw new LogicException();
        }
        
        $response = $this->createMailRequest($email, $envelope);
        try {
            $statusCode = $response->getStatusCode();
            $result = $response->toArray(false);
        } catch (DecodingExceptionInterface) {
            throw new HttpTransportException('Unable to send an email: '.$response->getContent(false).\sprintf(' (code %d).', $statusCode), $response);
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Could not reach the remote server.', $response, 0, $e);
        }

        if (! empty($result['error'])) {
            throw new HttpTransportException('Unable to send an email: '.$result['error']['message'].\sprintf(' (code %d).', $result['error']['errorCode']), $response);
        }

        // TODO: 요청 ID.
        $requestId = $result['requestId'];
        // TODO: 발송 메일 갯수.
        $count = $result['count'];
        
        return $response;
    }

    protected function getTargetUrl(string $relativeUrl = ''): string
    {
        return self::URIS[$this->region ?: 'KR'].$relativeUrl;
    }

    protected function makeRequestHeaders(string $method, string $uri): array
    {
        $timestamp = strval(time() * 1000);
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;

        $hmac = "{$method} {$uri}\n{$timestamp}\n{$accessKey}";
        $signature = base64_encode(hash_hmac('sha256', $hmac, $secretKey, true));

        $headers = [
            'x-ncp-apigw-timestamp' => $timestamp,
            'x-ncp-iam-access-key' => $accessKey,
            'x-ncp-apigw-signature-v2' => $signature,
        ];

        return $headers;
    }

    // 전송에 필요한 API는 두개 뿐이다.

    /**
     * createMailRequest 호출
     */
    public function createMailRequest(Email $email, Envelope $envelope, $attachFileIds = [])
    {
        $method = 'POST';
        $url = $this->getTargetUrl('/mails');
        $options = [
            'headers' => $this->makeRequestHeaders($method, $url),
            'json' => $this->getMailPayload($email, $envelope, $attachFileIds),
        ];
        return $this->client->request($method, 'https://'.self::HOST.$url, $options);
    }

    protected function getMailPayload(Email $email, Envelope $envelope, $attachFileIds = []): array
    {
        // https://api.ncloud-docs.com/docs/ai-application-service-cloudoutboundmailer-createmailrequest
        $payload = [];

        $payload['senderAddress'] = $envelope->getSender()->getAddress();
        if ($senderName = $envelope->getSender()->getName()) {
            $payload['senderName'] = $senderName;
        }
        $payload['title'] = $email->getSubject();
        $payload['recipients'] = $this->getRecipientsPayload($email, $envelope);
        $payload['body'] = $email->getHtmlBody() ?? $email->getTextBody();
        if (! empty($attachFileIds)) {
            $payload['attachFileIds'] = $attachFileIds;
        }

        return $payload;
    }

    protected function getRecipientsPayload(Email $email, Envelope $envelope): array
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

    /**
     * @param  array<\Symfony\Component\Mime\Part\DataPart>  $attachments
     */
    public function createFile(array $attachments)
    {
        // TODO

        $method = 'POST';
        $url = $this->getTargetUrl('/files');
        $options = [
            'headers' => $this->makeRequestHeaders($method, $url),
        ];
        return $this->client->request($method, 'https://'.self::HOST.$url, $options);
    }
}
