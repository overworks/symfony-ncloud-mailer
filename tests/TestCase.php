<?php

namespace Minhyung\Ncloud\Mailer\Tests;

use Faker\Generator;
use Faker\Factory;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Faker\Generator|null */
    protected $faker = null;

    /**
     * @param  string|null  $locale
     * @return \Faker\Generator
     */
    protected function setUpFaker($locale = null): Generator
    {
        return $this->faker ??= Factory::create($locale ?? Factory::DEFAULT_LOCALE);
    }

    /**
     * @return \Faker\Generator
     */
    protected function faker(): Generator
    {
        return $this->setUpFaker();
    }
}
