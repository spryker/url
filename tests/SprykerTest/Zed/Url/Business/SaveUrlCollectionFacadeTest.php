<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Url\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Url\Persistence\SpyUrlQuery;
use Orm\Zed\Url\Persistence\SpyUrlRedirectQuery;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Url
 * @group Business
 * @group Facade
 * @group SaveUrlCollectionFacadeTest
 * Add your own group annotations below this line
 */
class SaveUrlCollectionFacadeTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\Url\UrlBusinessTester
     */
    protected $tester;

    public function testSaveUrlCollectionCreatesNewUrls(): void
    {
        // Arrange
        $idLocale = $this->tester->haveLocale()->getIdLocale();
        $urlTransfer = (new UrlTransfer())
            ->setUrl('/save-url-collection-new-' . uniqid())
            ->setFkLocale($idLocale);

        // Act
        $savedUrlTransfers = $this->tester->getFacade()->saveUrlCollection([$urlTransfer]);

        // Assert
        $this->assertCount(1, $savedUrlTransfers);
        $this->assertSame(1, SpyUrlQuery::create()->filterByUrl($urlTransfer->getUrl())->count());
    }

    public function testSaveUrlCollectionCreatesUrlWhenUrlIsSourceOfExistingRedirect(): void
    {
        // Arrange
        $idLocale = $this->tester->haveLocale()->getIdLocale();
        $sourceUrl = '/save-url-collection-redirect-source-' . uniqid();
        $urlRedirectTransfer = $this->tester->haveUrlRedirect(
            [],
            [UrlTransfer::URL => $sourceUrl, UrlTransfer::FK_LOCALE => $idLocale],
        );
        $urlTransfer = (new UrlTransfer())
            ->setUrl($sourceUrl)
            ->setFkLocale($idLocale);

        // Act
        $this->tester->getFacade()->saveUrlCollection([$urlTransfer]);

        // Assert
        $urlEntities = SpyUrlQuery::create()->filterByUrl($sourceUrl)->find();
        $this->assertCount(1, $urlEntities);
        $this->assertNull($urlEntities->getFirst()->getFkResourceRedirect());
        $this->assertSame(
            0,
            SpyUrlRedirectQuery::create()->filterByIdUrlRedirect($urlRedirectTransfer->getIdUrlRedirect())->count(),
        );
    }

    public function testSaveUrlCollectionCreatesOnlyMissingUrlsWhenOneUrlIsSourceOfExistingRedirect(): void
    {
        // Arrange
        $idLocale = $this->tester->haveLocale()->getIdLocale();
        $sourceUrl = '/save-url-collection-mixed-source-' . uniqid();
        $this->tester->haveUrlRedirect(
            [],
            [UrlTransfer::URL => $sourceUrl, UrlTransfer::FK_LOCALE => $idLocale],
        );
        $newUrl = '/save-url-collection-mixed-new-' . uniqid();
        $urlTransfers = [
            (new UrlTransfer())->setUrl($newUrl)->setFkLocale($idLocale),
            (new UrlTransfer())->setUrl($sourceUrl)->setFkLocale($idLocale),
        ];

        // Act
        $savedUrlTransfers = $this->tester->getFacade()->saveUrlCollection($urlTransfers);

        // Assert
        $this->assertCount(2, $savedUrlTransfers);
        $this->assertSame(1, SpyUrlQuery::create()->filterByUrl($newUrl)->count());
        $this->assertSame(1, SpyUrlQuery::create()->filterByUrl($sourceUrl)->count());
    }

    public function testSaveUrlCollectionUpdatesExistingUrlToUrlThatIsSourceOfExistingRedirect(): void
    {
        // Arrange
        $idLocale = $this->tester->haveLocale()->getIdLocale();
        $sourceUrl = '/save-url-collection-update-source-' . uniqid();
        $urlRedirectTransfer = $this->tester->haveUrlRedirect(
            [],
            [UrlTransfer::URL => $sourceUrl, UrlTransfer::FK_LOCALE => $idLocale],
        );
        $existingUrlTransfer = $this->tester->haveUrl([
            UrlTransfer::URL => '/save-url-collection-update-current-' . uniqid(),
            UrlTransfer::FK_LOCALE => $idLocale,
        ]);
        $updatedUrlTransfer = (new UrlTransfer())
            ->setIdUrl($existingUrlTransfer->getIdUrl())
            ->setFkLocale($idLocale)
            ->setOriginalUrl($existingUrlTransfer->getUrl())
            ->setUrl($sourceUrl);

        // Act
        $this->tester->getFacade()->saveUrlCollection([$updatedUrlTransfer]);

        // Assert
        $urlEntity = SpyUrlQuery::create()->filterByIdUrl($existingUrlTransfer->getIdUrl())->findOne();
        $this->assertSame($sourceUrl, $urlEntity->getUrl());
        $this->assertSame(
            0,
            SpyUrlRedirectQuery::create()->filterByIdUrlRedirect($urlRedirectTransfer->getIdUrlRedirect())->count(),
        );
    }

    public function testSaveUrlCollectionAppliesNewValuesToReusedUrl(): void
    {
        // Arrange
        $redirectLocaleTransfer = $this->tester->haveLocale();
        $newLocaleTransfer = $this->tester->haveLocale();
        $sourceUrl = '/save-url-collection-reuse-values-' . uniqid();
        $this->tester->haveUrlRedirect(
            [],
            [UrlTransfer::URL => $sourceUrl, UrlTransfer::FK_LOCALE => $redirectLocaleTransfer->getIdLocale()],
        );
        $urlTransfer = (new UrlTransfer())
            ->setUrl($sourceUrl)
            ->setFkLocale($newLocaleTransfer->getIdLocale());

        // Act
        $this->tester->getFacade()->saveUrlCollection([$urlTransfer]);

        // Assert
        $urlEntity = SpyUrlQuery::create()->filterByUrl($sourceUrl)->findOne();
        $this->assertSame($newLocaleTransfer->getIdLocale(), $urlEntity->getFkLocale());
    }

    public function testSaveUrlCollectionCreatesAllUrlsThatAreSourcesOfExistingRedirects(): void
    {
        // Arrange
        $idLocale = $this->tester->haveLocale()->getIdLocale();
        $firstSourceUrl = '/save-url-collection-first-source-' . uniqid();
        $secondSourceUrl = '/save-url-collection-second-source-' . uniqid();
        $this->tester->haveUrlRedirect([], [UrlTransfer::URL => $firstSourceUrl, UrlTransfer::FK_LOCALE => $idLocale]);
        $this->tester->haveUrlRedirect([], [UrlTransfer::URL => $secondSourceUrl, UrlTransfer::FK_LOCALE => $idLocale]);
        $urlTransfers = [
            (new UrlTransfer())->setUrl($firstSourceUrl)->setFkLocale($idLocale),
            (new UrlTransfer())->setUrl($secondSourceUrl)->setFkLocale($idLocale),
        ];

        // Act
        $this->tester->getFacade()->saveUrlCollection($urlTransfers);

        // Assert
        $this->assertSame(1, SpyUrlQuery::create()->filterByUrl($firstSourceUrl)->count());
        $this->assertSame(1, SpyUrlQuery::create()->filterByUrl($secondSourceUrl)->count());
    }

    public function testSaveUrlCollectionReturnsEmptyArrayForEmptyCollection(): void
    {
        // Act
        $savedUrlTransfers = $this->tester->getFacade()->saveUrlCollection([]);

        // Assert
        $this->assertSame([], $savedUrlTransfers);
    }
}
