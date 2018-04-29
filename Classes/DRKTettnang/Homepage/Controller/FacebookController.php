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
        $requestedPostNumber = 0;

        if (empty($fb['pageid']) || empty($fb['token'])) {
           $this->view->assign('error', 'Empty pageid or token');
           return;
        }

        if (!$this->request->getInternalArgument('__ajax')) {
           $this->view->assign('identifier', $this->request->getInternalArgument('__identifier'));

           return;
        }

        $postUrl = 'https://graph.facebook.com/v2.12/'.$fb['pageid'].'/posts?';
        $postUrl .= http_build_query(array(
           'fields' => 'story,created_time,message,actions,likes{name},link',
           'access_token' => $fb['token'],
           'limit' => $fb['limit'],
           'locale' => 'de_DE'
        ));

        $this->systemLogger->log('Query facebook API.', LOG_DEBUG);
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
           $this->systemLogger->log('Could not parse JSON.', LOG_DEBUG);

            //@TODO report error
           return;
        }

        if (isset($json->error)) {
           $this->systemLogger->log('JSON error: '.$json->error->message, LOG_DEBUG);

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
                    $collection = $this->assetCollectionRepository->findByTitle('Facebook')->getFirst();
                    $hasCollection = ($collection !== null);

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
                        $image = $this->assetRepository->findOneByResourceSha1($sha1);

                        if ($image === null) {
                            $resource = $this->resourceManager->importResource($src);
                            $image = new \Neos\Media\Domain\Model\Image($resource);

                            if ($hasCollection) {
                               $collections = $image->getAssetCollections();
                               $collections->add($collection);
                               $image->setAssetCollections($collections);
                            }
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
