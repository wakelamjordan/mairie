<?php

namespace App\EventListener;

use App\Entity\Local;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class LocalListener
{
    private $defaultLocal = 'fr';
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }
    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(): void
    {
        $accepted = $this->parseStringLanguageToArrayLanguage();
        $available = $this->getLocalWebSite();
        // dd($available);
        $localMatched = $this->findMatches($accepted, $available);

        reset($localMatched);
        $localMatched = current($localMatched);
        // dd($localMatched);

        if (!$localMatched) {
            $localMatched = [];
            $localMatched[0] = $this->defaultLocal;
        }

        if ($this->localeSwitcher->getLocale() !== $localMatched[0]) {
            $this->localeSwitcher->setLocale($localMatched[0]);
        }

        // dd($localMatched[0]);
        return;
    }
    private function parseStringLanguageToArrayLanguage($languageList = null, $delimiteur = ',')
    {
        // Vérifier si la liste des langues est fournie, sinon utiliser $_SERVER['HTTP_ACCEPT_LANGUAGE']
        if (is_null($languageList)) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return array(); // Si la liste des langues n'est pas disponible, retourner un tableau vide
            }
            $languageList = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        // Initialiser un tableau pour stocker les langues et leurs qualités
        $languages = array();

        // Diviser la liste des langues en segments individuels basés sur le délimiteur
        $languageRanges = explode($delimiteur, trim($languageList));

        // Parcourir chaque segment de langue
        foreach ($languageRanges as $languageRange) {
            // Utiliser une expression régulière pour extraire la langue et sa qualité
            if (preg_match('/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/', trim($languageRange), $match)) {
                // Si la qualité n'est pas définie, utiliser la qualité maximale par défaut
                if (!isset($match[2])) {
                    $match[2] = '1.0';
                } else {
                    $match[2] = (string) floatval($match[2]);
                }
                // Ajouter la langue au tableau $languages associée à sa qualité
                if (!isset($languages[$match[2]])) {
                    $languages[$match[2]] = array();
                }
                $languages[$match[2]][] = strtolower($match[1]);
            }
        }

        // Trier les langues par qualité (ordre décroissant)
        krsort($languages);

        // Retourner le tableau des langues triées par qualité
        return $languages;
    }

    private function findMatches($accepted, $available)
    {
        $matches = array(); // Initialisation du tableau de correspondances
        $any = false; // Indicateur pour vérifier si une correspondance avec '*' a été trouvée

        // Parcourir chaque élément dans le tableau des langues acceptées
        foreach ($accepted as $acceptedQuality => $acceptedValues) {
            $acceptedQuality = floatval($acceptedQuality); // Convertir la qualité acceptée en float

            // Ignorer les langues avec une qualité de 0.0
            if ($acceptedQuality === 0.0) continue;

            // Parcourir chaque élément dans le tableau des langues disponibles
            foreach ($available as $availableQuality => $availableValues) {
                $availableQuality = floatval($availableQuality); // Convertir la qualité disponible en float

                // Ignorer les langues avec une qualité de 0.0
                if ($availableQuality === 0.0) continue;

                // Parcourir chaque langue acceptée
                foreach ($acceptedValues as $acceptedValue) {
                    // Si la langue acceptée est '*', définir $any à true
                    if ($acceptedValue === '*') {
                        $any = true;
                    }

                    // Parcourir chaque langue disponible
                    foreach ($availableValues as $availableValue) {
                        // Calculer le degré de correspondance entre la langue acceptée et la langue disponible
                        $matchingGrade = $this->matchLanguage($acceptedValue, $availableValue);

                        // Si la correspondance est supérieure à 0
                        if ($matchingGrade > 0) {
                            // Calculer la qualité de la correspondance
                            $q = (string) ($acceptedQuality * $availableQuality * $matchingGrade);

                            // Ajouter la langue disponible à la liste des correspondances avec la qualité calculée
                            if (!isset($matches[$q])) {
                                $matches[$q] = array(); // Initialiser le tableau s'il n'existe pas encore
                            }
                            if (!in_array($availableValue, $matches[$q])) {
                                $matches[$q][] = $availableValue; // Ajouter la langue disponible à la liste des correspondances
                            }
                        }
                    }
                }
            }
        }

        // Si aucune correspondance n'a été trouvée mais '*' est présent dans les langues acceptées, utiliser les langues disponibles
        if (count($matches) === 0 && $any) {
            $matches = $available;
        }

        // Trier les correspondances par qualité (ordre décroissant)
        krsort($matches);

        // Retourner les correspondances
        return $matches;
    }

    private function matchLanguage($a, $b)
    {
        // Diviser les tags de langue en parties en utilisant le tiret comme délimiteur
        $a = explode('-', $a);
        $b = explode('-', $b);

        // Parcourir les parties des deux tags de langue et comparer chaque partie
        for ($i = 0, $n = min(count($a), count($b)); $i < $n; $i++) {
            // Si une partie des tags de langue est différente, arrêter la comparaison
            if ($a[$i] !== $b[$i]) break;
        }

        // Retourner le degré de correspondance (0 pour aucune correspondance, 1 pour correspondance totale)
        return $i === 0 ? 0 : (float) $i / count($a);
    }

    function getLocalWebSite()
    {
        $locals = $this->entityManagerInterface->getRepository(Local::class)->findAll();

        // dd($locals);
        // for
        $localsGoodFormatArray = [];
        foreach ($locals as $local) {
            $localsGoodFormatArray[1.0][$local->getRank()] = $local->getLabel();
        }


        return $localsGoodFormatArray;
    }
}
