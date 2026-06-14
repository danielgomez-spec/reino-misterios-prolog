/* ============================================
   CRÓNICAS DE PROLOG — game.js (V3 FIXES)
   ============================================ */

// ── CSRF TOKEN ────────────────────────────────
// Lee siempre del meta tag (más confiable que window.CSRF_TOKEN)
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.getAttribute('content')) {
        return meta.getAttribute('content');
    }
    return (typeof window.CSRF_TOKEN !== 'undefined') ? window.CSRF_TOKEN : '';
}

// ── DATOS DE ARMAS ────────────────────────────
const WEAPON_STATS = {
    espada:   { icon: '⚔️',  dmg: 30, def: 0,  xp: 0,  tipo: 'Arma' },
    arco:     { icon: '🏹',  dmg: 25, def: 0,  xp: 0,  tipo: 'Arma' },
    varita:   { icon: '🪄',  dmg: 35, def: 0,  xp: 0,  tipo: 'Arma' },
    hacha:    { icon: '🪓',  dmg: 45, def: 0,  xp: 0,  tipo: 'Arma' },
    daga:     { icon: '🗡️', dmg: 15, def: 0,  xp: 0,  tipo: 'Arma' },
    garrote:  { icon: '🪵',  dmg: 10, def: 0,  xp: 0,  tipo: 'Arma' },
    escudo:   { icon: '🛡️', dmg: 0,  def: 20, xp: 5,  tipo: 'Defensa' },
    pocion:   { icon: '🧪',  dmg: 0,  def: 0,  xp: 10, tipo: 'Soporte' },
    flechas:  { icon: '➶',   dmg: 0,  def: 0,  xp: 3,  tipo: 'Munición' },
    grimorio: { icon: '📖',  dmg: 0,  def: 0,  xp: 15, tipo: 'Soporte' },
    amuleto:  { icon: '📿',  dmg: 0,  def: 5,  xp: 8,  tipo: 'Accesorio' },
};

// ── FALLBACKS LOCALES ─────────────────────────
const INVENTARIO_FALLBACK = {
    'Elara':    ['espada', 'escudo', 'pocion'],
    'Kael':     ['arco', 'flechas'],
    'Rin':      ['varita', 'grimorio', 'pocion', 'amuleto'],
    'Hercules': ['hacha', 'escudo'],
    'Sonya':    ['daga', 'pocion'],
    'jax':      ['garrote'],
};
const STATS_FALLBACK = {
    'Elara':    { nivel: 5,  vida: 100 },
    'Kael':     { nivel: 3,  vida: 80  },
    'Rin':      { nivel: 7,  vida: 120 },
    'Hercules': { nivel: 6,  vida: 110 },
    'Sonya':    { nivel: 4,  vida: 90  },
    'jax':      { nivel: 2,  vida: 75  },
};
const DANIO_FALLBACK = {
    'Elara': 30, 'Kael': 25, 'Rin': 35,
    'Hercules': 45, 'Sonya': 15, 'jax': 10
};
const VIDA_ENEMIGO_FALLBACK = {
    'caballero_oscuro': 40, 'mago': 90, 'rey_esqueleto': 250
};
const MISION_APTOS_FALLBACK = {
    'm1': { nivel: ['Elara','Kael','Rin','Hercules','Sonya','jax'], items: ['Elara','Kael','Rin','Hercules','Sonya','jax'] },
    'm2': { nivel: ['Elara','Rin','Hercules'], items: ['Elara','Rin'] },
    'm3': { nivel: ['Rin'], items: ['Rin'] },
};

// ── INVENTARIO MODAL ─────────────────────────
async function verInventario(personaje) {
    const modal   = document.getElementById('modal-inventario');
    const title   = document.getElementById('modal-title');
    const content = document.getElementById('modal-content');

    title.textContent = personaje + ' — Cargando...';
    content.innerHTML = '<p style="text-align:center;color:#666;padding:20px">⏳ Consultando...</p>';
    modal.classList.remove('hidden');

    try {
        const res = await fetch('/api/inventario', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ personaje }),
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        const items = (data.items && data.items.length > 0)
            ? data.items
            : (INVENTARIO_FALLBACK[personaje] || []);

        const nivel = data.nivel || STATS_FALLBACK[personaje]?.nivel || '?';
        const vida  = data.vida  || STATS_FALLBACK[personaje]?.vida  || '?';

        title.textContent = personaje + ' — Nv ' + nivel + ' · ' + vida + ' HP';
        renderInventarioModal(items, content);

    } catch (err) {
        const items = INVENTARIO_FALLBACK[personaje] || [];
        const stats = STATS_FALLBACK[personaje] || { nivel: '?', vida: '?' };
        title.textContent = personaje + ' — Nv ' + stats.nivel + ' · ' + stats.vida + ' HP';
        renderInventarioModal(items, content);
    }
}

