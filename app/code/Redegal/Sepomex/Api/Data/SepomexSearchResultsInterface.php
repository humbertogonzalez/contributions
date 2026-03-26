<?php


namespace Redegal\Sepomex\Api\Data;

interface SepomexSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get sepomex list.
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface[]
     */
    public function getItems();

    /**
     * Set d_codigo list.
     * @param \Redegal\Sepomex\Api\Data\SepomexInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
