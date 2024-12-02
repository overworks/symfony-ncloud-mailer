<?php

namespace Minhyung\Ncloud\Mailer;

use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class NcloudTransportFactory extends AbstractTransportFactory
{
    /**
     * @throws UnsupportedSchemeException
     * @throws IncompleteDsnException
     */
    public function create(Dsn $dsn): TransportInterface
    {
        // TODO: Implement create() method.
        $scheme = $dsn->getScheme();

        if ('ncloud+api' === $scheme) {
            $accessKey = $dsn->getUser();
            $secretKey = $dsn->getPassword();
            $region = $dsn->getOption('region', 'KR');

            return new NcloudApiTransport($accessKey, $secretKey, $region, $this->client, $this->dispatcher, $this->logger);
        }

        throw new UnsupportedSchemeException($dsn, 'ncloud', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['ncloud+api'];
    }
}
