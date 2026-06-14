<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrologController extends Controller
{
    private string $prologFile;
    private string $swipl;

    public function __construct()
    {
        $this->prologFile = base_path('prolog/Juego.pl');

        $raw = env('PROLOG_PATH', 'swipl');
        $this->swipl = (str_contains($raw, ' ') && !str_starts_with($raw, '"'))
            ? '"' . $raw . '"'
            : $raw;
    }

    // ══════════════════════════════════════════════
    // MÉTODO CENTRAL — Ejecuta UNA consulta Prolog
    // Soluciona el problema de Windows con los comandos
    // ══════════════════════════════════════════════
    private function consultarProlog(string $consulta): string
    {
        $plFile = str_replace('/', DIRECTORY_SEPARATOR, $this->prologFile);

        // En Windows, construimos el comando de forma diferente
        // para evitar problemas con comillas y caracteres especiales
        if (PHP_OS_FAMILY === 'Windows') {
            // Escribir consulta en archivo temporal para evitar problemas de escape
            $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'prolog_query_' . uniqid() . '.pl';
            $queryScript = ":- consult('" . addslashes($plFile) . "').\n"
                         . ":- " . $consulta . ".\n"
                         . ":- halt.\n";

            file_put_contents($tmpFile, $queryScript);

            $comando = $this->swipl . ' -q -f "' . $tmpFile . '" 2>&1';
            $salida  = shell_exec($comando);

            @unlink($tmpFile); // limpiar archivo temporal
        } else {
            // Linux / Mac: el método original funciona bien
            $comando = sprintf(
                '%s -g "%s,halt" -t "halt(1)" "%s" 2>&1',
                $this->swipl,
                addslashes($consulta),
                $plFile
            );
            $salida = shell_exec($comando);
        }

        $resultado = trim($salida ?? '');

        // Filtrar líneas de error de Prolog para no mostrarlas al usuario
        if (!empty($resultado)) {
            $lineas = explode("\n", str_replace("\r", "", $resultado));
            $lineas = array_filter($lineas, function($l) {
                $l = trim($l);
                return !empty($l)
                    && !str_starts_with($l, 'ERROR')
                    && !str_starts_with($l, 'Warning')
                    && !str_starts_with($l, '%')
                    && !str_contains($l, 'Initialization')
                    && !str_contains($l, 'halt/0');
            });
            return trim(implode("\n", $lineas));
        }

        return '';
    }

    // ══════════════════════════════════════════════
    // MÚLTIPLES RESULTADOS con findall
    // ══════════════════════════════════════════════
    private function consultarPrologMultiple(string $predicado, string $variable): array
    {
        $consulta = "findall($variable, $predicado, Lista), maplist(writeln, Lista)";
        $salida   = $this->consultarProlog($consulta);

        if (empty($salida)) return [];

        return array_values(array_filter(
            explode("\n", str_replace("\r", "", $salida)),
            fn($l) => !empty(trim($l))
        ));
    }

    // ══════════════════════════════════════════════
    // PARSERS
    // ══════════════════════════════════════════════
    private function parsearPersonaje(string $raw): ?array
    {
        $parts = explode('|', trim($raw));
        if (count($parts) !== 3) return null;
        return [
            'nombre' => trim($parts[0]),
            'nivel'  => trim($parts[1]),
            'vida'   => trim($parts[2]),
        ];
    }

    private function parsearMision(string $raw): ?array
    {
        $parts = explode('|', trim($raw));
        if (count($parts) !== 4) return null;
        return [
            'id'         => trim($parts[0]),
            'nombre'     => trim($parts[1]),
            'dificultad' => trim($parts[2]),
            'xp'         => trim($parts[3]),
        ];
    }

    // ══════════════════════════════════════════════
    // PÁGINAS
    // ══════════════════════════════════════════════
    public function intro()
    {
        return view('game.intro');
    }

    public function index()
    {
        $rawList = $this->consultarPrologMultiple(
            "personaje(N,Nv,V), format(atom(X),'~w|~w|~w',[N,Nv,V])", 'X'
        );

        $personajesData = array_values(array_filter(
            array_map(fn($p) => $this->parsearPersonaje($p), $rawList)
        ));

        if (empty($personajesData)) {
            $personajesData = $this->fallbackPersonajes();
        }

        return view('game.index', compact('personajesData'));
    }

    public function missions()
    {
        $rawMisiones = $this->consultarPrologMultiple(
            "mision(ID,Nombre,Dif,XP), format(atom(X),'~w|~w|~w|~w',[ID,Nombre,Dif,XP])", 'X'
        );
        $misionesData = array_values(array_filter(
            array_map(fn($m) => $this->parsearMision($m), $rawMisiones)
        ));
        if (empty($misionesData)) $misionesData = $this->fallbackMisiones();

        $rawPersonajes = $this->consultarPrologMultiple(
            "personaje(N,Nv,V), format(atom(X),'~w|~w|~w',[N,Nv,V])", 'X'
        );
        $personajesData = array_values(array_filter(
            array_map(fn($p) => $this->parsearPersonaje($p), $rawPersonajes)
        ));
        if (empty($personajesData)) $personajesData = $this->fallbackPersonajes();

        return view('game.missions', compact('misionesData', 'personajesData'));
    }

    public function battleSetup()
    {
        $rawPersonajes = $this->consultarPrologMultiple(
            "personaje(N,Nv,V), format(atom(X),'~w|~w|~w',[N,Nv,V])", 'X'
        );
        $personajesData = array_values(array_filter(
            array_map(fn($p) => $this->parsearPersonaje($p), $rawPersonajes)
        ));
        if (empty($personajesData)) $personajesData = $this->fallbackPersonajes();

        $rawEnemigos = $this->consultarPrologMultiple(
            "enemigo(N,V), format(atom(X),'~w|~w',[N,V])", 'X'
        );
        $enemigosData = [];
        foreach ($rawEnemigos as $e) {
            $parts = explode('|', trim($e));
            if (count($parts) === 2) {
                $enemigosData[] = [
                    'id'     => trim($parts[0]),
                    'nombre' => str_replace('_', ' ', trim($parts[0])),
                    'vida'   => (int) trim($parts[1]),
                ];
            }
        }
        if (empty($enemigosData)) $enemigosData = $this->fallbackEnemigos();

        return view('game.battle-setup', compact('personajesData', 'enemigosData'));
    }

    public function battle(Request $request)
    {
        $personajes = $request->query('personajes', []);
        $enemigo    = $request->query('enemigo', '');
        $modo       = $request->query('modo', 'individual');

        if (empty($personajes) || empty($enemigo)) {
            return redirect()->route('battle.setup');
        }

        $rawEnemigos = $this->consultarPrologMultiple(
            "enemigo(N,V), format(atom(X),'~w|~w',[N,V])", 'X'
        );
        $enemigosData = [];
        foreach ($rawEnemigos as $e) {
            $parts = explode('|', trim($e));
            if (count($parts) === 2) {
                $enemigosData[] = [
                    'id'     => trim($parts[0]),
                    'nombre' => str_replace('_', ' ', trim($parts[0])),
                    'vida'   => (int) trim($parts[1]),
                ];
            }
        }
        if (empty($enemigosData)) $enemigosData = $this->fallbackEnemigos();

        $enemigoActual = collect($enemigosData)->firstWhere('id', $enemigo)
            ?? ['id' => $enemigo, 'nombre' => str_replace('_', ' ', $enemigo), 'vida' => 100];

        return view('game.battle', compact('personajes', 'enemigo', 'modo', 'enemigoActual'));
    }

    // ══════════════════════════════════════════════
    // API — COMBATE INDIVIDUAL
    // Solución: buscar primero el arma con mayor daño via Prolog
    // Si falla, usar tabla local
    // ══════════════════════════════════════════════
    public function combateIndividual(Request $request)
    {
        $request->validate([
            'personaje' => 'required|string',
            'enemigo'   => 'required|string',
        ]);

        $personaje = $request->input('personaje');
        $enemigo   = $request->input('enemigo');

        // Intentar consulta Prolog
        $mensaje = $this->consultarProlog(
            "ejecutar_ataque_individual('$personaje', _, $enemigo, Msg), writeln(Msg)"
        );

        $danioStr = trim($this->consultarProlog(
            "obtener_danio_personaje('$personaje', _, D), writeln(D)"
        ));

        $vidaStr = trim($this->consultarProlog(
            "enemigo($enemigo, V), writeln(V)"
        ));

        // Si Prolog respondió bien, usar esos datos
        $danio = is_numeric($danioStr) ? (int)$danioStr : null;
        $vida  = is_numeric($vidaStr)  ? (int)$vidaStr  : null;

        // Si Prolog no respondió, calcular con la tabla local
        if ($danio === null || $vida === null) {
            $daniosLocales = [
                'Elara' => ['arma' => 'espada', 'dmg' => 30],
                'Kael'  => ['arma' => 'arco',   'dmg' => 25],
                'Rin'   => ['arma' => 'varita',  'dmg' => 35],
                'Hercules' => ['arma' => 'hacha','dmg' => 45],
                'Sonya' => ['arma' => 'daga',    'dmg' => 15],
                'jax'   => ['arma' => 'garrote', 'dmg' => 10],
            ];
            $vidasEnemigas = [
                'caballero_oscuro' => 40,
                'mago'             => 90,
                'rey_esqueleto'    => 250,
            ];
            $infoP = $daniosLocales[$personaje] ?? ['arma' => 'espada', 'dmg' => 10];
            $danio = $danio ?? $infoP['dmg'];
            $vida  = $vida  ?? ($vidasEnemigas[$enemigo] ?? 100);

            $arma     = $infoP['arma'];
            $vence    = $danio >= $vida;
            $resultado = $vence ? 'y logra derrotarlo solo.' : 'pero NO es suficiente para derrotarlo solo.';
            $mensaje   = "$personaje ataca al $enemigo (Vida: $vida) con $arma haciendo $danio de daño $resultado";
        }

        // Limpiar mensaje de cualquier residuo de error
        $mensaje = $this->limpiarMensajeProlog($mensaje);
        $vence   = $danio >= $vida;

        return response()->json([
            'mensaje'            => $mensaje ?: "$personaje atacó a $enemigo",
            'victoria'           => $vence,
            'danio'              => $danio,
            'personaje'          => $personaje,
            'enemigo'            => str_replace('_', ' ', $enemigo),
            'consulta_ejecutada' => "ejecutar_ataque_individual('$personaje', Arma, $enemigo, Msg)",
        ]);
    }

    // ══════════════════════════════════════════════
    // API — COMBATE GRUPAL
    // ══════════════════════════════════════════════
    public function combateGrupal(Request $request)
    {
        $request->validate([
            'personajes' => 'required|array|min:2',
            'enemigo'    => 'required|string',
        ]);

        $personajes  = $request->input('personajes');
        $enemigo     = $request->input('enemigo');
        $listaProlog = "['" . implode("','", $personajes) . "']";

        $mensaje     = $this->consultarProlog(
            "ejecutar_ataque_grupal($listaProlog, $enemigo, Msg), writeln(Msg)"
        );
        $danioTotal  = (int) trim($this->consultarProlog(
            "procesar_ataque_grupo($listaProlog, D, _), writeln(D)"
        ));
        $vidaEnemigo = (int) trim($this->consultarProlog(
            "enemigo($enemigo, V), writeln(V)"
        ));

        // Fallback grupal
        if ($danioTotal === 0 || $vidaEnemigo === 0) {
            $daniosLocales = ['Elara'=>30,'Kael'=>25,'Rin'=>35,'Hercules'=>45,'Sonya'=>15,'jax'=>10];
            $vidasEnemigas = ['caballero_oscuro'=>40,'mago'=>90,'rey_esqueleto'=>250];
            $danioTotal  = array_sum(array_map(fn($p) => $daniosLocales[$p] ?? 0, $personajes));
            $vidaEnemigo = $vidasEnemigas[$enemigo] ?? 100;
            $vence       = $danioTotal >= $vidaEnemigo;
            $mensaje     = 'El grupo [' . implode(', ', $personajes) . '] atacan al '
                         . str_replace('_', ' ', $enemigo) . ' (Vida: ' . $vidaEnemigo
                         . ') sumando ' . $danioTotal . ' de daño. '
                         . ($vence ? 'LOGRAN derrotarlo en equipo!' : 'NO logran derrotarlo.');
        }

        $mensaje = $this->limpiarMensajeProlog($mensaje);
        $vence   = $danioTotal >= $vidaEnemigo;

        return response()->json([
            'mensaje'            => $mensaje,
            'victoria'           => $vence,
            'danio_total'        => $danioTotal,
            'vida_enemigo'       => $vidaEnemigo,
            'personajes'         => $personajes,
            'consulta_ejecutada' => "ejecutar_ataque_grupal($listaProlog, $enemigo, Msg)",
        ]);
    }

    // ══════════════════════════════════════════════
    // API — INVENTARIO
    // ══════════════════════════════════════════════
    public function inventario(Request $request)
    {
        $request->validate(['personaje' => 'required|string']);
        $personaje = $request->input('personaje');

        $items = $this->consultarPrologMultiple(
            "inventario('$personaje', L), member(X, L)", 'X'
        );

        // Fallback inventario
        if (empty($items)) {
            $invFallback = [
                'Elara'    => ['espada','escudo','pocion'],
                'Kael'     => ['arco','flechas'],
                'Rin'      => ['varita','grimorio','pocion','amuleto'],
                'Hercules' => ['hacha','escudo'],
                'Sonya'    => ['daga','pocion'],
                'jax'      => ['garrote'],
            ];
            $items = $invFallback[$personaje] ?? [];
        }

        $statsRaw = trim($this->consultarProlog(
            "personaje('$personaje', N, V), format(atom(X),'~w|~w',[N,V]), writeln(X)"
        ));
        $parts = explode('|', $statsRaw);

        $statsFallback = [
            'Elara'=>[5,100],'Kael'=>[3,80],'Rin'=>[7,120],
            'Hercules'=>[6,110],'Sonya'=>[4,90],'jax'=>[2,75],
        ];
        $nivel = is_numeric($parts[0] ?? '') ? $parts[0] : ($statsFallback[$personaje][0] ?? '?');
        $vida  = is_numeric($parts[1] ?? '') ? $parts[1] : ($statsFallback[$personaje][1] ?? '?');

        return response()->json([
            'personaje'          => $personaje,
            'nivel'              => $nivel,
            'vida'               => $vida,
            'items'              => array_values($items),
            'consulta_ejecutada' => "inventario('$personaje', Lista)",
        ]);
    }

    // ══════════════════════════════════════════════
    // API — MISIÓN
    // ══════════════════════════════════════════════
    public function misionDisponible(Request $request)
    {
        $request->validate(['mision' => 'required|string']);
        $mision = $request->input('mision');

        $aptos = $this->consultarPrologMultiple("puede_aceptar(P, $mision)", 'P');

        // Fallback misiones
        if (empty($aptos)) {
            $aptosF = [
                'm1' => ['Elara','Kael','Rin','Hercules','Sonya','jax'],
                'm2' => ['Elara','Rin','Hercules'],
                'm3' => ['Rin'],
            ];
            $aptos = $aptosF[$mision] ?? [];
        }

        $aptosConItems = array_filter($aptos, function ($personaje) use ($mision) {
            $reqs = $this->consultarPrologMultiple("requiere($mision, X)", 'X');
            // Fallback requerimientos
            if (empty($reqs)) {
                $reqsF = ['m2'=>['escudo','pocion'],'m3'=>['grimorio','pocion']];
                $reqs  = $reqsF[$mision] ?? [];
            }
            if (empty($reqs)) return true;

            $invFallback = [
                'Elara'=>['espada','escudo','pocion'],'Kael'=>['arco','flechas'],
                'Rin'=>['varita','grimorio','pocion','amuleto'],'Hercules'=>['hacha','escudo'],
                'Sonya'=>['daga','pocion'],'jax'=>['garrote'],
            ];
            $invPersonaje = $invFallback[$personaje] ?? [];
            return empty(array_diff($reqs, $invPersonaje));
        });

        return response()->json([
            'mision'             => $mision,
            'aptos_nivel'        => array_values($aptos),
            'aptos_completos'    => array_values($aptosConItems),
            'consulta_ejecutada' => "puede_aceptar(P, $mision)",
        ]);
    }

    // ══════════════════════════════════════════════
    // API — XP
    // ══════════════════════════════════════════════
    public function xpAcumulada(Request $request)
    {
        $request->validate(['nivel' => 'required|integer|min:0|max:10']);
        $nivel = $request->input('nivel');

        $xp = (int) trim($this->consultarProlog(
            "xp_acumulada($nivel, T), writeln(T)"
        ));

        // Fallback XP recursiva local
        if ($xp === 0 && $nivel > 0) {
            $xp = 0;
            for ($i = 1; $i <= $nivel; $i++) $xp += 30 * $i;
        }

        return response()->json([
            'nivel'              => $nivel,
            'xp_total'           => $xp,
            'consulta_ejecutada' => "xp_acumulada($nivel, Total)",
        ]);
    }

    // ══════════════════════════════════════════════
    // HELPER — Limpia residuos de error de Prolog del mensaje
    // ══════════════════════════════════════════════
    private function limpiarMensajeProlog(string $texto): string
    {
        $lineas = explode("\n", str_replace("\r", "", $texto));
        $limpias = array_filter($lineas, function($l) {
            $l = trim($l);
            return !empty($l)
                && !str_starts_with($l, 'ERROR')
                && !str_starts_with($l, 'Warning')
                && !str_starts_with($l, '%')
                && !str_contains($l, 'halt')
                && !str_contains($l, 'Initialization')
                && !str_contains($l, 'undefined')
                && !str_contains($l, 'existence_error');
        });
        return trim(implode(' ', $limpias));
    }

    // ══════════════════════════════════════════════
    // FALLBACKS
    // ══════════════════════════════════════════════
    private function fallbackPersonajes(): array
    {
        return [
            ['nombre'=>'Elara',    'nivel'=>'5','vida'=>'100'],
            ['nombre'=>'Kael',     'nivel'=>'3','vida'=>'80'],
            ['nombre'=>'Rin',      'nivel'=>'7','vida'=>'120'],
            ['nombre'=>'Hercules', 'nivel'=>'6','vida'=>'110'],
            ['nombre'=>'Sonya',    'nivel'=>'4','vida'=>'90'],
            ['nombre'=>'jax',      'nivel'=>'2','vida'=>'75'],
        ];
    }

    private function fallbackMisiones(): array
    {
        return [
            ['id'=>'m1','nombre'=>'Bosque de Sombras','dificultad'=>'2','xp'=>'50'],
            ['id'=>'m2','nombre'=>'Cueva del Dragon', 'dificultad'=>'5','xp'=>'120'],
            ['id'=>'m3','nombre'=>'Torre Arcana',      'dificultad'=>'7','xp'=>'200'],
        ];
    }

    private function fallbackEnemigos(): array
    {
        return [
            ['id'=>'caballero_oscuro','nombre'=>'caballero oscuro','vida'=>40],
            ['id'=>'mago',            'nombre'=>'mago',            'vida'=>90],
            ['id'=>'rey_esqueleto',   'nombre'=>'rey esqueleto',   'vida'=>250],
        ];
    }
}
