<?php 
namespace DRKTettnang\Homepage\ViewHelpers\Format;

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface;

class CssClassViewHelper extends AbstractViewHelper implements CompilableInterface {
   
        
	/**
	 * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
	 * @see AbstractViewHelper::isOutputEscapingEnabled()
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;
   
        /**
         * @var boolean
         */
        protected $escapeChildren = FALSE;

        /**
         * Escapes special characters with their escaped counterparts as needed using PHPs strip_tags() function.
         *
         * @param string $value string to format
         * @return mixed
         * @see http://www.php.net/manual/function.strip-tags.php
         * @api
         */
        public function render($value = NULL) {
           return self::renderStatic(array('value' => $value), $this->buildRenderChildrenClosure(), $this->renderingContext);
        }

        /**
         * Applies strip_tags() on the specified value.
         *
         * @param array $arguments
         * @param \Closure $renderChildrenClosure
         * @param \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
         * @return string
         */
        static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
                $value = $arguments['value'];
                if ($value === NULL) {
                        $value = $renderChildrenClosure();
                }
                if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
                   $value = str_replace('ä', 'ae', $value);
                   $value = str_replace('ö', 'oe', $value);
                   $value = str_replace('ü', 'ue', $value);
                   $value = str_replace('ß', 'ss', $value);
                   return lcfirst(preg_replace('/\W+/', '',
                       ucwords(
                           $value, '/-.'
                       )
                    ));
                }

                return $value;
        }
}

 ?>
