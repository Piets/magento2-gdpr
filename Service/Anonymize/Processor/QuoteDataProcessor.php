<?php
/**
 * Copyright © 2018 OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types = 1);

namespace Opengento\Gdpr\Service\Anonymize\Processor;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Math\Random;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Address;
use Opengento\Gdpr\Service\Anonymize\ProcessorInterface;

/**
 * Class QuoteDataProcessor
 */
class QuoteDataProcessor implements ProcessorInterface
{
    /**
     * @var \Opengento\Gdpr\Service\Anonymize\AnonymizeTool
     */
    private $anonymizeTool;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    private $quoteAddressResourceModel;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address $quoteAddressResourceModel
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Address $quoteAddressResourceModel,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteAddressResourceModel = $quoteAddressResourceModel;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function execute(int $customerId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(CartInterface::KEY_CUSTOMER, $customerId);
        $quoteList = $this->quoteRepository->getList($searchCriteria->create());
        $anonymousValue = $this->anonymizeTool->anonymousValue();

        /** @var \Magento\Quote\Model\Quote $quote */
        foreach ($quoteList->getItems() as $quote) {
            $quote->setCustomerFirstname($anonymousValue);
            $quote->setCustomerLastname($anonymousValue);
            $quote->setCustomerMiddlename($anonymousValue);
            $quote->setCustomerEmail($this->anonymizeTool->anonymousEmail());

            $this->quoteRepository->save($quote);

            /** @var \Magento\Quote\Api\Data\AddressInterface $quoteAddress */
            foreach ([$quote->getBillingAddress(), $quote->getShippingAddress()] as $quoteAddress) {
                if ($quoteAddress) {
                    $quoteAddress->setFirstname($anonymousValue);
                    $quoteAddress->setMiddlename($anonymousValue);
                    $quoteAddress->setLastname($anonymousValue);
                    $quoteAddress->setPostcode($this->anonymizeTool->randomValue(5, Random::CHARS_DIGITS));
                    $quoteAddress->setCity($anonymousValue);
                    $quoteAddress->setStreet([$anonymousValue]);
                    $quoteAddress->setEmail($this->anonymizeTool->anonymousEmail());
                    $quoteAddress->setTelephone($this->anonymizeTool->randomValue(10, Random::CHARS_DIGITS));

                    /** @var \Magento\Quote\Model\Quote\Address $quoteAddress */
                    $this->quoteAddressResourceModel->save($quoteAddress);
                }
            }
        }

        return true;
    }
}