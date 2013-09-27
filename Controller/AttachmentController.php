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
use Aws\Common\Aws;
use Aws\Common\Enum\Region;
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
     * @Route("/download", name="attachments_download")
     */
    public function downloadAction(Request $request)
    {
        $key = $request->get('key');
        $url = urlencode($request->get('url'));
        // var_dump(array($key,$url));die();
        $aws = Aws::factory(array(
            'key'    => $this->container->getParameter('s3_access_key'),
            'secret' => $this->container->getParameter('s3_secret_key'),
            'region' => Region::IRELAND
        ));

        $s3 = $aws->get('s3');

        $bucket = $this->container->getParameter('s3_bucket');
        $filename = implode('/',$key);
        $disp = utf8_encode("?response-content-disposition=attachment; filename=\"$filename\"");

        $request = $s3->get("$bucket/$filename".$disp);

        $_url = $s3->createPresignedUrl($request, '+15 minutes');
        // var_dump($request);die();
        return $this->redirect($_url);
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
        //get additional parameters from the form
        $_uploadedFiles = $request->get('uploadedfiles')?$request->get('uploadedfiles'):array();
        $uploadedFiles = array();
        $id = $request->get('id');
        $id_name = $request->get('id_name');
        //new attachment
        $attachment  = $this->get('g_service.attachment')->createNew();
        $form = $this->createForm(new AttachmentType(), $attachment);
        $form->bind($request);
        $result = array('success' => false);

        if ($form->isValid()) {

            foreach ($_uploadedFiles as $_file) {
                $fileEncoded = json_decode(base64_decode($_file), true);
                if (!$fileEncoded) {
                    continue;
                }
                $uploadedKey = $fileEncoded['key'];
                $uploadedFiles[] = array(
                    'url'     => urldecode($fileEncoded['url']),
                    'key'     => $uploadedKey,
                    'name'    => array_pop($uploadedKey),
                    'id'      => array_pop($uploadedKey),
                    'service' => array_pop($uploadedKey),
                );
            }

            $user = $this->get('security.context')->getToken()->getUser();
            $userId = $user->getId();

            $attachment->setFilesS3($uploadedFiles);
            //save attachment
            $this->get('g_service.attachment')->save($attachment);

            //recognise request and redirect to correct service
            if ($id_name == 'client_id') {
                 $this->get('g_service.client')->addAttachment($id, $userId, $attachment);
            } elseif ($id_name == 'dokument_id') {
                $this->get('g_service.magazyn')->addAttachment($id, $userId, $attachment);
            } elseif ($id_name == 'artykul_id') {
                $this->get('g_service.magazyn.artykul')->addAttachment($id, $userId, $attachment);
            } elseif ($id_name == 'imlorder_id') {
                $this->get('g_service.imlorder')->addAttachment($id, $userId, $attachment);
            } elseif ($id_name == 'calculation_id') {
                $this->get('g_service.calculation')->addAttachment($id, $userId, $attachment);
            } elseif ($id_name == 'user_id') {
                $this->get('g_service.user')->addAttachment($id, $userId, $attachment);
            } else {
                throw $this->createNotFoundException("Nie znaleziono obiektu dla zalacznika ({$id_name})");
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

    /**
     *
     * @Route("/setup", name="file_s3_setup")
     * @Template()
     */
    public function setupS3Action(Request $request)
    {
        $doc = $request->get('doc');
        $key = "uploads/{$doc['idname']}/{$doc['id']}/{$doc['title']}";

        $now = strtotime(date("Y-m-d\TG:i:s"));
        $expire = date('Y-m-d\TG:i:s\Z', strtotime('+ 10 minutes', $now)); // credentials valid 10 minutes from now

        $aws_access_key_id = $this->container->getParameter('s3_access_key');
        $aws_secret_key    = $this->container->getParameter('s3_secret_key');
        $bucket            = $this->container->getParameter('s3_bucket');
        $acl = 'private';
        $url = 'http://'.$bucket.'.s3.amazonaws.com';

        $policy_document='
        {"expiration": "'.$expire.'",
          "conditions": [
            {"bucket": "'.$bucket.'"},
            ["starts-with", "$key", "uploads/"],
            {"acl": "'.$acl.'"},
            {"success_action_status": "201"},
            ["starts-with", "$uploadedfiles[]",""],
            ["starts-with", "$id",""],
            ["starts-with", "$id_name",""],
            ["starts-with", "$grafix_servicebundle_attachmenttype[_token]",""],
            ["starts-with", "$grafix_servicebundle_attachmenttype[info]",""],
            ["starts-with", "$grafix_servicebundle_attachmenttype[type]",""]
          ]
        }';
        // create policy
        $policy = base64_encode($policy_document);

        // create signature
        // hex2b64 and hmacsha1 are functions that we will create
        $signature = base64_encode(
            hash_hmac(
                'sha1',
                $policy,
                $aws_secret_key,
                true
            )
        );

        $return = array(
            'policy'    => $policy,
            'signature' => $signature,
            'key'       => $key,
            );

        $response = new Response(json_encode($return));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
