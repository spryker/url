<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Url\Business\Redirect;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\UrlRedirectTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Locale\Persistence\SpyLocale;
use Orm\Zed\Url\Persistence\SpyUrl;
use Orm\Zed\Url\Persistence\SpyUrlRedirect;
use Spryker\Zed\Url\Business\Exception\RedirectLoopException;
use Spryker\Zed\Url\Business\UrlFacade;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Url
 * @group Business
 * @group Redirect
 * @group RedirectLoopTest
 * Add your own group annotations below this line
 */
class RedirectLoopTest extends Unit
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

    public function testCreatingCyclicRedirectsThrowsException(): void
    {
        $this->expectException(RedirectLoopException::class);
        $localeTransfer = $this->prepareTestData();

        $sourceUrlTransfer = new UrlTransfer();
        $sourceUrlTransfer
            ->setUrl('/test-bar')
            ->setFkLocale($localeTransfer->getIdLocale());

        $urlRedirectTransfer = new UrlRedirectTransfer();
        $urlRedirectTransfer
            ->setSource($sourceUrlTransfer)
            ->setToUrl('/test-foo')
            ->setStatus(123);

        $this->urlFacade->createUrlRedirect($urlRedirectTransfer);
    }

    protected function prepareTestData(): LocaleTransfer
    {
        $localeEntity = $this->createLocaleEntity();
        $localeTransfer = new LocaleTransfer();
        $localeTransfer->fromArray($localeEntity->toArray(), true);

        $this->createUrlRedirectEntity('/test-foo', '/test-bar', $localeEntity);

        return $localeTransfer;
    }

    protected function createLocaleEntity(): SpyLocale
    {
        $localeEntity = new SpyLocale();
        $localeEntity
            ->setLocaleName('ab_CD')
            ->save();

        return $localeEntity;
    }

    protected function createUrlRedirectEntity(string $source, string $target, SpyLocale $localeEntity): SpyUrl
    {
        $redirectEntity = new SpyUrlRedirect();
        $redirectEntity
            ->setToUrl($target)
            ->save();

        $urlEntity = new SpyUrl();
        $urlEntity
            ->setUrl($source)
            ->setFkResourceRedirect($redirectEntity->getIdUrlRedirect())
            ->setFkLocale($localeEntity->getIdLocale())
            ->save();

        return $urlEntity;
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
}
