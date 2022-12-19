<?php

namespace App\Services;

use DateTime;
use League\Csv\CannotInsertRecord;
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
        } catch (CannotInsertRecord $e) {
            $e->getRecord();
            return false;
        }
    }

    public function GetExportPath(): string
    {
        $path = $_ENV['CSV_EXPORT_PATH'] . '/courriers-' . date_format(new DateTime('now'), 'h-i') . '.csv';
        return $path;
    }
}
