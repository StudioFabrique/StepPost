<?php

namespace App\Services;

use App\Repository\StatutRepository;
use DateTime;
use Exception;
use League\Csv\Writer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExportCSV
{
    private $parameters, $statutRepo;
    public function __construct(ParameterBagInterface $parameters, StatutRepository $statutRepo)
    {
        $this->parameters = $parameters;
        $this->statutRepo = $statutRepo;
    }

    public function ExportFile($data)
    {
        $csvCourriers[0] = ['Date', 'Expéditeur', 'Statut', 'Bordereau', 'Type', 'Nom', 'Prénom', 'Adresse', 'Code Postal', 'Ville'];
        $i = 1;
        foreach ($this->statutRepo->findAll() as $statut) {
            $statutArray[$statut->getStatutCode()] = $statut->getEtat();
        }
        foreach ($data as $courrier) {
            $csvCourriers[$i] = [
                $courrier['date'],
                $courrier['raison'],
                $statutArray[$courrier['statut']],
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
            $writer = Writer::createFromPath($this->parameters->get('csv_directory') . 'courriers.csv', 'w');
            $writer->insertAll($csvCourriers);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function GetFile()
    {
        $file = new BinaryFileResponse($this->parameters->get('csv_directory') . 'courriers.csv');
        $file->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, "courriers-" . (new DateTime("now"))->format("H-i") . ".csv");
        return $file;
    }
}
