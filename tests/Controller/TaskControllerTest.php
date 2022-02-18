<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private ?object $taskRepository;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
    }

    /**
     * @dataProvider notConnectedProviders
     */
    public function testNotConnected($uri): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', $uri);

        $response = $client->getResponse();
        $location = $response->headers->get('location');
        $this->assertEquals('/tasks', $location);

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Vous n’est pas autorisée');
    }

    public function notConnectedProviders(): array
    {
        $task = $this->taskRepository->findOneBy(array('title' => 'Tache N° 1'));

        return [
            [
                'create' => '/tasks/create',
                'edit' => '/tasks/' . $task->getId() . '/edit',
                'toggle' => '/tasks/' . $task->getId() . '/toggle',
                'delete' => '/tasks/' . $task->getId() . '/delete'
            ]
        ];
    }

    public function testListAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();

        $taskCount = $this->taskRepository->count(array());
        $this->assertCount($taskCount, $crawler->filter('.task-card'));
    }

    public function testListDoneAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks/done');

        $this->assertResponseIsSuccessful();

        $taskCount = $this->taskRepository->count(array('isDone' => true));
        $this->assertCount($taskCount, $crawler->filter('.task-card'));
    }

    public function testCreateAction(): void
    {
        $client = static::createClient();

//        Connect user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('anon@test.com');
        $client->loginUser($testUser);

//        Go to page
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

//        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        $form = $buttonCrawlerNode->form();
        $form['task[title]'] = 'Title';
        $form['task[content]'] = 'Content';
        $client->submit($form);

//        Follow redirect
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success.mt-4');
        $this->assertSelectorTextContains('.alert.alert-success.mt-4', 'La tâche a bien été ajoutée.');
    }


    /**
     * @dataProvider taskCreateEditActionValidationErrorsProvider
     */
    public function testCreateActionValidationErrors(string $title, string $content)
    {
        $client = static::createClient();

//        Connect user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('anon@test.com');
        $client->loginUser($testUser);

        //        Go to page
        $crawler = $client->request('GET', '/tasks/create');

        //        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        $form = $buttonCrawlerNode->form();
        $form['task[title]'] = $title;
        $form['task[content]'] = $content;
        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');
    }


    public function taskCreateEditActionValidationErrorsProvider(): array
    {
        return [
            ['', ''],
            ['title', ''],
            ['', 'content']
        ];
    }

    public function testEditAction()
    {
        $client = static::createClient();

//        Connect user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('anon@test.com');
        $client->loginUser($user);

//        Get a task
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy(array('title' => 'Tache N° 1'));
        $taskId = $task->getId();

//        Go to page
        $crawler = $client->request('GET', '/tasks/' . $taskId . '/edit');
        $this->assertResponseIsSuccessful();

//        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        $form = $buttonCrawlerNode->form();
        $form['task[title]'] = 'Title';
        $form['task[content]'] = 'Content';
        $client->submit($form);

//        Follow redirect and assert success message
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', 'La tâche a bien été modifiée.');
    }


//    /**
//     * @dataProvider taskCreateEditActionValidationErrorsProvider
//     */
//    public function testEditActionValidationErrors(string $title, string $content)
//    {
//        $client = static::createClient();
//
////        Connect user
//        $userRepository = static::getContainer()->get(UserRepository::class);
//        $testUser = $userRepository->findOneByEmail('anon@test.com');
//        $client->loginUser($testUser);
//
//        //        Get a task
//        $taskRepository = static::getContainer()->get(TaskRepository::class);
//        $task = $taskRepository->findOneBy(array('title' => 'Tache N° 1'));
//        $taskId = $task->getId();
//
////        Go to page
//        $crawler = $client->request('GET', '/tasks/'.$taskId.'/edit');
//        $this->assertResponseIsSuccessful();
//
//
//        //        Submit form
//        $buttonCrawlerNode = $crawler->selectButton('Modifier');
//        $form = $buttonCrawlerNode->form();
//        $form['task[title]'] = $title;
//        $form['task[content]'] = $content;
//        $client->submit($form);
//
//        $this->assertSelectorExists('.invalid-feedback');
//    }


    public function testToggleTaskAction()
    {
        $client = static::createClient();

//        Connect user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('anon@test.com');
        $client->loginUser($user);

        //        Get a task
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy(array('title' => 'Tache N° 1'));
        $taskId = $task->getId();
        $isDone = $task->isDone();
        $status = !$isDone ? 'faite' : 'à faire';

//        Go to page
        $client->request('GET', '/tasks/' . $taskId . '/toggle');
        $this->assertEquals(!$isDone, $task->isDone());
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status));
    }


    public function testDeleteTaskAction()
    {
        $client = static::createClient();

//        Connect user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('anon@test.com');
        $client->loginUser($user);

        //        Get a task
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy(array('title' => 'Tache N° 1'));
        $taskId = $task->getId();

//        Go to page
        $client->request('GET', '/tasks/' . $taskId . '/delete');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $task = $taskRepository->findOneBy(array('id' => $taskId));
        $this->assertEquals(null, $task);
    }
}
