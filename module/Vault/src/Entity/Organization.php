<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zetta\DoctrineUtil\Entity\AbstractDeletableEntity;

/**
 * Organization
 *
 * @ORM\Entity
 * @ORM\Table(name="zv_organizations")
 */
class Organization extends AbstractDeletableEntity
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string")
     */
    protected $displayName;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var Collection|Account[]
     *
     * @ORM\OneToMany(targetEntity="Account", mappedBy="organization", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $accounts;

    /**
     * Organization constructor.
     */
    public function __construct()
    {
        $this->accounts = new ArrayCollection();
    }

    /**
     * Get the Organization id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Organization id
     * @param int $id
     * @return Organization
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the Organization name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the Organization name
     * @param string $name
     * @return Organization
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the Organization displayName
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set the Organization displayName
     * @param string $displayName
     * @return Organization
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * Get the Organization description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the Organization description
     * @param string $description
     * @return Organization
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the Organization accounts
     * @return Collection|Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set the Organization accounts
     * @param Collection|Account[] $accounts
     * @return Organization
     */
    public function setAccounts($accounts)
    {
        $this->accounts = $accounts;
        return $this;
    }
}
