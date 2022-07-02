<style>
    .carousel-col {
        min-height: 21.5rem;
    }
</style>

<div class="mb-5">
    <h2>Newest Songs</h2>

    <div class="row mb-3">
        <div class="col-md-1 p-2">
            <button data-bs-target="#carousel-new" data-bs-slide="prev" class="btn btn-outline-secondary">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <div class="col-md-10  carousel-col">
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
    <div class="row" >
        <div class="col-md-1 p-2 carousel-col">
            <button data-bs-target="#carousel-featured" data-bs-slide="prev" class="btn btn-outline-secondary">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <div class="col-md-10 carousel-col">
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

    <div>
        <h2>All the songs:</h2>
        <h5>
            <a href="/songs" >Search and Filter <i class="fas fa-arrow-right"></i></a>
        </h5>
    </div>
</div>