<?php
namespace DRKTettnang\Homepage\DataSource;

use TYPO3\Neos\Service\DataSource\AbstractDataSource;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Flow\Annotations as Flow;

class FormsDataSource extends AbstractDataSource {

        /**
         * @var string
         */
        static protected $identifier = 'drktettnang-homepage-operation-forms';
        
        /**
         * @Flow\InjectConfiguration(path="forms")
         * @var array
         */
        protected $forms = array();

        /**
         * Get data
         *
         * @param NodeInterface $node The node that is currently edited (optional)
         * @param array $arguments Additional arguments (key / value)
         * @return array JSON serializable data
         */
        public function getData(NodeInterface $node = NULL, array $arguments) {
                return $this->forms;
        }
}
