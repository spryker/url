<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Url\Business\Redirect\Observer;

use Generated\Shared\Transfer\UrlRedirectTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\Url\Business\Redirect\UrlRedirectActivatorInterface;
use Spryker\Zed\Url\Business\Url\UrlCreatorAfterSaveObserverInterface;
use Spryker\Zed\Url\Business\Url\UrlUpdaterAfterSaveObserverInterface;
use Spryker\Zed\Url\Persistence\UrlQueryContainerInterface;

class UrlRedirectAppendObserver implements UrlCreatorAfterSaveObserverInterface, UrlUpdaterAfterSaveObserverInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Url\Persistence\UrlQueryContainerInterface
     */
    protected $urlQueryContainer;

    /**
     * @var \Spryker\Zed\Url\Business\Redirect\UrlRedirectActivatorInterface
     */
    protected $urlRedirectActivator;

    /**
     * @param \Spryker\Zed\Url\Persistence\UrlQueryContainerInterface $urlQueryContainer
     * @param \Spryker\Zed\Url\Business\Redirect\UrlRedirectActivatorInterface $urlRedirectActivator
     */
    public function __construct(UrlQueryContainerInterface $urlQueryContainer, UrlRedirectActivatorInterface $urlRedirectActivator)
    {
        $this->urlQueryContainer = $urlQueryContainer;
        $this->urlRedirectActivator = $urlRedirectActivator;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     *
     * @return void
     */
    public function handleUrlCreation(UrlTransfer $urlTransfer)
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($urlTransfer): void {
            $this->handleRedirectAppend($urlTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     * @param \Generated\Shared\Transfer\UrlTransfer $originalUrlTransfer
     *
     * @return void
     */
    public function handleUrlUpdate(UrlTransfer $urlTransfer, UrlTransfer $originalUrlTransfer)
    {
        if ($urlTransfer->getUrl() === $originalUrlTransfer->getUrl()) {
            return;
        }

        $this->getTransactionHandler()->handleTransaction(function () use ($urlTransfer): void {
            $this->handleRedirectAppend($urlTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     *
     * @return void
     */
    protected function handleRedirectAppend(UrlTransfer $urlTransfer)
    {
        $urlRedirectEntity = $this->urlQueryContainer
            ->queryUrlRedirectByIdUrl($urlTransfer->getIdUrl())
            ->findOne();

        if (!$urlRedirectEntity) {
            return;
        }

        /** @var \Propel\Runtime\Collection\ObjectCollection $chainRedirectEntities */
        $chainRedirectEntities = $this->findUrlRedirectEntitiesByTargetUrl($urlTransfer->getUrl());

        foreach ($chainRedirectEntities as $chainRedirectEntity) {
            $chainRedirectEntity->setToUrl($urlRedirectEntity->getToUrl());
            $chainRedirectEntity->save();

            $this->activateUrlRedirect($chainRedirectEntity->getIdUrlRedirect());
        }
    }

    /**
     * @param string $targetUrl
     *
     * @return \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Url\Persistence\SpyUrlRedirect>
     */
    protected function findUrlRedirectEntitiesByTargetUrl($targetUrl)
    {
        /** @var \Propel\Runtime\Collection\ObjectCollection $chainRedirectEntities */
        $chainRedirectEntities = $this->urlQueryContainer
            ->queryRedirects()
            ->findByToUrl($targetUrl);

        return $chainRedirectEntities;
    }

    /**
     * @param int $idUrlRedirect
     *
     * @return void
     */
    protected function activateUrlRedirect($idUrlRedirect)
    {
        $urlRedirectTransfer = new UrlRedirectTransfer();
        $urlRedirectTransfer->setIdUrlRedirect($idUrlRedirect);

        $this->urlRedirectActivator->activateUrlRedirect($urlRedirectTransfer);
    }

    /**
     * @param string $sourceUrl
     *
     * @return \Orm\Zed\Url\Persistence\SpyUrlRedirect|null
     */
    protected function findUrlRedirectEntityBySourceUrl($sourceUrl)
    {
        return $this->urlQueryContainer
            ->queryUrlRedirectBySourceUrl($sourceUrl)
            ->findOne();
    }
}
