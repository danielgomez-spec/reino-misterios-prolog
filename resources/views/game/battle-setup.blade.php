@extends('layouts.game')
@section('content')

<div class="game-page setup-page">
    <header class="page-header">
        <a href="{{ route('game.missions') }}" class="back-btn">← MISIONES</a>
        <h1 class="page-title">⚔ CONFIGURAR COMBATE</h1>
    </header>

    <form id="battle-form" action="{{ route('game.battle') }}" method="GET">

        <!-- PASO 1: Modo de combate -->
        <div class="setup-step" id="step-modo">
            <div class="step-header">
                <span class="step-num">01</span>
                <span class="step-title">MODO DE COMBATE</span>
            </div>
            <div class="mode-cards">
                <label class="mode-card" id="mode-individual">
                    <input type="radio" name="modo" value="individual" checked hidden>
                    <div class="mode-icon">🗡️</div>
                    <div class="mode-name">INDIVIDUAL</div>
                    <div class="mode-desc">Un héroe vs el enemigo.<br>Máximo daño concentrado.</div>
                </label>
                <label class="mode-card" id="mode-grupal">
                    <input type="radio" name="modo" value="grupal" hidden>
                    <div class="mode-icon">👥</div>
                    <div class="mode-name">GRUPAL</div>
                    <div class="mode-desc">Equipo completo ataca.<br>Daño combinado de todos.</div>
                </label>
            </div>
        </div>

        <!-- PASO 2: Selección de personajes -->
        <div class="setup-step" id="step-heroes">
            <div class="step-header">
                <span class="step-num">02</span>
                <span class="step-title">SELECCIONA TUS HÉROES</span>
                <span class="step-hint" id="heroes-hint">Selecciona 1 héroe</span>
            </div>
            <div class="heroes-select-grid">
                @php
                $avatarClases = ['Elara'=>'avatar-elara','Kael'=>'avatar-kael','Rin'=>'avatar-rin',
                                 'Hercules'=>'avatar-hercules','Sonya'=>'avatar-sonya','jax'=>'avatar-jax'];
                @endphp
                @foreach($personajesData as $p)
                <div class="hero-select-card" data-nombre="{{ $p['nombre'] }}"
                     onclick="toggleHero('{{ $p['nombre'] }}', this)">
                    <div class="hero-mini-avatar {{ $avatarClases[$p['nombre']] ?? '' }}">
                        <div class="avatar-sprite"></div>
                    </div>
                    <div class="hero-mini-name">{{ strtoupper($p['nombre']) }}</div>
                    <div class="hero-mini-stats">LV{{ $p['nivel'] }} · {{ $p['vida'] }}HP</div>
                    <div class="hero-check">✓</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- PASO 3: Escenario -->
        <div class="setup-step" id="step-stage">
            <div class="step-header">
                <span class="step-num">03</span>
                <span class="step-title">ESCOGE EL ESCENARIO</span>
            </div>
            <div class="stages-grid">
                <div class="stage-card stage-bosque" onclick="selectStage('bosque_sombras', this)">
                    <div class="stage-art">🌲🌙🌲</div>
                    <div class="stage-name">BOSQUE DE SOMBRAS</div>
                    <div class="stage-mood">Dificultad: Fácil</div>
                </div>
                <div class="stage-card stage-cueva" onclick="selectStage('cueva_dragon', this)">
                    <div class="stage-art">🐉🔥⛏</div>
                    <div class="stage-name">CUEVA DEL DRAGÓN</div>
                    <div class="stage-mood">Dificultad: Media</div>
                </div>
                <div class="stage-card stage-torre" onclick="selectStage('torre_arcana', this)">
                    <div class="stage-art">🗼⚡🌑</div>
                    <div class="stage-name">TORRE ARCANA</div>
                    <div class="stage-mood">Dificultad: Difícil</div>
                </div>
            </div>
        </div>

        <!-- PASO 4: Selección enemigo -->
        <div class="setup-step" id="step-enemy">
            <div class="step-header">
                <span class="step-num">04</span>
                <span class="step-title">ELIGE TU ENEMIGO</span>
            </div>
            <div class="enemies-select-grid">
                @foreach($enemigosData as $e)
                <div class="enemy-select-card" data-id="{{ $e['id'] }}"
                     onclick="selectEnemy('{{ $e['id'] }}', this)">
                    <div class="enemy-select-sprite enemy-{{ $e['id'] }}">
                        <div class="enemy-art"></div>
                    </div>
                    <div class="enemy-select-name">{{ strtoupper($e['nombre']) }}</div>
                    <div class="enemy-select-hp">
                        <div class="mini-hp-bar">
                            <div class="mini-hp-fill" style="width:{{ min(100, ($e['vida']/250)*100) }}%"></div>
                        </div>
                        <span>{{ $e['vida'] }} HP</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Campos hidden -->
        <input type="hidden" id="selected-enemy-input" name="enemigo" value="">
        <div id="heroes-hidden-inputs"></div>

        <div class="setup-actions">
            <div id="setup-summary" class="setup-summary hidden">
                <div id="summary-text"></div>
            </div>
            <button type="button" id="btn-start-battle" class="btn-start-battle" onclick="iniciarCombate()">
                ⚔ ¡COMENZAR COMBATE!
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
const PERSONAJES = @json($personajesData);
const ENEMIGOS   = @json($enemigosData);
const FAV        = '{{ request()->query("fav", "") }}';
</script>
@endpush