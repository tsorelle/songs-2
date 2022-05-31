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


class ConvertdrupalcontentTest extends TestScript
{

    private DrupalContentConverter $converter;

    private function createPage(array $properties) {
        $parentPage = \Page::getByPath('/news/articles');
        $pageType = \PageType::getByHandle('pnut_news_item');
        $template = \PageTemplate::getByHandle('full');
        $entry = $parentPage->add($pageType,
            $properties, $template);
        /*
                    example properties:
                    array(
                         'cName' => 'Hello World!',
                         'cDescription' => 'Just a quick blog post.',
                         'cHandle ' => 'hello-all'
                    )
        */

    }

    private function getAuthorId($authorUserName)
    {
        $newsAuthor = \UserInfo::getByUserName($authorUserName);
        if ($newsAuthor) {
            $uID = $newsAuthor->getUserID(); // updates the author ID on the page
            print "Author: $authorUserName ($uID)\n";
            return $uID;
        }
        return null;
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
    public function getPageProperties(DrupalArticle $article) {
        $summary = $article->summary;
        if (!$summary) {
            $parts = explode('<!--break-->',$article->content);
            if (count($parts) > 1) {
                $description = $parts[0];
            }
        }
        /*
                $props['cName'] = $article->title;
                $props['cDescription'] = ;
                $props['cHandle'] = $;
                $props['cDatePublic'] = $;
                $props['uID'] = $;*/

    }

    private function getDescription($text) {
        $text = strip_tags($text);
        if (strlen($text) > 128); {
            $text = explode("\n",$text)[0];
        }
        $text = str_replace(["\r\n", "\n", "\r","\t","&nbsp;"],' ',$text);
        $text = preg_replace('/[[:^print:]]/', "", $text);
        return substr(trim($text),0,512);
    }

    public function execute()
    {
        $this->converter = new DrupalContentConverter();
        $actual = class_exists('\Soundasleep\Html2Text');
        $actual = class_exists('\Peanut\QnutMigration\db\model\repository\DrupalArticlesRepository');
        $this->assert($actual,'Repository Class not found');
        $actual = class_exists('\Peanut\QnutMigration\db\model\Entity\DrupalArticle');
        $this->assert($actual,'Entity Class not found');
        $actual = class_exists('\Peanut\QnutMigration\DrupalContentConverter');
        $this->assert($actual,'Converter Class not found');

        $repository = new DrupalArticlesRepository();
        $this->assertNotEmpty($repository,'Failed to create repo');
        $articles = $repository->getAll();
        $this->assertNotEmpty($articles,'no articles');
//        $count = count($articles);
//        $this->assertEquals($count,162,'wrong record count. Actual: '.$count);
        $repository->clearRefs('img');
        $repository->clearRefs('href');
        foreach ($articles as $article
        ) {

            $imgs = $this->converter->extractImageRefs($article->content);
            foreach ($imgs as $img) {
                $repository->insertRef($article->id,$img,'img');
            }
            $hrefs = $this->converter->extractPageRefs($article->content);
            foreach ($hrefs as $href) {
                $repository->insertRef($article->id,$href,'href');
            }


            // clear previous results
            $article->contentNew = null;
            $article->summaryNew = null;
            $article->description = null;
            $article->contentError = null;
            $article->summaryError = null;

            $content = $this->getContent($article);
            $ok = true;
            if (isset($content->contentError)) {
                $ok = false;
                $article->contentError = $content->contentError;
                $article->contentNew = $content->content;
            }
            if (isset($content->summaryError)) {
                $article->summaryError = $content->summaryError;
                $article->summaryNew = $content->summary;
                $ok = false;
            }
            if ($ok) {
                $article->contentError = null;
                $article->summaryError = null;
                $article->contentNew = $content->content;
                $article->summaryNew = $content->summary;
                $article->description = $content->description;
            }
            $repository->update($article,'migration');

        }

        /*
        $username = 'tsorelle';
        $actual = $this->getAuthorId($username);
        $this->assertNotEmpty($actual,'Cant find user');*/





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
}