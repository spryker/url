<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Functional\Spryker\Zed\Url\Redirect;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\UrlRedirectTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Locale\Persistence\SpyLocale;
use Orm\Zed\Url\Persistence\Base\SpyUrlRedirectQuery;
use Orm\Zed\Url\Persistence\SpyUrl;
use Orm\Zed\Url\Persistence\SpyUrlRedirect;
use Spryker\Zed\Url\Business\UrlFacade;

/**
 * @group Functional
 * @group Spryker
 * @group Zed
 * @group Url
 * @group Redirect
 * @group RedirectChainAppendTest
 */
class RedirectChainAppendTest extends Test
{

    /**
     * @var \Spryker\Zed\Url\Business\UrlFacade
     */
    protected $urlFacade;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->urlFacade = new UrlFacade();
    }

    /**
     * @return void
     */
    public function testAvoidRedirectChainByCreatingRedirectToAlreadyRedirectedUrl()
    {
        $localeTransfer = $this->prepareTestData();

        $fooUrlRedirectTransfer = $this->createUrlRedirectTransfer('/test-foo', '/test-bar', $localeTransfer);
        $fooUrlRedirectTransfer = $this->urlFacade->createUrlRedirect($fooUrlRedirectTransfer);

        $barUrlRedirectTransfer = $this->createUrlRedirectTransfer('/test-bar', '/test-baz', $localeTransfer);
        $barUrlRedirectTransfer = $this->urlFacade->createUrlRedirect($barUrlRedirectTransfer);

        $actualRedirectEntity = SpyUrlRedirectQuery::create()->findOneByIdUrlRedirect($fooUrlRedirectTransfer->getIdUrlRedirect());

        $this->assertEquals(
            $barUrlRedirectTransfer->getToUrl(),
            $actualRedirectEntity->getToUrl(),
            'Redirects that has the same target as a newly created source of a redirect should point to the new target.'
        );
    }

    /**
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    protected function prepareTestData()
    {
        $localeEntity = $this->createLocaleEntity();

        $localeTransfer = new LocaleTransfer();
        $localeTransfer->fromArray($localeEntity->toArray(), true);

        return $localeTransfer;
    }

    /**
     * @return \Orm\Zed\Locale\Persistence\SpyLocale
     */
    protected function createLocaleEntity()
    {
        $localeEntity = new SpyLocale();
        $localeEntity
            ->setLocaleName('ab_CD')
            ->save();

        return $localeEntity;
    }

    /**
     * @param \Orm\Zed\Locale\Persistence\SpyLocale $localeEntity
     * @param string $url
     *
     * @return \Orm\Zed\Url\Persistence\SpyUrl
     */
    protected function createUrlEntity(SpyLocale $localeEntity, $url)
    {
        $urlEntity = new SpyUrl();
        $urlEntity
            ->setUrl($url)
            ->setFkLocale($localeEntity->getIdLocale())
            ->save();

        return $urlEntity;
    }

    /**
     * @param \Orm\Zed\Url\Persistence\SpyUrl $urlEntity
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    protected function mapEntityToTransfer(SpyUrl $urlEntity)
    {
        $urlTransfer = new UrlTransfer();
        $urlTransfer->fromArray($urlEntity->toArray(), true);

        return $urlTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     * @param string $url
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    protected function changeUrl(UrlTransfer $urlTransfer, $url)
    {
        $urlTransfer->setUrl($url);

        return $this->urlFacade->updateUrl($urlTransfer);
    }

    /**
     * @param string $source
     * @param string $target
     * @param \Orm\Zed\Locale\Persistence\SpyLocale $localeEntity
     *
     * @return \Orm\Zed\Url\Persistence\SpyUrl
     */
    protected function createUrlRedirectEntity($source, $target, SpyLocale $localeEntity)
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

    /**
     * @param string $sourceUrl
     * @param string $targetUrl
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    protected function createUrlRedirectTransfer($sourceUrl, $targetUrl, LocaleTransfer $localeTransfer)
    {
        $sourceUrlTransfer = new UrlTransfer();
        $sourceUrlTransfer
            ->setUrl($sourceUrl)
            ->setFkLocale($localeTransfer->getIdLocale());
        $urlRedirectTransfer = new UrlRedirectTransfer();
        $urlRedirectTransfer
            ->setSource($sourceUrlTransfer)
            ->setToUrl($targetUrl)
            ->setStatus(123);

        return $urlRedirectTransfer;
    }

}