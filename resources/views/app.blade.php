<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title inertia>Grillstone</title>

  <!-- Template assets -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  @routes

  <!-- Tailwind CDN (temp to prove styling works) -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome (icons) -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Template CSS (global) -->
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
  * { font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto,
      'Apple Color Emoji','Segoe UI Emoji'; }

  .gradient-bg {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 25%, #dc2626 50%, #92400e 75%, #451a03 100%);
  }
  .glass-effect {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  .product-card { transition: all .3s cubic-bezier(0.4,0,0.2,1); }
  .product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04);
  }
  .cart-item { animation: slideIn .3s ease-out; }
  @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
  .notification { position: fixed; top: 20px; right: 20px; z-index: 1000; transform: translateX(100%); transition: transform .3s ease; }
  .notification.show { transform: translateX(0); }
  .modal { backdrop-filter: blur(10px); }
  .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>


  @vite('resources/js/app.ts')
  @inertiaHead
</head>
<body class="min-h-screen bg-gray-50">
  @inertia
</body>
</html>
