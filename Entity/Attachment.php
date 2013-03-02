<?php

namespace Pois\AttachmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Pois\ServiceBundle\Entity\EntityInterface;

/**
 * Pois\AttachmentBundle\Entity\Attachment
 *
 * @ORM\Table(name="attachment")
 * @ORM\Entity(repositoryClass="Pois\AttachmentBundle\Entity\AttachmentRepository")
 * @ORM\HasLifecycleCallbacks
 *
 */
class Attachment implements EntityInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var AttachmentType $type
     *
     * @ORM\ManyToOne(targetEntity="AttachmentType", cascade={"persist"})
     */
    private $type;

    /**
     * @var string $info
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;

    /**
     * @var array $path
     *
     * @ORM\Column(name="path", type="array", nullable=true)
     */
    private $files;

    /**
     * Users who added message
     *
     * @ORM\ManyToOne(targetEntity="Grafix\UserBundle\Entity\User", cascade={"persist"})
     */
    private $createdBy;


    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Attachment
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Attachment
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate() {
        $this->updated = new \DateTime();
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Attachment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Attachment
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Attachment
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set createdBy
     *
     * @param Grafix\UserBundle\Entity\User $createdBy
     * @return Attachment
     */
    public function setCreatedBy(\Grafix\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return Grafix\UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
    }



    /**
     * Set info
     *
     * @param string $info
     * @return Attachment
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set files
     *
     * @param array $files
     * @return Attachment
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return Array
     */
    public function toArray()
    {
        return array();
    }
}
