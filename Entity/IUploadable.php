<?php

namespace Pois\AttachmentBundle\Entity;

/**
 * interface klasy z zalacznikami
 * @author peterg
 */
interface IUploadable
{
    /**
     * ORM\ManyToMany(targetEntity="\Pois\AttachmentBundle\Entity\Attachment", cascade={"remove"})
     */
    // protected $attachments;
    public function getAttachments();
    public function addAttachment(\Pois\AttachmentBundle\Entity\Attachment $AAttachment);
    public function removeAttachment(\Pois\AttachmentBundle\Entity\Attachment $AAttachment);
}
