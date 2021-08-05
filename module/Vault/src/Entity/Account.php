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
 * Account
 *
 * @ORM\Entity
 * @ORM\Table(name="zv_accounts")
 */
class Account extends AbstractDeletableEntity
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
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="accounts")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $organization;

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
     * @var Collection|Section[]
     *
     * @ORM\ManyToMany(targetEntity="Section", inversedBy="accounts", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="zv_account_sections",
     *      joinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="section_id", referencedColumnName="id")}
     * )
     */
    protected $sections;

    /**
     * @var Collection|Credential[]
     *
     * @ORM\OneToMany(targetEntity="Credential", mappedBy="account", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $credentials;

    /**
     * @var Collection|Tag[]
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="accounts", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="zv_tag_accounts",
     *      joinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    protected $tags;

    /**
     * @var Collection|Permission[]
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="account", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    protected $permissions;

    /**
     * Account constructor.
     */
    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->credentials = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * Get the Account id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Account id
     * @param int $id
     * @return Account
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the Account organization
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set the Account organization
     * @param Organization $organization
     * @return Account
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * Get the Account name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the Account name
     * @param string $name
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the Account description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the Account description
     * @param string $description
     * @return Account
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the Account sections
     * @return Collection|Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Set the Account sections
     * @param Collection|Section[] $sections
     * @return Account
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * Add section to the Account sections
     * @param Section $section
     * @return Account
     */
    public function addSection($section)
    {
        $this->sections->add($section);
        return $this;
    }

    /**
     * Remove section from the Account sections
     * @param Section $section
     * @return Account
     */
    public function removeSection($section)
    {
        $this->sections->removeElement($section);
        return $this;
    }

    /**
     * Add sections to the Account sections
     * @param Collection|Section[] $sections
     * @return Account
     */
    public function addSections($sections)
    {
        foreach ($sections as $section) {
            $this->sections->add($section);
        }
        return $this;
    }

    /**
     * Remove sections from the Account sections
     * @param Collection|Section[] $sections
     * @return Account
     */
    public function removeSections($sections)
    {
        foreach ($sections as $section) {
            $this->sections->removeElement($section);
        }
        return $this;
    }

    /**
     * Get the Account credentials
     * @return Collection|Credential[]
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Set the Account credentials
     * @param Collection|Credential[] $credentials
     * @return Account
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Get the Account tags
     * @return Collection|Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the Account tags
     * @param Collection|Tag[] $tags
     * @return Account
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Add tag to the Account tags
     * @param Tag $tag
     * @return Account
     */
    public function addTag($tag)
    {
        $this->tags->add($tag);
        return $this;
    }

    /**
     * Remove tag from the Account tags
     * @param Tag $tag
     * @return Account
     */
    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    /**
     * Add tags to the Account tags
     * @param Collection|Tag[] $tags
     * @return Account
     */
    public function addTags($tags)
    {
        foreach ($tags as $tag) {
            $this->tags->add($tag);
        }
        return $this;
    }

    /**
     * Remove tags from the Account tags
     * @param Collection|Tag[] $tags
     * @return Account
     */
    public function removeTags($tags)
    {
        foreach ($tags as $tag) {
            $this->tags->removeElement($tag);
        }
        return $this;
    }

    /**
     * Get the Account permissions
     * @return Collection|Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set the Account permissions
     * @param Collection|Permission[] $permissions
     * @return Account
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }
}
