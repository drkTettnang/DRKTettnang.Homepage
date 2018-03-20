<?php 
namespace DRKTettnang\Homepage\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Flow\Annotations as Flow;

class OperationBosViewHelper extends AbstractViewHelper {
        
        
	/**
	 * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
	 * @see AbstractViewHelper::isOutputEscapingEnabled()
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;
        
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
