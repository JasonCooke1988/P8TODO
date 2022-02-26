<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\TaskController
 */
class TaskControllerTest extends WebTestCase
{

    private ?object $userRepository;
    private ?object $taskRepository;
    private User $adminUser;
    private User $testUser;
    private User $anonUser;
    private Task $anonTask;
    private Task $testTask;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

//        Get repositories
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);

//        Get users
        $this->anonUser = $this->userRepository->findOneBy(array('email' => 'anon@test.com'));
        $this->adminUser = $this->userRepository->findOneBy(array('email' => 'admin@test.com'));
        $this->testUser = $this->userRepository->findOneBy(array('email' => 'test@test.com'));

//        Get tasks
        $this->anonTask = $this->taskRepository->findOneBy(array('user' => $this->anonUser));
        $this->testTask = $this->taskRepository->findOneBy(array('user' => $this->testUser));
    }

    /**
     * @dataProvider connectionDataProviders
     * @covers \App\Controller\TaskController::createAction
     * @covers \App\Controller\TaskController::editAction
     * @covers \App\Controller\TaskController::toggleTaskAction
     * @covers \App\Controller\TaskController::deleteTaskAction
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

    /**
     * @dataProvider connectionDataProviders
     * @covers \App\Controller\TaskController::createAction
     * @covers \App\Controller\TaskController::editAction
     * @covers \App\Controller\TaskController::toggleTaskAction
     * @covers \App\Controller\TaskController::deleteTaskAction
     */
    public function testConnected($uri, $redirect = false): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->loginUser($this->testUser);
        $client->request('GET', $uri);
        $client->getResponse();

        if ($redirect) $client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function connectionDataProviders(): array
    {
        return [
            'Task create' => ['/tasks/create'],
            'Task edit' => ['/tasks/' . $this->testTask->getId() . '/edit'],
        ];
    }

    /**
     * @covers \App\Controller\TaskController::listAction
     */
    public function testListAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks');

        $taskCount = $this->taskRepository->count(array('isDone' => false));
        $this->assertCount($taskCount, $crawler->filter('.task-card'));
    }

    /**
     * @covers \App\Controller\TaskController::listDoneAction
     */
    public function testListDoneAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks/done');

        $taskCount = $this->taskRepository->count(array('isDone' => true));
        $this->assertCount($taskCount, $crawler->filter('.task-card'));
    }

    /**
     * @covers \App\Controller\TaskController::createAction
     */
    public function testCreateAction(): void
    {
        $client = static::createClient();

//        Connect user
        $client->loginUser($this->testUser);

//        Go to page
        $crawler = $client->request('GET', '/tasks/create');

//        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        $form = $buttonCrawlerNode->form();
        $form['task[title]'] = 'Title';
        $form['task[content]'] = 'Content';
        $client->submit($form);

//        Follow redirect
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été ajoutée.');
    }


    /**
     * @dataProvider taskCreateEditActionValidationErrorsProvider
     * @covers \App\Controller\TaskController::createAction
     */
    public function testCreateActionValidationErrors(string $title, string $content)
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->adminUser);

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

    #[ArrayShape(['Blank title or content' => "string[]", 'Blank content' => "string[]", 'Blank title' => "string[]"])]
    public function taskCreateEditActionValidationErrorsProvider(): array
    {
        return [
            'Blank title or content' => ['', ''],
            'Blank content' => ['title', ''],
            'Blank title' => ['', 'content']
        ];
    }

    /**
     * @covers \App\Controller\TaskController::editAction
     */
    public function testEditAction()
    {
        $client = static::createClient();

//        Connect user
        $client->loginUser($this->testUser);

//        Go to page
        $crawler = $client->request('GET', '/tasks/' . $this->testTask->getId() . '/edit');

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


    /**
     * @dataProvider taskCreateEditActionValidationErrorsProvider
     * @covers \App\Controller\TaskController::editAction
     */
    public function testEditActionValidationErrors(string $title, string $content)
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Go to page
        $crawler = $client->request('GET', '/tasks/' . $this->anonTask->getId() . '/edit');

        //        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        $form = $buttonCrawlerNode->form();
        $form['task[title]'] = $title;
        $form['task[content]'] = $content;
        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');
    }

    /**
     * @covers \App\Controller\TaskController::toggleTaskAction
     */
    public function testToggleTaskAction()
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Cast task original state into $isDone
        //        Fresh repo and entity are needed
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy(array('user' => $this->testUser));
        $isDone = $task->isDone();
        $status = !$isDone ? 'faite' : 'à faire';

        //        Go to page
        $client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertEquals(!$isDone, $task->isDone());
        $client->followRedirect();

        //        Check task has been toggled
        $this->assertSelectorTextContains(
            '.alert.alert-success',
            sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status)
        );
    }

    /**
     * @covers \App\Controller\TaskController::deleteTaskAction
     */
    public function testDeleteTestTaskAsAdminAction()
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->adminUser);

        //        Get task ID
        $taskId = $this->testTask->getId();

        //        Delete task
        $client->request('GET', '/tasks/' . $taskId . '/delete');
        $client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', 'Vous n’est pas autorisée');
    }

    /**
     * @covers \App\Controller\TaskController::deleteTaskAction
     */
    public function testDeleteAnonTaskAsAdmin()
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->adminUser);

        //        Get task ID
        $taskId = $this->anonTask->getId();

        //        Delete task
        $client->request('GET', '/tasks/' . $taskId . '/delete');

        $client->followRedirect();

        $this->assertSelectorTextContains(
            '.alert.alert-success',
            'La tâche a bien été supprimée.'
        );
    }

    /**
     * @covers \App\Controller\TaskController::deleteTaskAction
     */
    public function testDeleteOwnTask()
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Get task ID
        $taskId = $this->testTask->getId();

        //        Delete task
        $client->request('GET', '/tasks/' . $taskId . '/delete');

        $client->followRedirect();
        $this->assertSelectorTextContains(
            '.alert.alert-success',
            'La tâche a bien été supprimée.'
        );
    }

    /**
     * @covers \App\Controller\TaskController::deleteTaskAction
     */
    public function testDeleteAnonTaskAsTest()
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Get task ID
        $taskId = $this->anonTask->getId();

        //        Delete task
        $client->request('GET', '/tasks/' . $taskId . '/delete');

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Vous n’est pas autorisée');
    }
}
