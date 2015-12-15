<?php 
namespace DRKTettnang\Homepage\ViewHelpers;

use TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class SubViewHelper extends AbstractTagBasedViewHelper {
        /*public function initializeArguments() {
                $this->registerArgument('title', 'integer', 'The Title to render');
                $this->registerArgument('flag', 'boolean', 'A ');
        }*/
        public function render($x, $y) {
                return $x - $y;
        }
}
 ?>
