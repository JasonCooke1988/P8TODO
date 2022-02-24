<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\User
 */
class UserTest extends TestCase
{

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @covers \App\Entity\User::setRoles
     * @covers \App\Entity\User::getRoles
     */
    public function testRoles()
    {
        $user = new User();
        $roles = ['ROLE_USER'];
        $user->setRoles($roles);

        $this->assertEquals($roles, $user->getRoles());
    }

    /**
     * @covers \App\Entity\User::setUsername
     * @covers \App\Entity\User::getUsername
     */
    public function testUsername()
    {
        $user = new User();
        $username = 'username';
        $user->setUsername($username);

        $this->assertEquals($username, $user->getUsername());
    }

    /**
     * @covers \App\Entity\User::getUserIdentifier
     */
    public function testUserIdentifier()
    {
        $user = new User();
        $username = 'username';
        $user->setUsername($username);

        $this->assertEquals($username, $user->getUserIdentifier());
    }

    /**
     * @covers \App\Entity\User::setPassword
     * @covers \App\Entity\User::getPassword
     */
    public function testPassword()
    {
        $user = new User();
        $password = 'password';
        $user->setPassword($password);

        $this->assertEquals($password, $user->getPassword());
    }

    /**
     * @covers \App\Entity\User::setEmail
     * @covers \App\Entity\User::getEmail
     */
    public function testEmail()
    {
        $user = new User();
        $email = 'username@test.com';
        $user->setEmail($email);

        $this->assertEquals($email, $user->getEmail());
    }

    /**
     * @covers \App\Entity\User::getTasks
     * @covers \App\Entity\User::addTask
     * @covers \App\Entity\User::removeTask
     */
    public function testTask()
    {
        $user = new User();
        $task = new Task();
        $taskCollection = new ArrayCollection([$task]);

        $user->addTask($task);
        $this->assertEquals($taskCollection, $user->getTasks());

        $user->removeTask($task);
        $taskCollection->removeElement($task);
        $this->assertEquals($taskCollection, $user->getTasks());
    }

    /**
     * @covers \App\Entity\User::setCreatedAt
     * @covers \App\Entity\User::getCreatedAt
     */
    public function testCreatedAt()
    {
        $user = new User();
        $createdAt = new \DateTimeImmutable();
        $user->setCreatedAt($createdAt);

        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

}