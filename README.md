# symfony-ncloud-mailer
Symfony Mailer for [Cloud Outbound Mailer](https://www.ncloud.com/product/applicationService/cloudOutboundMailer)

## Install
```bash
composer require minhyung/ncloud-mailer
```

## Usage
```php
use Minhyung\Ncloud\Mailer\NcloudApiTransport;
use Minhyung\Ncloud\Mailer\NcloudTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$accessKey = {NCLOUD_ACCESS_KEY};
$secretKey = {NCLOUD_SECRET_KEY};
$options = ['region' => 'KR'];  # option

$dsn = new Dsn('ncloud+api', 'default', $accessKey, $secretKey, options: $options);
$transport = (new NcloudTransportFactory())->create($dsn);

$email = (new Email())
    ->from('hello@example.com')
    ->to('you@example.com')
    //->cc('cc@example.com')
    //->bcc('bcc@example.com')
    //->replyTo('fabien@example.com')
    //->priority(Email::PRIORITY_HIGH)
    ->subject('Time for Symfony Mailer!')
    ->text('Sending emails is fun again!')
    ->html('<p>See Twig integration for better HTML integration!</p>');

$mailer->send($email);
```
