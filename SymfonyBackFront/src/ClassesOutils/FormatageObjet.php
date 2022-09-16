<?php

namespace App\ClassesOutils;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FormatageObjet
{
    public function stringToLowerObject($object, $objectClass, array $exclude, bool $isArray = false)
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $objectToTransform = $serializer->normalize($object);
        $newArray = array();

        foreach ($objectToTransform as $data => $value) {
            !is_array($value) ? ($data == 'id' ? intval($value)
                : (in_array($data, $exclude) ? NULL
                    : $newArray[$data] = strtolower(strip_tags($value))))
                : NULL;
        }

        return !$isArray ? $serializer->denormalize($newArray, $objectClass) : $newArray;
    }
}
