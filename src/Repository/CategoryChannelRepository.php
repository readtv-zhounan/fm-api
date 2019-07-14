<?php

namespace App\Repository;

use App\Entity\CategoryChannel;
use App\Entity\Channel;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ChannelCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChannelCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChannelCategory[]    findAll()
 * @method ChannelCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryChannelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CategoryChannel::class);
    }

    public function orderFindByCategory(Category $category)
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.category = :category')
            ->orderBy('cc.sequence')
            ->setParameters(compact('category'))->getQuery()
            ->getResult()
        ;
    }
}
