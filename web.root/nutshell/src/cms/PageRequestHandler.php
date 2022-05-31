<?php

namespace Nutshell\cms {

    class PageRequestHandler
    {
        protected $theme;
        protected $view;
        public function __construct($view=null, $theme=null)
        {
            $this->theme = $theme;
            $this->view = $view;
        }
    }
}