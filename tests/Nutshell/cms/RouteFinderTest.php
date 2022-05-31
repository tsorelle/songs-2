<?php

namespace Nutshell\cms;

use PHPUnit\Framework\TestCase;

class RouteFinderTest extends TestCase
{
    public function testMatch()
    {
        include_once 'D:\dev\twoquakers\nutshell-1\web.root\nutshell\src\cms\routing\RouteFinder.php';
        $uris =[
            '/',
            'tools/tests/smoketest',
            'tools/tests/simpletest',
            'tools/tests/componentstest',
            'tools/tests/modaltest',
            'tools/tests/servicetest',
            'tools/tests',
            'tools',
            'peanut/settings',
            'about/terry',
            'about/nutshell/libs',
            'about/nutshell/design',
            'about/nutshell',
            'about'
            ];
        foreach ($uris as $uri) {
            $matched = RouteFinder::match($uri);
            $this->assertTrue($matched,$uri);
            $configuration = RouteFinder::$matched;
            $matchPath = $configuration['path'];
            $expected = $uri === '/' ? 'home' : $uri;
            $this->assertEquals($expected, $matchPath);
        }

        $uri = 'peanut/service/execute/name/params';
        $matched = RouteFinder::match($uri);
        $this->assertTrue($matched);
        $configuration = RouteFinder::$matched;
        $matchPath = $configuration['path'];
        $argValues = $configuration['argValues'];
        $expected = 'peanut/service/execute';
        $this->assertEquals($expected,$matchPath);
        $this->assertContains('name',$argValues);
        $this->assertContains('params',$argValues);

        $uri = 'songs/cowboy';
        $matched = RouteFinder::match($uri);
        $this->assertTrue($matched);
        $configuration = RouteFinder::$matched;
        $matchPath = $configuration['path'];
        $argValues = $configuration['argValues'];
        $expected = 'songs';
        $this->assertEquals($expected,$matchPath);
        $this->assertContains('cowboy',$argValues);
        $this->assertEquals('songtype',$configuration['args']);

        $uri = 'song/1';
        $matched = RouteFinder::match($uri);
        $this->assertTrue($matched);
        $configuration = RouteFinder::$matched;
        $matchPath = $configuration['path'];
        $argValues = $configuration['argValues'];
        $expected = 'song';
        $this->assertEquals($expected,$matchPath);
        $this->assertContains('1',$argValues);
        $this->assertEquals('songid',$configuration['args']);
    }

    public function testMismatch()
    {
        include_once 'D:\dev\twoquakers\nutshell-1\web.root\nutshell\src\cms\routing\RouteFinder.php';
        $unregistered = [
            'tools/tests/notest',
            'peanut/service/exec/one/two',
            'song/cowboy',
            'notfound'
        ];
        foreach ($unregistered as $uri) {
            $uri = 'tools/test/componentstest';
            $matched = RouteFinder::match($uri);
            if ($matched) {
                $configuration = RouteFinder::$matched;
                $matchPath = $configuration['path'];
                $argValues = $configuration['argValues'];
            }
            $this->assertFalse($matched);
        }
    }

}
