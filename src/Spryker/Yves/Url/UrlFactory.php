<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Url;

use Spryker\Yves\Kernel\AbstractFactory;
use Spryker\Yves\Url\Dependency\Client\UrlToLocaleClientInterface;

class UrlFactory extends AbstractFactory
{
    public function getLocaleClient(): UrlToLocaleClientInterface
    {
        return $this->getProvidedDependency(UrlDependencyProvider::CLIENT_LOCALE);
    }
}
