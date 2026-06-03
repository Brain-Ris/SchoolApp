<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SchoolApp') — SchoolApp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

<div class="layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-brand">
            <div class="brand-icon">S</div>
            <div>
                <div class="brand-name">SchoolApp</div>
                <div class="brand-sub">Gestion scolaire</div>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section-label">Principal</div>

            <div class="nav-item">
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Tableau de bord</span>
                </a>
            </div>

            <div class="nav-section-label">Scolarité</div>

            <div class="nav-item">
                <a href="{{ route('eleves.index') }}"
                   class="nav-link {{ request()->routeIs('eleves.*') ? 'active' : '' }}">
                    <span class="nav-icon">🎓</span>
                    <span class="nav-label">Élèves</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('classes.index') }}"
                   class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
                    <span class="nav-icon">🏫</span>
                    <span class="nav-label">Classes</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('matieres.index') }}"
                   class="nav-link {{ request()->routeIs('matieres.*') ? 'active' : '' }}">
                    <span class="nav-icon">📚</span>
                    <span class="nav-label">Matières</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('notes.index') }}"
                   class="nav-link {{ request()->routeIs('notes.*') ? 'active' : '' }}">
                    <span class="nav-icon">📝</span>
                    <span class="nav-label">Notes</span>
                </a>
            </div>
            
            @if(Auth::user()->role==='gestionnaire')
            <div class="nav-section-label">Administration</div>

            <div class="nav-item">
                <a href="{{ route('enseignants.index') }}"
                   class="nav-link {{ request()->routeIs('enseignants.*') ? 'active' : '' }}">
                    <span class="nav-icon">👨‍🏫</span>
                    <span class="nav-label">Enseignants</span>
                </a>
            </div>

            
            <div class="nav-item">
                <a href="{{ route('paiements.index') }}"
                   class="nav-link {{ request()->routeIs('paiements.*') ? 'active' : '' }}">
                    <span class="nav-icon">💰</span>
                    <span class="nav-label">Paiements</span>
                </a>
            </div>
            @endif

        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="btn-logout" title="Déconnexion">⏻</button>
                </form>
            </div>
        </div>

    </aside>

    <!-- ===== CONTENU PRINCIPAL ===== -->
    <div class="main-wrapper">

        <!-- Topbar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
            <div class="topbar-title">@yield('page-title', 'Accueil')</div>

            @yield('topbar-extras')

            <span class="topbar-badge">Année {{ date('Y') }}</span>
        </header>

        <!-- Alertes globales -->
        <div style="padding: 0 28px; margin-top: 14px;">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    ⚠️
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Contenu de la page -->
        <main class="page-content">
            @yield('content')
        </main>

    </div><!-- fin main-wrapper -->

</div><!-- fin layout -->

<!-- Overlay sidebar mobile -->
<div id="sidebarOverlay"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:99;"
     onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const isOpen   = sidebar.classList.toggle('open');
    overlay.style.display = isOpen ? 'block' : 'none';
}

/* ---- Gestion générique des modals ---- */
function openModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Fermer en cliquant sur l'overlay
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal(overlay.id);
        });
    });

    // Ouvrir le modal d'erreur s'il y a des erreurs de validation
    @if($errors->any())
        const errorModal = document.getElementById('modal-add') || document.getElementById('modal-edit');
        if (errorModal) openModal(errorModal.id);
    @endif
});
</script>

@stack('scripts')
</body>
</html>
