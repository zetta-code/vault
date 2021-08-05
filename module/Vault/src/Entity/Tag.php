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
 * Tag
 *
 * @ORM\Entity
 * @ORM\Table(name="zv_tags")
 */
class Tag extends AbstractDeletableEntity
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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $encrypted;

    /**
     * @var Collection|Credential[]
     *
     * @ORM\ManyToMany(targetEntity="Credential", mappedBy="tags", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $credentials;

    /**
     * @var Collection|Section[]
     *
     * @ORM\ManyToMany(targetEntity="Section", inversedBy="tags", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="zv_tag_sections",
     *      joinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="section_id", referencedColumnName="id")}
     * )
     */
    protected $sections;
    
    /**
     * @var Collection|Account[]
     *
     * @ORM\ManyToMany(targetEntity="Account", mappedBy="tags", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $accounts;

    /**
     * Conta constructor.
     */
    public function __construct()
    {
        $this->encrypted = false;
        $this->credentials = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->accounts = new ArrayCollection();
    }

    /**
     * Get the Tag id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Tag id
     * @param int $id
     * @return Tag
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the Tag name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the Tag name
     * @param string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the Tag encrypted
     * @return bool
     */
    public function isEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * Set the Tag encrypted
     * @param bool $encrypted
     * @return Tag
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;
        return $this;
    }

    /**
     * Get the Tag credentials
     * @return Collection|Credential[]
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Set the Tag credentials
     * @param Collection|Credential[] $credentials
     * @return Tag
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Get the Tag sections
     * @return Collection|Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Set the Tag sections
     * @param Collection|Section[] $sections
     * @return Tag
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * Get the Tag accounts
     * @return Collection|Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set the Tag accounts
     * @param Collection|Account[] $accounts
     * @return Tag
     */
    public function setAccounts($accounts)
    {
        $this->accounts = $accounts;
        return $this;
    }
}
