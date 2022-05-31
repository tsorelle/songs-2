<?php

namespace Nutshell\cms;

use PHPUnit\Framework\TestCase;
use Tops\sys\TParseDown;
use Tops\sys\TPath;

class SiteMapTest extends TestCase
{

    public function testGetMenu()
    {

        $map = new SiteMap(null,DIR_TEST_DATA.'/test-sitemap.xml');
        $actual = $map->getMenu();
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('songs');
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('songs/cowboy');
        $this->assertNotEmpty($actual);

        $map = new SiteMap(null,DIR_TEST_DATA.'/test-sitemap.xml');


    }

    public function testGetSiteMenu()
    {

        // $map = new SiteMap(DIR_TEST_DATA.'/test-sitemap.xml');
        $map = new SiteMap();
        $actual = $map->getMenu();
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('about');
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('about/nutshell');
        $this->assertNotEmpty($actual);

    }

    public function testRenderTopNav()
    {
        $map = new SiteMap('tools');
        $actual = $map->renderTopNav();
        $this->assertNotEmpty($actual);

        $map = new SiteMap(null,DIR_TEST_DATA.'/test-sitemap.xml');
        $actual = $map->renderTopNav();
        $this->assertNotEmpty($actual);



    }

    public function testRenderMenu()
    {
        $map = new SiteMap(null,DIR_TEST_DATA.'/test-sitemap.xml');
        $actual = $map->renderMenu('roy-rogers','songs/cowboy');
        $this->assertNotEmpty($actual);

    }

    public function testRenderBreadcrumbs()
    {
        $map = new SiteMap(null,DIR_TEST_DATA.'/test-sitemap.xml');
        $actual = $map->renderBreadcrumbs('songs/cowboy/roy-rogers','>');
        $this->assertNotEmpty($actual);

        $actual = $map->renderBreadcrumbs('songs/cowboy');
        $this->assertNotEmpty($actual);

        $actual = $map->renderBreadcrumbs('songs');
        $this->assertNotEmpty($actual);

        $actual = $map->renderBreadcrumbs('');
        $this->assertEmpty($actual);

    }
}
