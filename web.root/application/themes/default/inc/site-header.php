<header class="p-1 text-dark">
    <nav id="top-navbar" class="navbar navbar-expand-sm navbar-light bg-light"
         aria-label="Top navigation">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <img style="height:3rem" class="img img-fluid" src="/assets/img/songs-logo.gif">
            </a>
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
                    <i class="fas fa-search"></i><a style="text-decoration: none; color:saddlebrown;font-weight: bold" href="/songs"> &nbsp;Search songs</a>
                </form>
            </div>
        </div>
    </nav>

</header>

