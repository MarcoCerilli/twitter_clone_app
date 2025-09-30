<?php

namespace App\Security; // <-- PRIMO CONTROLLO: Il namespace deve essere questo.

// SECONDO CONTROLLO: Assicurati che tutti questi "use" siano presenti.
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;


// TERZO CONTROLLO: La classe deve implementare UserCheckerInterface.
class UserChecker implements UserCheckerInterface
{
    /**
     * Controlla l'utente PRIMA dell'autenticazione (controllo password).
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        if($user->getBannedUntil() !== null && $user->getBannedUntil() > new \DateTimeImmutable()) {
            $banEnd = $user->getBannedUntil()->format('d/m/Y H:i');
            throw new CustomUserMessageAuthenticationException(
                "Il tuo account è stato bannato fino al $banEnd. Contatta l'amministratore per maggiori informazioni."
            );
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException(
                'Il tuo account non è ancora stato verificato. Controlla la tua email per il link di attivazione.'
            );
        }
    }

     /**
     * @param UserInterface $user
     * @param TokenInterface|null $token // ✅ IL NUOVO ARGOMENTO È QUI
     * @return void
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // Per ora non facciamo nulla qui.
    }
}