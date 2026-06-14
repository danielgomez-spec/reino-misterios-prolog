% ============================================
% BASE DE CONOCIMIENTO — El Reino de los Misterios
% Archivo: prolog/Juego.pl
% ============================================

% ── PERSONAJES ────────────────────────────────
personaje('Elara', 5, 100).
personaje('Kael', 3, 80).
personaje('Rin', 7, 120).
personaje('Hercules', 6, 110).
personaje('Sonya', 4, 90).
personaje('jax', 2, 75).

% ── MISIONES ──────────────────────────────────
mision(m1, 'Bosque de Sombras', 2, 50).
mision(m2, 'Cueva del Dragon', 5, 120).
mision(m3, 'Torre Arcana', 7, 200).

% ── INVENTARIOS ───────────────────────────────
inventario('Elara',    [espada, escudo, pocion]).
inventario('Kael',     [arco, flechas]).
inventario('Rin',      [varita, grimorio, pocion, amuleto]).
inventario('Hercules', [hacha, escudo]).
inventario('Sonya',    [daga, pocion]).
inventario('jax',      [garrote]).

% ── REQUERIMIENTOS DE MISIONES ────────────────
requiere(m2, escudo).
requiere(m2, pocion).
requiere(m3, grimorio).
requiere(m3, pocion).

% ── ENEMIGOS ──────────────────────────────────
enemigo(caballero_oscuro, 40).
enemigo(mago, 90).
enemigo(rey_esqueleto, 250).

% ── FUERZA DE ARMAS ───────────────────────────
fuerza_arma(espada,   30).
fuerza_arma(arco,     25).
fuerza_arma(varita,   35).
fuerza_arma(hacha,    45).
fuerza_arma(daga,     15).
fuerza_arma(garrote,  10).
fuerza_arma(escudo,    0).
fuerza_arma(pocion,    0).
fuerza_arma(flechas,   0).
fuerza_arma(grimorio,  0).
fuerza_arma(amuleto,   0).

% ============================================
% REGLAS DE COMBATE
% ============================================

% Obtener el daño de un personaje con su mejor arma
obtener_danio_personaje(Personaje, Arma, Danio) :-
    inventario(Personaje, Inventario),
    member(Arma, Inventario),
    fuerza_arma(Arma, Danio),
    Danio > 0.

% Ataque individual
ejecutar_ataque_individual(Personaje, Arma, Enemigo, Mensaje) :-
    enemigo(Enemigo, VidaEnemigo),
    obtener_danio_personaje(Personaje, Arma, Danio),
    ( Danio >= VidaEnemigo ->
        Resultado = 'y logra derrotarlo solo.'
    ;
        Resultado = 'pero NO es suficiente para derrotarlo solo.'
    ),
    atomic_list_concat([Personaje, ' ataca al ', Enemigo,
        ' (Vida: ', VidaEnemigo, ') con ', Arma,
        ' haciendo ', Danio, ' de danio ', Resultado], Mensaje).

% Procesar ataque de grupo (recursivo)
procesar_ataque_grupo([], 0, []).
procesar_ataque_grupo([P|Ps], DanioTotal, [DetalleP|DetallesResto]) :-
    obtener_danio_personaje(P, Arma, DanioP),
    atomic_list_concat([P, ' con ', Arma, ' (', DanioP, ')'], DetalleP),
    procesar_ataque_grupo(Ps, DanioResto, DetallesResto),
    DanioTotal is DanioP + DanioResto.

% Ataque grupal
ejecutar_ataque_grupal(ListaPersonajes, Enemigo, Mensaje) :-
    enemigo(Enemigo, VidaEnemigo),
    procesar_ataque_grupo(ListaPersonajes, DanioTotal, ListaDetalles),
    formatear_nombres_rec(ListaDetalles, DetalleGrupoFormateado),
    ( DanioTotal >= VidaEnemigo ->
        Resultado = 'LOGRAN derrotarlo en equipo!'
    ;
        Resultado = 'NO logran derrotarlo.'
    ),
    atomic_list_concat(['El grupo [', DetalleGrupoFormateado,
        '] atacan al ', Enemigo, ' (Vida: ', VidaEnemigo,
        ') sumando ', DanioTotal, ' de danio. ', Resultado], Mensaje).

% ============================================
% REGLAS ARITMÉTICAS Y RECURSIVAS
% ============================================

puede_aceptar(Personaje, ID_Mision) :-
    personaje(Personaje, Nivel, _),
    mision(ID_Mision, _, Dificultad, _),
    Nivel >= Dificultad.

xp_acumulada(0, 0).
xp_acumulada(N, Total) :-
    N > 0,
    N1 is N - 1,
    xp_acumulada(N1, Prev),
    Total is Prev + (30 * N).

tiene_requerido(Personaje, Objeto) :-
    inventario(Personaje, Lista),
    member(Objeto, Lista).

mismo_nivel(P1, P2) :-
    personaje(P1, N, _),
    personaje(P2, N, _),
    P1 \== P2.

es_balanceado(Personaje) :-
    personaje(Personaje, _, Vida),
    Vida =:= 100.

fusionar_equipo(P1, P2, EquipoFusionado) :-
    inventario(P1, L1),
    inventario(P2, L2),
    append(L1, L2, EquipoFusionado).

formatear_nombres_rec([P], P).
formatear_nombres_rec([P1, P2], Resultado) :-
    atomic_list_concat([P1, ' y ', P2], Resultado).
formatear_nombres_rec([P|Ps], Resultado) :-
    Ps = [_, _|_],
    formatear_nombres_rec(Ps, Resto),
    atomic_list_concat([P, ', ', Resto], Resultado).

todos_pueden_aceptar([], _).
todos_pueden_aceptar([P|Ps], MisionID) :-
    puede_aceptar(P, MisionID),
    todos_pueden_aceptar(Ps, MisionID).

generar_reporte_grupal(ListaPersonajes, MisionID, Mensaje) :-
    todos_pueden_aceptar(ListaPersonajes, MisionID),
    mision(MisionID, NombreMision, _, XP),
    formatear_nombres_rec(ListaPersonajes, Sujetos),
    atomic_list_concat([Sujetos, ' pueden completar ',
        NombreMision, ' y ganar ', XP, ' XP'], Mensaje).

% ── RESULTADO DE COMBATE ──────────────────────
resultado_combate(Personaje, Enemigo, Resultado) :-
    obtener_danio_personaje(Personaje, _, Danio),
    enemigo(Enemigo, Vida),
    ( Danio >= Vida -> Resultado = gana ; Resultado = pierde ).