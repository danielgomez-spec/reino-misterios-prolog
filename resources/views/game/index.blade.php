@extends('layouts.game')
@section('content')

<div class="game-page characters-page">
    <header class="page-header">
        <a href="{{ route('game.intro') }}" class="back-btn">← VOLVER</a>
        <h1 class="page-title">👥 PERSONAJES DEL REINO</h1>
        <a href="{{ route('game.missions') }}" class="next-btn">MISIONES →</a>
    </header>

    <div class="characters-grid">
@php
$avatarClases = [
    'Elara'    => 'avatar-elara',
    'Kael'     => 'avatar-kael',
    'Rin'      => 'avatar-rin',
    'Hercules' => 'avatar-hercules',
    'Sonya'    => 'avatar-sonya',
    'jax'      => 'avatar-jax',
];
$clases = [
    'Elara'    => 'Guerrera',
    'Kael'     => 'Arquero',
    'Rin'      => 'Hechicera',
    'Hercules' => 'Bárbaro',
    'Sonya'    => 'Asesina',
    'jax'      => 'Berseker',
];
$inventarios = [
    'Elara'    => [
        ['nombre'=>'espada',  'icono'=>'⚔️',  'stat'=>'⚔ 30 DMG', 'tipo'=>'Arma'],
        ['nombre'=>'escudo',  'icono'=>'🛡️', 'stat'=>'🛡 20 DEF', 'tipo'=>'Defensa'],
        ['nombre'=>'pocion',  'icono'=>'🧪',  'stat'=>'✨ 10 XP',  'tipo'=>'Soporte'],
    ],
    'Kael'     => [
        ['nombre'=>'arco',    'icono'=>'🏹',  'stat'=>'⚔ 25 DMG', 'tipo'=>'Arma'],
        ['nombre'=>'flechas', 'icono'=>'➶',   'stat'=>'✨ 3 XP',   'tipo'=>'Munición'],
    ],
    'Rin'      => [
        ['nombre'=>'varita',  'icono'=>'🪄',  'stat'=>'⚔ 35 DMG', 'tipo'=>'Arma'],
        ['nombre'=>'grimorio','icono'=>'📖',  'stat'=>'✨ 15 XP',  'tipo'=>'Soporte'],
        ['nombre'=>'pocion',  'icono'=>'🧪',  'stat'=>'✨ 10 XP',  'tipo'=>'Soporte'],
        ['nombre'=>'amuleto', 'icono'=>'📿',  'stat'=>'🛡 5 DEF',  'tipo'=>'Accesorio'],
    ],
    'Hercules' => [
        ['nombre'=>'hacha',   'icono'=>'🪓',  'stat'=>'⚔ 45 DMG', 'tipo'=>'Arma'],
        ['nombre'=>'escudo',  'icono'=>'🛡️', 'stat'=>'🛡 20 DEF', 'tipo'=>'Defensa'],
    ],
    'Sonya'    => [
        ['nombre'=>'daga',    'icono'=>'🗡️', 'stat'=>'⚔ 15 DMG', 'tipo'=>'Arma'],
        ['nombre'=>'pocion',  'icono'=>'🧪',  'stat'=>'✨ 10 XP',  'tipo'=>'Soporte'],
    ],
    'jax'      => [
        ['nombre'=>'garrote', 'icono'=>'🪵',  'stat'=>'⚔ 10 DMG', 'tipo'=>'Arma'],
    ],
];
@endphp

        @foreach($personajesData as $p)
        <div class="char-card" id="card-{{ $p['nombre'] }}" onclick="openCharacterModal('{{ $p['nombre'] }}')">

            <div class="char-avatar-wrap">
                <div class="char-avatar {{ $avatarClases[$p['nombre']] ?? 'avatar-default' }}">
                    <div class="avatar-sprite"></div>
                    <div class="avatar-shadow"></div>
                </div>
            </div>

            <div class="char-info">
                <div class="char-name">{{ strtoupper($p['nombre']) }}</div>
                <div class="char-class">{{ $clases[$p['nombre']] ?? 'Aventurero' }}</div>

                <div class="char-stats">
                    <span class="stat-badge lv">LV {{ $p['nivel'] }}</span>
                    <span class="stat-badge hp">{{ $p['vida'] }} HP</span>
                </div>

                <div class="hp-track">
                    <div class="hp-fill" style="width: {{ min(100, ($p['vida']/120)*100) }}%"></div>
                </div>
                <div class="hp-label">
                    <span>0</span>
                    <span>{{ $p['vida'] }}/120</span>
                </div>

                <div class="card-actions">
                    <button class="btn-view-details" type="button" onclick="event.stopPropagation(); openCharacterModal('{{ $p['nombre'] }}')">
                        👁 Ver Detalles
                    </button>

                    <a href="{{ route('battle.setup') }}?fav={{ $p['nombre'] }}"
                       class="btn-fight" onclick="event.stopPropagation()">
                        ⚔ Combatir
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- MODAL DE DETALLES DEL PERSONAJE -->
    <div id="characterModal" class="character-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeCharacterModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-character-title">
                    <h2 id="modalCharName" class="modal-char-name">NOMBRE</h2>
                    <p id="modalCharClass" class="modal-char-class">Clase</p>
                </div>
                <button class="modal-close" onclick="closeCharacterModal()">✕</button>
            </div>

            <div class="modal-body">
                <!-- Sección: Avatar y Stats principales -->
                <div class="modal-section modal-avatar-section">
                    <div id="modalCharAvatar" class="modal-avatar"></div>
                    <div class="modal-main-stats">
                        <div class="stat-row">
                            <span class="stat-label">NIVEL:</span>
                            <span id="modalCharLevel" class="stat-value">1</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">VIDA:</span>
                            <span id="modalCharLife" class="stat-value">100</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">EXPERIENCIA:</span>
                            <span id="modalCharXp" class="stat-value">0</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">DAÑO:</span>
                            <span id="modalCharDamage" class="stat-value">15</span>
                        </div>
                    </div>
                </div>

                <!-- Sección: Inventario completo -->
                <div class="modal-section modal-inventory-section">
                    <h3 class="modal-section-title">🎒 INVENTARIO COMPLETO</h3>
                    <div id="modalInventoryGrid" class="modal-inventory-grid">
                        <!-- Items del inventario se generarán aquí -->
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a href="" id="modalFightBtn" class="btn-fight-modal">⚔ COMBATIR CON ESTE HÉROE</a>
                <button class="btn-modal-close" onclick="closeCharacterModal()">CERRAR</button>
            </div>
        </div>
    </div>

    <div class="page-actions">
        <a href="{{ route('battle.setup') }}" class="btn-primary-large">
            ⚔ IR A COMBATE
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const PERSONAJES = @json($personajesData);
    const INVENTARIOS = @json($inventarios);
    const CLASES = @json($clases);
    const AVATARCLASES = @json($avatarClases);
    
    function openCharacterModal(characterName) {
        const modal = document.getElementById('characterModal');
        const char = PERSONAJES.find(p => p.nombre === characterName);
        
        if (!char) return;
        
        // Actualizar información del personaje
        document.getElementById('modalCharName').textContent = characterName.toUpperCase();
        document.getElementById('modalCharClass').textContent = CLASES[characterName] || 'Aventurero';
        document.getElementById('modalCharLevel').textContent = char.nivel;
        document.getElementById('modalCharLife').textContent = char.vida + '/120';
        document.getElementById('modalCharXp').textContent = char.xp || 0;
        document.getElementById('modalCharDamage').textContent = char.damage || '15';
        
        // Actualizar botón de combate
        document.getElementById('modalFightBtn').href = "{{ route('battle.setup') }}?fav=" + characterName;
        
        // Generar inventario
        const inventoryGrid = document.getElementById('modalInventoryGrid');
        inventoryGrid.innerHTML = '';
        
        const items = INVENTARIOS[characterName] || [];
        if (items.length === 0) {
            inventoryGrid.innerHTML = '<p class="no-items">Sin inventario</p>';
        } else {
            items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'modal-inventory-item';
                itemDiv.innerHTML = `
                    <div class="item-icon">${item.icono}</div>
                    <div class="item-name">${item.nombre}</div>
                    <div class="item-stat">${item.stat}</div>
                    <div class="item-type">${item.tipo}</div>
                `;
                inventoryGrid.appendChild(itemDiv);
            });
        }
        
        // Mostrar modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeCharacterModal() {
        const modal = document.getElementById('characterModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Cerrar modal al presionar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCharacterModal();
        }
    });
</script>
@endpush
