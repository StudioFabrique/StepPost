<?php

namespace App\Services;

class MailService
{
    public function getSignature() : string
    {
        return "<p>
        <br>Technopôle Hélioparc
        <br>CS 8011
        <br>2 Av. du Président Pierre Angot, 64000 Pau
        <br>64053 PAU Cedex
        <br>T 05 59 14 78 79
        <br>www.step.eco</p>
        <p style='font-size:10px;'>Ce mail n'affiche volontairement aucun logo et n'a pas vocation à être imprimé #ecoresponsable</p>";
    }
}
