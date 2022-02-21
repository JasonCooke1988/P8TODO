<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{

    private ?object $userRepository;

    private User $testUser;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->testUser = $this->userRepository->findOneBy(array('email' => 'anon@test.com'));
    }


    /**
     * @dataProvider connectionTestDataProviders
     */
    public function testNotConnected($uri): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', $uri);

        $response = $client->getResponse();
        $location = $response->headers->get('location');
        $this->assertEquals('/', $location);

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Vous n’est pas autorisée');
    }

    #[ArrayShape(['User list' => "string[]", 'User create' => "string[]", 'User edit' => "string[]"])]
    public function connectionTestDataProviders(): array
    {
        return [
            'User list' => ['/users'],
            'User create' => ['/users/create'],
            'User edit' => ['/users/' . $this->testUser->getId() . '/edit']
        ];
    }

    /**
     * @dataProvider connectionTestDataProviders
     */
    public function testConnected($uri): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->loginUser($this->testUser);

        //        Go to page
        $client->request('GET', $uri);
        $client->getResponse();
        $this->assertResponseIsSuccessful();
    }

    public function testCreateAction(): void
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Go to page
        $crawler = $client->request('GET', '/users/create');

        //        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        $form = $buttonCrawlerNode->form();
        $form['user[username]'] = 'Test-user';
        $form['user[password][first]'] = '123password';
        $form['user[password][second]'] = '123password';
        $form['user[roles]'] = ['ROLE_USER'];
        $form['user[email]'] = 'testuser@test.com';
        $client->submit($form);

        //        Follow redirect
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'L\'utilisateur a bien été ajouté.');
    }

    public function testEditAction()
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Go to page
        $crawler = $client->request('GET', '/users/' . $this->testUser->getId() . '/edit');

        //        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        $form = $buttonCrawlerNode->form();
        $form['user[username]'] = 'Anewusername';
        $form['user[password][first]'] = 'anewpassword';
        $form['user[password][second]'] = 'anewpassword';
        $form['user[roles]'] = ['ROLE_USER'];
        $form['user[email]'] = 'anew@email.com';
        $client->submit($form);

        //        Follow redirect
        $client->followRedirect();
        $client->followRedirect();
//        dd($client->getResponse()->getContent());
        $this->assertSelectorTextContains('.alert-success', 'L\'utilisateur a bien été modifié');
    }

    /**
     * @dataProvider userCreateActionValidationErrorsProvider
     */
    public function testCreateActionValidationErrors(string $username, string $firstPassword, string $secondPassword, array $roles, string $email)
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Go to page
        $crawler = $client->request('GET', '/users/create');

        //        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        $form = $buttonCrawlerNode->form();
        $form['user[username]'] = $username;
        $form['user[password][first]'] = $firstPassword;
        $form['user[password][second]'] = $secondPassword;

        if (!empty($roles)) $form['user[roles]'] = $roles;

        $form['user[email]'] = $email;
        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');
    }

    #[ArrayShape([
        'Blank username' => "array",
        'Blank role' => "array",
        'Blank password' => "array",
        'Blank email' => "array",
        'Passwords do not match' => "array",
        'Unique email constraint check' => "array",
        'Unique username constraint check' => "array"
    ])]
    public function userCreateActionValidationErrorsProvider(): array
    {
        return [
            'Blank username' => ['', '123password', '123password', ['ROLE_USER'], 'testuser@test.com'],
            'Blank role' => ['username', '123password', '123password', [], 'testuser@test.com'],
            'Blank password' => ['username', '', '', ['ROLE_USER'], 'testuser@test.com'],
            'Blank email' => ['username', '123password', '123password', ['ROLE_USER'], ''],
            'Passwords do not match' => ['username', '123password', '124password', ['ROLE_USER'], 'testuser@test.com'],
            'Unique email constraint check' => ['username', '123password', '123password', ['ROLE_USER'], 'anon@test.com'],
            'Unique username constraint check' => ['Anon', '123password', '123password', ['ROLE_USER'], 'anon@test.com'],
        ];
    }


    /**
     * @dataProvider userEditActionValidationErrorsProvider
     */
    public function testEditActionValidationErrors(string $username, string $firstPassword, string $secondPassword, array $roles, string $email)
    {
        $client = static::createClient();

        //        Connect user
        $client->loginUser($this->testUser);

        //        Go to page
        $crawler = $client->request('GET', '/users/' . $this->testUser->getId() . '/edit');

        //        Submit form
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        $form = $buttonCrawlerNode->form();
        $form['user[username]'] = $username;
        $form['user[password][first]'] = $firstPassword;
        $form['user[password][second]'] = $secondPassword;

        if (!empty($roles)) $form['user[roles]'] = $roles;

        $form['user[email]'] = $email;
        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');
    }

    #[ArrayShape([
        'Blank username' => "array",
        'Blank role' => "array",
        'Blank password' => "array",
        'Blank email' => "array",
        'Passwords do not match' => "array",
        'Unique email constraint check' => "array",
        'Unique username constraint check' => "array"
    ])]
    public function userEditActionValidationErrorsProvider(): array
    {
        return [
            'Blank username' => ['', '123password', '123password', ['ROLE_USER'], 'anon@test.com'],
            'Blank email' => ['username', '123password', '123password', ['ROLE_USER'], ''],
            'Passwords do not match' => ['username', '123password', '124password', ['ROLE_USER'], 'anon@test.com'],
            'Unique email constraint check' => ['username', '123password', '123password', ['ROLE_USER'], 'test@test.com'],
            'Unique username constraint check' => ['Test', '123password', '123password', ['ROLE_USER'], 'anon@test.com'],
        ];
    }

}