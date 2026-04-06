<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CMS') — {{ config('app.name') }}</title>
    @yield('head')
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-w: 260px;
            --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'SF Pro Display', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --bg: #fbfbfd;
            --sidebar-bg: #fbfbfd;
            --sidebar-text: #515154;
            --sidebar-active-bg: rgba(0,0,0,0.05);
            --sidebar-active-text: #1d1d1f;
            --card-bg: #ffffff;
            --border: #d2d2d7;
            --border-light: rgba(0,0,0,0.08);
            --text: #1d1d1f;
            --text-secondary: #86868b;
            --accent: #1d1d1f;
            --accent-hover: #333336;
            --accent-light: #f5f5f7;
            --danger: #e3242b;
            --success: #28a745;
            --warning: #f39c12;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.02);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.08);
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
        }

        body { font-family: var(--font); background: var(--bg); color: var(--text); line-height: 1.47059; font-size: 14px; font-weight: 400; -webkit-font-smoothing: antialiased; letter-spacing: -0.015em; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: var(--sidebar-w);
            background: var(--sidebar-bg); padding: 0; z-index: 100;
            display: flex; flex-direction: column; transition: transform 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
            border-right: 1px solid var(--border-light);
        }
        .sidebar-brand {
            padding: 32px 24px 24px;
        }
        .sidebar-brand h1 { font-size: 20px; font-weight: 600; color: var(--text); letter-spacing: -0.021em; }
        .sidebar-brand span { font-size: 13px; color: var(--text-secondary); display: block; margin-top: 4px; font-weight: 500; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 0 12px 24px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px; padding: 10px 14px;
            color: var(--sidebar-text); text-decoration: none; font-size: 14px;
            font-weight: 500; transition: all 0.2s ease; border-radius: var(--radius-sm);
            margin-bottom: 2px;
        }
        .sidebar-nav a:hover { color: var(--text); background: rgba(0,0,0,0.03); }
        .sidebar-nav a.active { color: var(--sidebar-active-text); background: var(--sidebar-active-bg); font-weight: 600; }
        .sidebar-nav a svg { width: 18px; height: 18px; flex-shrink: 0; stroke-width: 2px; }
        .sidebar-section { padding: 24px 14px 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); font-weight: 600; }
        .sidebar-footer { padding: 16px 24px; border-top: 1px solid var(--border-light); background: var(--sidebar-bg); }
        .sidebar-footer .user-info { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .sidebar-footer .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--border-light); display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--text); font-weight: 600; border: 1px solid var(--border); }
        .sidebar-footer .user-name { font-size: 13px; color: var(--text); font-weight: 600; }
        .sidebar-footer .user-role { font-size: 12px; color: var(--text-secondary); }

        /* Main content */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: rgba(251, 251, 253, 0.72); backdrop-filter: saturate(180%) blur(20px); -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid var(--border-light); padding: 0 40px; height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { font-size: 20px; font-weight: 600; letter-spacing: -0.021em; color: var(--text); }
        .content { padding: 40px; max-width: 1200px; width: 100%; margin: 0 auto; flex: 1; }

        /* Cards */
        .card { background: var(--card-bg); border-radius: var(--radius-md); border: 1px solid var(--border-light); padding: 24px; box-shadow: var(--shadow-sm); transition: box-shadow 0.3s ease; }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-header { font-size: 17px; font-weight: 600; color: var(--text); margin-bottom: 20px; letter-spacing: -0.021em; }

        /* Stats grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: var(--card-bg); border-radius: var(--radius-md); border: 1px solid var(--border-light); padding: 24px; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .stat-label { font-size: 13px; color: var(--text-secondary); font-weight: 500; margin-bottom: 8px; letter-spacing: 0.01em; }
        .stat-value { font-size: 32px; font-weight: 600; letter-spacing: -0.025em; color: var(--text); line-height: 1.1; }
        .stat-sub { font-size: 13px; color: var(--text-secondary); margin-top: 8px; font-weight: 400; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }

        /* Table */
        .table-wrap { overflow-x: auto; margin: -10px; padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); font-weight: 600; text-align: left; padding: 12px 16px; border-bottom: 1px solid var(--border-light); }
        td { padding: 14px 16px; border-bottom: 1px solid var(--border-light); font-size: 14px; vertical-align: middle; color: var(--text); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--accent-light); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 980px; font-size: 14px; font-weight: 500; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s ease; font-family: var(--font); letter-spacing: -0.01em; outline: none; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn-secondary { background: var(--card-bg); color: var(--text); border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
        .btn-secondary:hover { background: var(--accent-light); }
        .btn-danger { background: #fff; color: var(--danger); border: 1px solid var(--border); }
        .btn-danger:hover { background: #fff5f5; border-color: #ffb3b0; color: #cc1b21;}
        .btn-sm { padding: 6px 14px; font-size: 12px; }
        .btn-group { display: flex; gap: 12px; align-items: center; }

        /* Forms */
        .form-group { margin-bottom: 24px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; color: var(--text); margin-bottom: 8px; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius-sm);
            font-size: 15px; font-family: var(--font); background: #fff; color: var(--text);
            transition: all 0.2s ease; outline: none; box-shadow: var(--shadow-sm);
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(0,0,0,0.06); }
        .form-textarea { resize: vertical; min-height: 120px; line-height: 1.5; }
        .form-hint { font-size: 13px; color: var(--text-secondary); margin-top: 6px; }
        .form-error { font-size: 13px; color: var(--danger); margin-top: 6px; font-weight: 500; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* Badge */
        .badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 980px; font-size: 12px; font-weight: 500; letter-spacing: -0.01em; }
        .badge-success { background: #e3f5e9; color: #14833b; border: 1px solid #bde6c9; }
        .badge-warning { background: #fff2d6; color: #995c00; border: 1px solid #ffe1a1;}
        .badge-danger { background: #ffe9e6; color: #d92d20; border: 1px solid #ffd1cd;}
        .badge-default { background: var(--accent-light); color: var(--text-secondary); border: 1px solid var(--border-light); }

        /* Alert */
        .alert { padding: 16px 20px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 24px; font-weight: 500; }
        .alert-success { background: #e3f5e9; color: #14833b; border: 1px solid #bde6c9; }
        .alert-error { background: #ffe9e6; color: #d92d20; border: 1px solid #ffd1cd; }

        /* Pagination */
        .pagination { display: flex; gap: 4px; align-items: center; justify-content: center; padding: 32px 0; flex-wrap: nowrap; }
        .pagination-pages { display: flex; gap: 4px; align-items: center; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; max-width: calc(100vw - 120px); }
        .pagination-pages::-webkit-scrollbar { display: none; }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 8px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; text-decoration: none; color: var(--text-secondary); transition: all 0.2s ease; border: 1px solid transparent; flex-shrink: 0; }
        .pagination a:hover { background: var(--accent-light); border-color: var(--border-light); color: var(--text); }
        .pagination .active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .pagination-prev, .pagination-next { font-size: 20px; font-weight: 400; min-width: 40px; height: 40px; border-radius: 50%; background: var(--card-bg); border: 1px solid var(--border-light) !important; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .pagination-prev:hover, .pagination-next:hover { background: var(--accent-light); }
        .pagination-dots { color: var(--text-secondary); border: none !important; min-width: 24px; padding: 0; }

        /* Property card */
        .listing-card { background: var(--card-bg); border-radius: var(--radius-md); border: 1px solid var(--border-light); overflow: hidden; transition: all 0.3s ease; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; }
        .listing-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .listing-card-img { width: 100%; height: 220px; object-fit: cover; background: var(--accent-light); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-size: 13px; }
        .listing-card-body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
        .listing-card-price { font-size: 22px; font-weight: 600; letter-spacing: -0.021em; margin-bottom: 8px; color: var(--text); }
        .listing-card-title { font-size: 16px; font-weight: 500; color: var(--text); margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
        .listing-card-meta { display: flex; gap: 16px; flex-wrap: wrap; margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border-light); }
        .listing-card-meta span { font-size: 13px; color: var(--text-secondary); font-weight: 500; }

        /* Filters */
        .filters { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; margin-bottom: 24px; padding: 20px; background: var(--card-bg); border-radius: var(--radius-md); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); }
        .filters .form-input, .filters .form-select { width: auto; min-width: 180px; padding: 10px 14px; font-size: 14px; }
        .filter-search { flex: 1; min-width: 240px; }

        /* Empty state */
        .empty-state { text-align: center; padding: 80px 20px; color: var(--text-secondary); background: var(--card-bg); border-radius: var(--radius-md); border: 1px dashed var(--border); margin-bottom: 24px; }
        .empty-state p { font-size: 15px; margin-bottom: 20px; font-weight: 500; }

        /* Tabs */
        .tabs { display: flex; gap: 24px; border-bottom: 1px solid var(--border); margin-bottom: 32px; }
        .tab { padding: 12px 0; font-size: 15px; color: var(--text-secondary); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; font-weight: 500; transition: all 0.2s ease; }
        .tab.active { color: var(--text); border-bottom-color: var(--accent); }
        .tab:hover:not(.active) { color: var(--text); border-bottom-color: var(--border); }

        /* Mobile toggle */
        .mobile-toggle { display: none; background: none; border: none; cursor: pointer; padding: 8px; color: var(--text); margin-left: -8px; }
        .mobile-toggle svg { width: 24px; height: 24px; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.2); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 99; transition: opacity 0.3s ease; opacity: 0; }
        .sidebar-overlay.open { display: block; opacity: 1; }

        /* Logout button */
        .logout-btn { width: 100%; padding: 10px 16px; background: #fff; border: 1px solid var(--border); color: var(--text); border-radius: var(--radius-sm); font-size: 13px; cursor: pointer; font-family: var(--font); font-weight: 500; transition: all 0.2s; box-shadow: var(--shadow-sm); }
        .logout-btn:hover { background: var(--accent-light); }

        /* Upload Placeholder */
        .upload-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 160px; border: 2px dashed var(--border); border-radius: var(--radius-md); background: #fafafc; color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease; font-size: 14px; }
        .upload-placeholder:hover { border-color: var(--text-secondary); background: #f0f0f4; }
        .upload-placeholder svg { width: 32px; height: 32px; margin-bottom: 12px; color: var(--border); }

        /* Details page structure */
        .details-header { display: flex; gap: 24px; align-items: flex-start; margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-light); flex-wrap: wrap; }
        .details-hero-img { flex: 0 0 400px; max-width: 100%; height: 280px; object-fit: cover; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); background: var(--accent-light); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); }
        .details-info { flex: 1; min-width: 300px; }
        .details-title { font-size: 32px; font-weight: 600; letter-spacing: -0.025em; color: var(--text); margin-bottom: 12px; line-height: 1.1; }
        .details-price { font-size: 28px; font-weight: 500; color: var(--text); margin-bottom: 24px; }
        .details-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; }
        .details-meta-item { display: flex; flex-direction: column; }
        .details-meta-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); font-weight: 600; margin-bottom: 4px; }
        .details-meta-val { font-size: 15px; color: var(--text); font-weight: 500; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 4px; border: 2px solid transparent; background-clip: padding-box; }
        ::-webkit-scrollbar-thumb:hover { background-color: rgba(0,0,0,0.3); }

        /* Responsive */
        @media (max-width: 1024px) {
            .grid-2 { grid-template-columns: 1fr; }
            .grid-3 { grid-template-columns: 1fr 1fr; }
            .content { padding: 32px 24px; }
            .details-hero-img { flex: 0 0 100%; height: 320px; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; opacity: 1; }
            .main { margin-left: 0; }
            .topbar { padding: 0 20px; height: 56px; }
            .topbar h2 { font-size: 18px; }
            .mobile-toggle { display: block; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
            .grid-3 { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; gap: 16px; }
            .filters { flex-direction: column; align-items: stretch; gap: 12px; }
            .filters .form-input, .filters .form-select { width: 100%; min-width: unset; }
            .filter-search { min-width: unset; }
            .tabs { gap: 16px; overflow-x: auto; padding-bottom: 2px; }
            .tab { white-space: nowrap; }
            .details-title { font-size: 24px; }
            .details-price { font-size: 22px; }
            .card, .stat-card { padding: 20px; }
            .pagination { padding: 24px 0; gap: 2px; }
            .pagination a, .pagination span { min-width: 32px; height: 32px; font-size: 13px; padding: 0 6px; }
            .pagination-prev, .pagination-next { min-width: 36px; height: 36px; font-size: 18px; }
            .pagination-pages { max-width: calc(100vw - 140px); gap: 2px; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .content { padding: 24px 16px; }
            .card { padding: 16px; }
            .details-hero-img { height: 240px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h1>PropertyAgent</h1>
            <span>Content Management</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">Main</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Dashboard
            </a>

            <div class="sidebar-section">Content</div>
            <a href="{{ route('articles.index') }}" class="{{ request()->routeIs('articles.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Articles
            </a>
            <a href="{{ route('listings.index') }}" class="{{ request()->routeIs('listings.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>
                Listings
            </a>
            <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25H5.625A2.25 2.25 0 013.375 18V7.875c0-.621.504-1.125 1.125-1.125h3.375" /></svg>
                News
            </a>
            <a href="{{ route('recently-deleted.index') }}" class="{{ request()->routeIs('recently-deleted.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.021.166m-1.021-.165L18.16 19.674A2.25 2.25 0 0115.916 21.75H8.084A2.25 2.25 0 015.84 19.674L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0A48.11 48.11 0 017.5 5.625m6.75 0a48.11 48.11 0 00-3.75 0m3.75 0V4.5A1.125 1.125 0 0013.125 3.375h-2.25A1.125 1.125 0 009.75 4.5v1.125" /></svg>
                Recently Deleted
            </a>

            <div class="sidebar-section">Marketing</div>
            <a href="{{ route('seo.index') }}" class="{{ request()->routeIs('seo.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                SEO
            </a>
            <a href="{{ route('social.index') }}" class="{{ request()->routeIs('social.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                Social Media
            </a>

            <div class="sidebar-section">Analytics</div>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                Reports
            </a>

            <div class="sidebar-section">Account</div>
            <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                Profile
            </a>
            <a href="{{ route('billing.index') }}" class="{{ request()->routeIs('billing.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                Billing
            </a>
        </nav>
        <div class="sidebar-footer">
            @auth('agent')
            <div class="user-info">
                <div class="avatar">{{ strtoupper(substr(Auth::guard('agent')->user()->username, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ Auth::guard('agent')->user()->full_name }}</div>
                    <div class="user-role">{{ Auth::guard('agent')->user()->username }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">Sign Out</button>
            </form>
            @endauth
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Main -->
    <main class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <h2>@yield('page-title', 'Dashboard')</h2>
            </div>
            <div class="btn-group">
                @yield('topbar-actions')
            </div>
        </header>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
            document.body.style.overflow = document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
        }
    </script>
    @yield('scripts')
</body>
</html>
