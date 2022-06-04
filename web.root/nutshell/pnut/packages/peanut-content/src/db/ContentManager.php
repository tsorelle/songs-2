<?php
namespace Peanut\content\db;


use Peanut\content\db\model\repository\ContentRepository;

class ContentManager
{
    private $contentRepository;
    private function getContentRepository() {
        if (!isset($this->contentRepository)) {
            $this->contentRepository = new ContentRepository();
        }
        return $this->contentRepository;
    }

    public function getContent($id) {
        return $this->getContentRepository()->get($id);
    }
}