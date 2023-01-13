<?php

namespace App\Services;

use Exception;
use Symfony\Component\Form\Form;

class FormVerification
{
    public function verifyField(Form $form, string $type)
    {
        switch ($type) {
            case 'add':
                if (strlen(intval($form->get('codePostal')->getData())) != 5) {
                    throw new Exception("Le code postal est incorrect");
                }
                break;
        }
    }
}
