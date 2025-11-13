<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-3" href="#" style="letter-spacing: 1px;">
            <span class="text-white">Fit</span><span class="text-warning">&</span><span class="text-white">Fun</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-2">
                <li class="nav-item">
                    <a class="nav-link active px-4 py-2 rounded-pill bg-white text-primary fw-semibold" aria-current="page" href="#" id="home-tab2" data-bs-toggle="tab" role="tab" aria-selected="true">
                        Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4 py-2 rounded-pill text-white fw-semibold" href="#" id="profile-tab2" data-bs-toggle="tab" role="tab" aria-selected="false" style="transition: all 0.3s ease;">
                        Espace Adhérent
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .nav-link:not(.active):hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }
    
    .nav-link.active {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
</style>