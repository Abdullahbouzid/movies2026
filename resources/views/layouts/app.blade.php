<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'سينما بلاس')</title>
@hasSection('meta')
@yield('meta')
@endif
<meta name="robots" content="@yield('robots', 'index,follow')">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#ffffff;--card:#ffffff;--card-2:#f4f4f6;
  --line:rgba(0,0,0,.09);--text:#0a0a10;--muted:#6a6a72;
  --gold:#e71d24;--gold-soft:rgba(231,29,36,.10);
}
html{scroll-behavior:smooth}
body{font-family:'Tajawal','Segoe UI',Tahoma,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;-webkit-font-smoothing:antialiased}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(900px 500px at 85% -10%,rgba(231,29,36,.06),transparent 60%),radial-gradient(800px 500px at 10% 110%,rgba(231,29,36,.05),transparent 60%);pointer-events:none;z-index:0}
.container{width:100%;max-width:1240px;margin:0 auto;padding:0 22px;position:relative;z-index:1}
a{color:inherit}
header{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.88);backdrop-filter:blur(18px);border-bottom:1px solid var(--line)}
.nav{display:flex;align-items:center;justify-content:space-between;gap:16px;height:72px}
.logo{display:flex;align-items:center;gap:10px;font-weight:900;font-size:22px;text-decoration:none;color:inherit}
.logo-badge{width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--gold),#8b1115);display:grid;place-items:center;color:#fff;font-size:20px}
.links{display:flex;gap:20px;font-size:14px;font-weight:500}
.links a{color:var(--muted);text-decoration:none;transition:.2s;padding:24px 0;position:relative}
.links a:hover,.links a.active{color:var(--text)}
.links a.active::after{content:'';position:absolute;bottom:0;right:0;left:0;height:2px;background:var(--gold);border-radius:2px}
.btn{font-family:inherit;font-size:15px;font-weight:700;padding:13px 26px;border-radius:13px;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:9px;transition:.25s;text-decoration:none}
.btn-gold{background:linear-gradient(135deg,var(--gold),#8b1115);color:#fff;box-shadow:0 12px 32px -12px rgba(231,29,36,.8)}
.btn-gold:hover{transform:translateY(-2px)}
.btn-ghost{background:rgba(0,0,0,.04);color:var(--text);border:1px solid var(--line)}
.btn-ghost:hover{background:rgba(0,0,0,.08)}
.btn-sm{padding:9px 16px;font-size:13px;border-radius:10px}
.section-head{display:flex;align-items:baseline;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.section-head h2{font-size:clamp(22px,3vw,30px);font-weight:900;letter-spacing:-.5px}
.section-head .muted{color:var(--muted);font-size:14px}
footer{margin-top:70px;padding:34px 0;border-top:1px solid var(--line);color:var(--muted);font-size:13.5px;text-align:center}
.alert{padding:13px 18px;border-radius:12px;font-size:14px;font-weight:600;margin-bottom:18px}
.alert-error{background:rgba(231,29,36,.08);color:#b3121a;border:1px solid rgba(231,29,36,.25)}
.alert-success{background:rgba(16,150,90,.09);color:#0f7a4a;border:1px solid rgba(16,150,90,.25)}
</style>
@stack('head')
@stack('styles')
</head>
<body>
<header>
  <div class="container nav">
    <a class="logo" href="{{ route('movies.index') }}">
      <img src="{{ asset('logonew1.png') }}" alt="سينما بلاس" style="height:40px;width:auto;display:block">
    </a>
    <nav class="links">
      <a href="{{ route('site.home') }}">الرئيسية</a>
      <a href="{{ route('movies.index') }}" class="@yield('nav_movies')">الأفلام</a>
      <a href="{{ route('site.games') }}">صالة الألعاب</a>
      <a href="{{ route('site.club') }}">النادي الاجتماعي</a>
      <a href="{{ route('site.cafe') }}">حجز مناسبات داخل مقهى الكلية</a>
    </nav>
    <a class="btn btn-ghost btn-sm" href="/admin" target="_blank">لوحة التحكم</a>
  </div>
</header>

<main>
@yield('content')
</main>

<footer>
  <div class="container">
    سينما بلاس — الكلية الدولي للغات · ليبيا، طرابلس / زناتة · 0913504510
  </div>
</footer>
@stack('scripts')
</body>
</html>
