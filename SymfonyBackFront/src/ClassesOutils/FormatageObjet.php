<?php

namespace App\ClassesOutils;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Cette classe contient des fonctions permettant de formatter des objets selons certains critères.

class FormatageObjet
{
    // Cette fonction permet de tranformer tous les champs d'une entité (venant d'un formulaire par exemple) en lowercase.

    public function stringToLowerObject($object, $objectClass, array $exclude = null, bool $isArrayOut = false)
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
