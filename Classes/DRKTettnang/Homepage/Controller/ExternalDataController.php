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

    public function bloodDonationAction()
    {
        $location = $this->request->getArgument('location');

        if (!preg_match('/^([0-9]{5}|[a-z]+)(\|([0-9]{5}|[a-z]+))*$/i', $location)) {
            $this->view->assign('value', ['error' => 'No valid location provided']);
            $this->response->setStatusCode(500);

            return;
        }

        $content = file_get_contents('https://www.drk-blutspende.de/blutspendetermine/ergebnisse.php?rss=1&plz_ort_eingabe=' . $location);

        $this->view->assign('value', ['content' => $content]);
    }

    public function eventAction()
    {
        $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $url = 'https://www.kurs-anmeldung.de/go.dll?'.$query;

        $content = file_get_contents($url);

        if (!$content) {
            $this->view->assign('value', ['error' => 'Could not get '.$url]);
            $this->response->setStatusCode(500);

            return;
        }

        $content = preg_replace_callback(
            '#<url_anmeldung>(.+)</url_anmeldung>#',
            function ($matches) {
                $url = $matches[1];
                $url = htmlspecialchars_decode($url); //prevents double encoding
                $url = htmlspecialchars($url);

                return '<url_anmeldung>'.$url.'</url_anmeldung>';
            },
            $content
        );

        $this->view->assign('value', ['content' => base64_encode($content)]);
    }
}
