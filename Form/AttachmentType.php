<?php

namespace Pois\AttachmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type','entity',array(
                'class' => 'Pois\AttachmentBundle\Entity\AttachmentType',
                'property' => 'name'
                ))
            ->add('info')
            // ->add('url')
            // ->add('created')
            // ->add('createdBy')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pois\AttachmentBundle\Entity\Attachment'
        ));
    }

    public function getName()
    {
        return 'grafix_servicebundle_attachmenttype';
    }
}
