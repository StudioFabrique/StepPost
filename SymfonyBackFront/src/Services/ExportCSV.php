<?php

namespace App\Services;

use DateTime;
use Exception;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExportCSV
{
    public function ExportFile($data): bool
    {
        $csvCourriers[0] = ['Date', 'Expéditeur', 'Statut', 'Bordereau', 'Type', 'Nom', 'Prénom', 'Adresse', 'Code Postal', 'Ville'];
        $i = 1;
        foreach ($data as $courrier) {
            $csvCourriers[$i] = [
                $courrier['date'],
                $courrier['raison'],
                $courrier['etat'],
                $courrier['bordereau'],
                $courrier['type'] == 0 ? 'Lettre avec suivi' : ($courrier['type'] == 1 ? 'Lettre avec accusé de reception' : 'Colis'),
                $courrier['nom'],
                $courrier['prenom'],
                $courrier['adresse'],
                $courrier['codePostal'],
                $courrier['ville']
            ];
            $i++;
        }

        try {
            $writer = Writer::createFromPath('courriers.csv', 'w');
            $writer->insertAll($csvCourriers);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function GetFile(): BinaryFileResponse
    {
        $file = new BinaryFileResponse($_ENV["PUBLIC_PATH"] . "courriers.csv");
        $file->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, "courriers-" . (new DateTime("now"))->format("H-m") . ".csv");
        return $file;
    }
}
