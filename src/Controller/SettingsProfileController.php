<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\ProfileImageType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')] // Aggiungi questo per proteggere la pagina
final class SettingsProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'app_settings_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        // Logica elegante per ottenere il profilo esistente o crearne uno nuovo
        $userProfile = $user->getUserProfile() ?? new UserProfile();

        $form = $this->createForm(UserProfileType::class, $userProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($userProfile->getId() === null) {
                $userProfile->setCreated(new \DateTimeImmutable());
            }

            // ✅ CORREZIONE 2: Associa il profilo all'utente corrente
            // Questo crea il collegamento tra le due entità.
            $user->setUserProfile($userProfile);

            // ✅ CORREZIONE 3: Salva le modifiche nel database
            // Persist è necessario solo se $userProfile è un oggetto nuovo, ma non fa male.
            $entityManager->persist($userProfile);

            // Flush esegue la query di salvataggio.
            $entityManager->flush();

            // ✅ CORREZIONE 4: Aggiungi un messaggio di feedback per l'utente
            $this->addFlash('success', 'Il tuo profilo è stato aggiornato con successo.');

            // Reindirizza alla stessa pagina per mostrare il messaggio e ricaricare i dati
            return $this->redirectToRoute('app_settings_profile');
        }
        // --- GESTIONE FORM 2: IMMAGINE PROFILO (con logica corretta) ---
        $imageForm = $this->createForm(ProfileImageType::class);
        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $profileImageFile */
            $profileImageFile = $imageForm->get('profileImage')->getData();

            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profileImageFile->guessExtension();

                // Prova a spostare il file
                try {
                    $profileImageFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Impossibile caricare l\'immagine, riprova.');
                    // In caso di errore, reindirizziamo subito senza salvare nulla
                    return $this->redirectToRoute('app_settings_profile');
                }

                // ✅ CORREZIONE: QUESTA PARTE ORA È FUORI DAL CATCH
                // Se il "move" è andato a buon fine, salviamo il nome del file nel database
                $userProfile->setImage($newFilename);
                $entityManager->flush();

                $this->addFlash('success', 'Immagine del profilo aggiornata!');
                return $this->redirectToRoute('app_settings_profile');
            }
        }


        return $this->render('settings_profile/profile.html.twig', [
            'form' => $form->createView(),
            'imageForm' => $imageForm->createView(), // <-- 4. Passa il secondo form al template

        ]);
    }
}
