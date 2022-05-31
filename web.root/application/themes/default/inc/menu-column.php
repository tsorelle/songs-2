<?php
/** @var \Nutshell\cms\SiteMap $sitemap */
/** @var int $colsize */
/** @var string $menutype */
/** @var string $menutitle */

print sprintf("<div class='col-md-%s'>\n",$colsize);
if (!empty($menutitle)) { ?>
    <div class="menu-title">
        <h3>
            <?php print $menutitle ?>
        </h3>
    </div>
<?php
}
if ($menutype === 'sibling') {
    $sitemap->printSiblingMenu();
}
else {
    $sitemap->printChildMenu();
}

print '</div>';
