<?php

namespace App\Services;

use App\Repository\StatutRepository;
use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Ce service contient les méthodes pour l'export en csv.
 */
class ExportXLS
{
    private $parameters, $statutRepo;
    /**
     * Constructeur
     */
    public function __construct(ParameterBagInterface $parameters, StatutRepository $statutRepo)
    {
        $this->parameters = $parameters;
        $this->statutRepo = $statutRepo;
    }

    /**
     * Créer un fichier en .csv à partir des données sous forme de tableau des courriers
     */
    public function ExportFile($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $xlsCourriers = [
            'date' => 'A',
            'raison' => 'B',
            'statut' => 'C',
            'bordereau' => 'D',
            'type' => 'E',
            'nom' => 'F',
            'prenom' => 'G',
            'adresse' => 'H',
            'codePostal' => 'I',
            'ville' => 'J'
        ];

        foreach ($xlsCourriers as $columnName => $coordinate) {
            $worksheet->setCellValue($coordinate . '1', $columnName);
        }

        foreach ($this->statutRepo->findAll() as $statut) {
            $statutArray[$statut->getStatutCode()] = $statut->getEtat();
        }

        $i = 2;
        foreach ($data as $courrier) {
            foreach ($xlsCourriers as $columnName => $coordinate) {
                $worksheet->setCellValue(
                    $coordinate . $i,
                    $columnName === 'statut'
                        ? $statutArray[$courrier['statut']]
                        : ($columnName === 'type'
                            ? ($courrier['type'] == 0 ? 'Lettre avec suivi' : ($courrier['type'] == 1 ? 'Lettre avec accusé de reception' : 'Colis'))
                            : $courrier[$columnName])
                );
            }
            $i++;
        }


        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($this->parameters->get('xls_directory') . 'courriers.xlsx');
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Récupère le fichier en .csv et le retourne afin que le téléchargement soit proposé dans le navigateur de l'utilisateur
     */
    public function GetFile()
    {
        $file = new BinaryFileResponse($this->parameters->get('xls_directory') . 'courriers.xlsx');
        $file->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, "courriers-" . (new DateTime("now"))->format("H-i") . ".xlsx");
        return $file;
    }
}
