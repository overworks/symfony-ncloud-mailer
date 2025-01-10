<?php

namespace Minhyung\Ncloud\Mailer;

use Symfony\Component\Mailer\Exception\IncompleteDsnException;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class NcloudTransportFactory extends AbstractTransportFactory
{
    /**
     * @throws \Symfony\Component\Mailer\Exception\UnsupportedSchemeException
     * @throws \Symfony\Component\Mailer\Exception\IncompleteDsnException
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('ncloud+api' === $scheme) {
            $accessKey = $dsn->getUser();
            if (! $accessKey) {
                throw new IncompleteDsnException('Access Key is required');
            }
            $secretKey = $dsn->getPassword();
            if (! $secretKey) {
                throw new IncompleteDsnException('Secret Key is required');
            }
            $region = $dsn->getOption('region', '');

            return new NcloudApiTransport($accessKey, $secretKey, $region, $this->client, $this->dispatcher, $this->logger);
        }

        throw new UnsupportedSchemeException($dsn, 'ncloud', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['ncloud+api'];
    }
}
