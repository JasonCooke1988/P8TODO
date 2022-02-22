<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @codeCoverageIgnore
 */
class AccessDeniedListener implements EventSubscriberInterface
{

    protected int $count = 0;

    protected string $authMessage = 'Vous n’est pas autorisée';

    protected array $uriMessage = array(
        'tasks-delete' => 'à supprimer cette tâche',
        'tasks-edit' => 'à modifier cette tâche.',
        'tasks-create' => 'à créer une tâche.',
        'tasks-toggle' => 'à changer l\'état de cette tâche.',
        'users-edit' => 'à modifier cet utilisateur.',
        'users-create' => 'à créer un utilisateur.',
        'default' => 'à accéder à cette page',
    );

//    Target these uri regex
    protected array $targets = array(
        '^\/tasks\/[0-9]+\/delete^',
        '^\/tasks\/[0-9]+\/edit^',
        '^\/tasks\/create^',
        '^\/tasks\/[0-9]+\/toggle^',
        '^\/users\/[0-9]+\/edit^',
        '^\/users\/create^'
    );

    public static function getSubscribedEvents(): array
    {
        return [
            // the priority must be greater than the Security HTTP
            // ExceptionListener, to make sure it's called before
            // the default exception listener
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $uri = $request->getRequestUri();

//        Default Access denied response params
        $returnUri = '/';
        $flashMessage = $this->authMessage . $this->uriMessage["default"];

//        Generate response based on exception origin
        foreach($this->targets as $t) {
            if (preg_match($t,$uri)) {
                $uri_segments = explode('/', $uri);
                $returnUri .= $uri_segments[1] === 'tasks' ? $uri_segments[1] : '';
                $flashMessage = $this->authMessage . $this->uriMessage[$uri_segments[1] . '-' . str_replace('?','',end($uri_segments))];
            }
        }

        $event->setResponse(new RedirectResponse($returnUri));
        $request->getSession()->getFlashBag()->add('error', $flashMessage);
    }
}