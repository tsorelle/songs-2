<?php

namespace PeanutTest\scripts;

use Peanut\QnutMigration\db\model\entity\DrupalArticle;
use Peanut\QnutMigration\db\model\entity\DrupalPage;
use Peanut\QnutMigration\db\model\repository\DrupalArticlesRepository;
use Peanut\QnutMigration\db\model\repository\DrupalPagesRepository;
use Peanut\QnutMigration\DrupalContentConverter;
use Soundasleep\Html2Text;

include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/db/model/entity/DrupalPage.php';
include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/db/model/repository/DrupalPagesRepository.php';
//include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/db/model/entity/DrupalArticle.php';
//include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/db/model/repository/DrupalArticlesRepository.php';
include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/DrupalContentConverter.php';
/*DIR_BASE
'D:\dev\scym2021\web.root\packages\knockout_view\

pnut\packages\qnut-migration\src\db\model\repository\DrupalArticlesRepository.php'*/


class CreatepagesTest extends TestScript
{

    /**
     * @var $contentConverter DrupalContentConverter;
     */
    private $contentConverter;

    private $defaultUid;
    private function createPage(DrupalPage $page) {
        $properties = $this->getPageProperties($page);
        // $path = $isArchive ? '/news/news-archives' : '/news/news-feed';
        $path = $this->getMenuPath($page);
        $parentPage = \Page::getByPath($path);
        $pageType = \PageType::getByHandle($page->cmspagetype);
        $templateHandle = 'full'; // $page->cmspagetype == 'pnut_sibling_page' ? 'right_sidebar' : 'full';
        $template = \PageTemplate::getByHandle($templateHandle);
        $entry = $parentPage->add($pageType,
            $properties, $template);
        if ($page->entityCode) {
            $entry->setAttribute('vm_context_value', $page->entityCode);
        }
        return $entry;
/*
            example properties:
            array(
                 'cName' => 'Hello World!',
                 'cDescription' => 'Just a quick blog post.',
                 'cHandle ' => 'hello-all'
            )
*/

    }
    private function createPageTest(DrupalPage $page) {
        $properties = $this->getPageProperties($page);
        // $path = $isArchive ? '/news/news-archives' : '/news/news-feed';
        // $path = $this->getMenuPath($page);
        $path = '/tasks/testing';
        $parentPage = \Page::getByPath($path);
        $pageType = \PageType::getByHandle('subpage');
        $template = \PageTemplate::getByHandle('full');
        $entry = $parentPage->add($pageType,
            $properties, $template);
        return $entry;
/*
            example properties:
            array(
                 'cName' => 'Hello World!',
                 'cDescription' => 'Just a quick blog post.',
                 'cHandle ' => 'hello-all'
            )
*/

    }
    private function createTestPage(DrupalPage $page) {
        $properties = $this->getPageProperties($page);
        $path = '/about';
        $parentPage = \Page::getByPath($path);
        $pageType = \PageType::getByHandle('subpage');
        $template = \PageTemplate::getByHandle('full');
        $entry = $parentPage->add($pageType,
            $properties, $template);
        return $entry;
/*
            example properties:
            array(
                 'cName' => 'Hello World!',
                 'cDescription' => 'Just a quick blog post.',
                 'cHandle ' => 'hello-all'
            )
*/

    }

    public function extractPageRefs($html) {
        $searchRef = 'https://scym.org/';
        $refLen = strlen($searchRef);
        $pages = [];

        while ($html) {
            $p =  strpos($html,$searchRef);
            if ($p === false) {
                break;
            }
            $html = substr($html,$p+$refLen);
            $end = strpos($html,'"');
            $pages [] = substr($html,0,$end);
            $html = substr($html,$end);
        }
        return $pages;
    }

    public function extractImageRefs($html) {
        $imgs = [];
        while (true) {
            $p = strpos($html, '<img');
            if ($p === false) {
                break;
            }
            $html = substr($html, $p + 4);
            $src = strpos($html, 'src=');
            if ($src !== false) {
                $qp = $src + 4;
                $q = substr($html, $qp, 1);
                $t = substr($html, $qp + 1);
                $e = strpos($t, $q);
                if ($e !== false &&  substr($t, 0, 4) != 'http') {
                    $ref = substr($t, 0, $e);
                    if (strlen($ref) > 512) {
                        $ref = substr($ref,0,512);
                    }
                    $imgs[] = $ref;

                }
            }
        }

        return $imgs;
    }


    public function extractDocRefs($html) {
        $searchRef = "'".'/documents/';
        $refLen = strlen($searchRef);
        $pages = [];

        while ($html) {
            $p =  strpos($html,$searchRef);
            if ($p === false) {
                break;
            }
            $html = substr($html,$p+$refLen);
            $end = strpos($html,'"');
            $pages [] = substr($html,0,$end);
            $html = substr($html,$end);
        }
        return $pages;
    }


