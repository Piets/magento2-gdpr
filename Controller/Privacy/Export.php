<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\Gdpr\Controller\Privacy;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Opengento\Gdpr\Api\ActionInterface;
use Opengento\Gdpr\Controller\AbstractPrivacy;
use Opengento\Gdpr\Model\Action\ArgumentReader;
use Opengento\Gdpr\Model\Action\ContextBuilder;
use Opengento\Gdpr\Model\Config;

class Export extends AbstractPrivacy
{
    /**
     * @var ActionInterface
     */
    private $action;

    /**
     * @var ContextBuilder
     */
    private $actionContextBuilder;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        Config $config,
        ActionInterface $action,
        ContextBuilder $actionContextBuilder,
        Session $customerSession
    ) {
        $this->action = $action;
        $this->actionContextBuilder = $actionContextBuilder;
        $this->customerSession = $customerSession;
        parent::__construct($context, $config);
    }

    protected function isAllowed(): bool
    {
        return parent::isAllowed() && $this->config->isExportEnabled();
    }

    protected function executeAction()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setRefererOrBaseUrl();

        $this->actionContextBuilder->setParameters([
            ArgumentReader::ENTITY_ID => (int) $this->customerSession->getCustomerId(),
            ArgumentReader::ENTITY_TYPE => 'customer'
        ]);

        try {
            $this->action->execute($this->actionContextBuilder->create());
            $this->messageManager->addSuccessMessage(new Phrase('You will be notified when the export is ready.'));
        } catch (AlreadyExistsException $e) {
            $this->messageManager->addNoticeMessage(new Phrase('A document is already available in your account.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, new Phrase('Something went wrong, please try again later!'));
        }

        return $resultRedirect;
    }
}
