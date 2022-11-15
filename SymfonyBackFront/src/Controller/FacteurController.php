<?php

namespace App\Controller;

use App\Entity\Facteur;
use App\Form\FacteurType;
use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
use DateTime;
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
#[IsGranted('ROLE_ADMIN')]
class FacteurController extends AbstractController
{
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

    #[Route('/nouveauFacteur', 'newFacteur')]
    public function newFacteur(Request $request, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["Création"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["Création"];

        return $this->renderForm('facteur/form.html.twig', [
            // 'form' => $form,
            'title' => 'Créer un facteur',
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'newFacteurEndpoint' => $_ENV["ENDPOINT_NEWFACTEUR"],
            'isAdding' => $request->get('isAdding')
        ]);
    }

    #[Route(path: '/api/newFacteur', name: 'api_newFacteur')]
    public function newFacteurApi(Request $request, FacteurRepository $facteurRepository): JsonResponse
    {
        $facteur = (new Facteur())
            ->setEmail($request->request->get('email'))
            ->setNom($request->request->get('nom'))
            ->setPassword($request->request->get('password'))
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime())
            ->setRoles(['ROLE_FACTEUR']);
        try {
            $facteurRepository->add($facteur, true);
            return new JsonResponse('facteur créé');
        } catch (Exception $e) {
            return new JsonResponse('erreur');
        }
    }

    #[Route('/modifierFacteur', 'editFacteur')]
    public function editFacteur(FacteurRepository $facteurRepo, Request $request, EntityManagerInterface $em, ExpediteurRepository $expediteurRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["Modification"];

        $facteur = $facteurRepo->find($request->get('id'));
        $form = $this->createForm(FacteurType::class, $facteur)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $facteur
                ->setRoles($facteur->getRoles())
                ->setCreatedAt($facteur->getCreatedAt())
                ->setUpdatedAt(new DateTime());

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
            'isAdding' => $request->get('isAdding')
        ]);
    }

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
}
