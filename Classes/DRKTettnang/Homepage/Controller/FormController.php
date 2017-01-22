<?php
namespace DRKTettnang\Homepage\Controller;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;
use DRKTettnang\Homepage\Vendor;

//require_once('Html2Text.php');

class FormController extends \TYPO3\Flow\Mvc\Controller\ActionController {
   
   const EMAILPATTERN = '/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/';
   
   /**
    * @Flow\InjectConfiguration()
    * @var array
    */
   protected $settings = array();
   
   /**
    * @var \TYPO3\Flow\Session\SessionInterface
    * @Flow\Inject
    *
    */
    protected $session;
    
    /**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Session(autoStart = TRUE) 
	 */
	public function step1Action() {

      $form = $this->request->getInternalArgument('__form');
      $data = $this->requestToArray(false);
      $captcha = $this->genCaptcha();

      $data['captcha'] = $captcha['data'];
      $this->session->putData('captcha', $captcha['code']);
      $this->session->putData('processed', false);

      $this->view->assign('data', $data);	
      $this->view->assign('form', $form);

      $this->view->assign('text1', $this->request->getInternalArgument('__text1'));
      $this->view->assign('text2', $this->request->getInternalArgument('__text2'));
      $this->view->assign('text3', $this->request->getInternalArgument('__text3'));
	}

   /**
    * @Flow\Session(autoStart = TRUE) 
    */
   public function step2Action() {
      
      $form = $this->request->getInternalArgument('__form');
      $data = $this->requestToArray();
      $captcha = $this->session->getData('captcha');

      if ($captcha !== $_POST['captcha']) {
         $this->addFlashMessage('Kontrollcode leider falsch.', null, \TYPO3\Flow\Error\Message::SEVERITY_WARNING);

         $this->systemLogger->log('Wrong captcha', LOG_INFO);

         $this->forward('step1');
      }else if ($data === false) {
         $this->forward('step1');
      }
      
      $processed = $this->session->getData('processed');
      
      if ($processed !== true) {
         $this->systemLogger->log('Process new form', LOG_INFO);

         $actions = $this->settings['forms'][$form]['actions'];
         
         foreach($actions as $action=>$config) {
            switch($action) {
               case 'email': 
                  $this->processEmailAction($config, $data);
               break;
            }
         }

         $this->systemLogger->log('Form processed', LOG_INFO);

         $this->session->putData('processed', true);
         $this->addFlashMessage('Formular erfolgreich verarbeitet.', null, \TYPO3\Flow\Error\Message::SEVERITY_OK);
      } else {
         $this->addFlashMessage('Dieses Formular wurde schon verarbeitet und wurde aus diesem Grund nicht erneut gesendet. Vermutlich haben Sie diese Seite neu geladen.', null, \TYPO3\Flow\Error\Message::SEVERITY_NOTICE);
      }

      $this->view->assign('data', $data);	
      $this->view->assign('form', $form);	
	}
   
   private function processEmailAction($config, $data) {
      $form = $this->request->getInternalArgument('__form');
      
      foreach($config['recipients'] as $layout=>$mails) {
         $mailTemplateFile = $form.$layout;
         
         for ($i = 0; $i < count($mails); $i++) {
            $to = $mails[$i];
            
            if (!preg_match(self::EMAILPATTERN, $to)) {
               if (!empty($data[$to]) && preg_match(self::EMAILPATTERN, $data[$to])) {
                  $to = $data[$to];
               } else {
                  continue;
               }
            }
            
            $this->sendMail($config['from'], $to, $data, $mailTemplateFile);
         }
      }
   }
   
   private function sendMail($from, $to, $data, $templateFile) {

      $template = new \TYPO3\Fluid\View\StandaloneView();
      $template->setFormat('html');
      $template->setTemplatePathAndFilename('resource://DRKTettnang.Homepage/Private/Templates/Mail/'.$templateFile.'.html');
      $template->setPartialRootPath('resource://DRKTettnang.Homepage/Private/Partials/');
      $template->assign('data', $data);

      $template->assign('text1', $this->request->getInternalArgument('__text1'));
      $template->assign('text2', $this->request->getInternalArgument('__text2'));
      $template->assign('text3', $this->request->getInternalArgument('__text3'));
      
      $subject = trim($template->renderSection('subject'));
      $html = $template->render();

      $body = trim($template->renderSection('body'));
      $html2text = new \DRKTettnang\Homepage\Vendor\Html2Text($body);
      $plain = $html2text->getText();

      $mail = new \TYPO3\SwiftMailer\Message();
      $mail->setTo($to);
      $mail->setFrom($from);
      $mail->setSubject($subject);
      $mail->addPart($html,'text/html','utf-8');
      $mail->addPart($plain,'text/plain','utf-8');
      $mail->send();

      $this->systemLogger->log('Sent mail to '.$to, LOG_INFO);
   }
   
   private function requestToArray($validate=true) {
      
      $form = $this->request->getInternalArgument('__form');
      $vars = $this->settings['forms'][$form]['inputs'];
      
      $data = array();
      $failed = false;
      
      foreach($vars as $var=>$config) {
         if (!empty($_POST[$var])) {
            
            if (in_array('email', $config) && !preg_match(self::EMAILPATTERN, $_POST[$var])) {
               $failed = true;
               
               if($validate) {
                  $this->addFlashMessage('Bitte geben Sie eine korrekte E-Mail Adresse ein', null, \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
               }
            }
            
            if (in_array('agree', $config) && $_POST[$var] !== 'true') {
               $failed = true;
               
               if($validate) {
                  $this->addFlashMessage('Bitte zeigen Sie sich einverstanden mit der untenstehenden Bedingung', null, \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
               }
            }
            
            $data[$var] = $_POST[$var];
         } elseif (in_array('required', $config)) {
            $failed = true;
            
            if($validate) {
               if (in_array('agree', $config)) {
                  $this->addFlashMessage('Bitte zeigen Sie sich einverstanden mit der untenstehenden Bedingung', null, \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
               } else {
                  $this->addFlashMessage('Bitte füllen Sie alle Pflichtfelder aus', null, \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
               }
            }
         }
      }
      
      return ($failed && $validate) ? false : $data;
   }
   
   private function genCaptcha() {
      $alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u ', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

      $width = 100;
      $height = 34;

      $im = imagecreatetruecolor ( $width , $height );

      $bg = imagecolorallocate($im, 245, 245, 245);
      $fg = array(
         imagecolorallocate($im, 128, 0, 128),
         imagecolorallocate($im, 128, 0, 0),
         imagecolorallocate($im, 0, 128, 0),
         imagecolorallocate($im, 0, 0, 128),
         imagecolorallocate($im, 0, 128, 128)
      );

      imagefill($im, 0, 0, $bg);
      
      $code = '';

      for($i = 1; $i <= 5; $i++){
         $c = $alphabet[rand(0, count($alphabet) - 1)];
         $code .= $c;
         $color = $fg[rand(0, count($fg) - 1)];
         
         imagestring($im, 5, 15*$i, 9, $c, $color);
         
         $x1 = rand(15*$i - 10, 15*$i + 10);
         $x2 = rand(15*$i - 10, 15*$i + 10);
         imageline($im , $x1, 0, $x2, $height, $color);
      }

      //header('Content-type: image/png');
      ob_start();
      imagepng($im);
      $buffer = ob_get_clean();
      if (ob_get_contents()) ob_end_clean();

      imagedestroy($im);
      
      return array(
         'data' => 'data:image/png;base64,'.base64_encode($buffer),
         'code' => $code
      );
   }
}
