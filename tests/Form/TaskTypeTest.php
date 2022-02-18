<?php

namespace App\Tests\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\Test\TypeTestCase;

class TaskTypeTest extends TypeTestCase
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ManagerRegistry
     */
    private mixed $emRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->em = DoctrineTestHelper::createTestEntityManager();
        $this->emRegistry = $this->createRegistryMock('default', $this->em);


        $schemaTool = new SchemaTool($this->em);

        // This is the important part for you !
        $classes = [$this->em->getClassMetadata(User::class)];

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em));

        return $registry;
    }

    protected function getExtensions(): array
    {
        return array_merge(parent::getExtensions(), array(
            new DoctrineOrmExtension($this->emRegistry),
        ));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em = null;
        $this->emRegistry = null;
    }

    public function testSubmitValidData()
    {

        $userRepository = $this->emRegistry->getRepository(UserRepository::class);
        $user = $userRepository->findOneBy(array('email' => 'anon@test.com'));
        dd($user);

        $formData = [
            'title' => 'Title',
            'content' => 'content',
            'user' => $user
        ];

        $model = new Task();

        $expected = clone $model;
        $expected->setTitle($formData['title']);
        $expected->setContent($formData['content']);
        $expected->setIsDone(false);
        $expected->setUser($user);

        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(TaskType::class, $model);
        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $model was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }

    public function testCustomFormView()
    {
        $formData = new TestObject();
        // ... prepare the data as you need

        // The initial data may be used to compute custom view variables
        $view = $this->factory->create(TestedType::class, $formData)
            ->createView();

        $this->assertArrayHasKey('custom_var', $view->vars);
        $this->assertSame('expected value', $view->vars['custom_var']);
    }
}