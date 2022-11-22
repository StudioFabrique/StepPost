<?php

namespace App\Repository;

use App\Entity\StatutCourrier;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatutCourrier>
 *
 * @method StatutCourrier|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatutCourrier|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatutCourrier[]    findAll()
 * @method StatutCourrier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutCourrierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatutCourrier::class);
    }

    public function add(StatutCourrier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StatutCourrier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return StatutCourrier[] Returns an array of StatutCourrier objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StatutCourrier
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findCourriers($order, DateTime $dateMin = null, DateTime $dateMax = null)
    {
        $qb = $dateMin == null || $dateMax == null ?
            $this->createQueryBuilder('s')
            ->select(
                'c.id AS courrier,
                MAX(s.date) AS date,
                MAX(d.statutCode) AS statut,
                d.etat AS etat,
                c.nom,
                c.prenom,
                c.adresse,
                c.complement,
                c.ville,
                c.codePostal,
                c.telephone,
                c.bordereau,
                c.civilite,
                c.type,
                e.id AS expediteur,
                e.nom AS nomExpediteur'
            )
            ->leftJoin('s.courrier', 'c')
            ->leftJoin('s.statut', 'd')
            ->leftJoin('c.expediteur', 'e')
            ->groupBy('c.id')
            ->orderBy('date', $order)
            ->getQuery()

            :

            $this->createQueryBuilder('s')
            ->select(
                'c.id AS courrier,
                MAX(s.date) AS date,
                MAX(d.statutCode) AS statut,
                d.etat AS etat,
                c.nom,
                c.prenom,
                c.adresse,
                c.complement,
                c.ville,
                c.codePostal,
                c.telephone,
                c.bordereau,
                c.civilite,
                c.type,
                e.id AS expediteur,
                e.nom AS nomExpediteur'
            )
            ->leftJoin('s.courrier', 'c')
            ->leftJoin('s.statut', 'd')
            ->leftJoin('c.expediteur', 'e')
            ->groupBy('c.id')
            ->orderBy('date', $order)
            ->having("MAX(s.date) > :dateMin and MAX(s.date) < :dateMax")
            ->setParameter('dateMin', $dateMin)
            ->setParameter('dateMax', $dateMax)
            ->getQuery();

        return $qb->getResult();
    }

    public function findCourriersByNomPrenom($valeur)
    {


        // SELECT
        //     courrier_id,
        //     MAX(DATE) AS DATE,
        //     MAX(status_id) AS etat
        // FROM
        //     statutCourrier
        // GROUP BY
        //     courrier_id
        $qb = $this->createQueryBuilder('s')
            ->select(
                'c.id AS courrier,
                MAX(s.date) AS date,
                MAX(d.statutCode) AS statut,
                c.nom,
                c.prenom,
                c.adresse,
                c.complement,
                c.ville,
                c.codePostal,
                c.telephone,
                c.bordereau,
                c.civilite,
                c.type,
                e.id AS expediteur,
                e.nom AS nomExpediteur'
            )
            ->andWhere("c.nom = :valeur OR c.prenom = :valeur")
            ->leftJoin('s.courrier', 'c')
            ->leftJoin('s.statut', 'd')
            ->leftJoin('c.expediteur', 'e')
            ->groupBy('c.id')
            ->setParameter('valeur', $valeur)
            ->getQuery();
        return $qb->getResult();
    }

    public function findCourriersByBordereau($valeur)
    {


        // SELECT
        //     courrier_id,
        //     MAX(DATE) AS DATE,
        //     MAX(status_id) AS etat
        // FROM
        //     statutCourrier
        // GROUP BY
        //     courrier_id
        $qb = $this->createQueryBuilder('s')
            ->select(
                'c.id AS courrier,
                MAX(s.date) AS date,
                MAX(d.statutCode) AS statut,
                c.nom,
                c.prenom,
                c.adresse,
                c.complement,
                c.ville,
                c.codePostal,
                c.telephone,
                c.bordereau,
                c.civilite,
                c.type,
                e.id AS expediteur,
                e.nom AS nomExpediteur'
            )
            ->andWhere("c.bordereau LIKE :valeur")
            ->leftJoin('s.courrier', 'c')
            ->leftJoin('s.statut', 'd')
            ->leftJoin('c.expediteur', 'e')
            ->groupBy('c.id')
            ->setParameter('valeur', '%' . $valeur . '%')
            ->getQuery();
        return $qb->getResult();
    }

    public function findCourriersByLastStatut($statutId, $facteurId = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select(
                '
                MAX(s.date),
                MAX(d.statutCode),
                f.id
                '
            )
            ->join('s.facteur', 'f')
            ->join('s.statut', 'd')
            ->groupBy('s.courrier')
            ->having($facteurId == null ? ("MAX(d.statutCode) = :statutid") : ("MAX(d.statutCode) = :statutid and f.id = :facteurid"))
            ->setParameter('statutid', $statutId)
            ->getQuery();
        if ($facteurId != null) {
            $qb->setParameter('facteurid', $facteurId);
        }
        return $qb->getResult();
    }

    public function findCourrierImpression(DateTime $date = null)
    {
        $qb = $this->_em
            ->createQueryBuilder('s')
            ->select('
                        s.date,
                        c.id
                    ')
            ->from('App\Entity\StatutCourrier', 's')
            ->where($date == null ? 'st.statutCode = 1' : 's.date >= :dateMin and s.date <= :dateMax and st.statutCode = 1')
            ->groupBy('c.id')
            ->join('s.courrier', 'c')
            ->join('s.statut', 'st')
            ->getQuery();

        if ($date != null) {
            $dateMin = $date;
            $dateMax = date_modify(new DateTime($date->format('Y-m-d')), '+1 month');
            $qb
                ->setParameter('dateMin', $dateMin)
                ->setParameter('dateMax', $dateMax);
        }
        return $qb->getResult();
    }

    public function findCourrierEnvoi(DateTime $date = null)
    {
        $qb = $this->_em
            ->createQueryBuilder('s')
            ->select('
                        s.date,
                        c.id
                    ')
            ->from('App\Entity\StatutCourrier', 's')
            ->where($date == null ? 'st.statutCode = 2' : 's.date >= :dateMin and s.date <= :dateMax and st.statutCode = 2')
            ->groupBy('c.id')
            ->join('s.courrier', 'c')
            ->join('s.statut', 'st')
            ->getQuery();

        if ($date != null) {
            $dateMin = $date;
            $dateMax = date_modify(new DateTime($date->format('Y-m-d')), '+1 month');
            $qb
                ->setParameter('dateMin', $dateMin)
                ->setParameter('dateMax', $dateMax);
        }
        return $qb->getResult();
    }

    public function findCourrierRecu(DateTime $date = null)
    {
        $qb = $this->_em
            ->createQueryBuilder('s')
            ->select('
                        s.date,
                        c.id
                    ')
            ->from('App\Entity\StatutCourrier', 's')
            ->where($date == null ? 'st.statutCode = 5' : 's.date >= :dateMin and s.date <= :dateMax and st.statutCode = 5')
            ->groupBy('c.id')
            ->join('s.courrier', 'c')
            ->join('s.statut', 'st')
            ->getQuery();

        if ($date != null) {
            $dateMin = $date;
            $dateMax = date_modify(new DateTime($date->format('Y-m-d')), '+1 month');
            $qb
                ->setParameter('dateMin', $dateMin)
                ->setParameter('dateMax', $dateMax);
        }
        return $qb->getResult();
    }

    public function countCourriersByFacteur(string $nom, DateTime $dateMin, DateTime $dateMax)
    {
        $qb = $this->_em->createQueryBuilder('s')
            ->select('
                count(distinct s.courrier) as nbCourrier
            ')
            ->from('App\Entity\StatutCourrier', 's')
            ->join('s.facteur', 'f')
            ->where('f.nom = :nom and s.date >= :dateMin and s.date <= :dateMax')
            ->setParameter('dateMin', $dateMin)
            ->setParameter('dateMax', $dateMax)
            ->setParameter('nom', $nom)
            ->getQuery();

        return $qb->getResult();
    }

    public function findTopExpediteurs()
    {


        // SELECT
        //     courrier_id,
        //     MAX(DATE) AS DATE,
        //     MAX(status_id) AS etat
        // FROM
        //     statutCourrier
        // GROUP BY
        //     courrier_id
        $qb = $this->createQueryBuilder('s')
            ->select(
                '
                    e.nom,
                    count(distinct s.courrier) as nombre_courriers
                '
            )
            ->orderBy("count(distinct s.courrier)", "desc")
            ->groupBy("e.nom")
            ->join('s.courrier', 'c')
            ->join('c.expediteur', 'e')
            ->getQuery();
        return $qb->getResult();
    }

    public function findExpediteurToKeep(DateTime $date)
    {
        $qb = $this->_em->createQueryBuilder('sc')
            ->select('
                e.id as expediteur
            ')
            ->from('App\Entity\StatutCourrier', 'sc')
            ->join('sc.courrier', 'c')
            ->join('c.expediteur', 'e')
            ->where('sc.date >= :dateMin AND sc.date <= :dateMax')
            ->groupBy('e.id')
            ->setParameter('dateMin', date_modify(new DateTime($date->format('Y-m-d')), '-6 month'))
            ->setParameter('dateMax', $date)
            ->getQuery();

        return $qb->getResult();
    }
}
