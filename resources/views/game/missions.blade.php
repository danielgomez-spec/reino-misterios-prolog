@extends('layouts.game')
@section('content')

<div class="game-page missions-page">
    <header class="page-header">
        <a href="{{ route('game.index') }}" class="back-btn">← PERSONAJES</a>
        <h1 class="page-title">🗺 MISIONES DISPONIBLES</h1>
        <a href="{{ route('battle.setup') }}" class="next-btn">COMBATE →</a>
    </header>

    @php
    $stageArt = [
        'm1' => ['icon'=>'🌲','clase'=>'stage-bosque','desc'=>'Nivel 2+','bg'=>'#0a1a0a'],
        'm2' => ['icon'=>'🐉','clase'=>'stage-cueva', 'desc'=>'Nivel 5+','bg'=>'#1a0a00'],
        'm3' => ['icon'=>'🗼','clase'=>'stage-torre',  'desc'=>'Nivel 7+','bg'=>'#0a0a1a'],
    ];
    @endphp

    <div class="missions-grid">
        @foreach($misionesData as $m)
        @php $art = $stageArt[$m['id']] ?? $stageArt['m1']; @endphp
        <div class="mission-card {{ $art['clase'] }}" data-id="{{ $m['id'] }}">
            <div class="mission-bg-art">
                <div class="mission-icon-large">{{ $art['icon'] }}</div>
            </div>
            <div class="mission-info">
                <div class="mission-name">{{ strtoupper($m['nombre']) }}</div>
                <div class="mission-badges">
                    <span class="badge-dif">DIF {{ $m['dificultad'] }}</span>
                    <span class="badge-xp">{{ $m['xp'] }} XP</span>
                    <span class="badge-req">{{ $art['desc'] }}</span>
                </div>
                <button class="btn-check" onclick="verificarMision('{{ $m['id'] }}', '{{ $m['nombre'] }}', this)">
                    🔍 ¿Quién puede ir?
                </button>
                <div class="mission-result-box hidden" id="result-{{ $m['id'] }}">
                    <div class="result-section">
                        <span class="result-label">Por nivel:</span>
                        <div class="result-chars" id="nivel-{{ $m['id'] }}"></div>
                    </div>
                    <div class="result-section">
                        <span class="result-label">Con ítems completos:</span>
                        <div class="result-chars" id="items-{{ $m['id'] }}"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Selector personaje → misiones -->
    <div class="char-missions-section">
        <h2 class="section-title">👤 MISIONES POR PERSONAJE</h2>
        <div class="char-selector">
            <select id="char-select" onchange="filtrarMisionesPersonaje(this.value)" class="game-select">
                <option value="">— Selecciona un personaje —</option>
                @foreach($personajesData as $p)
                <option value="{{ $p['nombre'] }}">{{ $p['nombre'] }} (Nv {{ $p['nivel'] }})</option>
                @endforeach
            </select>
        </div>
        <div id="char-missions-result" class="char-missions-result"></div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const MISIONES    = @json($misionesData);
const PERSONAJES  = @json($personajesData);
</script>
@endpush