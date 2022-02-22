<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @covers \App\Form\UserType
 */
class UserTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSubmitValidData()
    {
        $model = new User();

        $formData = [
            'username' => 'unittest',
            'password' => [
                'first' => '123test',
                'second' => '123test'
            ],
            'email' => 'unitTest@test.com',
            'roles' => ['ROLE_USER']
        ];

        $expected = clone $model;
        $expected->setUsername($formData['username']);
        $expected->setPassword($formData['password']['first']);
        $expected->setEmail($formData['email']);
        $expected->setRoles($formData['roles']);

        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(UserType::class, $model);
        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $model was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }

}