<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyFct extends AbstractController
{
    private string $url;
    private string $paramRequired;
    private object $user;
    private int $syllables;

    public function __construct(
        private EntityManagerInterface $entityManagerInterface
    ) {
    }

    public function updateLoginAt(User $user): void
    {
        $user->setLoginAt(new DateTimeImmutable());

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
        $listRequest = $user->getListRequest();
        if ($listRequest) {
            $emailConfirmationDate = $listRequest->getRequestAt();

            if ($emailConfirmationDate->modify('+30 minutes') > new DateTimeImmutable()) {
                return false;
            } else {
                $this->entityManagerInterface->remove($listRequest);
                $this->entityManagerInterface->flush();
            }
        }
        // dd($emailConfirmationDate->modify('+30 minutes') > new DateTimeImmutable());

        return true;
    }


    // if (!$this->myFct->checkCorrespondanceRequest($user, $request)) {
    //     return $this->redirectToRoute('app_home');
    // }
    public function checkCorrespondanceRequest(User $user, Request $request): bool
    {
        $listRequest = $user->getListRequest();

        if (!$listRequest) {
            $this->addFlash('error', 'Liens invalide.');
            return false;
        }

        if ($request->query->get('signature') !== $listRequest->getParam()) {
            $this->addFlash('error', 'Liens invalide.');
            return false;
        }

        return true;
    }
}
