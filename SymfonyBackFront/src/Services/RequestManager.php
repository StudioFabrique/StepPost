<?php

namespace App\Services;

use App\Repository\ExpediteurRepository;
use App\Repository\StatutRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class RequestManager
{
    public function __construct(private StatutRepository $statutRepo, ExpediteurRepository $expediteurRepo)
    {
        $this->statutRepo = $statutRepo;
        $this->expediteurRepo = $expediteurRepo;
    }

    public function GenerateRenderRequest(string $routeName, Request $request, $dataPagination = null, $data = null): array
    {
        switch ($routeName) {
            case "accueil":
                $requests = [
                    'isError' => $request->get('isError') ?? false,
                    'courriers' => $dataPagination,
                    'statuts' => $this->statutRepo->findAll(),
                    'order' => $request->get('order') ?? "DESC" == "DESC" ? "ASC" : "DESC",
                    'isSearching' => is_integer($request->get('recherche')) ? true : (is_string($request->get('recherche')) ? true : false),
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'nbCourriersTotal' => count($data),
                    'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $request->get('currentPage') ?? 1,
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'dateMin' => $request->get('dateMin') ?? null,
                    'dateMax' => $request->get('dateMax') ?? null,
                    'recherche' => $request->get('recherche')
                ];
                break;
            case "admin":
                $requests = [
                    'admins' => $dataPagination,
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false,
                    'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : ($request->get('currentPage')) ?? 1,
                    'nbAdminsTotal' => count($data)
                ];
                break;
        }

        return $requests;
    }

    public function GenerateRenderFormRequest(string $routeName, Request $request, Form $form): array
    {
        switch ($routeName) {
            case "admin_add":
                $requests = [
                    'form' => $form,
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false
                ];
                break;
        }

        return $requests;
    }

    public function __toString()
    {
        return '';
    }
}
