<aside class="dh-sidebar" id="dhSidebar">
    <div class="dh-sidebar-label">Main Menu</div>

    <a href="{{ route('dashboard') }}" class="dh-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('species.index') }}" class="dh-nav-link {{ request()->routeIs('species.*') ? 'active' : '' }}">
        <i class="bi bi-flower2"></i><span>Weed Species</span>
    </a>
    <a href="{{ route('developers.index') }}" class="dh-nav-link {{ request()->routeIs('developers.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i><span>Our Developers</span>
    </a>

    @if(auth()->user()->hasRole('Administrator'))
        <div class="dh-sidebar-label mt-3">Reference Data</div>
        <a href="{{ route('admin.references.index', 'classifications') }}"
           class="dh-nav-link {{ request()->is('admin/references/classifications*') ? 'active' : '' }}">
            <i class="bi bi-grid"></i><span>Classifications</span>
        </a>
        <a href="{{ route('admin.references.index', 'families') }}"
           class="dh-nav-link {{ request()->is('admin/references/families*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i><span>Families</span>
        </a>
        <a href="{{ route('admin.references.index', 'habitats') }}"
           class="dh-nav-link {{ request()->is('admin/references/habitats*') ? 'active' : '' }}">
            <i class="bi bi-tree"></i><span>Habitats</span>
        </a>
        <a href="{{ route('admin.references.index', 'life-cycles') }}"
           class="dh-nav-link {{ request()->is('admin/references/life-cycles*') ? 'active' : '' }}">
            <i class="bi bi-arrow-repeat"></i><span>Life Cycles</span>
        </a>

        <div class="dh-sidebar-label mt-3">Data Management</div>
        <a href="{{ route('admin.imports.index') }}" class="dh-nav-link {{ request()->routeIs('admin.imports.*') ? 'active' : '' }}">
            <i class="bi bi-cloud-upload"></i><span>Dataset Imports</span>
        </a>
        <a href="{{ route('admin.images.index') }}" class="dh-nav-link {{ request()->routeIs('admin.images.*') ? 'active' : '' }}">
            <i class="bi bi-images"></i><span>Image Management</span>
        </a>

        <div class="dh-sidebar-label mt-3">Administration</div>
        <a href="{{ route('admin.users.index') }}" class="dh-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i><span>User Management</span>
        </a>
        <a href="{{ route('admin.audit-logs.index') }}" class="dh-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <i class="bi bi-activity"></i><span>Audit Logs</span>
        </a>
        <a href="{{ route('admin.backups.index') }}" class="dh-nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
            <i class="bi bi-database"></i><span>Database Backups</span>
        </a>
    @endif

    <div class="dh-sidebar-footer">
        <div class="fw-semibold mb-1"><i class="bi bi-leaf me-1 text-success"></i>eDamuhanHub</div>
        <div class="small">Rice weed in the Philippines information and database management system.</div>
        <div class="text-muted mt-2" style="font-size: 11px;">2026</div>
    </div>
</aside>
