<?php

/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Entity\Listener;

use Chamilo\CoreBundle\Entity\AbstractResource;
use Chamilo\CoreBundle\Entity\ResourceFile;
use Chamilo\CoreBundle\Entity\ResourceLink;
use Chamilo\CoreBundle\Entity\ResourceNode;
use Chamilo\CoreBundle\Entity\ResourceToCourseInterface;
use Chamilo\CoreBundle\Entity\ResourceToRootInterface;
use Chamilo\CoreBundle\Entity\ResourceWithUrlInterface;
use Chamilo\CoreBundle\ToolChain;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Class ResourceListener.
 */
class ResourceListener
{
    protected $slugify;
    protected $request;
    protected $accessUrl;

    /**
     * ResourceListener constructor.
     */
    public function __construct(SlugifyInterface $slugify, ToolChain $toolChain, RequestStack $request, Security $security)
    {
        $this->slugify = $slugify;
        $this->security = $security;
        $this->toolChain = $toolChain;
        $this->request = $request;
        $this->accessUrl = null;
    }

    public function getAccessUrl($em)
    {
        if (null === $this->accessUrl) {
            $request = $this->request->getCurrentRequest();
            if (null === $request) {
                throw new \Exception('An Request is needed');
            }
            $sessionRequest = $request->getSession();

            if (null === $sessionRequest) {
                throw new \Exception('An Session request is needed');
            }

            $id = $sessionRequest->get('access_url_id');
            $url = $em->getRepository('ChamiloCoreBundle:AccessUrl')->find($id);

            if ($url) {
                $this->accessUrl = $url;

                return $url;
            }
        }

        if (null === $this->accessUrl) {
            throw new \Exception('An AccessUrl is needed');
        }

        return $this->accessUrl;
    }

    public function prePersist(AbstractResource $resource, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $request = $this->request;

        $url = null;
        if ($resource instanceof ResourceWithUrlInterface) {
            $url = $this->getAccessUrl($em);
            $resource->addUrl($url);
        }

        if ($resource->hasResourceNode()) {
            // This will attach the resource to the main resource node root (Example a course).
            if ($resource instanceof ResourceToRootInterface) {
                $url = $this->getAccessUrl($em);
                $resource->getResourceNode()->setParent($url->getResourceNode());
            }

            // Do not override resource node, it's already added.
            return true;
        }

        // Add resource node
        $creator = $this->security->getUser();

        if (null === $creator) {
            throw new \Exception('User creator not found');
        }

        $resourceNode = new ResourceNode();

        $resourceName = $resource->getResourceName();
        if (empty($resourceName)) {
            throw new \Exception('Resource needs a name');
        }

        $extension = $this->slugify->slugify(pathinfo($resourceName, PATHINFO_EXTENSION));
        if (empty($extension)) {
            $slug = $this->slugify->slugify($resourceName);
        } else {
            $originalExtension = pathinfo($resourceName, PATHINFO_EXTENSION);
            $originalBasename = \basename($resourceName, $originalExtension);
            $slug = sprintf('%s.%s', $this->slugify->slugify($originalBasename), $originalExtension);
        }

        $repo = $em->getRepository('ChamiloCoreBundle:ResourceType');
        $class = str_replace('Entity', 'Repository', get_class($args->getEntity()));
        $class .= 'Repository';
        $name = $this->toolChain->getResourceTypeNameFromRepository($class);
        $resourceType = $repo->findOneBy(['name' => $name]);

        if (null === $resourceType) {
            throw new \Exception('ResourceType not found');
        }

        $resourceNode
            ->setTitle($resourceName)
            ->setSlug($slug)
            ->setCreator($creator)
            ->setResourceType($resourceType)
        ;

        // Add resource directly to the resource node root (Example: for a course resource).
        if ($resource instanceof ResourceToRootInterface) {
            $url = $this->getAccessUrl($em);
            $resourceNode->setParent($url->getResourceNode());
        }

        if ($resource->hasParentResourceNode()) {
            $nodeRepo = $em->getRepository('ChamiloCoreBundle:ResourceNode');
            $parent = $nodeRepo->find($resource->getParentResourceNode());
            $resourceNode->setParent($parent);
        }

        if ($resource->hasUploadFile()) {
            /** @var File $uploadedFile */
            $uploadedFile = $request->getCurrentRequest()->files->get('uploadFile');
            if ($uploadedFile instanceof UploadedFile) {
                $resourceFile = new ResourceFile();
                $resourceFile->setName($uploadedFile->getFilename());
                $resourceFile->setOriginalName($uploadedFile->getFilename());
                $resourceFile->setFile($uploadedFile);
                $em->persist($resourceFile);
                $resourceNode->setResourceFile($resourceFile);
            }
        }

        $links = $resource->getResourceLinkList();
        if ($links) {
            $courseRepo = $em->getRepository('ChamiloCoreBundle:Course');
            $sessionRepo = $em->getRepository('ChamiloCoreBundle:Session');

            foreach ($links as $link) {
                $resourceLink = new ResourceLink();
                if (isset($link['c_id'])) {
                    $course = $courseRepo->find($link['c_id']);
                    $resourceLink->setCourse($course);
                }
                if (isset($link['session_id'])) {
                    $session = $sessionRepo->find($link['session_id']);
                    $resourceLink->setSession($session);
                }
                $resourceLink->setVisibility($link['visibility']);
                $resourceLink->setResourceNode($resourceNode);
                $em->persist($resourceLink);
            }
        }

        if ($resource instanceof ResourceToCourseInterface) {
            //$this->request->getCurrentRequest()->getSession()->get('access_url_id');
            //$resourceNode->setParent($url->getResourceNode());
        }

        $resource->setResourceNode($resourceNode);
        $em->persist($resourceNode);

        return $resourceNode;
    }

    /**
     * When updating a Resource.
     */
    public function preUpdate(AbstractResource $resource, PreUpdateEventArgs $event)
    {
        /*error_log('preUpdate');
        error_log($fieldIdentifier);
        $em = $event->getEntityManager();
        if ($event->hasChangedField($fieldIdentifier)) {
            error_log('changed');
            $oldValue = $event->getOldValue($fieldIdentifier);
            error_log($oldValue);
            $newValue = $event->getNewValue($fieldIdentifier);
            error_log($newValue);
            //$this->updateResourceName($resource, $newValue, $em);
        }*/
    }

    public function postUpdate(AbstractResource $resource, LifecycleEventArgs $args)
    {
        //error_log('postUpdate');
        //$em = $args->getEntityManager();
        //$this->updateResourceName($resource, $resource->getResourceName(), $em);
    }

    public function updateResourceName(AbstractResource $resource, $newValue, $em)
    {
        // Updates resource node name with the resource name.
        /*$resourceNode = $resource->getResourceNode();

        $newName = $resource->getResourceName();

        $name = $resourceNode->getSlug();

        if ($resourceNode->hasResourceFile()) {
            $originalExtension = pathinfo($name, PATHINFO_EXTENSION);
            $originalBasename = \basename($name, $originalExtension);
            $modified = sprintf('%s.%s', $this->slugify->slugify($originalBasename), $originalExtension);
        } else {
            $modified = $this->slugify->slugify($name);
        }

        error_log($name);
        error_log($modified);

        $resourceNode->setSlug($modified);

        if ($resourceNode->hasResourceFile()) {
            $resourceNode->getResourceFile()->setOriginalName($name);
        }
        $em->persist($resourceNode);
        $em->flush();*/
    }
}
