<?php

namespace App\Services;

use Symfony\Component\Form\Form;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


/**
 * Ce service contient les méthodes pour envoyer des mails à partir du mailer.
 */
class MailService
{
    private $formattingService, $mailer;

    /**
     * Constructeur
     */
    public function __construct(FormattingService $formattingService, MailerInterface $mailer)
    {
        $this->formattingService = $formattingService;
        $this->mailer = $mailer;
    }

    /**
     * Envoi un mail contenant le token fourni 5avec une durée d'expiration).
     */
    public function sendMail($token, $nbHeureExp, Form $form)
    {
        $body = "
            <p> Bonjour" . ($form->get('prenom')->getData() != null ? " " . $form->get('prenom')->getData() . ",</p>" : ",</p>") . "<p>veuillez confirmer la création de votre compte client associé à l'email " . $form->get('email')->getData() . " avec le bouton se trouvant ci-dessous. </p>
            <p><a href='https://step-post.fr/profil/validation-nouveau-compte?token=" . $token . "'> Confirmer la création de mon compte client </a></p>
            <p> La confirmation va expirer dans " . $nbHeureExp . ($nbHeureExp == 1 ? " heure </p>" : " heures </p>") . $this->getSignature();
        $mail = (new Email())
            ->from($this->formattingService->formatMailFromEnv()) // adresse de l'expéditeur de l'email ayant son email de configuré dans le .env
            ->to($form->get('email')->getData())
            ->subject('Création de votre compte client')
            ->html($body);
        $this->mailer->send($mail);
    }

    /**
     * Récupère la signature
     */
    private function getSignature(): string
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
