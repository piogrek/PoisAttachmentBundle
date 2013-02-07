<?php

namespace Pois\AttachmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pois\ServiceBundle\Entity\EntityInterface;


/**
 * Pois\AttachmentBundle\Entity\AttachmentType
 *
 * @ORM\Table(name="attachment_type")
 * @ORM\Entity
 */
class AttachmentType implements EntityInterface
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * @return AttachmentType
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
     * returns ready to read text for lists
     */
    public function __toString()
    {
        return $this->name;
    }  


    /**
     * @return Array
     */
    public function toArray()
    {
        return array();
    }  
}