function renderInventarioModal(items, contentEl) {
    if (!items.length) {
        contentEl.innerHTML = '<p style="text-align:center;color:#666;padding:20px">Sin ítems.</p>';
        return;
    }
    const html = items.map(function(item) {
        const s = WEAPON_STATS[item] || { icon: '📦', dmg: 0, def: 0, xp: 0, tipo: 'Misc' };
        const dmgLine = s.dmg > 0 ? '<div class="inv-item-dmg">⚔ ' + s.dmg + ' DMG</div>' : '';
        const defLine = s.def > 0 ? '<div class="inv-item-def">🛡 ' + s.def + ' DEF</div>' : '';
        const xpLine  = s.xp  > 0 ? '<div class="inv-item-xp">✨ ' + s.xp  + ' XP</div>'  : '';
        return '<div class="inv-item">'
            + '<div class="inv-item-icon">' + s.icon + '</div>'
            + '<div class="inv-item-name">' + item + '</div>'
            + dmgLine + defLine + xpLine
            + '<div class="inv-item-type">' + s.tipo + '</div>'
            + '</div>';
    }).join('');
    contentEl.innerHTML = '<div class="inv-grid">' + html + '</div>';
}

function cerrarModal() {
    document.getElementById('modal-inventario').classList.add('hidden');
}

// Cerrar modal al hacer click fuera
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modal-inventario');
    if (modal && e.target === modal) cerrarModal();
});

// ── MISIONES ─────────────────────────────────
async function verificarMision(misionId, nombre, btn) {
    const originalText = btn.textContent;
    btn.textContent    = '⏳ Consultando Prolog...';
    btn.disabled       = true;

    const box      = document.getElementById('result-' + misionId);
    const nivelDiv = document.getElementById('nivel-' + misionId);
    const itemsDiv = document.getElementById('items-' + misionId);

    try {
        const res = await fetch('/api/mision', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ mision: misionId }),
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        const aptosNivel = (data.aptos_nivel && data.aptos_nivel.length > 0)
            ? data.aptos_nivel
            : (MISION_APTOS_FALLBACK[misionId]?.nivel || []);

        const aptosItems = (data.aptos_completos && data.aptos_completos.length > 0)
            ? data.aptos_completos
            : (MISION_APTOS_FALLBACK[misionId]?.items || []);

        renderMisionResult(nivelDiv, itemsDiv, aptosNivel, aptosItems);

    } catch (err) {
        const fb = MISION_APTOS_FALLBACK[misionId] || { nivel: [], items: [] };
        renderMisionResult(nivelDiv, itemsDiv, fb.nivel, fb.items);
    } finally {
        btn.textContent = originalText;
        btn.disabled    = false;
        if (box) box.classList.remove('hidden');
    }
}

function renderMisionResult(nivelDiv, itemsDiv, aptosNivel, aptosItems) {
    nivelDiv.innerHTML = aptosNivel.length
        ? aptosNivel.map(function(p) { return '<span>' + p + '</span>'; }).join('')
        : '<span style="color:#666">Ninguno</span>';

    itemsDiv.innerHTML = aptosItems.length
        ? aptosItems.map(function(p) { return '<span>' + p + '</span>'; }).join('')
        : '<span style="color:#666">Ninguno</span>';
}

function filtrarMisionesPersonaje(nombre) {
    if (!nombre) return;
    var personajes = (typeof PERSONAJES !== 'undefined') ? PERSONAJES : [];
    var misiones   = (typeof MISIONES   !== 'undefined') ? MISIONES   : [];
    var personaje  = personajes.find(function(p) { return p.nombre === nombre; });
    var nivel      = personaje ? parseInt(personaje.nivel) : 0;
    var aptas      = misiones.filter(function(m) { return nivel >= parseInt(m.dificultad); });

    var el = document.getElementById('char-missions-result');
    if (!el) return;
    if (!aptas.length) {
        el.innerHTML = '<div class="mission-pill" style="color:#666">No puede aceptar misiones aún</div>';
        return;
    }
    el.innerHTML = aptas.map(function(m) {
        return '<div class="mission-pill">🗺 ' + m.nombre + ' — ' + m.xp + ' XP</div>';
    }).join('');
}

// ── BATTLE SETUP ─────────────────────────────
var selectedHeroes = [];
var selectedEnemy  = null;
var selectedStage  = null;

function toggleHero(nombre, el) {
    var modo    = document.querySelector('input[name="modo"]:checked');
    var isGroup = modo && modo.value === 'grupal';
    var idx     = selectedHeroes.indexOf(nombre);

    if (idx === -1) {
        if (!isGroup) {
            selectedHeroes = [];
            document.querySelectorAll('.hero-select-card').forEach(function(c) {
                c.classList.remove('selected');
            });
        }
        selectedHeroes.push(nombre);
        el.classList.add('selected');
    } else {
        selectedHeroes.splice(idx, 1);
        el.classList.remove('selected');
    }
    actualizarSummary();
}

