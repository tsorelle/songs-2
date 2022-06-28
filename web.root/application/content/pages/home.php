<h3>Home Page</h3>

<h4>
    IP: <?php print $_SERVER['REMOTE_ADDR'] ?>
</h4>

<h2>Newest Songs</h2>

<div class="row mb-3" style="height:20rem;overflow: auto">
    <div class="col-md-1 p-2">
        <button data-bs-target="#carousel-new" data-bs-slide="prev" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    <div class="col-md-10">
        <div id="carousel-new" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                Peanut\songs\SongsManager::renderFrontPageCarousel('latest');
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-1 p-2">
        <button data-bs-target="#carousel-new" data-bs-slide="next" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<h2>Featured Songs</h2>
<div class="row" style="height:100rem;overflow: auto">
    <div class="col-md-1 p-2">
        <button data-bs-target="#carousel-featured" data-bs-slide="prev" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    <div class="col-md-10">
        <div id="carousel-featured" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                Peanut\songs\SongsManager::renderFrontPageCarousel('featured');
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-1 p-2">
        <button data-bs-target="#carousel-featured" data-bs-slide="next" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

