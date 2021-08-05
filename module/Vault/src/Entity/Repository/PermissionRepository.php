<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Entity\Repository;

use Application\Entity\Role;
use Application\Entity\User;
use Doctrine\ORM\EntityRepository;
use Zetta\Vault\Entity\Account;
use Zetta\Vault\Entity\Credential;

class PermissionRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return User[]
     */
    public function findUsersByAccount($account)
    {
        return $this->getEntityManager()->getRepository(User::class)->createQueryBuilder('user')
            ->leftJoin('user.permissions', 'permission', 'WITH', 'permission.account = :account AND permission.credential IS NULL')
            ->where('user.deletedAt IS NULL AND (permission.id IS NULL OR permission.allow = FALSE) AND NOT user.role = :role')
            ->setParameter('role', Role::ID_ADMIN)
            ->setParameter('account', $account)
            ->orderBy('user.name', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * @param Credential $credential
     * @return User[]
     */
    public function findUsersByCredential($credential)
    {
        return $this->getEntityManager()->getRepository(User::class)->createQueryBuilder('user')
            ->leftJoin('user.permissions', 'permission', 'WITH', 'permission.credential = :credential')
            ->where('user.deletedAt IS NULL AND (permission.id IS NULL OR permission.allow = FALSE) AND NOT user.role = :role')
            ->setParameter('role', Role::ID_ADMIN)
            ->setParameter('credential', $credential)
            ->orderBy('user.name', 'ASC')
            ->getQuery()->getResult();
    }
}
