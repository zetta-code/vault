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
 * Section
 *
 * @ORM\Entity
 * @ORM\Table(name="zv_sections")
 */
class Section extends AbstractDeletableEntity
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var Collection|Account[]
     *
     * @ORM\ManyToMany(targetEntity="Account", mappedBy="sections", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $accounts;

    /**
     * @var Collection|Credential[]
     *
     * @ORM\OneToMany(targetEntity="Credential", mappedBy="section", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $credentials;

    /**
     * @var Collection|Tag[]
     *
     * @ORM\ManyToMany(targetEntity="Tag", mappedBy="sections", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $tags;

    /**
     * Section constructor.
     */
    public function __construct()
    {
        $this->accounts = new ArrayCollection();
        $this->credentials = new ArrayCollection();
    }

    /**
     * Get the Section id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Section id
     * @param int $id
     * @return Section
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the Section name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the Section name
     * @param string $name
     * @return Section
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the Section description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the Section description
     * @param string $description
     * @return Section
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the Section accounts
     * @return Collection|Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set the Section accounts
     * @param Collection|Account[] $accounts
     * @return Section
     */
    public function setAccounts($accounts)
    {
        $this->accounts = $accounts;
        return $this;
    }

    /**
     * Add accounts to the Section accounts
     * @param Collection|Account[] $accounts
     * @return Section
     */
    public function addAccounts($accounts)
    {
        foreach ($accounts as $account) {
            $this->accounts->add($account);
            $account->addSection($this);
        }

        return $this;
    }

    /**
     * Remove accounts from the Section accounts
     * @param Collection|Account[] $accounts
     * @return Section
     */
    public function removeAccounts($accounts)
    {
        foreach ($accounts as $account) {
            $this->accounts->removeElement($account);
            $account->removeSection($this);
        }
        return $this;
    }

    /**
     * Get the Section credentials
     * @return Collection|Credential[]
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Set the Section credentials
     * @param Collection|Credential[] $credentials
     * @return Section
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Get the Section tags
     * @return Collection|Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the Section tags
     * @param Collection|Tag[] $tags
     * @return Section
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }
}
