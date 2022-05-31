<!-- page header -->

<div class="container" id="page-header">

    <?php
    /** @var int $breadcrumbs */
    if ($breadcrumbs === 1) {
        print '    <div id="breakcrumb-menu">';
        /** @var \Nutshell\cms\SiteMap $sitemap */
        $sitemap->printBreadcrumbMenu('>');
        print '    </div>';
    }

    if (!empty($pageTitle)) {
        print "<div id='page-title-block'>\n";
        printf('<h1 id="page-title">%s</h1>', $pageTitle) . "\n";
        print "</div>\n";
    }

    ?>

</div>
<!-- end page header -->
