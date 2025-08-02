<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <img alt="image" src="{{ asset('logo.png') }}" class="mr-1" height="24">
            <a href="/">Ikat Digital</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <img alt="image" src="{{ asset('logo.png') }}" class="mr-1" height="24">
        </div>
        <ul class="sidebar-menu">
            <li class="nav-item  ">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Dashboard</span></a>

            </li>

            <li class="nav-item ">
                <a href="{{ route('users.index') }}" class="nav-link "><i class="fas fa-users"></i>
                    <span>Users</span></a>
            </li>

            <li class="nav-item">
                <a href="{{ route('companies.show', 1) }}" class="nav-link">
                    <i class="fas fa-building"></i>
                    <span>Company</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('attendances.index') }}" class="nav-link">
                    <i class="fas fa-check"></i>
                    <span>Attendances</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('permissions.index') }}" class="nav-link">
                    <i class="fas fa-columns"></i>
                    <span>Permission</span>
                </a>
            </li>

    </aside>
</div>
