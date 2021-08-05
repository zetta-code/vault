<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Entity;

use Application\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Zetta\DoctrineUtil\Entity\AbstractEntity;

/**
 * Permission
 *
 * @ORM\Entity(repositoryClass="Zetta\Vault\Entity\Repository\PermissionRepository")
 * @ORM\Table(name="zv_permissions")
 */
class Permission extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\User", inversedBy="permissions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="permissions")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $account;

    /**
     * @var Credential
     *
     * @ORM\ManyToOne(targetEntity="Credential", inversedBy="permissions")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $credential;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $allow;

    /**
     * Permission constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get the Permission id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Permission id
     * @param int $id
     * @return Permission
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the Permission user
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the Permission user
     * @param User $user
     * @return Permission
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the Permission account
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set the Permission account
     * @param Account $account
     * @return Permission
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Get the Permission credential
     * @return Credential
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Set the Permission credential
     * @param Credential $credential
     * @return Permission
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }

    /**
     * Get the Permission allow
     * @return bool
     */
    public function isAllow()
    {
        return $this->allow;
    }

    /**
     * Set the Permission allow
     * @param bool $allow
     * @return Permission
     */
    public function setAllow($allow)
    {
        $this->allow = $allow;
        return $this;
    }
}
