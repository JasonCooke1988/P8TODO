<?php

namespace App\Tests\Controller;

use App\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\DefaultController
 */
class DefaultControllerTest extends WebTestCase
{

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @covers \App\Controller\DefaultController::indexAction
     */
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