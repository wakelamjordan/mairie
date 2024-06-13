<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\ConfirmationEmail;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface; // Pour la journalisation des erreurs
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\ORMException; // Pour capturer les exceptions ORM spécifiques
use Symfony\Component\HttpKernel\Exception\HttpException; // Si vous souhaitez utiliser des exceptions HTTP (optionnel)
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MyFct extends AbstractController
{
    private string $url;
    private string $paramRequired;
    private object $user;
    private int $syllables;

    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private UserPasswordHasherInterface $userPasswordHasher,
        private LoggerInterface $logger,
        private TranslatorInterface $translate,
    ) {
    }

    public function updateLoginAt(User $user): void
    {
        $this->entityManagerInterface->persist($user);
        $this->entityManagerInterface->flush();
    }

    public function generateRandomSantence(): string
    {
        $subjects = ['the cat', 'the dog', 'the car', 'the house'];
        $verbs = ['ran', 'jumped', 'crashed', 'exploded'];
        $prepositions = ['on', 'in', 'under', 'over'];
        $objects = ['the table', 'the bed', 'the tree', 'the road'];

        // Sélectionner un sujet aléatoire
        $subject = $subjects[array_rand($subjects)];

        // Sélectionner un verbe aléatoire
        $verb = $verbs[array_rand($verbs)];

        // Sélectionner une préposition aléatoire
        $preposition = $prepositions[array_rand($prepositions)];

        // Sélectionner un objet aléatoire
        $object = $objects[array_rand($objects)];

        // Créer la phrase aléatoire en concaténant les mots sélectionnés
        $randomSentence = "$subject $verb $preposition $object";

        return $randomSentence;
    }

    public function generateRandomName($syllables = 3): string
    {
        // List of syllables to use for generating names
        $syllableList = [
            "ba", "be", "bi", "bo", "bu",
            "ca", "ce", "ci", "co", "cu",
            "da", "de", "di", "do", "du",
            "fa", "fe", "fi", "fo", "fu",
            "ga", "ge", "gi", "go", "gu",
            "ha", "he", "hi", "ho", "hu",
            "ja", "je", "ji", "jo", "ju",
            "ka", "ke", "ki", "ko", "ku",
            "la", "le", "li", "lo", "lu",
            "ma", "me", "mi", "mo", "mu",
            "na", "ne", "ni", "no", "nu",
            "pa", "pe", "pi", "po", "pu",
            "ra", "re", "ri", "ro", "ru",
            "sa", "se", "si", "so", "su",
            "ta", "te", "ti", "to", "tu",
            "va", "ve", "vi", "vo", "vu",
            "wa", "we", "wi", "wo", "wu",
            "ya", "ye", "yi", "yo", "yu",
            "za", "ze", "zi", "zo", "zu"
        ];

        $name = "";
        for ($i = 0; $i < $syllables; $i++) {
            // Randomly select a syllable from the list
            $name .= $syllableList[array_rand($syllableList)];
        }

        // Capitalize the first letter of the generated name
        return ucfirst($name);
    }

    public function getParamUrl(string $url, string $paramRequired): mixed
    {
        $parsedUrl = parse_url($url);

        $queryString = $parsedUrl['query'] ?? '';

        parse_str($queryString, $queryParams);

        // Récupérer le paramètre 'signature'
        $param = $queryParams[$paramRequired] ?? false;

        return $param;
    }

    public function getError($form)
    {
        $errors = [];
        // Parcours des erreurs du formulaire
        foreach ($form->getErrors(true) as $error) {
            // Récupération du nom du champ associé à l'erreur
            $fieldName = $error->getOrigin()->getName();

            // Récupération du message d'erreur
            $errorMessage = $error->getMessage();

            // Ajout de l'erreur au tableau d'erreurs
            $errors[$fieldName] = $errorMessage;
        }
        // $data=$form->getDa();
        dd($errors, $form->getData());

        // $this->myfct->getError($form);
    }

    public function checkLapsTimeRequest(User $user): bool
    {
        // $confirmationEmails = $user->getListRequests();
        // $listRequest = '';
        // $maxId = 0;

        // foreach ($listRequests as $r) {
        //     $rId = $r->getId();

        //     // Comparaison pour trouver l'ID le plus élevé
        //     if ($rId > $maxId) {
        //         $maxId = $rId;
        //         $listRequest = $r;
        //     }
        // }
        // if ($listRequest) {
        //     $emailConfirmationDate = $listRequest->getRequestAt();

        //     if ($emailConfirmationDate->modify('+30 minutes') > new DateTimeImmutable()) {
        //         return false;
        //     } else {
        //         $this->entityManagerInterface->remove($listRequest);
        //         $this->entityManagerInterface->flush();
        //     }
        // }
        // dd($emailConfirmationDate->modify('+30 minutes') > new DateTimeImmutable());

        return true;
    }

    // $listRequest = '';

    // foreach ($confirmationEmails as $r) {
    //     $rId = $r->getId();

    //     // Comparaison pour trouver l'ID le plus élevé
    //     if ($rId > $maxId) {
    //         $maxId = $rId;
    //         $listRequest = $r;
    //     }
    // }
    // if (!$this->myFct->checkCorrespondanceRequest($user, $request)) {
    //     return $this->redirectToRoute('app_home');
    // }



    /**
     * Vérifie si la requête correspond à l'email de confirmation d'un utilisateur.
     *
     * Cette méthode vérifie si l'utilisateur possède un email de confirmation associé,
     * et si la signature dans la requête correspond à la signature de l'email de confirmation.
     * Elle ajoute un message flash d'erreur si la vérification échoue.
     *
     * @param User $user L'utilisateur dont on vérifie la correspondance de la requête.
     * @param Request $request La requête HTTP contenant les paramètres à vérifier.
     *
     * @return bool Retourne true si la correspondance est vérifiée avec succès, sinon false.
     */
    public function checkCorrespondanceRequest(User $user, Request $request): bool
    {
        try {
            // Récupère l'email de confirmation de l'utilisateur
            $confirmationEmail = $user->getConfirmationEmail();
            // Vérifie si l'email de confirmation existe
            if (!$confirmationEmail) {
                $this->addFlash('error', $this->translate->trans('Lien invalide.'));
                return false;
            }

            // Compare la signature dans la requête avec celle de l'email de confirmation
            if ($request->query->get('signature') !== $confirmationEmail->getSignature()) {
                $this->addFlash('error', $this->translate->trans('Lien invalide.'));
                return false;
            }

            // Correspondance vérifiée avec succès
            return true;
        } catch (\Exception $e) {
            // Gestion des exceptions génériques ou inattendues
            $this->logger->error('Erreur dans checkCorrespondanceRequest: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de la vérification de la correspondance de la requête.');
        }
    }

    /**
     * Crée une entité ConfirmationEmail et la persiste dans la base de données.
     *
     * @param string $signedUrl L'URL signée contenant les paramètres de confirmation, y compris la signature.
     * @param User $user L'utilisateur pour lequel la confirmation email est générée.
     *
     * @throws \InvalidArgumentException Si l'URL signée ne contient pas de paramètres ou si le paramètre "signature" est manquant.
     * @throws \Doctrine\ORM\ORMException Si une erreur survient lors de la persistance de l'entité dans la base de données.
     * @throws \RuntimeException Si une erreur générique ou inattendue se produit.
     */
    public function createConfirmationEmail(string $signedUrl, User $user): void
    {
        try {
            // Analyse de l'URL signée pour extraire les paramètres
            $parsed_url = parse_url($signedUrl);
            if (!isset($parsed_url['query'])) {
                throw new \InvalidArgumentException('L\'URL signée ne contient pas de paramètres de requête.');
            }

            // Extraction des paramètres de la chaîne de requête
            $queryParams = $parsed_url['query'];
            parse_str($queryParams, $params);

            // Vérification de l'existence du paramètre 'signature'
            if (!isset($params['signature'])) {
                throw new \InvalidArgumentException('Le paramètre "signature" est manquant dans l\'URL signée.');
            }

            // Création de l'entité ConfirmationEmail
            $confirmationEmail = new ConfirmationEmail();
            $confirmationEmail
                ->setSignature($params['signature'])
                ->setUser($user);

            // Persistance de l'entité
            $this->entityManagerInterface->persist($confirmationEmail);
            $this->entityManagerInterface->flush();

            // Debug dump pour vérifier l'entité créée (à utiliser pour le développement)
            // dd($confirmationEmail);

        } catch (\InvalidArgumentException $e) {
            // Gestion des erreurs d'argument invalide
            $this->logger->error('Erreur dans createConfirmationEmail: ' . $e->getMessage());
            throw $e; // Relancer l'exception ou gérer comme approprié

        } catch (ORMException $e) {
            // Gestion des exceptions ORM spécifiques à Doctrine
            $this->logger->error('Erreur ORM dans createConfirmationEmail: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de l\'enregistrement de la confirmation email.');
        } catch (\Exception $e) {
            // Gestion des autres exceptions génériques
            $this->logger->error('Erreur générique dans createConfirmationEmail: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur inattendue est survenue.');
        }
    }

    /**
     * Met à jour l'utilisateur avec des données aléatoires et persiste ces modifications dans la base de données.
     *
     * @param User $user L'utilisateur à mettre à jour avec des données aléatoires.
     *
     * @throws \Doctrine\ORM\ORMException Si une erreur survient lors de la persistance de l'entité dans la base de données.
     * @throws \Exception Si une erreur générique ou inattendue se produit.
     */
    public function userWithRandomData(User $user): void
    {
        try {
            // Génère et définit un mot de passe hashé aléatoire pour l'utilisateur
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $this->generateRandomSantence()
                )
            );

            // Définit un prénom aléatoire pour l'utilisateur
            $user->setLastname($this->generateRandomName());

            // Définit un nom de famille aléatoire pour l'utilisateur
            $user->setFirstname($this->generateRandomName());

            // Persiste l'utilisateur mis à jour dans la base de données
            $this->entityManagerInterface->persist($user);
            $this->entityManagerInterface->flush();
        } catch (ORMException $e) {
            // Gestion des exceptions ORM spécifiques à Doctrine
            $this->logger->error('Erreur ORM dans userWithRandomData: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de la mise à jour de l\'utilisateur avec des données aléatoires.');
        } catch (\Exception $e) {
            // Gestion des autres exceptions génériques
            $this->logger->error('Erreur générique dans userWithRandomData: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur inattendue est survenue.');
        }
    }


    //     $user = [
    //     'firstname' => 'Jean',
    //     'lastname' => 'Dupont'
    // ];

    // // Générer l'URL de confirmation (exemple)
    // $confirmationUrl = 'https://votre-site.com/confirm/delete?token=XYZ';

    // // Rendre le contenu HTML avec Twig
    // $htmlContent = $twig->render('email/confirmation.html.twig', [
    //     'user' => $user,
    //     'confirmationUrl' => $confirmationUrl
    // ]);

    // // Créer l'e-mail
    // $email = (new Email())
    //     ->from('votre_email@example.com')
    //     ->to('destinataire@example.com')
    //     ->subject('Confirmez la suppression de votre compte')
    //     ->html($htmlContent);

    // // Envoyer l'e-mail
    // $mailer->send($email);
}
