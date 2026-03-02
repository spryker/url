<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Url\Business\Redirect;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Locale\Persistence\SpyLocale;
use Orm\Zed\Url\Persistence\Base\SpyUrlQuery;
use Orm\Zed\Url\Persistence\SpyUrl;
use Orm\Zed\Url\Persistence\SpyUrlRedirect;
use Spryker\Zed\Url\Business\UrlFacade;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Url
 * @group Business
 * @group Redirect
 * @group CreateRedirectOnUrlUpdateTest
 * Add your own group annotations below this line
 */
class CreateRedirectOnUrlUpdateTest extends Unit
{
    /**
     * @var \Spryker\Zed\Url\Business\UrlFacade
     */
    protected $urlFacade;

    public function setUp(): void
    {
        parent::setUp();

        $this->urlFacade = new UrlFacade();
    }

    public function testModifyingUrlCreatesRedirectToOldUrl(): void
    {
        $urlTransfer = $this->prepareTestData();

        $updatedUrlTransfer = $this->changeUrl(clone $urlTransfer, '/test-bar');

        $this->assertUrlUpdated($urlTransfer, $updatedUrlTransfer);
        $this->assertUrlRedirectCreated($urlTransfer, $updatedUrlTransfer);
    }

    protected function prepareTestData(): UrlTransfer
    {
        $localeEntity = $this->createLocaleEntity();
        $urlEntity = $this->createUrlEntity($localeEntity, '/test-foo');
        $urlTransfer = $this->mapEntityToTransfer($urlEntity);

        return $urlTransfer;
    }

    protected function createLocaleEntity(): SpyLocale
    {
        $localeEntity = new SpyLocale();
        $localeEntity
            ->setLocaleName('ab_CD')
            ->save();

        return $localeEntity;
    }

    protected function createUrlEntity(SpyLocale $localeEntity, string $url): SpyUrl
    {
        $urlEntity = new SpyUrl();
        $urlEntity
            ->setUrl($url)
            ->setFkLocale($localeEntity->getIdLocale())
            ->save();

        return $urlEntity;
    }

    protected function mapEntityToTransfer(SpyUrl $urlEntity): UrlTransfer
    {
        $urlTransfer = new UrlTransfer();
        $urlTransfer->fromArray($urlEntity->toArray(), true);

        return $urlTransfer;
    }

    protected function changeUrl(UrlTransfer $urlTransfer, string $url): UrlTransfer
    {
        $urlTransfer->setUrl($url);

        return $this->urlFacade->updateUrl($urlTransfer);
    }

    protected function assertUrlUpdated(UrlTransfer $urlTransfer, UrlTransfer $updatedUrlTransfer): void
    {
        $this->assertSame($urlTransfer->getIdUrl(), $updatedUrlTransfer->getIdUrl(), 'Ids of url should match after update.');
        $this->assertNotEquals($urlTransfer->getUrl(), $updatedUrlTransfer->getUrl(), 'Url should change after update.');
    }

    protected function assertUrlRedirectCreated(UrlTransfer $urlTransfer, UrlTransfer $updatedUrlTransfer): void
    {
        $urlEntity = SpyUrlQuery::create()
            ->filterByUrl($urlTransfer->getUrl())
            ->filterByFkLocale($urlTransfer->getFkLocale())
            ->findOne();

        $this->assertInstanceOf(SpyUrl::class, $urlEntity, 'New url entity should have been created with old url.');

        $redirectEntity = $urlEntity->getSpyUrlRedirect();

        $this->assertInstanceOf(SpyUrlRedirect::class, $redirectEntity, 'Url entity should have associated redirect entity.');
        $this->assertSame($updatedUrlTransfer->getUrl(), $redirectEntity->getToUrl(), 'Redirect url should match the new url after update.');
    }
}
