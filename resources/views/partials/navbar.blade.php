<nav class="dh-navbar">
    <div class="dh-brand">
        @auth
            <button class="dh-icon-button dh-mobile-menu" id="mobileMenuButton" type="button" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
        @endauth

        <a class="d-flex align-items-center gap-2" href="{{ auth()->check() ? route('dashboard') : (request()->routeIs('setup.*') ? route('setup.create') : route('login')) }}">
            <span class="dh-brand-icon"><i class="bi bi-flower1"></i></span>
            <span>
                <span class="dh-brand-name d-block">eDamuhanHub</span>
                <span class="dh-brand-subtitle d-block">Rice Weed in the Philippines Database</span>
            </span>
        </a>
    </div>

    @auth
        <form class="dh-search" method="GET" action="{{ route('species.index') }}" role="search">
            <i class="bi bi-search"></i>
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="Search weeds, names, families, EPPO codes..."
                   aria-label="Search weed species">
        </form>

        <div class="dh-navbar-actions">
            <div class="dropdown">
                <button class="dh-user-button dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="dh-avatar"><i class="bi bi-person"></i></span>
                    <span class="dh-user-name small fw-semibold">{{ auth()->user()->name }}</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li>
                        <span class="dropdown-item-text small text-muted">
                            {{ auth()->user()->role?->name ?? 'Account' }}
                        </span>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-circle me-2"></i>My Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    @else
        <div class="dh-navbar-actions">
            <a class="btn btn-sm btn-outline-success" href="{{ route('developers.index') }}">Our Developers</a>
            <a class="btn btn-sm dh-btn-primary" href="{{ route('login') }}">Sign in</a>
        </div>
    @endauth
</nav>
