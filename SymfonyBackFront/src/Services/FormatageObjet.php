<?php

namespace App\Services;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Cette classe contient des méthodes permettant de formatter des objets selons certains critères.

class FormatageObjet
{
    /* 
        Cette méthode permet de tranformer tous les champs d'une entité (venant d'un formulaire par exemple) en lowercase.
        Les données de types array et de type int sont automatiquement ignorées lors de la conversion
        
        Cette méthode accepte en paramètres :
        - un objet de type Entité Symfony
        - le nom de la classe (type) de l'objet pour la conversion en sortie
        - (optionnel, null par defaut) un tableau (array) composé de chaînes de caractères (string) :
            pour indiquer les données associées à ne pas convertir afin d'éviter les erreurs lors de la denormalization
        - (optionnel, false par defaut) un type boolean pour extraire les valeurs sous formes de array au lieu d'un objet
     */

    public function stringToLowerObject($object, string $objectClass, array $exclude = null, bool $isArrayOut = false)
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $objectToTransform = $serializer->normalize($object);
        $newArray = array();

        foreach ($objectToTransform as $data => $value) {
            var_dump(in_array($data, $exclude));
            !is_array($value) ? (in_array($data, $exclude) ? NULL
                : $newArray[$data] = strtolower(strip_tags($value)))
                : NULL;
            if ($data == "id") $newArray[$data] = intval($value);
        }

        return !$isArrayOut ? $serializer->denormalize($newArray, $objectClass, null, [ObjectNormalizer::OBJECT_TO_POPULATE => $object]) : $newArray;
    }
}
