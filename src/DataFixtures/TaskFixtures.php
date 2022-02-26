<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class TaskFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $args = array(
            array(
                'created_at' => new \DateTime(),
                'title' => 'Tâche N° 1',
                'content' => 'Ammener voiture au garage',
                'user_id' => $this->getReference(UserFixtures::ANON_USER_REFERENCE)
            ),
            array(
                'created_at' => new \DateTime(),
                'title' => 'Tâche N° 2',
                'content' => 'Rendez-vous medecin',
                'user_id' => $this->getReference(UserFixtures::ANON_USER_REFERENCE)
            ),
            array(
                'created_at' => new \DateTime(),
                'title' => 'Tâche N° 3',
                'content' => 'Acheter : Liquide vaiselle, pain & fromage.',
                'user_id' => $this->getReference(UserFixtures::ANON_USER_REFERENCE)
            ),
            array(
                'created_at' => new \DateTime(),
                'title' => 'Tâche créer par Test',
                'content' => 'Envoyer e-mail important.',
                'user_id' => $this->getReference(UserFixtures::TEST_USER_REFERENCE)
            )

        );


        foreach($args as $t) {
            $task = new Task();
            $task->setCreatedAt($t['created_at']);
            $task->setTitle($t['title']);
            $task->setContent($t['content']);
            $task->setUser($t['user_id']);

            $manager->persist($task);
        }


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}