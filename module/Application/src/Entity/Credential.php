<?php
/**
 * @link      http://github.com/zetta-code/zend-skeleton-application for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zetta\DoctrineUtil\Entity\AbstractDeletableEntity;
use Zetta\ZendAuthentication\Entity\CredentialInterface;
use Zetta\ZendAuthentication\Entity\UserInterface;

/**
 * Credential
 *
 * @ORM\Entity
 * @ORM\Table(name="credentials")
 */
class Credential extends AbstractDeletableEntity implements CredentialInterface
{
    const TYPE_PASSWORD = 1;
    const TYPE_FACEBOOK = 2;
    const TYPE_API_TOKEN = 3;

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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="credentials")
     * @ORM\JoinColumn(nullable=false)
     **/
    protected $user;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Get the Credential user
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the Credential user
     * @param User $user
     * @return Credential
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the Credential type
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the Credential type
     * @param int $type
     * @return Credential
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * Hash the Credential value
     * @return Credential
     */
    public function hashValue()
    {
        $this->value = password_hash($this->value, PASSWORD_ARGON2I, [
            'memory_cost' => 1 << 17, // 128 Mb
            'time_cost' => 4,
            'threads' => 3,
        ]);
        return $this;
    }

    /**
     * Verify the Credential value
     * @param string $value
     * @return bool
     */
    public function verifyValue($value)
    {
        return password_verify($value, $this->getValue());
    }

    /**
     * Get the Credential active
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set the Credential active
     * @param bool $active
     * @return Credential
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Check a credential
     * @param UserInterface $user
     * @param CredentialInterface $credential
     * @param string $value
     * @return bool
     */
    public static function check(UserInterface $user, CredentialInterface $credential, $value)
    {
        if ($user->getId() === $credential->getUser()->getId() && $user->isSignAllowed()) {
            return $credential->verifyValue($value);
        }

        return false;
    }
}
