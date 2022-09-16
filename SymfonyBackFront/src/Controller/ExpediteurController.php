<?php

namespace App\Controller;

use App\Entity\Expediteur;
use App\Form\ExpediteurType;
use App\Repository\ExpediteurRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\JWT;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/*
Cette classe permet 
*/

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_ADMIN')]
class ExpediteurController extends AbstractController
{
    #[Route('/expediteurs', name: 'expediteur')]
    public function index(
        ExpediteurRepository $expediteurs,
        Request $request,
        PaginatorInterface $paginator
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $rechercheExpediteur = $request->get('recherche');
        $isCheckBoxExact = $request->get('checkBoxExact');

        if ($rechercheExpediteur != null && strval($rechercheExpediteur)) {
            $isCheckBoxExact ? $donner = $expediteurs->findBy(['nom' => $rechercheExpediteur])
                : $donner = $expediteurs->findLike($rechercheExpediteur);
        } else {
            $donner = $expediteurs->findAll([], ['id' => 'DESC']);
        }

        $expediteur = $paginator->paginate(
            $donner,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('expediteur/index.html.twig', [
            'expediteurs' => $expediteur,
            'isSearch' => $rechercheExpediteur
        ]);
    }



    #[Route('/ajouter', name: 'addExpediteur')]
    public function new(Request $request, MailerInterface $mailer, ExpediteurRepository $expediteurRepo): Response
    {
        $expediteur = new Expediteur();
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbHeureExp = 24;
            $token = (new JWT())->encode(
                [
                    'email' => $form->get('email')->getData(),
                    'nom' => $form->get('nom')->getData(),
                    'prenom' => $form->get('prenom')->getData() != null ? $form->get('prenom')->getData() : null,
                    'adresse' => $form->get('adresse')->getData(),
                    'complement' => $form->get('complement')->getData() != null ? $form->get('complement')->getData() : null,
                    'codePostal' => $form->get('codePostal')->getData(),
                    'ville' => $form->get('ville')->getData(),
                    'telephone' => $form->get('telephone')->getData(),
                    'roles' => ['ROLE_INACTIF']
                ],
                'test', // pass phrase
                'HS256', // protocole d'encodage
                head: ['exp' => time() + (3600 * $nbHeureExp)]
            );
            $expInHtml = $nbHeureExp == 1 ? " heure </p>" : " heures </p>";
            $body = "
            <p> Bonjour" . ($form->get('prenom')->getData() != null ? " " . $form->get('prenom')->getData() . ",</p>" : ",</p>") . "<p>veuillez confirmer la création de votre compte client associé à l'email " . $form->get('email')->getData() . " avec le bouton se trouvant ci-dessous. </p>
            <p><a href='http://localhost:4200/profil/new-client?token=" . $token . "'> Confirmer la création de mon compte client </a></p>
            <p> La confirmation va expirer dans " . $nbHeureExp . $expInHtml;


            try {
                $expediteur->setRoles(['ROLE_INACTIF']);
                $expediteurRepo->add($expediteur);
            } catch (UniqueConstraintViolationException $errorHandler) {
                return $this->redirectToRoute('app_token', [
                    'errorMessage' => $errorHandler ?? null
                ]);
            }

            try {

                $mail = (new Email())
                    ->from('insérer mail') // adresse de l'expéditeur de l'email ayant son email de configuré dans le .env
                    ->to($form->get('email')->getData())
                    ->subject('Création de votre compte client')
                    ->html($body);
                $mailer->send($mail);
            } catch (TransportExceptionInterface $e) {
                $errorHandler = "une erreur s'est produite lors de l'envoi du mail";
            }

            return $this->redirectToRoute('app_token', [
                'errorMessage' => $errorHandler ?? null
            ]);
        }

        return $this->renderForm('expediteur/new.html.twig', [
            'expediteur' => $expediteur,
            'form' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'editExpediteur')]
    public function edit(Request $request, Expediteur $expediteur, ExpediteurRepository $expediteurRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $expediteurRepository->add($expediteur);
            return $this->redirectToRoute('app_expediteur', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('expediteur/edit.html.twig', [
            'expediteur' => $expediteur,
            'form' => $form,
        ]);
    }


    #[Route('/delete/{id}', name: 'deleteExpediteur', methods: ['POST'])]
    public function Delete(Request $request, Expediteur $expediteur, ExpediteurRepository $expediteurRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $expediteur->getId(), $request->request->get('_token'))) {
            $expediteurRepository->remove($expediteur);
        }

        return $this->redirectToRoute('app_expediteur', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/detailsExpediteur', name: 'detailsExpediteur')]
    public function Details(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        $expediteurId = $request->get('expediteurId');
        $expediteur = $expediteurRepository->find($expediteurId);
        return $this->render('expediteur/details.html.twig', [
            'expediteur' => $expediteur
        ]);
    }

    #[Route('/activer', name: 'activateExpediteur')]
    public function Activate(Request $request, ExpediteurRepository $expediteurRepository, EntityManagerInterface $em): RedirectResponse
    {
        $expediteurId = $request->get('expediteurId');
        $expediteur = ($expediteurRepository->find($expediteurId))->setRoles(['ROLE_CLIENT']);
        $em->persist($expediteur);
        $em->flush();
        return $this->redirectToRoute('app_expediteur');
    }

    #[Route('/mailToken', name: 'token')]
    public function RedirectTokenMailView(Request $request)
    {
        $errorHandler = $request->get('errorMessage') ?? "L'email a bien été envoyé";

        return $this->render('expediteur/tokenMailRedirect.html.twig', [
            'errorHandler' => $errorHandler
        ]);
    }
}
