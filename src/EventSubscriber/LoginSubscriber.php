<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class LoginSubscriber
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security
    ) {}

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        // Otteniamo l'utente che ha appena effettuato l'accesso
        /** @var User|null $user */
        $user = $this->security->getUser();

        // Prepariamo un messaggio di benvenuto personalizzato
        $username = $user?->getUserProfile() ? $user->getUserProfile()->getUsername() : $user?->getEmail();
        $message = "Bentornato, " . ($username ?? 'utente') . "!";

        $this->addFlash('success', $message);
    }

    #[AsEventListener(event: LoginFailureEvent::class)]
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        // In caso di errore, mostriamo un messaggio generico per sicurezza
        $this->addFlash('danger', 'Credenziali non valide. Riprova.');
    }

    /**
     * Helper privato per aggiungere messaggi flash in modo pulito.
     */
    private function addFlash(string $type, string $message): void
    {
        // Prende la sessione corrente dalla RequestStack
        $session = $this->requestStack->getSession();

        // Assicura che la sessione sia istanza di Session per accedere a getFlashBag
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\Session) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
