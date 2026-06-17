@extends('layouts.game')
@section('content')

<div class="intro-page">
    <!-- Fondo: castillo con antorchas animadas -->
    <div class="castle-bg">
        <div class="sky"></div>
        <div class="stars">
            @for($i=0;$i<60;$i++)
            <div class="star" style="
                left:{{ rand(0,100) }}%;
                top:{{ rand(0,50) }}%;
                animation-delay:{{ rand(0,30)/10 }}s;
                width:{{ rand(1,3) }}px;height:{{ rand(1,3) }}px
            "></div>
            @endfor
        </div>
        <div class="castle">
            <!-- Torre izquierda -->
            <div class="tower tower-left">
                <div class="battlements"><span></span><span></span><span></span></div>
                <div class="tower-body">
                    <div class="torch torch-left">
                        <div class="torch-stick"></div>
                        <div class="flame"><span></span><span></span><span></span></div>
                    </div>
                    <div class="window-slit"></div>
                </div>
            </div>
            <!-- Cuerpo central -->
            <div class="castle-center">
                <div class="battlements center-batt">
                    @for($i=0;$i<7;$i++)<span></span>@endfor
                </div>
                <div class="castle-wall">
                    <div class="gate">
                        <div class="gate-arch"></div>
                        <div class="portcullis">
                            @for($i=0;$i<4;$i++)<div class="pc-bar"></div>@endfor
                        </div>
                    </div>
                    <div class="torch torch-center-l">
                        <div class="torch-stick"></div>
                        <div class="flame"><span></span><span></span><span></span></div>
                    </div>
                    <div class="torch torch-center-r">
                        <div class="torch-stick"></div>
                        <div class="flame"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>
            <!-- Torre derecha -->
            <div class="tower tower-right">
                <div class="battlements"><span></span><span></span><span></span></div>
                <div class="tower-body">
                    <div class="torch torch-right">
                        <div class="torch-stick"></div>
                        <div class="flame"><span></span><span></span><span></span></div>
                    </div>
                    <div class="window-slit"></div>
                </div>
            </div>
        </div>
        <div class="ground"></div>
    </div>

    <!-- Contenido central -->
    <div class="intro-content">
        <div class="intro-logo">
            <div class="logo-line1">⚔ EL REINO DE</div>
            <div class="logo-line2">LOS M!STER!OS</div>
        </div>

        <a href="{{ route('game.index') }}" class="btn-play">
            <span class="btn-play-inner">▶ A JUGAR</span>
        </a>

        <div class="intro-lore">
            <p>Las puertas del reino se abren ante ti.<br>
            Elige tu camino y descubre los secretos ocultos.<br>
            <span class="lore-highlight">Solo el más valiente escribirá la historia.</span></p>
        </div>
    </div>
</div>
@endsection
