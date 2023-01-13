<?php

namespace App\Services;

use App\Repository\ExpediteurRepository;
use App\Repository\StatutRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class RequestManager
{
    private $statutRepo, $expediteurRepo, $dataFinder;
    public function __construct(StatutRepository $statutRepo, ExpediteurRepository $expediteurRepo, DataFinder $dataFinder)
    {
        $this->statutRepo = $statutRepo;
        $this->expediteurRepo = $expediteurRepo;
        $this->dataFinder = $dataFinder;
    }

    public function GenerateRenderRequest(string $routeName, Request $request, $dataPagination = null, $data = null): array
    {
        switch ($routeName) {
            case "accueil":
                return [
                    'isError' => $request->get('isError') ?? false,
                    'courriers' => $dataPagination,
                    'statuts' => $this->statutRepo->findAll(),
                    'order' => ($request->get('order') ?? "DESC") == "DESC" ? "ASC" : "DESC",
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
                return [
                    'admins' => $dataPagination,
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false,
                    'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : ($request->get('currentPage')) ?? 1,
                    'nbAdminsTotal' => count($data)
                ];
                break;
            case "expediteur":
                return [
                    'expediteurs' => $dataPagination,
                    'expediteursInactifs' => $this->dataFinder->getExpediteurs($request, true),
                    'isSearch' => $request->get('recherche'),
                    'openDetails' => $request->get('openDetails') ?? false,
                    'currentPage' => $request->query->getInt('page') > 1 ? $request->query->getInt('page') <= 2 : $request->get('currentPage') ?? 1,
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false,
                    'nbExpediteursTotal' => count($data),
                    'checkBoxExact' => $request->get('checkBoxExact') ?? false
                ];
                break;
            case "detailsExpediteur":
                return [
                    'expediteur' => $this->expediteurRepo->find($request->get('expediteurId')),
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false,
                    'recherche' => $request->get('recherche'),
                    'dateMin' => $request->get('dateMin'),
                    'dateMax' => $request->get('dateMax'),
                    'redirectTo' => $request->get("redirectTo")
                ];
                break;
        }
    }

    public function GenerateRenderFormRequest(string $routeName, Request $request, Form $form): array
    {
        switch ($routeName) {
            case "admin":
                return [
                    'form' => $form,
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false,
                ];
                break;
            case "expediteur":
                return [
                    'form' => $form,
                    'expediteursInactifs' => $this->expediteurRepo->findAllInactive(),
                    'errorMessage' => $request->get('errorMessage') ?? null,
                    'isError' => $request->get('isError') ?? false
                ];
        }
    }

    public function __toString()
    {
        return '';
    }
}
