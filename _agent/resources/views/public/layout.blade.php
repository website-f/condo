<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title', $agent->full_name ?? $agent->username) — condo.com.my</title>
<meta name="description" content="@yield('description', 'Property listings and articles from ' . ($agent->full_name ?? $agent->username))">
<style>
    *,*::before,*::after{box-sizing:border-box}
    body{margin:0;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1f2937;background:#f8fafc;line-height:1.5}
    a{color:#2563eb;text-decoration:none}
    a:hover{text-decoration:underline}
    .site-header{background:#fff;border-bottom:1px solid #e5e7eb}
    .site-header-inner{max-width:1100px;margin:0 auto;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap}
    .site-brand{font-weight:700;font-size:18px;color:#0f172a}
    .site-brand span{color:#64748b;font-weight:500;font-size:14px;margin-left:6px}
    .site-nav a{display:inline-block;padding:8px 14px;border-radius:8px;color:#334155;font-weight:500}
    .site-nav a.active,.site-nav a:hover{background:#eef2ff;color:#1e40af;text-decoration:none}
    .container{max-width:1100px;margin:0 auto;padding:32px 20px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;display:flex;flex-direction:column}
    .card-img{width:100%;aspect-ratio:16/10;object-fit:cover;background:#e2e8f0}
    .card-body{padding:14px 16px;display:flex;flex-direction:column;gap:6px;flex:1}
    .card-title{font-weight:600;color:#0f172a;font-size:15px;line-height:1.35}
    .card-meta{font-size:13px;color:#64748b}
    .price{font-weight:700;color:#0f172a}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
    .badge.icp{background:#fef3c7;color:#92400e}
    .hero{background:linear-gradient(135deg,#1e3a8a,#3b82f6);color:#fff;border-radius:14px;padding:36px 28px;margin-bottom:28px}
    .hero h1{margin:0 0 6px;font-size:26px}
    .hero p{margin:0;opacity:.85}
    .section-title{font-size:20px;margin:24px 0 14px;color:#0f172a}
    .pagination{margin-top:24px;display:flex;gap:6px;flex-wrap:wrap}
    .pagination a,.pagination span{padding:8px 12px;border-radius:8px;background:#fff;border:1px solid #e5e7eb;color:#334155;font-size:14px}
    .pagination .active{background:#2563eb;color:#fff;border-color:#2563eb}
    .filter-bar{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap}
    .filter-bar a{padding:8px 14px;border-radius:8px;background:#fff;border:1px solid #e5e7eb;color:#334155;font-weight:500;font-size:14px}
    .filter-bar a.active{background:#2563eb;border-color:#2563eb;color:#fff}
    .listing-detail-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:24px}
    @media (max-width:780px){.listing-detail-grid{grid-template-columns:1fr}}
    .listing-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
    .listing-gallery img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;background:#e2e8f0}
    .listing-gallery img:first-child{grid-column:span 3;aspect-ratio:16/10}
    .detail-panel{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px}
    .detail-panel dl{margin:0;display:grid;grid-template-columns:auto 1fr;gap:8px 16px}
    .detail-panel dt{color:#64748b;font-size:13px}
    .detail-panel dd{margin:0;font-weight:600;color:#0f172a;font-size:14px}
    .article-meta{color:#64748b;font-size:13px;margin-bottom:14px}
    .article-content{font-size:16px;line-height:1.7}
    .article-content img{max-width:100%;height:auto;border-radius:8px}
    footer{padding:40px 20px;text-align:center;color:#64748b;font-size:13px}
</style>
</head>
<body>
<header class="site-header">
    <div class="site-header-inner">
        <div class="site-brand">{{ $agent->full_name ?? $agent->username }}<span>{{ $publicHost ?? '' }}</span></div>
        <nav class="site-nav">
            <a href="/" class="@if(request()->path()==='/' || request()->is('/')) active @endif">Home</a>
            <a href="/listings" class="@if(request()->is('listings*')) active @endif">Listings</a>
            <a href="/articles" class="@if(request()->is('articles*')) active @endif">Articles</a>
        </nav>
    </div>
</header>

<main class="container">
    @yield('content')
</main>

<footer>
    &copy; {{ date('Y') }} {{ $agent->full_name ?? $agent->username }} &middot; Powered by condo.com.my
</footer>
</body>
</html>
