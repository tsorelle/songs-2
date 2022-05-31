<header class="p-3 bg-light text-dark">
    <nav class="navbar navbar-expand-sm navbar-light bg-light"
         aria-label="Top navigation">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Site Logo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#nutshell-top-navigation-menu"
                    aria-controls="nutshell-top-navigation-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nutshell-top-navigation-menu">
                <ul class="navbar-nav me-auto mb-2 mb-sm-0">


                <?php
                    /** @var \Nutshell\cms\SiteMap $sitemap */
                    $sitemap->printTopMenu();
                ?>

                </ul>
                <form>
                    <input class="form-control" type="text" placeholder="Search" aria-label="Search">
                </form>
            </div>
        </div>
    </nav>

</header>

