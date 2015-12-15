<?php 
namespace DRKTettnang\Homepage\ViewHelpers;

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Annotations as Flow;

class OperationBosViewHelper extends AbstractViewHelper {
        
        /**
         * @Flow\InjectConfiguration(path="operation.bos")
         * @var array
         */
        protected $bos = array();
        
        public function render($key) {
                return (array_key_exists($key, $this->bos)) ? $this->bos[$key]['label'] : $key;
        }
}
 ?>
