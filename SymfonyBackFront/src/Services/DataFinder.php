<?php

namespace App\Services;

use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\StatutCourrierRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class DataFinder
{
    private $statutCourrierRepo, $paginator, $userRepo, $dateMaker, $expediteurRepo, $clientRepo;
    public function __construct(
        StatutCourrierRepository $statutCourrierRepo,
        PaginatorInterface $paginator,
        UserRepository $userRepo,
        DateMaker $dateMaker,
        ExpediteurRepository $expediteurRepo,
        ClientRepository $clientRepo
    ) {
        $this->statutCourrierRepo = $statutCourrierRepo;
        $this->paginator = $paginator;
        $this->userRepo = $userRepo;
        $this->dateMaker = $dateMaker;
        $this->expediteurRepo = $expediteurRepo;
        $this->clientRepo = $clientRepo;
    }

    public function GetCourriers(Request $request, UserInterface $user): array
    {
        $raison = in_array('ROLE_MAIRIE', $user->getRoles()) ? 'mairie de pau' : null;
        $data = $this->statutCourrierRepo->findCourriers(
            $request->get('order') ?? "DESC",
            $request->get('recherche'),
            $this->dateMaker->convertDateDefault($request->get('dateMin')),
            $this->dateMaker->convertDateDefault($request->get('dateMax')),
            $raison
        );
        return $data;
    }

    public function GetAdmins(): array
    {
        $data = $this->userRepo->findAll([], ['id' => 'DESC']);
        return $data;
    }

    public function GetExpediteurs(Request $request, bool $inactives = false): array
    {
        if ($inactives) {
            $index = 0;
            $expediteursInactifs = $this->expediteurRepo->findAllInactive();
            foreach ($this->expediteurRepo->findAllInactive() as $expediteur) {
                $expediteursInactifs[$index]["raisonSociale"] = str_replace("tmp_", "", $expediteur["raisonSociale"]);
                $index++;
            }
            return $expediteursInactifs;
        } else {
            $rechercheExpediteur = $request->get('recherche');
            if ($rechercheExpediteur != null && strval($rechercheExpediteur)) {
                $request->get("checkBoxExact") ? $data = $this->expediteurRepo->findBy(['nom' => $rechercheExpediteur])
                    : $data = $this->expediteurRepo->findLike($rechercheExpediteur);
            } else {
                $data = $this->expediteurRepo->findAll([], ['id' => 'DESC']);
            }
            return $data;
        }
    }

    public function getRaisonSocialActive()
    {
        return $this->clientRepo->findActiveClients();
    }

    public function Paginate($data, Request $request): PaginationInterface
    {
        $currentPage = $request->get('currentPage') ?? 1;

        $courriers = $this->paginator->paginate(
            $data,
            $request->query->getInt('page') < 2 ? $currentPage : $request->query->getInt('page')
        );
        return $courriers;
    }
}
