<?php

namespace App\Tests\Controller;

class DefaultControllerTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function testIndexAction()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        //        Go to page
        $client->request('GET', '/');
        $client->getResponse();
        $this->assertResponseIsSuccessful();
    }
}