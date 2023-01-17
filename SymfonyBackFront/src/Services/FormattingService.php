<?php

namespace App\Services;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Cette classe contient des méthodes permettant de formatter des objets selons certains critères.

class FormattingService
{
    /**
     * Cette méthode permet de tranformer tous les champs d'une entité (venant d'un formulaire par exemple) en lowercase.
     * Les données de types array et de type int sont automatiquement ignorées lors de la conversion
     * @param Entity $object un objet de type Entité
     * @param string $objectClass le nom de la classe (type) de l'objet pour la conversion en sortie
     * @param array $exclude (optionnel, null par defaut) un tableau (array) composé de chaînes de caractères (string) pour indiquer les données associées à ne pas convertir afin d'éviter les erreurs lors de la denormalization
     * @param bool $isArrayOut (optionnel, false par defaut) un type boolean pour extraire les valeurs sous formes de array au lieu d'un objet
     * @return mixed si $isArrayOut est défini sur false alors retourne une entité sinon retourne un tableau
     */
    public function stringToLowerObject($object, string $objectClass, array $exclude = null, bool $isArrayOut = false)
    {
        $serializer = new Serializer([(new ObjectNormalizer())]);
        $objectToTransform = $serializer->normalize($object, null, ['ignored_attributes' => ["courriers", "destinataires"]]);
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
