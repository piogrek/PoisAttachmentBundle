<?php

namespace Pois\AttachmentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pois\AttachmentBundle\Entity\Attachment;
use Pois\AttachmentBundle\Form\AttachmentType;

/**
 * Attachment controller.
 *
 * @Route("/attachments")
 */
class AttachmentController extends Controller
{
    /**
     * Lists all Attachment entities.
     *
     * @Route("/", name="attachments")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->get('g_service.attachment')->getAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Attachment entity.
     *
     * @Route("/{id}/show", name="attachments_show")
     * @Template()
     */
    public function showAction($id)
    {
        $entity = $this->get('g_service.attachment')->get($id);

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Finds and downloads an Attachment entity.
     *
     * @Route("/{id}/download", name="attachments_download")
     */
    public function downloadAction($id)
    {
        $entity = $this->get('g_service.attachment')->get($id);
        header('Content-Disposition: attachment; filename="'.$entity->getName().'"');
        header('Pragma: no-cache');
        readfile($entity->getAbsolutePath());
    }

    /**
     * Displays a form to create a new Attachment entity.
     *
     * @Route("/new", name="attachments_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = $this->get('g_service.attachment')->createNew();
        $form   = $this->createForm(new AttachmentType(), $entity);

        return array(
            'entity'  => $entity,
            'form'    => $form->createView(),
            'id'      => $request->get('id'),
            'id_name' => $request->get('id_name'),
        );
    }

    /**
     * Creates a new Attachment entity.
     *
     * @Route("/create/", name="attachment_create")
     * @Method("POST")
     * @Template("PoisAttachmentBundle:Attachment:new.html.twig")
     */
    public function createAction(Request $request)
    {
        //get additional parameters from form
        $_uploadedFiles = $request->get('uploadedfiles')?$request->get('uploadedfiles'):array();
        $uploadedFiles = array();

        foreach ($_uploadedFiles as $_file) {
            $_fname = explode('/', $_file);
            $uploadedFiles[] = array(
                'name' => array_pop($_fname),
                'path' => '/uploads/'.$_file
                );
        }

        $attachment  = $this->get('g_service.attachment')->createNew();
        $form = $this->createForm(new AttachmentType(), $attachment);
        $form->bind($request);
        $result = array('success' => false);

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $userId = $user->getId();

            $attachment->setFiles($uploadedFiles);
            //save attachment
            $this->get('g_service.attachment')->save($attachment);

            //recognise request and redirect to correct service
            if ( $id = $request->get('client_id') ) {
                 $this->get('g_service.client')->addAttachment($id, $userId, $attachment);
            } elseif ( $id = $request->get('dokument_id') ) {
                $this->get('g_service.magazyn')->addAttachment($id, $userId, $attachment);
            } elseif ( $id = $request->get('message_id') ) {
                $this->get('g_service.magazyn.artykul')->addAttachment($id, $userId, $attachment);
            } elseif ( $id = $request->get('zlecenie_id') ) {
                $this->get('g_service.magazyn.zlecenie')->addAttachment($id, $userId, $attachment);
            } elseif ( $id = $request->get('calculation_id') ) {
                $this->get('g_service.calculation')->addAttachment($id, $userId, $attachment);
            } else {
                throw $this->createNotFoundException('Nie znaleziono obiektu dla zalacznika');
            }


            if ($request->isXmlHttpRequest()) {
                $result['success'] = true;
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                return $this->redirect(urldecode($redirect_path));
            }
        }

        if ($request->isXmlHttpRequest()) {
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            return array(
                'entity' => $attachment,
                'form'   => $form->createView(),
            );
        }

    }

    /**
     * Displays a form to edit an existing Attachment entity.
     *
     * @Route("/{id}/edit", name="attachments_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $entity = $this->get('g_service.attachment')->get($id);

        $editForm = $this->createForm(new AttachmentType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Attachment entity.
     *
     * @Route("/{id}/update", name="attachments_update")
     * @Method("POST")
     * @Template("PoisAttachmentBundle:Attachment:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('g_service.attachment')->get($id);

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new AttachmentType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $this->get('g_service.attachment')->save($entity);

            return $this->redirect($this->generateUrl('attachments_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Attachment entity.
     *
     * @Route("/{id}/delete", name="attachments_delete")
     * @Template()
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        if ($request->isMethod('POST')) {

            $this->get('g_service.attachment')->delete($id);

            //return json
            if ($request->isXmlHttpRequest()) {
                $result = array('success' => true);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                throw $this->createNotFoundException('No direct view for this page');
            }
        } else {
            return array(
                'delete_form' => $form->createView(),
                'entity'      => $this->get('g_service.attachment')->get($id)
            );
        }

    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     *
     * @Route("/upload", name="file_upload")
     * @Template()
     */
    public function uploadAction(Request $request)
    {
        $key = uniqid();

        $response = new Response($this->get('g_service.fileupload')->handleRequest($key));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     *
     * @Route("/list", name="attachment_get_list")
     * @Template()
     */
    public function getListAction(Request $request)
    {
        //recognise request and redirect to correct service
        if ( $id = $request->get('client_id') ) {
            $entity = $this->get('g_service.client')->get($id);
        } elseif ( $id = $request->get('dokument_id') ) {
            $entity = $this->get('g_service.magazyn')->get($id);
        } elseif ( $id = $request->get('message_id') ) {
            $entity = $this->get('g_service.magazyn.artykul')->get($id);
        } elseif ( $id = $request->get('zlecenie_id') ) {
            $entity = $this->get('g_service.magazyn.zlecenie')->get($id);
        } else {
            throw $this->createNotFoundException('Nie znaleziono obiektu dla zalacznika');
        }

        $attachments = $entity->getAttachments();
        return array(
            'attachments' => $attachments
            );
    }
}
