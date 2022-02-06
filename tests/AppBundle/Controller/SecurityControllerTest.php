<?php

namespace App\Tests\AppBundle\Controller;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testloginAction(ManagerRegistry $managerRegistry)
    {
        $client = static::createClient();

        // get or create the user somehow (e.g. creating some users only
        // for tests while loading the test fixtures)
        $em = $managerRegistry->getManager(UserRepository::class);
//        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $em->findOneBy(array('email' => 'anon@test.com'));

        $client->loginUser($testUser);

        // user is now logged in, so you can test protected resources
//        $client->request('GET', '/profile');
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'Hello Username!');
    }
}