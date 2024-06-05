<?php

namespace App\Repository;

use App\Entity\Facteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facteur>
 *
 * @method Facteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Facteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Facteur[]    findAll()
 * @method Facteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facteur::class);
    }

    public function add(Facteur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Facteur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLike($nom){
        return $this->_em->createQueryBuilder('e')
            ->select('
                e.id,
                e.email,
                e.nom,
                e.roles,
                e.createdAt,
                e.updatedAt
            ')
            ->from('App\Entity\Facteur' ,'e')
            ->where('e.nom LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Facteur[] Returns an array of Facteur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Facteur
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    }
