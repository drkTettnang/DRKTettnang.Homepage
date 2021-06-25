<?php
namespace DRKTettnang\Homepage\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractTagBasedViewHelper;

class SubViewHelper extends AbstractTagBasedViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('x', 'integer', 'Minuend');
        $this->registerArgument('y', 'integer', 'Subtrahend');
    }

    public function render()
    {
        $x = $this->arguments['x'];
        $y = $this->arguments['y'];

        return $x - $y;
    }
}
