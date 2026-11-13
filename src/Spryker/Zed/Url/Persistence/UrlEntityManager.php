<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Url\Persistence;

use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Url\Persistence\SpyUrl;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\Url\Persistence\UrlPersistenceFactory getFactory()
 */
class UrlEntityManager extends AbstractEntityManager implements UrlEntityManagerInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     * @param bool|null $isNew
     *
     * @return void
     */
    public function saveUrlEntities(array $urlTransfers, ?bool $isNew = true): void
    {
        $isNew = $isNew === true;
        $existingUrlEntitiesByUrl = $isNew ? $this->getUrlEntitiesIndexedByUrl($urlTransfers) : [];

        $collection = new ObjectCollection();
        $collection->setModel(SpyUrl::class);

        foreach ($urlTransfers as $urlTransfer) {
            $collection->append($this->createUrlEntity($urlTransfer, $existingUrlEntitiesByUrl, $isNew));
        }

        $collection->save();
    }

    /**
     * @param array<string, \Orm\Zed\Url\Persistence\SpyUrl> $existingUrlEntitiesByUrl
     */
    protected function createUrlEntity(UrlTransfer $urlTransfer, array $existingUrlEntitiesByUrl, bool $isNew): SpyUrl
    {
        $existingUrlEntity = $existingUrlEntitiesByUrl[$urlTransfer->getUrl()] ?? null;

        if ($existingUrlEntity !== null) {
            $existingUrlEntity->fromArray($urlTransfer->modifiedToArray());

            return $existingUrlEntity;
        }

        $urlEntity = $this->getFactory()->createSpyUrlEntity();
        $urlEntity->fromArray($urlTransfer->toArray());
        $urlEntity->setNew($isNew);

        return $urlEntity;
    }

    /**
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     *
     * @return array<string, \Orm\Zed\Url\Persistence\SpyUrl>
     */
    protected function getUrlEntitiesIndexedByUrl(array $urlTransfers): array
    {
        $urls = [];

        foreach ($urlTransfers as $urlTransfer) {
            $urls[] = $urlTransfer->getUrlOrFail();
        }

        $urlEntitiesByUrl = [];

        foreach ($this->getFactory()->createUrlQuery()->filterByUrl($urls, Criteria::IN)->find() as $urlEntity) {
            $urlEntitiesByUrl[$urlEntity->getUrl()] = $urlEntity;
        }

        return $urlEntitiesByUrl;
    }
}
