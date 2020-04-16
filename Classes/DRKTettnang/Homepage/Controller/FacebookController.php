<?php

namespace DRKTettnang\Homepage\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;

class FacebookController extends \Neos\Flow\Mvc\Controller\ActionController
{
   /**
    * @Flow\Inject
    * @var \Neos\Media\Domain\Repository\AssetCollectionRepository
    */
   protected $assetCollectionRepository;

   /**
    * @Flow\Inject
    *
    * @var Neos\Media\Domain\Repository\AssetRepository
    */
   protected $assetRepository;

    /**
     * @Flow\Inject
     *
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

   /**
    * @Flow\Inject
    *
    * @var ResourceManager
    */
   protected $resourceManager;

    public function indexAction()
    {
        $fb = array(
           'pageid' => $this->request->getInternalArgument('__pageid'),
           'token' => $this->request->getInternalArgument('__token'),
           'limit' => $this->request->getInternalArgument('__limit'),
           'ignore' => $this->request->getInternalArgument('__ignore'),
           'links' => $this->request->getInternalArgument('__links')
        );

        if (empty($fb['pageid']) || empty($fb['token'])) {
           $this->view->assign('error', 'Empty pageid or token');
           return;
        }

        if (!$this->request->getInternalArgument('__ajax')) {
           $this->view->assign('identifier', $this->request->getInternalArgument('__identifier'));

           return;
        }

        $publicFacebookUrl = 'https://facebook.com/'.$fb['pageid'];
        $l18n = array(
            'Comment' => 'Kommentieren',
            'Like' => 'Gefällt mir',
            'Share' => 'Teilen',
        );

        $posts = $this->requestPosts($fb['pageid'], $fb['token'], $fb['limit']);

        if ($posts === null) {
            $this->view->assign('error', 'Posts konnten nicht abgerufen werden.');

            return;
        }

        $post = $this->getLastPost($posts, $fb['ignore']);

        if ($post === null) {
            $this->view->assign('error', 'Posts konnten nicht abgerufen werden.');

            return;
        }

        $story = (isset($post->story)) ? $post->story : null;
        $message = (isset($post->message)) ? $post->message : $story;

        $actions = [];

        if (isset($post->actions)) {
            foreach ($post->actions as $action) {
               //@TODO use neos l18n
                $action->label = $l18n[$action->name];
            }

            $actions = array_merge($actions, array_filter($post->actions, function($v) use ($fb){
               return in_array(strtolower($v->name), $fb['links']);
            }));
        }

        if (in_array('more', $fb['links'])) {
           $actions = array_merge($actions, array(array(
              'label' => 'Mehr',
              'name' => 'more',
              'link' => $publicFacebookUrl
           )));
        }

        if (count($actions) > 0) {
           $this->view->assign('actions', $actions);
        }

        if (isset($post->likes)) {
            $concat = '';
            for ($i = 0; $i < count($post->likes->data) && $i < 3; ++$i) {
                $concat .= $post->likes->data[$i]->name.', ';
            }
            $concat = rtrim($concat, ', ');

            $more_concat = '';
            for ($i = 3; $i < count($post->likes->data); ++$i) {
                $more_concat .= $post->likes->data[$i]->name.', ';
            }
            $more_concat = rtrim($more_concat, ', ');

            $this->view->assign('likes_count', count($post->likes->data));
            $this->view->assign('likes_first_concat', $concat);
            $this->view->assign('likes_more_count', count($post->likes->data) - 3);
            $this->view->assign('likes_more_concat', $more_concat);
            $this->view->assign('likes', $post->likes->data);
        }

        if (isset($post->link)) {
            $this->view->assign('link', $post->link);
        }

        // (https?:\/\/([-\w\.]+)+(\/[\w\/_\.%\-,=#?]*[\w\/%\-,=#?])?\/?)
        // $this->view->assign('story', $story);
        $this->view->assign('message', $message);
        $this->view->assign('created_time', $post->created_time);

        $attachments = $this->requestAttachments($post->id, $fb['token']);
        $images = $this->processAttachments($attachments);

        $this->view->assign('images', $images);
    }

    private function requestPosts($pageId, $token, $limit) {
        $postUrl = 'https://graph.facebook.com/v2.12/'.$pageId.'/posts?';
        $postUrl .= http_build_query(array(
           'fields' => 'story,created_time,message,actions,likes{name},link',
           'access_token' => $token,
           'limit' => $limit,
           'locale' => 'de_DE'
        ));

        $response = file_get_contents($postUrl);

        try {
           $json = json_decode($response);
        } catch (Exception $e) {
        }

        if ($json === null || !isset($json)) {
           $this->systemLogger->log('Could not parse JSON.', LOG_INFO);

           return null;
        }

        if (isset($json->error)) {
           $this->systemLogger->log('JSON error: '.$json->error->message, LOG_INFO);

           return null;
        }

        return $json->data;
    }

    private function getLastPost($posts, $filter) {
        for ($i = 0; $i < count($posts); ++$i) {
            $post = $posts[$i];
            $story = (isset($post->story)) ? $post->story : null;
            $message = (isset($post->message)) ? $post->message : $story;

            if (empty($filter) || !preg_match($filter, $message)) {
                return $post;
            }
        }

        $this->systemLogger->log('Found no Facebook post', LOG_INFO);

        return null;
    }

    private function requestAttachments($postId, $token) {
        $attachmentUrl = 'https://graph.facebook.com/v2.4/'.$postId.'/attachments?';
        $attachmentUrl .= http_build_query(array(
            'access_token' => $token,
        ));

        $attachmentResponse = file_get_contents($attachmentUrl);
        $attachmentJson = json_decode($attachmentResponse);

        if (isset($attachmentJson) && $attachmentJson !== null) {
            if (isset($attachmentJson->data)) {
                return (isset($attachmentJson->data[0]->subattachments)) ? $attachmentJson->data[0]->subattachments->data : $attachmentJson->data;
            } elseif (isset($attachmentJson->error)) {
                $this->systemLogger->log('JSON attachment error: '.$attachmentJson->error->message, LOG_INFO);
            }
        }

        return [];
    }

    private function processAttachments($attachments) {
        $collection = $this->assetCollectionRepository->findByTitle('Facebook')->getFirst();
        $hasCollection = ($collection !== null);

        $images = [];

        for ($j = 0; $j < count($attachments); ++$j) {
            if ($attachments[$j]->type !== 'photo') {
                continue;
            }

            if (!isset($attachments[$j]->media->image)) {
                continue;
            }

            $src = $attachments[$j]->media->image->src;
            $sha1 = sha1_file($src);
            $image = $this->assetRepository->findOneByResourceSha1($sha1);

            if ($image === null) {
                // we need to use a temp file, because fb filename is too long
                $tmpFilePath = tempnam(sys_get_temp_dir(), 'NeosFacebookImage');
                file_put_contents($tmpFilePath, file_get_contents($src));

                $resource = $this->resourceManager->importResource($tmpFilePath);
                $image = new \Neos\Media\Domain\Model\Image($resource);

                if ($hasCollection) {
                   $collections = $image->getAssetCollections();
                   $collections->add($collection);
                   $image->setAssetCollections($collections);
                }

                unlink($tmpFilePath);
            }

            // Allow image to be persisted even if this is a "safe" HTTP request:
            $this->persistenceManager->whiteListObject($image);
            $this->persistenceManager->whiteListObject($image->getResource());

            $images[] = $image;
        }

        return $images;
    }
}
