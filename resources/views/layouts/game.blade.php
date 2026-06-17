<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>⚔ El Reino de los M!ster!os</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Rajdhani:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/game.css') }}">
</head>
<body class="page-transition">
    <div id="game-wrap">
        @yield('content')
    </div>

    <!-- Modal inventario global -->
    <div id="modal-inventario" class="modal hidden" role="dialog" aria-modal="true">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modal-title">Inventario</h3>
                <button onclick="cerrarModal()" class="modal-close" type="button">✕</button>
            </div>
            <div id="modal-content"></div>
        </div>
    </div>

    {{-- CSRF disponible globalmente ANTES de cargar game.js --}}
    <script>
        window.CSRF_TOKEN = '{{ csrf_token() }}';

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
        });

        // Transición suave entre páginas
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript')) return;
            if (link.hostname !== window.location.hostname) return;
            e.preventDefault();
            document.body.classList.add('page-exit');
            setTimeout(function() { window.location = link.href; }, 280);
        });
    </script>

    <script src="{{ asset('js/game.js') }}"></script>
    @stack('scripts')
</body>
</html>