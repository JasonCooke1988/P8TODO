<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\Task
 */
class TaskTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @covers \App\Entity\Task::setCreatedAt
     * @covers \App\Entity\Task::getCreatedAt
     */
    public function testCreatedAt()
    {
        $task = new Task();
        $createdAt = new \DateTime();

        $task->setCreatedAt($createdAt);
        $this->assertEquals($createdAt,$task->getCreatedAt());
    }

    /**
     * @covers \App\Entity\Task::setTitle
     * @covers \App\Entity\Task::getTitle
     */
    public function testTitle()
    {
        $task = new Task();
        $title = 'Title';

        $task->setTitle($title);
        $this->assertEquals($title,$task->getTitle());
    }

    /**
     * @covers \App\Entity\Task::setContent
     * @covers \App\Entity\Task::getContent
     */
    public function testContent()
    {
        $task = new Task();
        $content = 'Content';

        $task->setContent($content);
        $this->assertEquals($content,$task->getContent());
    }

    /**
     * @covers \App\Entity\Task::setIsDone
     * @covers \App\Entity\Task::getIsDone
     * @covers \App\Entity\Task::toggle
     */
    public function testIsDone()
    {
        $task = new Task();
        $isDone = false;

        $task->setIsDone($isDone);
        $this->assertEquals($isDone,$task->getIsDone());

        $task->toggle(!$isDone);
        $this->assertEquals(!$isDone, $task->getIsDone());
    }

    /**
     * @covers \App\Entity\Task::setUser
     * @covers \App\Entity\Task::getUser
     */
    public function testUser()
    {
        $task = new Task();
        $user = new User();

        $task->setUser($user);
        $this->assertEquals($user,$task->getUser());
    }

}