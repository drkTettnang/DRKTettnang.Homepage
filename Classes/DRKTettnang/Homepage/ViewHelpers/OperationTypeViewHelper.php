<?php 
namespace DRKTettnang\Homepage\ViewHelpers;

use TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\Flow\Annotations as Flow;

class OperationTypeViewHelper extends AbstractTagBasedViewHelper {

        /**
         * @Flow\InjectConfiguration(path="operation.types")
         * @var array
         */
        protected $types = array();
        
        public function render($key) {
                return (array_key_exists($key, $this->types)) ? $this->types[$key]['label'] : $key;
        }
}
 ?>
