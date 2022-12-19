<?php

namespace App\Services;

use App\Repository\StatutCourrierRepository;

class DataFinder
{
    public function getCourriers(StatutCourrierRepository $statutCourrierRepo, $order, $rechercheCourrier = null, $dateMin = null, $dateMax = null)
    {

        $data = $statutCourrierRepo->findCourriers($order, $rechercheCourrier, $dateMin, $dateMax);

        return $data;
    }
}
