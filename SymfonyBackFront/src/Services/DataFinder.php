<?php

namespace App\Services;

use App\Repository\StatutCourrierRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class DataFinder
{
    public function __construct(private StatutCourrierRepository $statutCourrierRepo, PaginatorInterface $paginator, UserRepository $userRepo)
    {
        $this->statutCourrierRepo = $statutCourrierRepo;
        $this->paginator = $paginator;
        $this->userRepo = $userRepo;
    }

    public function GetCourriers($order, $rechercheCourrier = null, $dateMin = null, $dateMax = null): array
    {
        $data = $this->statutCourrierRepo->findCourriers($order, $rechercheCourrier, $dateMin, $dateMax);

        return $data;
    }

    public function GetAdmins(): array
    {
        $data = $this->userRepo->findAll([], ['id' => 'DESC']);
        return $data;
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

    public function __toString()
    {
        return '';
    }
}
