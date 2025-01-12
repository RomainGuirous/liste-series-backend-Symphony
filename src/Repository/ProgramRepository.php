<?php

namespace App\Repository;

use App\Entity\Program;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Program>
 */
class ProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Program::class);
    }

    public function findThreeCategoryDesc(int $id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM program p
            WHERE p.category_id = :id
            ORDER BY p.id DESC
            LIMIT 3
            ';

        $resultSet = $conn->executeQuery($sql, ['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

    //fonction utilisant QueryBuilder
    public function findLikeNameOrActor(string $name)
    {
        $qB = $this->createQueryBuilder('p')
            // obligatoire mettre alias, par convention initiale entité p->Program
            ->innerJoin('p.actors', 'a')
            // 1) join => préciser prop, 2)alias, (les prochains paramètres sont nulles de base) 3)type de condition, 4) condition, 5) trié par
            ->where('p.title LIKE :name')
            // /!\ PAS SQL, Doctrine /!\ title-> $title de entité Program et non title de table program
            ->orWhere('a.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            // setParameter est equivalent à bindValue avec PDO, pour éviter injections SQL
            ->orderBy('p.title', 'ASC')
            // méthodes Doctrine
            ->getQuery();
        // construit la requete SQL en fonction des paramètres précédents

        return $qB->getResult();
        // exécute et retourne résultats
        // comme situé dans ProgramRepository, par défaut retourne tableau d'objets Program
        // d'autres méthodes existent
    }

    //fonction utilisant DQL
    public function findRecentPrograms()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT p, s FROM App\Entity\Program p
            INNER JOIN p.seasons s
            WHERE s.year>2010'
        );

        return $query->execute();
    }
    //    /**
    //     * @return Program[] Returns an array of Program objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Program
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
