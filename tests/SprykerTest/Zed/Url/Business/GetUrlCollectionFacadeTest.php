<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Url\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Generated\Shared\Transfer\UrlConditionsTransfer;
use Generated\Shared\Transfer\UrlCriteriaTransfer;
use Generated\Shared\Transfer\UrlTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Url
 * @group Business
 * @group Facade
 * @group GetUrlCollectionFacadeTest
 * Add your own group annotations below this line
 */
class GetUrlCollectionFacadeTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\Url\UrlBusinessTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetUrlCollectionFiltersUrlsByLocale(): void
    {
        // Arrange
        $localeTransfer = $this->tester->haveLocale();
        $expectedUrl = '/url-collection-locale-filter-test-' . uniqid();
        $this->tester->haveUrl([
            UrlTransfer::URL => $expectedUrl,
            UrlTransfer::FK_LOCALE => $localeTransfer->getIdLocale(),
        ]);

        $urlCriteriaTransfer = (new UrlCriteriaTransfer())->setUrlConditions(
            (new UrlConditionsTransfer())
                ->addIdLocale($localeTransfer->getIdLocale()),
        );

        // Act
        $urlCollectionTransfer = $this->tester->getFacade()
            ->getUrlCollection($urlCriteriaTransfer);

        // Assert
        $foundUrls = [];
        foreach ($urlCollectionTransfer->getUrls() as $foundUrlTransfer) {
            // The locale filter must hold for every returned URL (a broken
            // filter that ignored the locale condition would fail here).
            $this->assertSame($localeTransfer->getIdLocale(), $foundUrlTransfer->getFkLocale());
            $foundUrls[] = $foundUrlTransfer->getUrl();
        }

        // The specific URL we created for this locale is present, without
        // assuming the locale has no other (seeded) URLs in the collection.
        $this->assertContains($expectedUrl, $foundUrls);
    }

    public function testGetUrlCollectionPaginatesUrls(): void
    {
        // Arrange
        $this->tester->haveUrl();
        $this->tester->haveUrl();
        $this->tester->haveUrl();

        $urlCriteriaTransfer = (new UrlCriteriaTransfer())
            ->setPagination(
                (new PaginationTransfer())
                    ->setLimit(2)
                    ->setOffset(0),
            );

        // Act
        $urlCollectionTransfer = $this->tester->getFacade()
            ->getUrlCollection($urlCriteriaTransfer);

        // Assert
        $this->assertCount(2, $urlCollectionTransfer->getUrls());
    }

    public function testGetUrlCollectionSortsUrls(): void
    {
        // Arrange
        // The URLs are given a run-unique prefix and the query is scoped to exactly those URLs, so the
        // count and sort order are asserted only against the URLs this test creates - never against
        // other URLs that may already exist for the (reused) locale in a seeded database.
        $localeTransfer = $this->tester->haveLocale();
        $urlPrefix = '/url-collection-sort-test-' . uniqid() . '-';
        $this->tester->haveUrl([UrlTransfer::URL => $urlPrefix . 'abc', UrlTransfer::FK_LOCALE => $localeTransfer->getIdLocale()]);
        $this->tester->haveUrl([UrlTransfer::URL => $urlPrefix . 'bac', UrlTransfer::FK_LOCALE => $localeTransfer->getIdLocale()]);
        $this->tester->haveUrl([UrlTransfer::URL => $urlPrefix . 'cab', UrlTransfer::FK_LOCALE => $localeTransfer->getIdLocale()]);

        $urlCriteriaTransfer = (new UrlCriteriaTransfer())->setUrlConditions(
            (new UrlConditionsTransfer())
                ->addUrl($urlPrefix . 'abc')
                ->addUrl($urlPrefix . 'bac')
                ->addUrl($urlPrefix . 'cab'),
        )
            ->addSort(
                (new SortTransfer())->setField(UrlTransfer::URL)
                    ->setIsAscending(false),
            );

        // Act
        $urlCollectionTransfer = $this->tester->getFacade()
            ->getUrlCollection($urlCriteriaTransfer);

        // Assert
        $this->assertCount(3, $urlCollectionTransfer->getUrls());
        $this->assertSame($urlPrefix . 'cab', $urlCollectionTransfer->getUrls()->offsetGet(0)->getUrl());
        $this->assertSame($urlPrefix . 'bac', $urlCollectionTransfer->getUrls()->offsetGet(1)->getUrl());
        $this->assertSame($urlPrefix . 'abc', $urlCollectionTransfer->getUrls()->offsetGet(2)->getUrl());
    }
}
