<?php

namespace DRKTettnang\Homepage\Controller;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;

class FacebookController extends \TYPO3\Flow\Mvc\Controller\ActionController
{
    /**
    * @Flow\Inject
    *
    * @var TYPO3\Media\Domain\Repository\AssetRepository
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
    * @var \TYPO3\Flow\Resource\ResourceManager
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
        $requestedPostNumber = 0;
        
        if (empty($fb['pageid']) || empty($fb['token'])) {
           $this->view->assign('error', 'Empty pageid or token');
           return;
        }

        $postUrl = 'https://graph.facebook.com/v2.4/'.$fb['pageid'].'/posts?';
        $postUrl .= http_build_query(array(
           'fields' => 'story,created_time,message,actions,likes{name},link',
           'access_token' => $fb['token'],
           'limit' => $fb['limit'],
           'locale' => 'de_DE'
        ));

        $response = file_get_contents($postUrl);

        try {
           $json = json_decode($response);
        } catch (Exception $e) {
        }

        $l18n = array(
            'Comment' => 'Kommentieren',
            'Like' => 'Gefällt mir',
            'Share' => 'Teilen',
        );

        if ($json === null || !isset($json)) {
            //@TODO report error
           return;
        }

        if (isset($json->error)) {
            //@TODO report error $json->error->message;
           return;
        }

        $postNumber = 0;

        for ($i = 0; $i < count($json->data); ++$i) {
            $post = $json->data[$i];
            $story = (isset($post->story)) ? $post->story : null;
            $message = (isset($post->message)) ? $post->message : $story;

            if (!empty($fb['ignore']) && preg_match($fb['ignore'], $message)) {
                continue;
            }

            if ($postNumber < $requestedPostNumber) {
                ++$postNumber;
                continue;
            }
            
            $actions = array();

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
                  'link' => 'https://facebook.com/'.$fb['pageid']
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

            $attachmentUrl = 'https://graph.facebook.com/v2.4/'.$post->id.'/attachments?';
            $attachmentUrl .= http_build_query(array(
             'access_token' => $fb['token'],
           ));

            $attachmentResponse = file_get_contents($attachmentUrl);
            $attachmentJson = json_decode($attachmentResponse);

            if (isset($attachmentJson) && $attachmentJson !== null) {
                if (isset($attachmentJson->data)) {
                    $images = array();
                    $attachments = (isset($attachmentJson->data[0]->subattachments)) ? $attachmentJson->data[0]->subattachments->data : $attachmentJson->data;

                    for ($j = 0; $j < count($attachments); ++$j) {
                        if ($attachments[$j]->type !== 'photo') {
                            continue;
                        }

                        if (!isset($attachments[$j]->media->image)) {
                            continue;
                        }

                        $src = $attachments[$j]->media->image->src;

                        $sha1 = sha1_file($src);
                   //$resource = $this->resourceManager->getResourceBySha1($sha1);

                   $image = $this->assetRepository->findOneByResourceSha1($sha1);

                        if ($image === null) {
                            $resource = $this->resourceManager->importResource($src);
                            $image = new \TYPO3\Media\Domain\Model\Image($resource);
                        } else {
                            //$this->systemLogger->log('Image found', LOG_DEBUG);
                        }

                   // Allow image to be persisted even if this is a "safe" HTTP request:
                   $this->persistenceManager->whiteListObject($image);
                        $this->persistenceManager->whiteListObject($image->getResource());

                        $images[] = $image;
                    }

                    $this->view->assign('images', $images);
                } elseif (isset($attachmentJson->error)) {
                    //@TODO report error $attachmentJson->error->message;
                }
            }

            if ($postNumber === $requestedPostNumber) {
                break;
            }
        }
    }
}
