<?php

namespace App\Controller;

use App\Entity\Facteur;
use App\Form\FacteurType;
use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_')]
#[IsGranted('ROLE_GESTION')]
class FacteurController extends AbstractController
{

    /**
     * Retourne un template twig avec la liste de tous les facteurs
     */
    #[Route('/facteurs', name: 'facteur')]
    public function showFacteurs(FacteurRepository $facteurRepo, PaginatorInterface $paginatorInterface, Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $currentPage = $request->get('currentPage') ?? 1;
        $data = $facteurRepo->findAll();

        $facteurs = $paginatorInterface->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
        );

        return $this->render('facteur/index.html.twig', [
            'facteurs' => $facteurs,
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'nbFacteursTotal' => count($data)
        ]);
    }


    /**
     * Affiche la page de création de facteur
     */
    #[Route('/nouveauFacteur', 'newFacteur')]
    public function showNewFacteur(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["Création"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["Création"];

        return $this->renderForm('facteur/form.html.twig', [
            'title' => 'Créer un facteur',
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'isAdding' => $request->get('isAdding'),
            'isEdit' => $request->get('isEdit')
        ]);
    }

    /**
     * Modifie un facteur
     */
    #[Route('/modifierFacteur', 'editFacteur')]
    public function editFacteur(FacteurRepository $facteurRepo, Request $request, EntityManagerInterface $em, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $timezone = new DateTimeZone('UTC');

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["Modification"];

        $facteur = $facteurRepo->find($request->get('id'));
        $form = $this->createForm(FacteurType::class, $facteur)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $facteur
                ->setRoles($facteur->getRoles())
                ->setCreatedAt($facteur->getCreatedAt())
                ->setUpdatedAt(new DateTime(), $timezone);

            foreach ($facteur->getStatutsCourrier() as $statut) {
                $facteur->addStatutsCourrier($statut);
            }

            try {
                $em->persist($facteur);
                $em->flush();
                return $this->redirectToRoute('app_facteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $message) . ' a été modifié']);
            } catch (Exception) {
                return $this->redirectToRoute('app_editFacteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $messageErreur), 'isError' => true]);
            }
        }

        return $this->renderForm('facteur/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le facteur',
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'isAdding' => $request->get('isAdding'),
            'isEdit' => $request->get('isEdit'),
            'facteurId' => $request->get('id')
        ]);
    }

    /**
     * Supprime un facteur
     */
    #[Route('/supprimerFacteur', 'deleteFacteur')]
    public function deleteFacteur(Request $request, FacteurRepository $facteurRepo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["Suppression"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["Suppression"];

        $idFacteur = $request->get('id');
        $facteur = $facteurRepo->find($idFacteur);

        try {
            $facteurRepo->remove($facteur, true);
            return $this->redirectToRoute('app_facteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $message)]);
        } catch (Exception) {
            return $this->redirectToRoute('app_facteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $messageErreur), 'isError' => true]);
        }
    }

    /**
     * Api qui permet de créer un facteur.
     * @param Request $request POST : email, nom, prenom
     */
    #[Route(path: '/api/newFacteur', name: 'api_newFacteur')]
    public function newFacteur(Request $request, FacteurRepository $facteurRepository): JsonResponse
    {
        $timezone = new DateTimeZone('UTC');

        $email = $request->request->get('email');
        $nom = $request->request->get('nom');
        $password = $request->request->get('password');

        if (!$this->getUser() || $email == null || $nom == null || $password == null) {
            return new JsonResponse("Authentification échoué");
        }

        $facteur = (new Facteur())
            ->setEmail($email)
            ->setNom($nom)
            ->setPassword($password)
            ->setCreatedAt(new DateTime(), $timezone)
            ->setUpdatedAt(new DateTime(), $timezone)
            ->setRoles(['ROLE_FACTEUR']);
        try {
            $facteurRepository->add($facteur, true);
            return new JsonResponse('facteur créé');
        } catch (Exception $e) {
            return new JsonResponse('erreur');
        }
    }

    /**
     * Api qui permet de modifier un facteur.
     * @param Request $request POST : email, nom, prenom
     */
    #[Route(path: '/api/editPasswordFacteur', name: 'api_editPasswordFacteur')]
    public function editPasswordacteur(Request $request, FacteurRepository $facteurRepository): JsonResponse
    {
        $timezone = new DateTimeZone('UTC');

        $id = $request->request->get('id');
        $password = $request->request->get('password');

        if (!$this->getUser() || $id == null || $password == null) {
            return new JsonResponse("Authentification échoué");
        }

        $facteur = $facteurRepository->find($id)
            ->setPassword($password)
            ->setUpdatedAt(new DateTime(), $timezone);
        try {
            $facteurRepository->add($facteur, true);
            return new JsonResponse('facteur créé');
        } catch (Exception $e) {
            return new JsonResponse('erreur');
        }
    }
}
