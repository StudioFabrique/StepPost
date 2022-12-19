<?php

namespace App\Services;

use App\Repository\StatutCourrierRepository;
use Knp\Component\Pager\PaginatorInterface;

class DataFinder
{
    public function GetCourriers(StatutCourrierRepository $statutCourrierRepo, $order, $rechercheCourrier = null, $dateMin = null, $dateMax = null)
    {

        $data = $statutCourrierRepo->findCourriers($order, $rechercheCourrier, $dateMin, $dateMax);

        return $data;
    }

    public function Paginate($data, PaginatorInterface $paginator, $page, $currentPage)
    {
        $courriers = $paginator->paginate(
            $data,
            $page < 2 ? $currentPage : $page
        );

        return $courriers;
    }
}
