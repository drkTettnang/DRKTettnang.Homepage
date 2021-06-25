<?php
namespace DRKTettnang\Homepage\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractTagBasedViewHelper;
use Neos\Flow\Annotations as Flow;

class OperationTypeViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @Flow\InjectConfiguration(path="operation.types")
     * @var array
     */
    protected $types = array();

    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'Operation type key');
    }

    public function render()
    {
        $key = $this->arguments['key'];

        return (array_key_exists($key, $this->types)) ? $this->types[$key]['label'] : $key;
    }
}
