<?php

namespace App\Repository;

use App\Entity\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Transfer find($id, $lockMode = null, $lockVersion = null)
 * @method null|Transfer findOneBy(array $criteria, array $orderBy = null)
 * @method Transfer[]    findAll()
 * @method Transfer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferRepository extends ServiceEntityRepository implements TransferRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfer::class);
    }

    public function save(Transfer $transfer)
    {
        $this->_em->persist($transfer);
        $this->_em->flush();
    }
}
