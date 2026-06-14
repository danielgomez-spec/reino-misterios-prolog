@extends('layouts.game')
@section('content')
<div class="game-page battle-page">
    <div class="battle-stage-bg" id="battle-bg"></div>

    <header class="battle-header">
        <div class="battle-title">ARENA DE COMBATE</div>
        <div class="battle-mode-badge">{{ strtoupper($modo) }}</div>
    </header>

    <div class="battle-arena">
        <!-- Panel héroes -->
        <div class="heroes-panel">
            <div class="panel-label">HÉROES</div>
            @php
            $avatarClases = [
                'Elara'    => 'avatar-elara',
                'Kael'     => 'avatar-kael',
                'Rin'      => 'avatar-rin',
                'Hercules' => 'avatar-hercules',
                'Sonya'    => 'avatar-sonya',
                'jax'      => 'avatar-jax',
            ];
            $statsData = [
                'Elara'    => [5,  100],
                'Kael'     => [3,  80],
                'Rin'      => [7,  120],
                'Hercules' => [6,  110],
                'Sonya'    => [4,  90],
                'jax'      => [2,  75],
            ];
            @endphp

            @foreach($personajes as $p)
                @php [$nivel, $vida] = $statsData[$p] ?? [1, 100]; @endphp
                <div class="battle-char-card" id="hero-{{ $p }}">
                    <div class="battle-avatar {{ $avatarClases[$p] ?? '' }} fighting">
                        <div class="avatar-sprite"></div>
                    </div>
                    <div class="battle-char-name">{{ strtoupper($p) }}</div>
                    <div class="battle-hp-bar-wrap">
                        <div class="battle-hp-bar green"
                             id="hp-bar-{{ $p }}"
                             style="width:100%"
                             data-max="{{ $vida }}">
                        </div>
                    </div>
                    <div class="battle-hp-text" id="hp-text-{{ $p }}">
                        {{ $vida }}/{{ $vida }} HP
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Centro VS -->
        <div class="battle-center">
            <div class="vs-glow">VS</div>
            <div id="battle-log" class="battle-log">
                <p class="log-hint">Presiona ATACAR<br>para comenzar</p>
            </div>
        </div>

        <!-- Panel enemigo -->
        <div class="enemy-panel">
            <div class="panel-label">ENEMIGO</div>
            <div class="battle-enemy-card" id="enemy-card">
                <div class="battle-enemy-sprite enemy-{{ $enemigo }}" id="enemy-sprite">
                    <div class="enemy-art"></div>
                </div>
                <div class="battle-enemy-name">
                    {{ strtoupper(str_replace('_', ' ', $enemigo)) }}
                </div>
                <div class="battle-hp-bar-wrap">
                    <div class="battle-hp-bar red"
                         id="hp-bar-enemy"
                         style="width:100%"
                         data-max="{{ $enemigoActual['vida'] ?? 100 }}">
                    </div>
                </div>
                <div class="battle-hp-text" id="hp-text-enemy">
                    {{ $enemigoActual['vida'] ?? '?' }}/{{ $enemigoActual['vida'] ?? '?' }} HP
                </div>
            </div>
        </div>
    </div>

    {{-- Controles centrados debajo de la arena --}}
    <div class="battle-controls battle-controls-centered">
        <button class="btn-attack-big" id="btn-attack" onclick="ejecutarCombate()" type="button">
            <span class="btn-icon">⚔</span>
            <span class="btn-text">¡ATACAR!</span>
        </button>
        <div id="prolog-badge" class="prolog-badge hidden">
            <span class="prolog-label">PROLOG:</span>
            <code id="prolog-query-text"></code>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const MODO               = '{{ $modo }}';
    const PERSONAJES_BATALLA = @json($personajes);
    const ENEMIGO_ID         = '{{ $enemigo }}';
    const ENEMIGO_VIDA       = {{ $enemigoActual['vida'] ?? 100 }};
</script>
@endpush
