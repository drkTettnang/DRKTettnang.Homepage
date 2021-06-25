<?php
namespace DRKTettnang\Homepage\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Flow\Annotations as Flow;

class OperationBosViewHelper extends AbstractViewHelper
{


    /**
     * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
     * @see AbstractViewHelper::isOutputEscapingEnabled()
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\InjectConfiguration(path="operation.bos")
     * @var array
     */
    protected $bos = array();

    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'Operation BOS key');
    }

    public function render()
    {
        $key = $this->arguments['key'];

        return (array_key_exists($key, $this->bos)) ? $this->bos[$key]['label'] : $key;
    }
}
