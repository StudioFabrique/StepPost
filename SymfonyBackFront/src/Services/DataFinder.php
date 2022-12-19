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

    public function PaginateAndClean($data, PaginatorInterface $paginator, $page, $currentPage)
    {
        $courriers = $paginator->paginate(
            $data,
            $page < 2 ? $currentPage : $page
        );

        foreach ($courriers as $courrier) {
            $courrier["raison"] = str_replace("tmp_", "", $courrier["raison"]);
        }

        return $courriers;
    }
}
