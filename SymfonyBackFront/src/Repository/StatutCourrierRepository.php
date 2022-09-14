<?php

namespace App\Repository;

use App\Entity\StatutCourrier;
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

    public function findStatusOneAll($order)
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
                MAX(d.id) AS etat,
                MAX(d.etat) AS statut,
                c.nom,
                c.prenom,
                c.adresse,
                c.complement,
                c.ville,
                c.codePostal,
                c.telephone,
                c.bordereau,
                c.civilite,
                c.type'
            )
            ->leftJoin('s.courrier', 'c')
            ->leftJoin('s.statut', 'd')
            ->groupBy('c.id')
            ->orderBy('s.date', $order)
            ->getQuery();
        return $qb->getResult();
    }
}
