<?php

namespace Minhyung\Ncloud\Mailer\Tests;

use Faker\Generator;
use Faker\Factory;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Faker\Generator|null */
    protected $faker = null;

    /**
     * @param  string  $locale
     * @return \Faker\Generator
     */
    protected function faker($locale = Factory::DEFAULT_LOCALE): Generator
    {
        return $this->faker ??= Factory::create($locale);
    }
}
