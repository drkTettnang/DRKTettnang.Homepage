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
            'ov' => $this->request->getArgument('ov'),
        ];

        if (!preg_match('/^[a-z0-9]{1,10}$/i', $data['ov'])) {
            $this->view->assign('value', ['error' => 'No valid OV provided']);
            $this->response->setStatusCode(500);

            return;
        }

        $data['content'] = file_get_contents('https://hiorg-server.de/termine.php?onlytable=1&ov=' . $data['ov']);

        $this->view->assign('value', $data);
    }
}
