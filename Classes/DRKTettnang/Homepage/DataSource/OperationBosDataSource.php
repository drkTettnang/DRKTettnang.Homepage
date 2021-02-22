<?php
namespace DRKTettnang\Homepage\DataSource;

use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;

class OperationBosDataSource extends AbstractDataSource {

        /**
         * @var string
         */
        static protected $identifier = 'drktettnang-homepage-operation-bos';

        /**
         * @Flow\InjectConfiguration(path="operation.bos")
         * @var array
         */
        protected $bos = array();

        /**
         * Get data
         *
         * @param NodeInterface $node The node that is currently edited (optional)
         * @param array $arguments Additional arguments (key / value)
         * @return array JSON serializable data
         */
        public function getData(?NodeInterface $node = null, array $arguments = []) {
                return $this->bos;
        }
}
