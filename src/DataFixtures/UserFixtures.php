<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;
    public const ANON_USER_REFERENCE = 'anon-user';
    public const TEST_USER_REFERENCE = 'test-user';

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        $data = array(
            array(
                'email' => 'anon@test.com',
                'password' => 'test',
                'username' => 'Anon',
                'roles' => ['ROLE_ADMIN'],
                'reference' => self::ANON_USER_REFERENCE
            ),
            array(
                'email' => 'test@test.com',
                'password' => 'test',
                'username' => 'Test',
                'roles' => ['ROLE_USER'],
                'reference' => self::TEST_USER_REFERENCE
            )
        );

        foreach($data as $u) {
            $user = new User();
            $user->setEmail($u['email']);
            $password = $this->passwordHasher->hashPassword(
                $user,
                $u['password']
            );
            $user->setPassword($password);
            $user->setUsername($u['username']);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setRoles($u['roles']);
            $manager->persist($user);
            $this->addReference($u['reference'],$user);
        }
        $manager->flush();
    }
}
