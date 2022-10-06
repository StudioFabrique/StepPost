<?php

namespace App\Controller;

use App\ClassesOutils\FormatageObjet;
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
        return $this->renderForm('facteur/form.html.twig', [
            // 'form' => $form,
            'title' => 'Créer un facteur',
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/modifierFacteur', 'editFacteur')]
    public function editFacteur(FacteurRepository $facteurRepo, Request $request, EntityManagerInterface $em, ExpediteurRepository $expediteurRepository): Response
    {
        $ancienFacteur = $facteurRepo->find($request->get('id'));
        $form = $this->createForm(FacteurType::class, $ancienFacteur)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $facteur = (new FormatageObjet)->stringToLowerObject(
                $formData,
                Facteur::class,
                array('createdAt', 'updatedAt'),
                false
            );

            $facteur
                ->setRoles($ancienFacteur->getRoles())
                ->setCreatedAt($ancienFacteur->getCreatedAt())
                ->setUpdatedAt(new DateTime())
                ->setPassword($ancienFacteur->getPassword());

            foreach ($ancienFacteur->getStatutsCourrier() as $statut) {
                $facteur->addStatutsCourrier($statut);
            }

            try {
                $em->persist($facteur);
                $em->flush();
                return $this->redirectToRoute('app_facteur', ['errorMessage' => 'Le facteur ' . $facteur->getNom() . ' a été modifié']);
            } catch (Exception) {
                return $this->redirectToRoute('app_editFacteur', ['errorMessage' => "La modification a échoué, l'adresse mail saisie est déjà associée à un autre facteur", 'isError' => true]);
            }
        }

        return $this->renderForm('facteur/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le facteur',
            'expediteursInactifs' => $expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false
        ]);
    }

    #[Route('/supprimerFacteur', 'deleteFacteur')]
    public function deleteFacteur(Request $request, FacteurRepository $facteurRepo): Response
    {
        $idFacteur = $request->get('id');
        $facteur = $facteurRepo->find($idFacteur);

        try {
            $facteurRepo->remove($facteur, true);
            return $this->redirectToRoute('app_facteur', ['errorMessage' => 'Le facteur ' . $facteur->getNom() . ' a bien été supprimé']);
        } catch (Exception) {
            return $this->redirectToRoute('app_facteur', ['errorMessage' => 'La suppression du facteur a échoué', 'isError' => true]);
        }
    }
}
