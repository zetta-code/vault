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
 * Credential
 *
 * @ORM\Entity
 * @ORM\Table(name="zv_credentials")
 */
class Credential extends AbstractDeletableEntity
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
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="credentials")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $account;

    /**
     * @var Section
     *
     * @ORM\ManyToOne(targetEntity="Section", inversedBy="credentials")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $section;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var Collection|Tag[]
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="credentials", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="zv_tag_credentials",
     *      joinColumns={@ORM\JoinColumn(name="credential_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    protected $tags;

    /**
     * @var Collection|Permission[]
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="credential", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $permissions;

    /**
     * Credential constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * Get the Credential id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Credential id
     * @param int $id
     * @return Credential
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the Credential account
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set the Credential account
     * @param Account $account
     * @return Credential
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Get the Credential section
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set the Credential section
     * @param Section $section
     * @return Credential
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get the Credential name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the Credential name
     * @param string $name
     * @return Credential
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the Credential username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the Credential username
     * @param string $username
     * @return Credential
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get the Credential value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the Credential value
     * @param string $value
     * @return Credential
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the Credential description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the Credential description
     * @param string $description
     * @return Credential
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the Credential tags
     * @return Collection|Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the Credential tags
     * @param Collection|Tag[] $tags
     * @return Credential
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Add tag to the Credential tags
     * @param Tag $tag
     * @return Credential
     */
    public function addTag($tag)
    {
        $this->tags->add($tag);
        return $this;
    }

    /**
     * Remove tag from the Credential tags
     * @param Tag $tag
     * @return Credential
     */
    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    /**
     * Add tags to the Credential tags
     * @param Collection|Tag[] $tags
     * @return Credential
     */
    public function addTags($tags)
    {
        foreach ($tags as $tag) {
            $this->tags->add($tag);
        }
        return $this;
    }

    /**
     * Remove tags from the Credential tags
     * @param Collection|Tag[] $tags
     * @return Credential
     */
    public function removeTags($tags)
    {
        foreach ($tags as $tag) {
            $this->tags->removeElement($tag);
        }
        return $this;
    }

    /**
     * Get the Credential permissions
     * @return Collection|Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set the Credential permissions
     * @param Collection|Permission[] $permissions
     * @return Credential
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }
}