    private function getAuthorId($authorUserName)
    {
        $newsAuthor = \UserInfo::getByUserName($authorUserName);
        if ($newsAuthor) {
            $uID = $newsAuthor->getUserID(); // updates the author ID on the page
            // print "Author: $authorUserName ($uID)\n";
            return $uID;
        }
        return null;
    }

    private function getParentPagePath($menuname) {

    }

    public function getPageProperties(DrupalPage $page)
    {
        $props = [];
        $this->assertNotEmpty($page->description,'no description');
        // $author = $this->getAuthorId($article->username) ?? $this->defaultUid;
        $author = $this->defaultUid;

        $props['cName'] = $page->pagetitle;
        $props['cDescription'] = $page->description;
        $props['cHandle'] = $page->pagehandle;
        $props['cDatePublic'] = $page->createdon;
        $props['uID'] = $author;

        return $props;
    }



    public function execute()
    {

        $this->createPages(new DrupalPagesRepository());
        // $this->testPageCreation();

    }


    public function createArticlePages($repository)
    {
        $actual = class_exists('\Peanut\QnutMigration\db\model\repository\DrupalArticlesRepository');
        $this->assert($actual,'Repository Class not found');
        $actual = class_exists('\Peanut\QnutMigration\db\model\Entity\DrupalArticle');
        $this->assert($actual,'Entity Class not found');

        $this->defaultUid = $this->getAuthorId('admin');
        $this->assertNotEmpty($this->defaultUid,'admin user not found');
        $articles = $repository->getAll();
        usort($articles, function($x, $y) {
            return $x->createdon <=> $y->createdon;
        });

        $this->assertNotEmpty($articles,'no articles');

        foreach ($articles as $article) {
            try {
                $props = $this->getPageProperties($article);
                $page = $this->createPage($props, $article->archive == 1);
                $this->setAttributes($page,$article);
                $this->addContent($article, $page);
                $article->active = 0;
            }
            catch ( \Exception $ex) {
                $m = $ex->getMessage();
                $article->summaryError = substr($ex->getMessage(),0,1024);

            }
            $repository->update($article);
            // print_r($props);
        }
    }


    public function createPages(DrupalPagesRepository $repository)
    {
        $actual = class_exists('\Peanut\QnutMigration\db\model\repository\DrupalPagesRepository');
        $this->assert($actual,'Repository Class not found');
        $actual = class_exists('\Peanut\QnutMigration\db\model\Entity\DrupalPage');
        $this->assert($actual,'Entity Class not found');

        $this->defaultUid = $this->getAuthorId('admin');
        $this->assertNotEmpty($this->defaultUid,'admin user not found');

        // $pages = $repository->getPages();
        $pages = $repository->getCommitteePages();
        $this->assertNotEmpty($pages,'no pages');

        foreach ($pages as
                 /** @var $page \Peanut\QnutMigration\db\model\entity\DrupalPage */ $page
        ) {
            try {
                $cmspage = $this->createPage($page);
/*                if ($page->cmspagetype !== 'peanut_host_page') {
                    $this->addContent($cmspage,$page->contentNew);
                }*/
                $page->processed = 1;
                $repository->update($page);
            }
            catch ( \Exception $ex) {
                $m = $ex->getMessage();
                $page->contentError = substr($ex->getMessage(),0,1024);
            }
            // $repository->update($page);
            // print_r($props);
        }
    }


    private function setAttributes($page,DrupalArticle $article) {
        if ($article->hasDetails == 1) {
            $page->setAttribute('pnut_has_detail', true);
        }
        if ($article->featured == 1) {
            $page->setAttribute('is_featured', true);
        }

        if (!empty($article->summaryNew)) {
            $page->setAttribute('pnut_summary',
                $article->summaryNew);
        }
        // content??
    }
    /*
     * Attributes
     * author name
     * summary
     * has details
     * is featured
     * thumbnail
     * content
     */
    /**
     * @param $article
     * @param $page
     * @return void
     */
    protected function addContent($cmspage,$content): void
    {
        if (empty($content)) {
            return;
        }
        $block = \BlockType::getByHandle('content');
        $data = array(
            'content' => $content
        );
        $cmspage->addBlock($block, 'main', $data);
    }

    /**
     * @return void
     */
    protected function testContentAdd(): void
    {
        $repository = new DrupalArticlesRepository();
        $this->assertNotEmpty($repository, 'Failed to create repo');
        $article = $repository->get(292);

        // $page = \Page::getByPath('/news/articles/news_article_292');
        $page = \Page::getByPath('/news/articles/test4');
        //       $page = \Page::getByPath('/tasks/testing/testpage2');

//         $this->setAttributes($page,$article);
        $block = \BlockType::getByHandle('content');
        $this->assertNotEmpty($block, 'fooey');

        $data = array(
            // title' => 'An Exciting Title',
            'content' => $article->contentNew
        );

//        $r = $page->addBlock($block,'column_1',$data);
//       $r = $page->addBlock($block,'column 1',$data);
//        $r = $page->addBlock($block,'column',$data);
        $r = $page->addBlock($block, 'content', $data);
        $this->assert(true, 'stop');
    }