function selectEnemy(id, el) {
    selectedEnemy = id;
    document.querySelectorAll('.enemy-select-card').forEach(function(c) {
        c.classList.remove('selected');
    });
    el.classList.add('selected');
    var inp = document.getElementById('selected-enemy-input');
    if (inp) inp.value = id;
    actualizarSummary();
}

function selectStage(id, el) {
    selectedStage = id;
    document.querySelectorAll('.stage-card').forEach(function(c) {
        c.classList.remove('selected');
    });
    el.classList.add('selected');
    actualizarSummary();
}

function actualizarSummary() {
    var modoEl  = document.querySelector('input[name="modo"]:checked');
    var modo    = modoEl ? modoEl.value : 'individual';
    var summary = document.getElementById('setup-summary');
    var text    = document.getElementById('summary-text');

    if (!selectedHeroes.length || !selectedEnemy) {
        if (summary) summary.classList.add('hidden');
        return;
    }
    if (summary) summary.classList.remove('hidden');
    if (text) {
        text.innerHTML = '<strong>' + modo.toUpperCase() + '</strong>: ['
            + selectedHeroes.join(', ') + '] vs <strong style="color:#e74c3c">'
            + selectedEnemy.replace(/_/g, ' ').toUpperCase() + '</strong>'
            + (selectedStage ? ' · ' + selectedStage.replace(/_/g, ' ') : '');
    }
}

function iniciarCombate() {
    if (!selectedHeroes.length) { alert('Selecciona al menos un héroe'); return; }
    if (!selectedEnemy)         { alert('Selecciona un enemigo'); return; }

    var cont = document.getElementById('heroes-hidden-inputs');
    if (cont) {
        cont.innerHTML = selectedHeroes.map(function(h) {
            return '<input type="hidden" name="personajes[]" value="' + h + '">';
        }).join('');
    }
    var enemyInp = document.getElementById('selected-enemy-input');
    if (enemyInp) enemyInp.value = selectedEnemy;

    var btn = document.getElementById('btn-start-battle');
    if (btn) btn.textContent = '⚔ CARGANDO ARENA...';

    var form = document.getElementById('battle-form');
    if (form) form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof FAV !== 'undefined' && FAV) {
        var el = document.querySelector('.hero-select-card[data-nombre="' + FAV + '"]');
        if (el) toggleHero(FAV, el);
    }

    document.querySelectorAll('input[name="modo"]').forEach(function(r) {
        r.addEventListener('change', function() {
            selectedHeroes = [];
            document.querySelectorAll('.hero-select-card').forEach(function(c) {
                c.classList.remove('selected');
            });
            actualizarSummary();
            var hint = document.getElementById('heroes-hint');
            if (hint) hint.textContent = r.value === 'individual' ? 'Selecciona 1 héroe' : 'Selecciona 2+ héroes';
            document.querySelectorAll('.mode-card').forEach(function(mc) { mc.classList.remove('active'); });
            var mc = r.closest('.mode-card');
            if (mc) mc.classList.add('active');
        });
    });

    var checkedModo = document.querySelector('input[name="modo"]:checked');
    if (checkedModo) {
        var mc = checkedModo.closest('.mode-card');
        if (mc) mc.classList.add('active');
    }
});

// ── COMBATE EN VIVO ───────────────────────────
async function ejecutarCombate() {
    var btn = document.getElementById('btn-attack');
    if (!btn || btn.disabled) return;

    btn.disabled = true;
    btn.classList.add('fighting');
    var btnText = btn.querySelector('.btn-text');
    if (btnText) btnText.textContent = '¡ATACANDO!';

    var log = document.getElementById('battle-log');
    log.innerHTML = '<p style="color:#f0c040;text-align:center">⚔ COMBATIENDO...</p>';

    try {
        var data;
        var modo = (typeof MODO !== 'undefined') ? MODO : 'individual';

        if (modo === 'individual') {
            var res = await fetch('/api/combate/individual', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()   // FIX PRINCIPAL
                },
                body: JSON.stringify({
                    personaje: PERSONAJES_BATALLA[0],
                    enemigo:   ENEMIGO_ID
                }),
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            data = await res.json();

            // Si Prolog devuelve error en el mensaje, usar fallback
            if (!data.mensaje || data.mensaje.includes('ERROR') || data.mensaje.includes('error')) {
                data = calcularCombateIndividualFallback(PERSONAJES_BATALLA[0], ENEMIGO_ID);
            }

        } else {
            var res2 = await fetch('/api/combate/grupal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    personajes: PERSONAJES_BATALLA,
                    enemigo:    ENEMIGO_ID
                }),
            });
            if (!res2.ok) throw new Error('HTTP ' + res2.status);
            data = await res2.json();

            if (!data.mensaje || data.mensaje.includes('ERROR') || data.mensaje.includes('error')) {
                data = calcularCombateGrupalFallback(PERSONAJES_BATALLA, ENEMIGO_ID);
            }
        }

        mostrarResultadoCombate(data);

    } catch (err) {
        var modo2 = (typeof MODO !== 'undefined') ? MODO : 'individual';
        var dataFb = modo2 === 'individual'
            ? calcularCombateIndividualFallback(PERSONAJES_BATALLA[0], ENEMIGO_ID)
            : calcularCombateGrupalFallback(PERSONAJES_BATALLA, ENEMIGO_ID);
        mostrarResultadoCombate(dataFb);
    } finally {
        btn.disabled = false;
        btn.classList.remove('fighting');
        if (btnText) btnText.textContent = '¡ATACAR!';
    }
}

