<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Exception;
use App\Entity\Facteur;
use App\Form\FacteurType;
use App\Repository\ExpediteurRepository;
use App\Repository\FacteurRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class FacteurService extends AbstractController
{

    private $facteurRepo, $paginatorInterface, $expediteurRepository, $facteurRepository;

    function __construct(FacteurRepository $facteurRepo, PaginatorInterface $paginatorInterface, ExpediteurRepository $expediteurRepository, FacteurRepository $facteurRepository){
        $this->facteurRepository = $facteurRepository;
        $this->facteurRepo = $facteurRepo;
        $this->paginatorInterface = $paginatorInterface;
        $this->expediteurRepository = $expediteurRepository;
}

    /**
     * Retourne un template twig avec la liste de tous les facteurs
     */

    function ShowFacteurService(Request $request):Response {
        $currentPage = $request->get('currentPage') ?? 1;
        $data = $this->facteurRepo->findAll();

        $facteurs = $this->paginatorInterface->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
        );

        return $this->render('facteur/index.html.twig', [
            'facteurs' => $facteurs,
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $currentPage,
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'nbFacteursTotal' => count($data)
        ]);
    }

    /**
     * Affiche la page de création de facteur
     */

    function ShowNewFacteurService(Request $request):Response {

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["1,Création"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["1,Création"];

        return $this->renderForm('facteur/form.html.twig', [
            'title' => 'Créer un facteur',
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
            'errorMessage' => $request->get('errorMessage') ?? null,
            'isError' => $request->get('isError') ?? false,
            'isAdding' => $request->get('isAdding'),
            'isEdit' => $request->get('isEdit')
        ]);

    }

    /**
     * Modifie un facteur
     */

     function EditFacteurService(Request $request, Facteur $facteur):Response {

        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        $message = $messages["Messages Informations"]["Facteur"]["2,Modification"];
        $messageErreur = $messages["Messages Erreurs"]["Facteur"]["2,Modification"];

        $form = $this->createForm(FacteurType::class, $facteur)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $facteur
                ->setEmail($form->get("email")->getData())
                ->setNom($form->get("nom")->getData());

            foreach ($facteur->getStatutsCourrier() as $statut) {
                $facteur->addStatutsCourrier($statut);
            }

            try {
                $this->facteurRepo->add($facteur, true);
                return $this->redirectToRoute('app_facteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $message) . ' a été modifié']);
            } catch (Exception) {
                return $this->redirectToRoute('app_editFacteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $messageErreur), 'isError' => true]);
            }
        }

        return $this->renderForm('facteur/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le facteur',
            'expediteursInactifs' => $this->expediteurRepository->findAllInactive(),
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

    //  function DeleteFacteurService(Request $request, EntityManager $em):Response {

    //     $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
    //     $message = $messages["Messages Informations"]["Facteur"]["3,Suppression"];
    //     $messageErreur = $messages["Messages Erreurs"]["Facteur"]["3,Suppression"];

    //     $idFacteur = $request->get('id');
    //     $facteur = $this->facteurRepo->find($idFacteur);

    //     try {
    //         $facteur->setRoles(['ROLE_INACTIF']);
    //         $em->persist($facteur);
    //         $em->flush();
    //         return $this->redirectToRoute('app_facteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $message)]);
    //     } catch (Exception) {
    //         return $this->redirectToRoute('app_facteur', ['errorMessage' => str_replace('[nom]', $facteur->getNom(), $messageErreur), 'isError' => true]);
    //     }
    //  }

     /**
     * Api qui permet de créer un facteur.
     * @param Request $request POST : email, nom, prenom
     */

     function NewFacteurService(Request $request):JsonResponse {

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
            $this->facteurRepository->add($facteur, true);
            return new JsonResponse('facteur créé');
        } catch (Exception $e) {
            return new JsonResponse('erreur');
        }
     }

     /**
     * Api qui permet de modifier un facteur.
     * @param Request $request POST : email, nom, prenom
     */

     function EditPasswordFacteurService(Request $request):JsonResponse {

        $timezone = new DateTimeZone('UTC');

        $id = $request->request->get('id');
        $password = $request->request->get('password');

        if (!$this->getUser() || $id == null || $password == null) {
            return new JsonResponse("Authentification échoué");
        }

        $facteur = $this->facteurRepository->find($id)
            ->setPassword($password)
            ->setUpdatedAt(new DateTime(), $timezone);
        try {
            $this->facteurRepository->add($facteur, true);
            return new JsonResponse('facteur créé');
        } catch (Exception $e) {
            return new JsonResponse('erreur');
        }
    }

    function togglefacteurService(Request $request, Facteur $facteur, EntityManagerInterface $em){
        
        $isFacteur = $request->request->get('toggle', 'off') === 'on'; 
        $facteur->setRoles($isFacteur ? ['ROLE_FACTEUR'] : ['ROLE_INACTIF']);
        $em->persist($facteur);
        $em->flush();
        return $this->redirectToRoute('app_facteur');
    }

}

