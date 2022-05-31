<?php

namespace PeanutTest\scripts;

use Peanut\QnutMigration\db\model\entity\DrupalArticle;
use Peanut\QnutMigration\db\model\repository\DrupalArticlesRepository;
use Peanut\QnutMigration\DrupalContentConverter;
use Soundasleep\Html2Text;

include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/db/model/entity/DrupalArticle.php';
include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/db/model/repository/DrupalArticlesRepository.php';
include_once DIR_BASE.'/packages/knockout_view/pnut/packages/qnut-migration/src/DrupalContentConverter.php';
/*DIR_BASE
'D:\dev\scym2021\web.root\packages\knockout_view\

pnut\packages\qnut-migration\src\db\model\repository\DrupalArticlesRepository.php'*/


class CreatearticlesTest extends TestScript
{

    private $defaultUid;
    private function createPage(array $properties,$isArchive) {
        $path = $isArchive ? '/news/news-archives' : '/news/news-feed';
        $typeHandle = $isArchive ? 'subpage' : 'pnut_news_item';
        $parentPage = \Page::getByPath($path);
        $pageType = \PageType::getByHandle($typeHandle);
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

    public function getPageProperties(DrupalArticle $article)
    {
        $props = [];
        $this->assertNotEmpty($article->description,'no description');
        $author = $this->getAuthorId($article->username) ?? $this->defaultUid;

        $props['cName'] = $article->title;
        $props['cDescription'] = $article->description;
        $props['cHandle'] = 'news_article_' . $article->id;
        $props['cDatePublic'] = $article->createdon;
        $props['uID'] = $author;

        return $props;
    }



    public function execute()
    {
        $actual = class_exists('\Peanut\QnutMigration\db\model\repository\DrupalArticlesRepository');
        $this->assert($actual,'Repository Class not found');
        $actual = class_exists('\Peanut\QnutMigration\db\model\Entity\DrupalArticle');
        $this->assert($actual,'Entity Class not found');

        $this->createPages(new DrupalArticlesRepository());


        // $article = $repository->get(292);

    }


    public function createPages($repository)
    {
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
    protected function addContent($article, $page): void
    {
        $block = \BlockType::getByHandle('content');
        $this->assertNotEmpty($block, 'fooey');
        $data = array(
            'content' => $article->contentNew
        );
        $page->addBlock($block, 'main', $data);
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
}