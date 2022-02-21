<?php

namespace App\Tests\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;

class TaskTypeTest extends TypeTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSubmitValidData()
    {
        $user = new User();

        $formData = [
            'title' => 'title',
            'content' => 'content',
            'user' => $user
        ];

        $model = new Task();
        $model->setUser($user);

        $expected = clone $model;
        $expected->setTitle($formData['title']);
        $expected->setContent($formData['content']);
        $expected->setIsDone(false);

        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(TaskType::class, $model);
        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $model was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }
}