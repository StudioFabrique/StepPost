<?php

namespace App\Services;

use DateTime;
use Exception;
use App\Repository\StatutRepository;
use League\Csv\Writer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Ce service contient les méthodes pour l'export en csv.
 */
class ExportCSVService
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

    /**
     * Récupère le fichier en .csv et le retourne afin que le téléchargement soit proposé dans le navigateur de l'utilisateur
     */
    public function GetFile()
    {
        $file = new BinaryFileResponse($this->parameters->get('csv_directory') . 'courriers.csv');
        $file->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, "courriers-" . (new DateTime("now"))->format("H-i") . ".csv");
        return $file;
    }
}
