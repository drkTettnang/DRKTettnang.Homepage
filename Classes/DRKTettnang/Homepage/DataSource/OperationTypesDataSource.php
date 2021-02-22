<?php
namespace DRKTettnang\Homepage\DataSource;

use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;

class OperationTypesDataSource extends AbstractDataSource {

        /**
         * @var string
         */
        static protected $identifier = 'drktettnang-homepage-operation-types';

        /**
         * @Flow\InjectConfiguration(path="operation.types")
         * @var array
         */
        protected $types = array();

        /**
         * Get data
         *
         * @param NodeInterface $node The node that is currently edited (optional)
         * @param array $arguments Additional arguments (key / value)
         * @return array JSON serializable data
         */
        public function getData(?NodeInterface $node = null, array $arguments = []) {
                return $this->types;
        }
}
