<?php


namespace Redegal\Sepomex\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SepomexRepositoryInterface
{


    /**
     * Save sepomex
     * @param \Redegal\Sepomex\Api\Data\SepomexInterface $sepomex
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Redegal\Sepomex\Api\Data\SepomexInterface $sepomex
    );

    /**
     * Retrieve sepomex
     * @param string $sepomexId
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sepomexId);

    /**
     * Retrieve sepomex matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Redegal\Sepomex\Api\Data\SepomexSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete sepomex
     * @param \Redegal\Sepomex\Api\Data\SepomexInterface $sepomex
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Redegal\Sepomex\Api\Data\SepomexInterface $sepomex
    );

    /**
     * Delete sepomex by ID
     * @param string $sepomexId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sepomexId);
}
