<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Url;

use Codeception\Test\Unit;
use Spryker\Service\UtilText\Model\Url\Url;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group Url
 * @group UrlTest
 * Add your own group annotations below this line
 */
class UrlTest extends Unit
{
    public function testUrlConstruct(): void
    {
        $url = new Url(['path' => '/foo/bar']);

        $this->assertSame('/foo/bar', $url->build());
    }

    public function testToString(): void
    {
        $url = new Url(['path' => '/foo/bar']);

        $this->assertSame('/foo/bar', (string)$url);
    }

    public function testToArray(): void
    {
        $url = new Url(['path' => '/foo/bar', 'query' => ['x' => 'y'], 'fragment' => 'z']);

        $expected = [
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => '/foo/bar',
            'query' => ['x' => 'y'],
            'fragment' => 'z',
        ];
        $result = $url->toArray();
        $this->assertSame($expected, $result);
    }

    public function testBuild(): void
    {
        $url = new Url(['path' => '/foo/bar', 'query' => ['x' => 'y'], 'fragment' => 'z']);

        $this->assertSame('/foo/bar?x=y#z', $url->build());
    }

    public function testBuildWithQueryAsString(): void
    {
        $url = new Url(['path' => '/foo/bar', 'query' => 'ö=ä', 'fragment' => 'z']);

        $this->assertSame('/foo/bar?%C3%B6=%C3%A4#z', $url->build());
    }

    public function testBuildEscaped(): void
    {
        $url = new Url(['path' => '/foo/bar', 'query' => ['x' => 'y', 'ö' => 'ä'], 'fragment' => 'z']);

        $this->assertSame('/foo/bar?x=y&amp;%C3%B6=%C3%A4#z', $url->buildEscaped());
    }

    public function testParse(): void
    {
        $url = Url::parse('/foo/bar?q=a#z');

        $this->assertSame('/foo/bar?q=a#z', (string)$url);
    }

    public function testGetPathSegments(): void
    {
        $url = new Url(['path' => '/foo/bar/baz', 'query' => 'q=a', 'fragment' => 'x']);
        $segments = $url->getPathSegments();
        $this->assertSame(['foo', 'bar', 'baz'], $segments);
    }

    public function testNormalizePath(): void
    {
        $url = new Url(['path' => '/foo/bar/baz//abc/', 'query' => ['x' => 'y'], 'fragment' => 'z']);
        $path = $url->normalizePath()->build();
        $this->assertSame('/foo/bar/baz/abc?x=y#z', $path);
    }

    public function testSetPathAsString(): void
    {
        $url = new Url(['path' => '/foo/bar/baz', 'query' => 'x=y', 'fragment' => 'z']);
        $url->setPath('/e/f');
        $this->assertSame('/e/f?x=y#z', $url->build());
    }

    public function testSetPathAsArray(): void
    {
        $url = new Url(['path' => '/foo/bar/baz', 'query' => 'x=y', 'fragment' => 'z']);
        $url->setPath(['e', 'f']);
        $this->assertSame('/e/f?x=y#z', $url->build());
    }

    public function testAddPathAsString(): void
    {
        $url = new Url(['path' => '/foo/bar/baz', 'query' => 'x=y', 'fragment' => 'z']);
        $url->addPath('/e/f/');

        $this->assertSame('/foo/bar/baz/e/f?x=y#z', $url->build());
    }

    public function testAddPathAsArray(): void
    {
        $url = new Url(['path' => '/foo/bar/baz', 'query' => 'x=y', 'fragment' => 'z']);
        $url->addPath(['e', 'f']);

        $this->assertSame('/foo/bar/baz/e/f?x=y#z', $url->build());
    }

    public function testSetQuery(): void
    {
        $url = new Url(['path' => '/foo/bar/baz', 'query' => 'x=y', 'fragment' => 'z']);
        $url->addQuery('c', 'd');
        $url->addQuery('e', 'f');
        $this->assertSame('/foo/bar/baz?x=y&c=d&e=f#z', $url->build());
    }

    public function testEmpty(): void
    {
        $url = new Url();
        $this->assertSame('/', $url->build(), 'Empty URL object must return homepage');
    }

    public function testFull(): void
    {
        $url = new Url();
        $url->addQuery('x', 'y');

        $url->setScheme('https');
        $url->setHost('www.foobar.dev');
        $url->setPort(81);

        $this->assertSame('https://www.foobar.dev:81/?x=y', $url->build());
    }
}
