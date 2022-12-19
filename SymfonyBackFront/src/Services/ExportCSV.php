<?php

namespace App\Services;

use Exception;
use League\Csv\Writer;

class ExportCSV
{
    public function ExportFileToPath($data, $exportPath = null): bool
    {
        $path = $exportPath == null ? $this->GetExportPath() : $exportPath;
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
            $writer = Writer::createFromPath($path, 'w');
            $writer->insertAll($csvCourriers);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function GetExportPath(): string
    {
        $path =  'csv/courriers.csv';
        return $path;
    }
}