    private function rawLength($html)
    {
        return strlen(strip_tags($html));
    }

    private function cleanHtml($html) {
        $html = preg_replace('/[[:^print:]]/', "", $html);
    }
    private function getContent(DrupalArticle $article) {

        $result = new \stdClass();
        $result->content = null;
        if ($article->summary) {
            $result->summary = $article->summary;
            $result->content = $article->content;
        }
        else {
            $parts = explode('<!--break-->',$article->content);
            if (count($parts) > 1) {
                $result->summary = $parts[0];
                $result->content = $parts[1];
            }
            else {
                if (strlen(strip_tags($article->content)) < 1028) {
                    $result->summary = $article->content;
                    $result->content = null;
                }
                else {
                    $result->summary = null;
                    $result->content = $article->content;
                }
            }
        }
        $result->description = $this->getDescription(
            @$result->summary ? $result->summary : $result->content
        );

        $error = '';
        if ($result->content) {
            try {
                $preprocess = $this->converter->preprocess($result->content);
                $result->content = $this->converter->convert($result->content);
            } catch (\Exception $exception) {
                $result->content = $preprocess;
                $result->contentError = $exception->getMessage()."\n";
            }
        }

        if ($result->summary) {
            try {
                $preprocess = $this->converter->preprocess($result->summary);
                $result->summary = $this->converter->convert($result->summary);
            } catch (\Exception $exception) {
                $result->content = $preprocess;
                $result->summaryError = $exception->getMessage();
            }
        }

        return $result;

    }
    public function getPageSummary(DrupalArticle $article) {
        $summary = $article->summary;
        if (!$summary) {
            $parts = explode('<!--break-->',$article->content);
            if (count($parts) > 1) {
                $description = $parts[0];
            }
        }


    }

    private function getDescription(DrupalPage $page) {
        $text = $page->description;
        if ($text) {
            return strip_tags($text);
        }
        $parts = explode('<!--break-->',$page->content);
        if (count($parts) > 1) {
            $text = $parts[0];
        }
        else {
            return $page->pagetitle;
        }
        $text = strip_tags($text);
        if (strlen($text) > 128); {
            $text = explode("\n",$text)[0];
        }
        $text = str_replace(["\r\n", "\n", "\r","\t","&nbsp;"],' ',$text);
        $text = preg_replace('/[[:^print:]]/', "", $text);
        return substr(trim($text),0,512);
    }

    private function convertPage(DrupalPage $page) {
        if ($page->pagetype != 'page') {
            return false;
        }
        try {
            if (strpos($page->content,'databind') !== false) {
                $page->pagetype = 'feature';
                $page->description = 'Qnut feature';
                return $page;
            }
            $page->description = $this->getDescription($page);
            $content = str_replace('<!--break-->', '', $page->content);
            $content = $this->contentConverter->preprocess($content);
            $content = $this->contentConverter->convert($content);
            $page->contentNew = $content;
        }
        catch (\Exception $ex) {
            $page->contentError = $ex->getMessage();
        }

        return $page;
    }

    private function convertPages()
    {
        $this->contentConverter = new DrupalContentConverter();
        $repository = new DrupalPagesRepository();
        $repository->clearRefs('img');
        $repository->clearRefs('href');
        $repository->clearRefs('doc');

        $pages = $repository->getAll();
        foreach ($pages as $page) {

            $page = $this->convertPage($page);
            if ($page !== false) {
                $imgs = $this->extractImageRefs($page->content);
                foreach ($imgs as $img) {
                    $repository->insertRef($page->id,$img,'img');
                }
                $hrefs = $this->extractPageRefs($page->content);
                foreach ($hrefs as $href) {
                    $repository->insertRef($page->id,$href,'href');
                }

                $hrefs = $this->extractDocRefs($page->contentNew);
                foreach ($hrefs as $href) {
                    $repository->insertRef($page->id,$href,'doc');
                }

                $repository->update($page);
            }
        }
    }

    function getMenuPath(DrupalPage $page) {
        switch($page->menuname) {
            // level 2
            case 'menu-about-menu'      : return '/about';
            case 'menu-resources-menu'  : return '/resources';
            case 'menu-sessions'        : return '/sessions';
            // level 3
            case 'menu-committees'      : return '/resources/committees';
            case 'menu-annual'          : return '/sessions/annual';
            // level 4
            case 'menu-yearly-meeting'  : return '/sessions/annual/upcoming';
            default:
                throw new \Exception("Invalid path for page ".$page->id);
        }
    }

    private function testCreatePage() {

    }

    private function testPageCreation()
    {
        $repo = new DrupalPagesRepository();
        $pg = $repo->get(788);
        $cmspage = $this->createPageTest($pg);
        // $this->addContent($cmspage,$pg->contentNew);
        // $this->addContent($cmspage,'<h1>Hello World</h1>');

    }


}