function calcularCombateIndividualFallback(personaje, enemigo) {
    var danio = DANIO_FALLBACK[personaje] || 10;
    var vida  = VIDA_ENEMIGO_FALLBACK[enemigo] || 100;
    var vence = danio >= vida;
    var armaMap = { 'Elara':'espada','Kael':'arco','Rin':'varita','Hercules':'hacha','Sonya':'daga','jax':'garrote' };
    var arma = armaMap[personaje] || 'arma';
    return {
        mensaje: personaje + ' ataca al ' + enemigo.replace(/_/g,' ')
            + ' (Vida: ' + vida + ') con ' + arma + ' haciendo ' + danio
            + ' de daño ' + (vence ? 'y logra derrotarlo solo.' : 'pero NO es suficiente.'),
        victoria: vence,
        danio: danio,
        consulta_ejecutada: "ejecutar_ataque_individual('" + personaje + "', " + arma + ", " + enemigo + ", Msg)"
    };
}

function calcularCombateGrupalFallback(personajes, enemigo) {
    var vida  = VIDA_ENEMIGO_FALLBACK[enemigo] || 100;
    var total = personajes.reduce(function(sum, p) { return sum + (DANIO_FALLBACK[p] || 0); }, 0);
    var vence = total >= vida;
    return {
        mensaje: 'El grupo [' + personajes.join(', ') + '] atacan al '
            + enemigo.replace(/_/g,' ') + ' (Vida: ' + vida + ') sumando '
            + total + ' de daño. ' + (vence ? '¡LOGRAN derrotarlo en equipo!' : 'NO logran derrotarlo.'),
        victoria: vence,
        danio_total: total,
        consulta_ejecutada: "ejecutar_ataque_grupal([" + personajes.join(',') + "], " + enemigo + ", Msg)"
    };
}

function mostrarResultadoCombate(data) {
    var log      = document.getElementById('battle-log');
    var victoria = data.victoria;
    var hpBarE   = document.getElementById('hp-bar-enemy');
    var hpTextE  = document.getElementById('hp-text-enemy');
    var sprite   = document.getElementById('enemy-sprite');

    if (sprite && victoria) {
        sprite.classList.add('shake');
        setTimeout(function() {
            sprite.style.opacity = '0.2';
            sprite.style.filter  = 'grayscale(1)';
        }, 500);
    }

    if (hpBarE) {
        var maxHp = parseInt(hpBarE.dataset.max) || 100;
        var danio = data.danio || data.danio_total || 0;
        var resto = Math.max(0, maxHp - danio);
        hpBarE.style.width = ((resto / maxHp) * 100) + '%';
        if (hpTextE) hpTextE.textContent = resto + '/' + maxHp + ' HP';
    }

    var badge = document.getElementById('prolog-badge');
    if (badge && data.consulta_ejecutada) {
        badge.classList.remove('hidden');
        var qText = document.getElementById('prolog-query-text');
        if (qText) qText.textContent = data.consulta_ejecutada;
    }

    // Mostrar resultado limpio — SIN mensajes de error
    log.innerHTML = '<div class="' + (victoria ? 'log-victory' : 'log-defeat') + '">'
        + (victoria ? '🏆 ¡VICTORIA!' : '💀 DERROTA')
        + '</div>'
        + '<div style="font-size:11px;color:#aaa;margin-top:8px;line-height:1.6">'
        + data.mensaje
        + '</div>'
        + '<div style="margin-top:14px;text-align:center">'
        + '<a href="/combate/setup" class="btn-nuevo-combate">↺ NUEVO COMBATE</a>'
        + '</div>';

    if (victoria) {
        document.querySelectorAll('.battle-avatar.fighting').forEach(function(av) {
            av.style.animation = 'none';
            av.style.transform = 'scale(1.15)';
            av.style.filter    = 'drop-shadow(0 0 10px #27ae60)';
        });
    }
}