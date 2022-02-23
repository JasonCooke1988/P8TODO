<?php

namespace App\Security;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @codeCoverageIgnore
 */
class TaskVoter extends Voter
{

    const DELETE = 'delete';
    const EDIT = 'edit';
    const CREATE = 'create';
    const TOGGLE = 'toggle';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DELETE, self::EDIT, self::CREATE,  self::TOGGLE])) {
            return false;
        }

        // only vote on `Task` objects
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Task object, thanks to `supports()`
        /** @var Task $task */
        $task = $subject;

        switch ($attribute) {
            case self::DELETE || self::EDIT:
                return $this->canEdit($task, $user);
            case self::CREATE || self::TOGGLE:
                return true;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Task $task, User $user): bool
    {

        if (in_array('ROLE_ADMIN', $user->getRoles()) && $task->getUser()->getEmail() === 'anon@test.com') {
            return true;
        }

        return $user === $task->getUser();
    }
}