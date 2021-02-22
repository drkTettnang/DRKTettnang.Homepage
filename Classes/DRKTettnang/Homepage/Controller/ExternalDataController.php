<?php

namespace DRKTettnang\Homepage\Controller;

use Neos\Flow\Mvc\Controller\ActionController;

class ExternalDataController extends ActionController
{
    /**
     * A list of IANA media types which are supported by this controller
     *
     * @var array
     */
    protected $supportedMediaTypes = array('application/json');

    /**
     * @var string
     */
    protected $defaultViewObjectName = \Neos\Flow\Mvc\View\JsonView::class;

    public function hiorgAction()
    {
        $data = [
            'limit' => $this->request->getInternalArgument('__limit'),
            'location' => $this->request->getInternalArgument('__location'),
            'category' => $this->request->getInternalArgument('__category'),
            'ov' => $this->request->getInternalArgument('__ov') || 'ttt',
        ];

        if (!preg_match('/^[a-z0-9]{1,10}$/i', $data['ov'])) {
            $this->view->assign('value', ['error' => 'No valid OV provided']);
            return;
        }

        $data['content'] = file_get_contents('https://hiorg-server.de/termine.php?onlytable=1&ov=' . $data['ov']);

        $this->view->assign('value', $data);
    }
}
