
<?php
if($_SERVER['HTTP_HOST'] == 'localhost'){
    $ERP_API = 'http://localhost/ventaweb/api/tienda/index.php';
}else{
    $ERP_API = 'https://sistema.ventaweb.com.ar/api/tienda/index.php';
}
$ERP_BASE = str_replace('/api/tienda/index.php', '', $ERP_API);

$comercio = 'DIENVER';
if (!$comercio) {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body style="font-family:sans-serif;display:grid;place-items:center;height:100vh;margin:0"><div style="text-align:center"><h2>Falta el parámetro <code>?comercio=CODIGO</code></h2><p>Ejemplo: <code>?comercio=DEMO</code></p></div></body></html>');
}

$status  = htmlspecialchars($_GET['status']             ?? '', ENT_QUOTES, 'UTF-8');
$idVenta = (int)($_GET['external_reference']            ?? 0);

// producto.php nos incluye con $VW_FICHA = true: la página arranca mostrando
// la ficha del producto ?p= y no carga la grilla ni las secciones de la home.
// También arranca en ficha cuando se entra directo con ?p= (sin pasar por
// productos_emma.php), así la tienda no depende de un segundo archivo.
$VW_FICHA = !empty($VW_FICHA) || (int)($_GET['p'] ?? 0) > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tienda</title>
<link rel="icon" type="image/png" sizes="64x64"   href="<?= $ERP_BASE ?>/assets/img/favicon.png">
<link rel="icon" type="image/png" sizes="32x32"   href="<?= $ERP_BASE ?>/assets/img/favicon-32.png">
<link rel="apple-touch-icon"      sizes="180x180" href="<?= $ERP_BASE ?>/assets/img/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300&display=swap" rel="stylesheet">
<style>
/* ── Variables de tema (sobreescritas por JS) ── */
:root {
    --brand: #64748b;
    --brand-dark: #475569;
    --brand-light: #f1f5f9;
}

/* ── Reset / Base ── */
*, *::before, *::after { box-sizing: border-box; }
body { background: #f6f8fb; font-family: 'Segoe UI', system-ui, sans-serif; color: #1a1a2e; }

/* ── Navbar ── */
.navbar-tienda {
    background: var(--brand);
    border-bottom: 1px solid var(--brand-dark);
    position: sticky;
    top: 0;
    /* Por encima del modal de producto (1071): la barra sigue visible y usable
       mientras se mira un producto */
    z-index: 1085;
    padding: .75rem 1rem;
}
.navbar-tienda .brand-logo {
    height: 38px;
    width: auto;
    object-fit: contain;
}
.navbar-tienda .brand-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}
.search-box {
    position: relative;
    flex: 1 1 0;
    max-width: 420px;
    min-width: 160px;
}
.search-box input {
    padding-left: 2.4rem;
    border-radius: 2rem;
    border: 1px solid rgba(255,255,255,.35);
    background: #fff;
    font-size: .9rem;
    transition: border .2s, box-shadow .2s;
}
.search-box input:focus {
    border-color: #fff;
    box-shadow: 0 0 0 3px rgba(255,255,255,.25);
    background: #fff;
}
/* Autocomplete dropdown */
.ac-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0; right: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.13);
    z-index: 1090;
    overflow: hidden;
    display: none;
}
.ac-dropdown.open { display: block; }
@media (max-width: 767px) {
    .ac-dropdown {
        position: fixed;
        top: auto;
        left: .75rem;
        right: .75rem;
        border-radius: .65rem;
    }
}
.ac-item {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    padding: .55rem 1rem;
    cursor: pointer;
    font-size: .88rem;
    color: #1e293b;
    border-bottom: 1px solid #f1f5f9;
    transition: background .1s;
}
.ac-item:last-child { border-bottom: none; }
.ac-item:hover, .ac-item.ac-active { background: #f0fdf4; }
.ac-item mark {
    background: none;
    color: var(--brand, #047857);
    font-weight: 700;
    padding: 0;
}
.ac-item-codigo {
    font-size: .72rem;
    color: #94a3b8;
    white-space: nowrap;
}
.ac-footer {
    padding: .4rem 1rem;
    font-size: .75rem;
    color: #94a3b8;
    text-align: center;
    background: #f8fafc;
}
.search-box .search-icon {
    position: absolute;
    left: .75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #8898aa;
    pointer-events: none;
}
.btn-cart {
    position: relative;
    background: #fff;
    color: var(--brand);
    border: none;
    border-radius: 2rem;
    padding: .45rem 1.1rem;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background .2s, color .2s;
}
.btn-cart:hover { background: var(--brand-dark); color: #fff; }
.cart-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #e03d3d;
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 3px;
}

/* ── Hero ── */
.hero {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
    color: #fff;
    padding: 3.5rem 1.5rem 3rem;
    text-align: center;
    border: 0;
}
.hero-inner {
    max-width: 880px;
    margin: 0 auto;
}
.hero .hero-eyebrow {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    font-size: .72rem;
    font-weight: 500;
    letter-spacing: .28em;
    text-transform: uppercase;
    color: rgba(255,255,255,.72);
    margin-bottom: 1.1rem;
}
.hero p {
    font-family: "Fraunces", Georgia, "Times New Roman", serif;
    font-style: italic;
    font-weight: 300;
    font-size: clamp(1.25rem, 2.6vw, 1.75rem);
    line-height: 1.45;
    letter-spacing: -.005em;
    color: #fff;
    margin: 0 auto;
    max-width: 640px;
}
@media (max-width: 640px) {
    .hero { padding: 2.25rem 1.25rem 2rem; }
}

/* ── Layout ── */
.tienda-layout {
    display: block;
    max-width: 1280px;
    margin: 0 auto;
    padding: 1.5rem 1rem 2rem;
}

/* ── Category selector (panel doble) ── */
.cat-selector-bar {
    background: #fff;
    border-bottom: 1px solid #e8ecf0;
    position: sticky;
    top: 62px;
    z-index: 900;
}
.cat-selector-bar-inner {
    max-width: 1280px;
    margin: 0 auto;
    display: flex;
    overflow-x: auto;
    align-items: center;
    gap: .75rem;
    padding: .5rem 1rem;
    position: relative;
}
/* Quick nav links */
.cat-nav-sep {
    width: 1px; height: 20px; background: #e2e8f0; flex-shrink: 0;
}
.cat-quicknav {
    display: flex;
    align-items: center;
    gap: .05rem;
    overflow-x: auto;
    scrollbar-width: none;
    flex: 1;
}
.cat-quicknav::-webkit-scrollbar { display: none; }
.cat-qlink {
    display: inline-block;
    padding: .35rem .7rem;
    font-size: .845rem;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    border-radius: 6px;
    white-space: nowrap;
    transition: color .12s;
}
.cat-qlink:hover { color: var(--brand); }
.cat-qlink.active { color: var(--brand); font-weight: 700; border-bottom: 2px solid var(--brand); border-radius: 0; }
.cat-qlink.disabled { color: #b0bac5; cursor: default; pointer-events: none; }
.cat-trigger {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .35rem .7rem;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: #334155;
    font-size: .845rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: color .12s;
}
.cat-trigger:hover, .cat-trigger.open {
    color: var(--brand);
    background: transparent;
}
.cat-trigger .chev { font-size: .6rem; opacity: .55; transition: transform .2s; }
.cat-trigger.open  .chev { transform: rotate(180deg); }
/* Breadcrumb ruta seleccionada */
.cat-active-path {
    display: flex;
    align-items: center;
    gap: .3rem;
    font-size: .83rem;
    color: #64748b;
    flex: 1;
    min-width: 0;
    flex-wrap: wrap;
}
.path-crumb { color: var(--brand); font-weight: 600; }
.path-crumb-btn { background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }
.path-crumb-btn:hover { opacity: .75; }
.path-sep   { color: #cbd5e1; font-size: .65rem; }
.btn-clear-cat {
    background: none; border: none;
    padding: 2px 5px; border-radius: 4px;
    color: #94a3b8; cursor: pointer; font-size: .75rem; line-height: 1;
}
.btn-clear-cat:hover { background: #fee2e2; color: #e03d3d; }
/* Panel doble (inline, no flotante) */
.cat-panel-wrap {
    display: none;
}
.cat-panel-wrap.open { display: block; }
.cat-panel {
    background: #fff;
    border-top: 1px solid #e8ecf0;
    border-bottom: 1px solid #e8ecf0;
    display: flex;
}
.cat-panel-parents {
    flex: 0 0 210px;
    border-right: 1px solid #e8ecf0;
    padding: .35rem 0;
}
.cat-panel-subs {
    flex: 1;
    background: #f8fafc;
    padding: .35rem 0;
}
.cp-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .52rem 1rem;
    font-size: .845rem;
    color: #334155;
    cursor: pointer;
    transition: background .1s, color .1s;
    border-left: 2.5px solid transparent;
    gap: .5rem;
    user-select: none;
}
.cp-item:hover, .cp-item.hover { background: var(--brand-light); color: var(--brand); border-left-color: var(--brand); }
.cp-item.active { color: var(--brand); font-weight: 600; border-left-color: var(--brand); }
.cp-arr { font-size: .58rem; opacity: .45; flex-shrink: 0; }
.cp-sub-item {
    display: block;
    padding: .48rem 1.25rem;
    font-size: .83rem;
    color: #475569;
    cursor: pointer;
    transition: background .1s, color .1s;
    user-select: none;
}
.cp-sub-item:hover { background: #fff; color: var(--brand); }
.cp-sub-item.active { color: var(--brand); font-weight: 600; }
.cp-subs-hdr {
    padding: .3rem 1.25rem .15rem;
    font-size: .66rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: .07em;
    text-transform: uppercase;
}
.cp-empty { padding: 1.25rem 1.25rem; color: #94a3b8; font-size: .83rem; }
/* Etiqueta estilo igual a marcas-filtro-label / atrib-filtro-label */
.filtro-label {
    font-size: .7rem;
    font-weight: 600;
    color: #94a3b8;
    letter-spacing: .05em;
    text-transform: uppercase;
}
/* Ocultar wraps legacy del sidebar/mobile */
#atribFiltrosWrap, #marcasFiltrosWrap,
#mobileAtribFiltros, #mobileMarcasFiltros { display: none !important; }
/* ── Filtros colapsables (web3) ── */
.filtros-wrap {
    background: #f8fafc;
    border-bottom: 1px solid #e8ecf0;
}
.filtros-header {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .45rem 1rem;
    max-width: 1280px;
    margin: 0 auto;
}
.filtros-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: #fff;
    border: 1.5px solid #d0d7de;
    border-radius: 8px;
    padding: .3rem .85rem;
    font-size: .82rem;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    user-select: none;
    transition: all .15s;
}
.filtros-toggle-btn:hover { border-color: var(--brand); color: var(--brand); }
.filtros-toggle-btn.open  { background: var(--brand); color: #fff; border-color: var(--brand); }
.filtros-toggle-btn .filtros-chevron { font-size: .65rem; transition: transform .2s; }
.filtros-toggle-btn.open .filtros-chevron { transform: rotate(180deg); }
.filtros-panel {
    display: block;
}
.filtros-panel-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .6rem;
    padding: .5rem 1rem .85rem;
    max-width: 1280px;
    margin: 0 auto;
}
/* Ancho máximo de selects e inputs en el panel de filtros */
.filtros-panel .form-select { max-width: 190px; }
.filtros-panel .form-control { max-width: 140px; }
/* ── Web4: sidebar de filtros siempre visible en desktop ── */
.w4-layout { max-width: 1280px; margin: 0 auto; }
/* Sin categoría/búsqueda activa: no se muestran los filtros */
.w4-layout.sin-filtros .w4-sidebar { display: none; }
.w4-sidebar-title { display: none; }
.w4-sidebar-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; margin-bottom: .75rem;
}
.w4-filtro-bloque { width: 100%; }
@media (min-width: 768px) {
    .w4-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        align-items: start;
    }
    .w4-sidebar {
        position: sticky;
        top: 128px;
        background: #fff;
        border-right: 1px solid #e8ecf0;
        padding: 1.25rem 1rem 2rem;
        min-height: calc(100vh - 130px);
    }
    .w4-sidebar-title {
        display: block;
        font-size: .78rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 0;
    }
    .w4-sidebar-head { margin-bottom: 1.1rem; }
    .w4-sidebar .filtros-header { display: none; }
    .w4-sidebar .filtros-panel { max-height: none !important; overflow: visible !important; }
    .w4-sidebar .filtros-panel-inner {
        flex-direction: column;
        align-items: flex-start;
        padding: 0;
        gap: 1.4rem;
    }
    /* Precio: slider ocupa todo el ancho en sidebar */
    .w4-sidebar .price-slider-wrap { padding: .2rem 0 .4rem; }
    /* Selects y inputs a ancho completo en sidebar */
    .w4-sidebar .form-select,
    .w4-sidebar .form-control { max-width: 100% !important; width: 100%; }
    /* Chips de marca: alineados a la izquierda */
    .w4-sidebar #marcasFiltrosInline { align-items: flex-start; }
    /* Atrib items: ancho completo */
    .w4-sidebar #atribFiltrosInline .atrib-filtro-item { width: 100%; }
    /* Main sin max-width propio */
    .w4-main .tienda-layout { max-width: none; margin: 0; padding-left: 1.25rem; }
    .w4-layout.sin-filtros { grid-template-columns: 1fr; }
    .w4-layout.sin-filtros .w4-main .tienda-layout { padding-left: 0; }
}
@media (max-width: 767px) {
    .w4-sidebar { background: #f8fafc; border-bottom: 1px solid #e8ecf0; }
    .w4-main .tienda-layout { max-width: none; margin: 0; }
}
/* ── Dual range slider precio ── */
.price-slider-wrap { padding: .25rem 0 .3rem; }
.price-slider-labels {
    display: flex; justify-content: space-between;
    font-size: .75rem; font-weight: 700; color: #334155; margin-bottom: .55rem;
}
.price-slider-track {
    position: relative; height: 22px; margin: 0 6px;
}
.price-slider-bar {
    position: absolute; top: 50%; left: 0; width: 100%;
    height: 4px; background: #e2e8f0; border-radius: 2px; transform: translateY(-50%);
}
.price-slider-range {
    position: absolute; top: 50%; height: 4px;
    background: var(--brand); border-radius: 2px; transform: translateY(-50%);
}
.price-slider-track input[type=range] {
    position: absolute; top: 50%; left: 0; width: 100%;
    height: 0; margin: 0; padding: 0;
    background: transparent; outline: none; border: none;
    -webkit-appearance: none; appearance: none;
    pointer-events: none; transform: translateY(-50%);
}
.price-slider-track input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none; appearance: none;
    width: 16px; height: 16px; border-radius: 50%;
    background: var(--brand); border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.25);
    cursor: pointer; pointer-events: all;
}
.price-slider-track input[type=range]::-moz-range-thumb {
    width: 14px; height: 14px; border-radius: 50%;
    background: var(--brand); border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.25);
    cursor: pointer; pointer-events: all;
}
.btn-clear-price {
    margin-top: .4rem; font-size: .71rem; color: #94a3b8;
    background: none; border: none; cursor: pointer; padding: 0;
}
.btn-clear-price:hover { color: var(--brand); }
/* Botón para volver todos los filtros a su estado inicial */
.btn-reset-filtros {
    display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
    background: #fff; color: #475569;
    border: 1px solid #e2e8f0; border-radius: 8px;
    padding: .4rem .7rem; font-size: .78rem; font-weight: 600;
    cursor: pointer; transition: color .15s, border-color .15s, background .15s;
}
.btn-reset-filtros:hover { color: var(--brand); border-color: var(--brand); background: var(--brand-light); }
.btn-reset-filtros:disabled { opacity: .45; pointer-events: none; }
@keyframes dropIn { from { opacity:0; transform:translateY(-6px) } to { opacity:1; transform:translateY(0) } }

/* ── Sidebar categorías ── */
.sidebar-cats {
    position: sticky;
    top: 72px;
    max-height: calc(100vh - 90px);
    display: flex;
    flex-direction: column;
}
.sidebar-cats .card {
    border: 1px solid #e8ecf0;
    border-radius: 12px;
    min-height: 0;
    display: flex;
    flex-direction: column;
}
.sidebar-cats .card-body {
    overflow-y: auto;
    overscroll-behavior: contain;
}
/* Filtros de marcas en sidebar */
#marcasFiltrosWrap { border-top: 1px solid #e8ecf0; margin-top: .25rem; padding: .5rem .75rem .25rem; }
/* Filtros de marcas en mobile */
#mobileMarcasFiltros { background: #fff; border-bottom: 1px solid #e8ecf0; padding: .5rem 1rem .75rem; }
/* Chip de marca — global para sidebar y mobile */
.marcas-filtro-label { font-size: .7rem; font-weight: 600; color: #94a3b8; letter-spacing: .05em; text-transform: uppercase; margin-bottom: .4rem; display: block; }
/* Bloque de marcas: colapsable en mobile, siempre abierto en desktop */
.marcas-filtro-bloque { width: 100%; }
.marcas-toggle {
    display: flex; align-items: center; gap: .3rem; width: 100%;
    background: none; border: 0; padding: 0; text-align: left; cursor: pointer;
}
.marcas-toggle .marcas-filtro-label { margin-bottom: 0; }
.marcas-sel { color: var(--brand); }
.marcas-toggle .chev { margin-left: auto; font-size: .7rem; color: #94a3b8; transition: transform .2s; }
.marcas-filtro-bloque.open .marcas-toggle .chev { transform: rotate(180deg); }
.marcas-chips { display: flex; flex-wrap: wrap; gap: .2rem; margin-top: .3rem; }
@media (max-width: 767px) {
    .marcas-filtro-bloque:not(.open) .marcas-chips { display: none; }
}
@media (min-width: 768px) {
    .marcas-toggle { cursor: default; }
    .marcas-toggle .chev { display: none; }
}
.chip-marca {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .22rem .7rem;
    border-radius: 999px;
    border: 1.5px solid #d0d7de;
    font-size: .78rem; font-weight: 500;
    color: #475569; background: #fff;
    cursor: pointer; margin: .15rem;
    transition: all .15s;
    white-space: nowrap;
    appearance: none; -webkit-appearance: none;
    line-height: 1.4;
}
.chip-marca:hover  { border-color: var(--brand); color: var(--brand); }
.chip-marca.active { background: var(--brand); color: #fff; border-color: var(--brand); }
/* Filtros de atributos en sidebar */
#atribFiltrosWrap { border-top: 1px solid #e8ecf0; margin-top: .25rem; padding: .5rem .75rem .25rem; }
#atribFiltrosWrap .atrib-filtro-label { font-size: .7rem; font-weight: 600; color: #94a3b8; letter-spacing: .05em; text-transform: uppercase; margin-bottom: .4rem; }
#atribFiltrosWrap .atrib-filtro-item { margin-bottom: .5rem; }
#atribFiltrosWrap .atrib-filtro-item label { font-size: .78rem; color: #475569; margin-bottom: .15rem; display: block; }
#atribFiltrosWrap select, #atribFiltrosWrap input { font-size: .78rem; }
/* Filtros de atributos inline (cat-filter-bar, web2) */
#atribFiltrosInline .atrib-filtro-item { display: inline-flex; flex-direction: column; gap: .1rem; }
#atribFiltrosInline .atrib-filtro-item label { font-size: .7rem; font-weight: 600; color: #94a3b8; letter-spacing: .05em; text-transform: uppercase; margin: 0; white-space: nowrap; }
/* Filtros de atributos en mobile (solo visible en pantallas chicas) */
@media (min-width: 768px) { #mobileAtribFiltros { display: none !important; } #mobileMarcasFiltros { display: none !important; } }
#mobileAtribFiltros .atrib-filtro-label { font-size: .7rem; font-weight: 600; color: #94a3b8; letter-spacing: .05em; text-transform: uppercase; margin-bottom: .5rem; }
#mobileAtribFiltros .atrib-filtro-item { display: inline-block; margin-right: .75rem; margin-bottom: .25rem; vertical-align: top; min-width: 130px; }
#mobileAtribFiltros .atrib-filtro-item label { font-size: .75rem; color: #475569; margin-bottom: .1rem; display: block; }
#mobileAtribFiltros select, #mobileAtribFiltros input { font-size: .78rem; }
/* Atributos en modal de producto */
#prodModalAtributos { margin-top: .75rem; }
#prodModalAtributos table { width: 100%; font-size: .82rem; border-collapse: collapse; }
#prodModalAtributos td { padding: .25rem .4rem; vertical-align: top; }
#prodModalAtributos td:first-child { color: #64748b; white-space: nowrap; width: 40%; font-weight: 500; }
#prodModalAtributos tr:not(:last-child) td { border-bottom: 1px solid #f1f5f9; }
.cat-item {
    display: flex;
    align-items: flex-start;
    width: 100%;
    padding: .55rem .9rem;
    font-size: .875rem;
    color: #334155;
    cursor: pointer;
    border-radius: 8px;
    transition: background .15s, color .15s;
    text-decoration: none;
}
.cat-item:hover { background: var(--brand-light); color: var(--brand); }
.cat-item.active { background: var(--brand); color: #fff; font-weight: 600; }
.cat-item-label { flex: 1; min-width: 0; word-break: break-word; line-height: 1.3; }
.cat-count {
    background: #e8ecf0;
    color: #555;
    font-size: .65rem;
    border-radius: 9px;
    padding: 1px 6px;
    flex-shrink: 0;
    margin-left: .4rem;
    margin-top: 2px;
}
.cat-item.active .cat-count { background: rgba(255,255,255,.25); color: #fff; }
.cat-chevron {
    background: none; border: none; padding: 0 4px;
    color: inherit; opacity: .7; font-size: .7rem;
    line-height: 1; cursor: pointer; flex-shrink: 0;
}
.cat-chevron i { display: inline-block; transition: transform .2s; }
.cat-chevron.open i { transform: rotate(90deg); }
.cat-item.active .cat-chevron { opacity: .75; }
.cat-sub { font-size: .8rem; }
.cat-sub-marker { opacity: .35; margin-right: 3px; font-size: .75em; }

/* ── Mobile cats ── */
.mobile-cats {
    display: none;
    flex-direction: column;
    background: #fff;
    border-bottom: 1px solid #e8ecf0;
}
.mobile-cats-row {
    display: flex;
    gap: .4rem;
    overflow-x: auto;
    padding: .6rem 1rem;
    scrollbar-width: none;
}
.mobile-cats-row::-webkit-scrollbar { display: none; }
.mobile-cats-subs {
    border-top: 1px dashed #e8ecf0;
    background: #f8fafc;
    padding: .5rem 1rem;
}
.chip-cat {
    white-space: nowrap;
    padding: .35rem .9rem;
    border-radius: 2rem;
    font-size: .82rem;
    font-weight: 500;
    border: 1.5px solid #d0d7de;
    color: #475569;
    cursor: pointer;
    background: #fff;
    transition: all .15s;
    flex-shrink: 0;
}
.chip-cat:hover  { border-color: var(--brand); color: var(--brand); }
.chip-cat.active { background: var(--brand); color: #fff; border-color: var(--brand); }
.chip-sub { font-size: .78rem; padding: .28rem .75rem; }

/* Chips de subcategorías dentro del sidebar de filtros */
.cat-filtro-chips { display: flex; flex-wrap: wrap; gap: .3rem; }
.cat-filtro-chips .chip-cat { font-size: .76rem; padding: .25rem .7rem; white-space: normal; }

/* ── Toolbar (ordenar / resultados) ── */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .85rem;
    flex-wrap: wrap;
    gap: .5rem;
}
.toolbar-label { font-size: .88rem; color: #64748b; }

/* ── Auth navbar ── */
.btn-auth {
    display: inline-flex; align-items: center;
    background: transparent; border: 1px solid rgba(255,255,255,.5);
    border-radius: 2rem; padding: .35rem .85rem;
    font-size: .88rem; font-weight: 500; color: #fff;
    cursor: pointer; transition: background .15s, border-color .15s, color .15s;
    white-space: nowrap;
}
.btn-auth:hover { background: #fff; border-color: #fff; color: var(--brand); }
.btn-auth-sm { padding: .3rem .55rem; }
.auth-user-name { font-size: .88rem; font-weight: 600; color: #fff; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Login modal ── */
.login-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 2000; display: none; align-items: center; justify-content: center;
}
.login-overlay.open { display: flex; }
.login-modal {
    background: #fff; border-radius: 1rem; padding: 2rem 1.75rem;
    width: 100%; max-width: 380px; position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    animation: loginSlideIn .2s ease;
}
@keyframes loginSlideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.login-close {
    position: absolute; top: .75rem; right: .9rem;
    background: none; border: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer; line-height: 1;
}
.login-close:hover { color: #374151; }

/* ── Grid de productos ── */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 1rem;
}
@media (max-width: 480px) {
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: .6rem; }
}

/* ── Card de producto ── */
.product-card {
    background: #fff;
    border: 1px solid #e8ecf0;
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
    display: flex;
    flex-direction: column;
}
.product-card:hover { box-shadow: 0 8px 24px rgba(67,97,238,.12); transform: translateY(-2px); }
.product-img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    background: #f0f2f5;
}
.product-img-placeholder {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f2f5;
    color: #94a3b8;
    font-size: 2.5rem;
}
.product-body {
    padding: .75rem .8rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.product-cat  { font-size: .7rem; color: var(--brand); font-weight: 600; text-transform: uppercase; margin-bottom: .2rem; }
.product-name { font-size: .9rem; font-weight: 600; line-height: 1.3; margin-bottom: .4rem; color: #1a1a2e; flex: 1; }
.product-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
}
.product-price small { font-size: .72rem; font-weight: 400; color: #64748b; }
.price-original { font-size: .78rem; font-weight: 400; color: #94a3b8; text-decoration: line-through; display: block; line-height: 1; margin-bottom: .1rem; }
.badge-descuento { font-size: .65rem; font-weight: 700; background: #ef4444; color: #fff; border-radius: 4px; padding: 1px 5px; vertical-align: middle; margin-left: .25rem; display: inline-block; white-space: nowrap; }
.badge-recargo   { font-size: .65rem; font-weight: 700; background: #f97316; color: #fff; border-radius: 4px; padding: 1px 5px; vertical-align: middle; margin-left: .25rem; }
.fp-disc-info    { font-size: .8rem; padding: .3rem .7rem; border-radius: 6px; margin-bottom: .5rem; display:none; }
.fp-disc-info.fp-descuento { background: #dcfce7; color: #166534; }
.fp-disc-info.fp-recargo   { background: #fef3c7; color: #92400e; }
/* Banner medios de pago en home */
#fpMethodsBanner { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; padding:.6rem 1rem; background:#f8fafc; border-bottom:1px solid #e2e8f0; font-size:.82rem; }
.fp-banner-label { color:#64748b; font-weight:600; white-space:nowrap; }
.fp-banner-pill  { display:inline-flex; align-items:center; gap:.25rem; padding:.25rem .6rem; border-radius:20px; font-size:.78rem; font-weight:600; white-space:nowrap; }
.fp-banner-pill-desc    { background:#dcfce7; color:#166534; }
.fp-banner-pill-recargo { background:#fef3c7; color:#92400e; }
/* Medios de pago en modal de producto */
.prod-modal-medios-pago  { margin:.6rem 0 .75rem; padding:.55rem .7rem; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0; }
.prod-modal-medios-titulo{ font-size:.73rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.4rem; }
.prod-modal-medio-row    { display:flex; align-items:center; gap:.45rem; padding:.2rem 0; font-size:.83rem; }
.prod-modal-medio-name   { flex:1; color:#374151; }
.prod-modal-medio-precio { font-weight:700; color:#1a1a2e; }
.prod-modal-medio-badge  { font-size:.68rem; }
.product-footer { padding: .5rem .8rem .7rem; }
.btn-add {
    width: 100%;
    background: var(--brand);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .45rem;
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    transition: background .15s;
}
.btn-add:hover { background: var(--brand-dark); }
.btn-add:disabled { background: #94a3b8; cursor: not-allowed; }

/* ── Paginación ── */
.pagination-wrap { margin-top: 1.5rem; display: flex; justify-content: center; gap: .4rem; flex-wrap: wrap; }
.page-btn {
    width: 36px; height: 36px;
    border: 1.5px solid #d0d7de;
    border-radius: 8px;
    background: #fff;
    font-size: .82rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
    color: #475569;
}
.page-btn:hover   { border-color: var(--brand); color: var(--brand); }
.page-btn.active  { background: var(--brand); color: #fff; border-color: var(--brand); }
.page-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── Skeleton loader ── */
.skeleton {
    background: linear-gradient(90deg, #f0f2f5 25%, #e2e8f0 50%, #f0f2f5 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Cart Drawer ── */
.cart-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1086;
    opacity: 0;
    visibility: hidden;
    transition: opacity .25s, visibility .25s;
}
.cart-overlay.open { opacity: 1; visibility: visible; }
.cart-drawer {
    position: fixed;
    top: 0; right: -420px;
    width: 100%;
    max-width: 420px;
    height: 100%;
    background: #fff;
    z-index: 1087;
    display: flex;
    flex-direction: column;
    transition: right .3s cubic-bezier(.4,0,.2,1);
    box-shadow: -4px 0 24px rgba(0,0,0,.12);
}
.cart-drawer.open { right: 0; }
.cart-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #e8ecf0;
    font-weight: 700;
    font-size: 1rem;
}
.cart-body { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
.cart-empty { text-align: center; color: #94a3b8; padding: 3rem 1rem; }
.cart-empty i { font-size: 3rem; display: block; margin-bottom: .75rem; }
.cart-item {
    display: flex; gap: .75rem; align-items: flex-start;
    padding: .75rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.cart-item-img {
    width: 56px; height: 56px;
    object-fit: cover;
    border-radius: 8px;
    background: #f0f2f5;
    flex-shrink: 0;
}
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name { font-size: .875rem; font-weight: 600; margin-bottom: .2rem; }
.cart-item-price { font-size: .8rem; color: #64748b; }
.qty-control {
    display: flex; align-items: center; gap: .3rem; margin-top: .4rem;
}
.qty-btn {
    width: 26px; height: 26px;
    border: 1.5px solid #d0d7de;
    border-radius: 6px;
    background: #fff;
    font-size: .85rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: border-color .15s;
    padding: 0;
}
.qty-btn:hover { border-color: var(--brand); color: var(--brand); }
.qty-val { font-size: .85rem; font-weight: 600; min-width: 20px; text-align: center; }
/* Pesables: se compran escribiendo los gramos, no sumando de a una unidad. */
.qty-peso {
    width: 66px; height: 26px;
    border: 1.5px solid #d0d7de;
    border-radius: 6px;
    background: #fff;
    font-size: .85rem; font-weight: 600;
    text-align: right;
    padding: 0 4px;
}
.qty-peso:focus { outline: none; border-color: var(--brand); }
.qty-peso-uni { font-size: .8rem; color: #64748b; }
.btn-remove { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; margin-left: auto; }
.btn-remove:hover { color: #e03d3d; }
.cart-item-subtotal { font-size: .875rem; font-weight: 700; color: #1a1a2e; }
.cart-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e8ecf0;
}
.cart-total { font-size: 1.1rem; font-weight: 700; margin-bottom: .75rem; display: flex; justify-content: space-between; }
.btn-checkout {
    width: 100%;
    background: var(--brand);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: .75rem;
    font-size: .95rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
}
.btn-checkout:hover { background: var(--brand-dark); }

/* ── Checkout Modal ── */
.checkout-modal {
    position: fixed; inset: 0;
    z-index: 1088;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    background: rgba(0,0,0,.55);
    opacity: 0; visibility: hidden;
    transition: opacity .25s, visibility .25s;
}
.checkout-modal.open { opacity: 1; visibility: visible; }
#checkoutBackdrop {
    position: absolute;
    inset: 0;
    cursor: pointer;
}
.checkout-box {
    background: #fff;
    border-radius: 16px;
    width: 100%; max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    transform: scale(.95);
    transition: transform .25s;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
}
@media (min-width: 768px) { .checkout-box { max-width: 760px; } }
.checkout-modal.open .checkout-box { transform: scale(1); }
.checkout-box-header {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #fff;
    padding: 1.25rem 1.75rem 1rem;
    border-bottom: 1px solid #e8ecf0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 16px 16px 0 0;
    flex-shrink: 0;
}
.checkout-box-body {
    padding: 1.25rem 1.75rem 1.75rem;
    overflow-y: auto;
    flex: 1;
}
.checkout-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem; }
.order-summary { background: #f6f8fb; border-radius: 10px; padding: .9rem 1rem; margin-bottom: 1.25rem; }
.order-summary-item { display: flex; justify-content: space-between; font-size: .88rem; padding: .2rem 0; }
.order-summary-total { display: flex; justify-content: space-between; font-weight: 700; font-size: 1rem; margin-top: .5rem; padding-top: .5rem; border-top: 1px solid #e2e8f0; }
.btn-mp {
    width: 100%;
    background: var(--brand);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: .8rem;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s;
    margin-top: .75rem;
}
.btn-mp:hover    { background: var(--brand-dark); }
.btn-mp:disabled { background: #94a3b8; cursor: not-allowed; }
.btn-mp-logo { height: 22px; width: auto; }
.btn-pedido {
    width: 100%;
    background: #fff;
    color: var(--brand);
    border: 2px solid var(--brand);
    border-radius: 10px;
    padding: .75rem;
    font-size: .95rem;
    font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s, color .15s;
    margin-top: .75rem;
}
.btn-pedido:hover { background: var(--brand-light); }
.btn-pedido:disabled { opacity: .6; cursor: not-allowed; }
/* ── Botones de pago adicionales ── */
.btn-transf {
    width: 100%; border: none; border-radius: 12px;
    padding: .85rem 1rem;
    background: #f0fdf4; color: #166534; border: 1.5px solid #bbf7d0;
    font-size: .95rem; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s; margin-top: .75rem;
}
.btn-transf:hover { background: #dcfce7; }
.btn-transf:disabled { opacity: .6; cursor: not-allowed; }
.btn-efectivo {
    width: 100%; border: none; border-radius: 12px;
    padding: .85rem 1rem;
    background: #fffbeb; color: #92400e; border: 1.5px solid #fde68a;
    font-size: .95rem; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s; margin-top: .75rem;
}
.btn-efectivo:hover { background: #fef3c7; }
.btn-efectivo:disabled { opacity: .6; cursor: not-allowed; }
/* ── Botón Nave ── */
.btn-nave {
    width: 100%;
    background: #ff6900; color: #fff;
    border: none; border-radius: 10px;
    padding: .85rem 1.25rem;
    font-size: 1rem; font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s; margin-top: .75rem;
}
.btn-nave:hover { background: #e55e00; }
.btn-nave:disabled { opacity: .6; cursor: not-allowed; }
/* ── Botón GoCuotas ── */
.btn-gocuotas {
    width: 100%;
    background: #00a86b; color: #fff;
    border: none; border-radius: 10px;
    padding: .85rem 1.25rem;
    font-size: 1rem; font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s; margin-top: .75rem;
}
.btn-gocuotas:hover { background: #008f5a; }
.btn-gocuotas:disabled { opacity: .6; cursor: not-allowed; }
/* ── Botón Payway ── */
.btn-payway {
    width: 100%;
    background: #003087; color: #fff;
    border: none; border-radius: 10px;
    padding: .85rem 1.25rem;
    font-size: 1rem; font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s; margin-top: .5rem;
}
.btn-payway:hover { background: #00256b; }
.btn-payway:disabled { opacity: .6; cursor: not-allowed; }
/* ── Formulario de tarjeta Payway ── */
.pw-card-form { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:1rem; margin-top:.5rem; }
.pw-card-form .form-control { font-size:.9rem; }
.pw-card-row { display:flex; gap:.5rem; }
.pw-card-row .form-control { flex:1; }
/* ── Info de transferencia en checkout ── */
.ck-transf-info {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    padding: .75rem 1rem; margin-top: .75rem; font-size: .88rem;
}
/* ── Categorías destacadas scroll horizontal ── */
.cat-dest-card {
    flex: 0 0 auto !important;
    width: 34vw !important;
    max-width: 34vw !important;
    scroll-snap-align: start;
}
@media (min-width: 576px) { .cat-dest-card { width: 36vw !important; max-width: 36vw !important; } }
@media (min-width: 768px) { .cat-dest-card { width: 20% !important; max-width: 20% !important; } }
/* Búsqueda activa: se ocultan los bloques promocionales para que los
   resultados queden arriba de todo. Se usa una clase en <body> para no pisar
   el estado propio de cada bloque (algunos se muestran según el comercio). */
body.busqueda-activa #heroSection,
body.busqueda-activa #carouselBannersWrap,
body.busqueda-activa #categoriasDestacadasWrap,
body.busqueda-activa #descuentosSectionsWrap,
body.busqueda-activa > .cuotas-section { display: none !important; }

/* Flechas de navegación (solo desktop) */
.cat-dest-shell { position: relative; }
.cat-dest-nav {
    display: none;
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 40px; height: 40px; z-index: 3;
    align-items: center; justify-content: center;
    background: #fff; color: #334155;
    border: 1px solid #e8ecf0; border-radius: 50%;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .14);
    font-size: 1.05rem; line-height: 1; cursor: pointer;
    transition: color .15s, border-color .15s, box-shadow .15s, opacity .15s, transform .15s;
}
.cat-dest-nav:hover {
    color: var(--brand); border-color: var(--brand);
    box-shadow: 0 5px 16px rgba(15, 23, 42, .2);
    transform: translateY(-50%) scale(1.06);
}
.cat-dest-nav:active { transform: translateY(-50%) scale(.96); }
.cat-dest-nav:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
.cat-dest-nav[disabled] { opacity: 0; pointer-events: none; }
.cat-dest-nav.prev { left: -14px; }
.cat-dest-nav.next { right: -14px; }
@media (min-width: 768px) {
    .cat-dest-shell.has-nav .cat-dest-nav { display: flex; }
    /* Desktop: se navega con flechas, sin barra de scroll visible */
    .cat-dest-shell.has-nav #categoriasDestacadasRow {
        scrollbar-width: none;
        scroll-behavior: smooth;
    }
    .cat-dest-shell.has-nav #categoriasDestacadasRow::-webkit-scrollbar { display: none; }
}
/* ── Barra de envío ── */
.envio-bar {
    background: #fff; border-bottom: 1px solid #e8ecf0;
    padding: .45rem 1.25rem; text-align: center; font-size: .85rem;
    color: #64748b;
}
/* ── Benefits bar (5 props debajo del carrusel) ── */
.benefits-bar { background:#fff; border-bottom:1px solid #e8ecf0; margin-top:.6rem; }
.benefits-bar-inner {
    max-width:1280px; margin:0 auto;
    display:flex; align-items:stretch;
    padding:.15rem 1rem;
}
.benefit-item {
    flex:1; display:flex; align-items:center; gap:.55rem;
    padding:.55rem .7rem;
    border-right:1px solid #f0f4f8;
    min-width:0;
}
.benefit-item:last-child { border-right:none; }
.benefit-icon { font-size:1.35rem; color:var(--brand); flex-shrink:0; }
.benefit-title { font-size:.74rem; font-weight:700; color:#0f3460; line-height:1.2; }
.benefit-sub { font-size:.7rem; color:var(--brand); text-decoration:none; }
.benefit-sub:hover { text-decoration:underline; }
@media (max-width:767px) {
    .benefits-bar-inner { overflow-x:auto; scrollbar-width:none; flex-wrap:nowrap; padding:.1rem .5rem; }
    .benefits-bar-inner::-webkit-scrollbar { display:none; }
    .benefit-item { flex:0 0 auto; padding:.4rem .5rem; gap:.4rem; border-right:1px solid #f0f4f8; }
    .benefit-title { display:none; }
    .benefit-sub { font-size:.68rem; white-space:nowrap; }
}
/* ── Cuotas bancos ── */
.cuotas-section { background:#fff; border-bottom:1px solid #e8ecf0; padding:.65rem 0; }
.cuotas-inner { max-width:1280px; margin:0 auto; display:flex; align-items:center; gap:.5rem; padding:0 .75rem; }
.cuotas-track { display:flex; gap:.7rem; overflow-x:auto; scroll-behavior:smooth; scrollbar-width:none; flex:1; padding:.1rem .05rem; }
@media (min-width:768px) { .cuotas-track { justify-content:center; } }
.cuotas-track::-webkit-scrollbar { display:none; }
.cuota-card { flex:0 0 auto; display:flex; flex-direction:column; align-items:center; gap:.3rem; cursor:default; }
.cuota-card-box {
    width:78px; background:#94a3b8; border-radius:10px;
    padding:.45rem .45rem .35rem;
    display:flex; flex-direction:column; align-items:center; gap:.3rem;
}
.cuota-logo-wrap {
    width:100%; background:#fff; border-radius:6px;
    height:38px; display:flex; align-items:center; justify-content:center;
    overflow:hidden; padding:0 4px;
}
.cuota-logo-wrap img { max-height:28px; max-width:100%; object-fit:contain; }
.cuota-logo-text { font-size:.7rem; font-weight:900; text-align:center; line-height:1.1; }
.cuota-desc { color:#fff; font-size:.7rem; font-weight:700; text-align:center; line-height:1.15; }
.cuota-desc strong { font-size:.95rem; display:block; }
.cuota-banco-nombre { font-size:.6rem; font-weight:700; color:#64748b; text-align:center; letter-spacing:.02em; }
.cuotas-arrow {
    flex-shrink:0; background:#fff; border:1px solid #e2e8f0;
    border-radius:50%; width:30px; height:30px;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:.75rem; color:#475569;
    transition:background .12s, border-color .12s;
}
.cuotas-arrow:hover { background:#f1f5f9; border-color:#cbd5e1; }
/* ── WhatsApp floating button ── */
.wa-float {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    width: 56px; height: 56px;
    background: #25d366; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(0,0,0,.25);
    z-index: 1050; text-decoration: none;
    transition: transform .2s, box-shadow .2s;
}
.wa-float:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(0,0,0,.3); }
.wa-float svg { width: 30px; height: 30px; fill: #fff; }
.checkout-divider {
    display: flex; align-items: center; gap: .75rem;
    margin: .9rem 0 0;
    color: #94a3b8; font-size: .8rem;
}
.checkout-divider::before, .checkout-divider::after {
    content: ''; flex: 1; height: 1px; background: #e2e8f0;
}
/* ── Modal de detalle de producto (fullscreen) ── */
.prod-modal-overlay {
    position: fixed; inset: var(--nav-h, 63px) 0 0 0;
    background: rgba(0,0,0,.4);
    z-index: 1070;
    opacity: 0; visibility: hidden;
    transition: opacity .25s, visibility .25s;
}
.prod-modal-overlay.open { opacity: 1; visibility: visible; }
/* Modal de producto — ocupa la pantalla debajo de la navbar, que queda
   visible y usable (buscador, carrito, ingresar, logo → inicio) */
.prod-modal {
    position: fixed; inset: var(--nav-h, 63px) 0 0 0;
    z-index: 1071;
    display: flex;
    pointer-events: none;
    opacity: 0; visibility: hidden;
    transition: opacity .25s, visibility .25s;
}
.prod-modal.open { opacity: 1; visibility: visible; pointer-events: auto; }
.prod-modal-box {
    background: #f6f7fb;
    width: 100%;
    height: 100%;
    overflow-y: auto;
    transform: translateY(30px);
    transition: transform .25s;
    pointer-events: auto;
}
.prod-modal.open .prod-modal-box { transform: translateY(0); }

/* Ficha de producto: dos columnas sobre tarjeta blanca (como producto.php) */
.pm-page { max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
.pm-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    padding: 2rem;
    margin-bottom: 1.5rem;
}
.pm-info { display: flex; flex-direction: column; gap: .65rem; }
.pm-section-title {
    font-size: .7rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: .35rem;
}
@media (max-width: 767px) {
    .pm-layout { grid-template-columns: 1fr; padding: 1rem; gap: 1.25rem; }
    .pm-page   { padding: .75rem .5rem 2rem; }
}
/* La barra de cuotas y los similares van fuera de la tarjeta, como en la ficha */
.pm-page .cuotas-section {
    border: 1px solid #e8ecf0; border-radius: 14px;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    margin-bottom: 1.5rem;
}
.prod-modal-img-wrap {
    width: 100%;
    aspect-ratio: 1/1;
    background: #f6f8fb;
    border-radius: 10px;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.prod-modal-img-wrap img {
    width: 100%; height: 100%; object-fit: contain;
    padding: 1.25rem;
}
.prod-modal-placeholder {
    font-size: 5rem; color: #cbd5e1;
}
.prod-modal-cat {
    font-size: .73rem; font-weight: 600;
    color: var(--brand); text-transform: uppercase; letter-spacing: .05em;
}
.prod-modal-nombre {
    font-size: 1.4rem; font-weight: 800; color: #1a1a2e;
    line-height: 1.2;
}
.prod-modal-precio {
    font-size: 1.75rem; font-weight: 800; color: var(--brand);
}
.prod-modal-precio small { font-size: .85rem; font-weight: 500; color: #64748b; }
.prod-modal-desc {
    font-size: .9rem; color: #475569; line-height: 1.65;
    white-space: pre-line;
}
/* Barra superior del producto: volver al listado + cerrar */
.prod-modal-topbar {
    position: sticky; top: 0; z-index: 5;
    width: 100%;
    display: flex; align-items: center; justify-content: space-between;
    gap: .75rem;
    padding: .5rem .75rem;
    background: rgba(255,255,255,.94);
    backdrop-filter: blur(6px);
    border-bottom: 1px solid #eef2f6;
}
.prod-modal-back {
    display: inline-flex; align-items: center; gap: .4rem;
    background: none; border: none; padding: .3rem .5rem;
    border-radius: 8px;
    font-size: .88rem; font-weight: 600; color: #334155;
    cursor: pointer;
    transition: color .15s, background .15s;
}
.prod-modal-back:hover { color: var(--brand); background: #f1f5f9; }
.prod-modal-back:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
.prod-modal-close {
    background: none;
    border: none; border-radius: 50%;
    width: 34px; height: 34px;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; color: #64748b;
    transition: background .15s, color .15s;
}
.prod-modal-close:hover { background: #f1f5f9; color: #e03d3d; }
.prod-modal-img-wrap { position: relative; }
/* ── Productos similares (dentro del modal de producto) ── */
.similares-section { margin-top: 1.5rem; }
.similares-title { font-size: 1rem; font-weight: 800; color: #1a1a2e; margin-bottom: .75rem; }
.similares-shell { position: relative; }
.similares-grid {
    display: flex; gap: 1rem;
    overflow-x: auto; scrollbar-width: none;
    padding-bottom: .4rem;
    scroll-behavior: smooth;
    scroll-snap-type: x proximity;
    /* margen final para que la última tarjeta no quede pegada al borde */
    padding-right: .25rem;
}
.similares-grid::-webkit-scrollbar { display: none; }
/* Flechas del carrusel de similares */
.similares-nav {
    position: absolute; top: calc(50% - .2rem); transform: translateY(-50%);
    width: 34px; height: 34px; z-index: 2;
    display: flex; align-items: center; justify-content: center;
    background: #fff; color: #334155;
    border: 1px solid #e8ecf0; border-radius: 50%;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .16);
    font-size: .95rem; line-height: 1; cursor: pointer;
    transition: color .15s, border-color .15s, box-shadow .15s, opacity .15s, transform .15s;
}
.similares-nav:hover {
    color: var(--brand); border-color: var(--brand);
    box-shadow: 0 5px 16px rgba(15, 23, 42, .22);
    transform: translateY(-50%) scale(1.06);
}
.similares-nav:active { transform: translateY(-50%) scale(.96); }
.similares-nav:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
.similares-nav[disabled] { opacity: 0; pointer-events: none; }
.similares-nav.prev { left: -12px; }
.similares-nav.next { right: -12px; }
/* En mobile el padding lateral es menor: las flechas no deben desbordar */
@media (max-width: 767px) {
    .similares-nav.prev { left: -6px; }
    .similares-nav.next { right: -6px; }
}
.similares-card {
    scroll-snap-align: start;
    flex: 0 0 155px; background: #fff; border-radius: 12px;
    box-shadow: 0 1px 5px rgba(0,0,0,.07); overflow: hidden;
    cursor: pointer; transition: box-shadow .15s, transform .15s; text-decoration: none; color: inherit;
    display: flex; flex-direction: column;
}
.similares-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.13); transform: translateY(-2px); }
.similares-card-img { aspect-ratio: 1/1; background: #f6f8fb; display: flex; align-items: center; justify-content: center; padding: .75rem; overflow: hidden; }
.similares-card-img img { width: 100%; height: 100%; object-fit: contain; }
.similares-card-body { padding: .55rem .7rem .7rem; flex: 1; display: flex; flex-direction: column; gap: .2rem; }
.similares-card-name { font-size: .76rem; font-weight: 700; color: #1a1a2e; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; }
.similares-card-price { font-size: .88rem; font-weight: 800; color: var(--brand); margin-top: auto; padding-top: .25rem; }

/* ── Carousel fotos adicionales ── */
.pm-carousel { position: relative; width: 100%; height: 100%; }
.pm-carousel img { width: 100%; height: 100%; object-fit: contain; padding: 1rem; display: block; }
.pm-carousel-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,.88); border: none; border-radius: 50%;
    width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .85rem; color: #1a1a2e; z-index: 3;
    box-shadow: 0 2px 8px rgba(0,0,0,.2); transition: background .15s;
}
.pm-carousel-btn:hover { background: #fff; }
.pm-carousel-btn.pm-prev { left: .5rem; }
.pm-carousel-btn.pm-next { right: .5rem; }
#publicidadesWrap .carousel { border-radius: 12px; overflow: hidden; }
.pm-carousel-dots {
    position: absolute; bottom: .45rem; left: 0; right: 0;
    display: flex; justify-content: center; gap: .3rem; z-index: 3;
}
.pm-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: rgba(0,0,0,.22); border: none; padding: 0; cursor: pointer;
    transition: background .15s;
}
.pm-dot.active { background: var(--brand); }
/* card — zona clickable */
.product-card .product-body { cursor: pointer; }
.product-card .product-body:hover .product-name { color: var(--brand); }
.form-label-req::after { content: ' *'; color: #e03d3d; }

/* ── Toast ── */
.toast-container-custom {
    position: fixed; bottom: 1.5rem; left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    display: flex; flex-direction: column; gap: .5rem;
    pointer-events: none;
    min-width: 220px;
}
.toast-msg {
    background: #1a1a2e;
    color: #fff;
    padding: .65rem 1.2rem;
    border-radius: 999px;
    font-size: .875rem;
    font-weight: 500;
    text-align: center;
    animation: toastIn .25s ease;
    pointer-events: auto;
}
.toast-msg.error { background: #e03d3d; }
.toast-msg.success { background: #16a34a; }
@keyframes toastIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

/* ── Banners de estado de pago ── */
.payment-banner {
    padding: 1.1rem 1.5rem;
    display: flex; align-items: center; gap: .75rem;
    font-weight: 600;
    font-size: .95rem;
}
.payment-banner.approved { background: #dcfce7; color: #15803d; border-bottom: 2px solid #16a34a; }
.payment-banner.failure  { background: #fee2e2; color: #b91c1c; border-bottom: 2px solid #dc2626; }
.payment-banner.pending  { background: #fef3c7; color: #92400e; border-bottom: 2px solid #d97706; }

/* ── Pantalla de inicio (cubre el flash de estilos) ── */
#pageLoader {
    position: fixed; inset: 0;
    background: #fff;
    z-index: 10000;
    display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 1rem;
    transition: opacity .3s ease;
}
#pageLoader.oculto { opacity: 0; pointer-events: none; }
.pl-spinner {
    width: 44px; height: 44px;
    border: 3px solid #e2e8f0;
    border-top-color: var(--brand);
    border-radius: 50%;
    animation: spin .75s linear infinite;
}
.pl-texto {
    font-size: .85rem; color: #94a3b8;
    font-family: 'Segoe UI', system-ui, sans-serif;
    letter-spacing: .03em;
}

/* ── Loading ── */
.loading-overlay {
    position: fixed; inset: 0;
    background: rgba(255,255,255,.9);
    z-index: 9998;
    display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 1rem;
    opacity: 0; visibility: hidden; transition: opacity .3s, visibility .3s;
}
.loading-overlay.show { opacity: 1; visibility: visible; }
.spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: var(--brand); border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Skeleton product card ── */
.product-card-skeleton {
    background: #fff;
    border: 1px solid #e8ecf0;
    border-radius: 14px;
    overflow: hidden;
}

/* ── Footer ── */
.site-footer {
    background: #0f172a;
    color: #94a3b8;
    padding: 3rem 1.5rem 0;
    margin-top: 4rem;
}
.footer-inner {
    max-width: 1280px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 2.5rem;
    padding-bottom: 2.5rem;
}
@media (max-width: 767px) {
    .footer-inner { grid-template-columns: 1fr; gap: 2rem; }
}
/* Categorías destacadas: tira horizontal deslizable en celular */
@media (max-width: 575.98px) {
    #categoriasDestacadasRow {
        flex-wrap: nowrap;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    #categoriasDestacadasRow::-webkit-scrollbar { display: none; }
    #categoriasDestacadasRow > div {
        flex: 0 0 78%;
        max-width: 78%;
        scroll-snap-align: start;
    }
}

/* ── Secciones de descuentos (home) ── */
/* --dsc-main: color propio del descuento (configurable por comercio).
   Sin configurar, hereda el color general de la tienda. */
.dsc-section {
    --dsc-main: var(--brand);
    position: relative;
    border-radius: 18px;
    padding: .8rem .9rem .9rem;
    margin-bottom: 1rem;
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    box-shadow: 0 6px 20px rgba(15,23,42,.05);
    overflow: hidden;
    isolation: isolate;
}
.dsc-section--recargo { --dsc-main: #b45309; }
.dsc-section-head {
    display: flex; align-items: baseline; gap: .6rem;
    margin: 0 .35rem .55rem;
}
.dsc-section-titles { display: flex; flex-direction: column; gap: .12rem; min-width: 0; }
.dsc-section-eyebrow {
    font-size: .62rem; font-weight: 800; letter-spacing: .2em; text-transform: uppercase;
    color: var(--dsc-main); display: inline-flex; align-items: center; gap: .35rem;
}
.dsc-section-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(1.15rem, .9rem + 1.2vw, 1.55rem);
    font-weight: 400; font-style: italic; color: #1e293b; margin: 0; line-height: 1.02;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dsc-section-vertodos {
    margin-left: auto; flex-shrink: 0; align-self: center;
    display: inline-flex; align-items: center; gap: .35rem;
    background: #fff; color: var(--dsc-main);
    border: 1px solid rgba(15,23,42,.12); border-radius: 999px;
    padding: .34rem .85rem; font-size: .76rem; font-weight: 700;
    cursor: pointer; transition: background .18s, transform .18s;
    text-decoration: none; white-space: nowrap;
}
.dsc-section-vertodos:hover { background: var(--dsc-main); border-color: var(--dsc-main); transform: translateX(2px); color: #fff; }
.dsc-section-shell { position: relative; }
.dsc-section-row {
    display: flex; flex-wrap: nowrap; gap: .85rem;
    overflow-x: auto; scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch; scroll-behavior: smooth;
    padding: .15rem .35rem .3rem; margin: 0 -.1rem;
    scrollbar-width: none;
}
.dsc-section-row::-webkit-scrollbar { display: none; }
/* Flechas del carrusel de ofertas */
.dsc-section-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 36px; height: 36px; z-index: 2;
    display: flex; align-items: center; justify-content: center;
    background: #fff; color: #334155;
    border: 1px solid #e8ecf0; border-radius: 50%;
    box-shadow: 0 3px 12px rgba(15,23,42,.16);
    font-size: 1rem; line-height: 1; cursor: pointer;
    transition: color .15s, border-color .15s, box-shadow .15s, opacity .15s, transform .15s;
}
.dsc-section-nav:hover {
    color: var(--dsc-main); border-color: var(--dsc-main);
    box-shadow: 0 5px 16px rgba(15,23,42,.22);
    transform: translateY(-50%) scale(1.06);
}
.dsc-section-nav:active { transform: translateY(-50%) scale(.96); }
.dsc-section-nav:focus-visible { outline: 2px solid var(--dsc-main); outline-offset: 2px; }
.dsc-section-nav[disabled] { opacity: 0; pointer-events: none; }
.dsc-section-nav.prev { left: -10px; }
.dsc-section-nav.next { right: -10px; }
@media (max-width: 575.98px) {
    .dsc-section-nav { width: 32px; height: 32px; }
    .dsc-section-nav.prev { left: -4px; }
    .dsc-section-nav.next { right: -4px; }
}
.dsc-section-row > .product-card {
    flex: 0 0 155px; width: 155px; scroll-snap-align: start;
    box-shadow: 0 4px 14px rgba(15,23,42,.07);
}
/* Imagen más baja que en la grilla: la sección no debe comerse la pantalla */
.dsc-section-row > .product-card .product-img,
.dsc-section-row > .product-card .product-img-placeholder { aspect-ratio: 4/3; }
/* En las tarjetas de descuento solo se muestra la categoría, no el nombre */
.dsc-section-row > .product-card .product-name { display: none; }
@media (max-width: 575.98px) {
    .dsc-section { border-radius: 14px; padding: .75rem .6rem .8rem; }
    .dsc-section-row > .product-card { flex: 0 0 48%; width: 48%; max-width: 190px; }
}
.footer-logo {
    height: 48px;
    width: auto;
    object-fit: contain;
    margin-bottom: .85rem;
    border-radius: 6px;
}
#navLogo{
    border-radius: 6px;
}
.footer-brand-name {
    display: block;
    color: #f1f5f9;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: .5rem;
    line-height: 1.2;
}
.footer-desc {
    font-size: .875rem;
    line-height: 1.65;
    margin: 0;
    color: #64748b;
}
.footer-section-title {
    color: #e2e8f0;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 1rem;
}
.footer-item {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    font-size: .875rem;
    margin-bottom: .55rem;
    line-height: 1.45;
}
.footer-item i {
    color: var(--brand);
    flex-shrink: 0;
    margin-top: .1rem;
}
.footer-item a {
    color: #94a3b8;
    text-decoration: none;
    transition: color .15s;
}
.footer-item a:hover { color: #f1f5f9; }
.footer-bottom {
    border-top: 1px solid rgba(255,255,255,.07);
    padding: 1.1rem 0;
    max-width: 1280px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    font-size: .8rem;
    color: #475569;
}
.footer-bottom a { color: #475569; text-decoration: none; }
.footer-bottom a:hover { color: var(--brand); }
.footer-powered strong { color: var(--brand); }
.footer-legal-links { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.footer-arrepentimiento-btn { background: none; border: 1px solid #475569; color: #475569; border-radius: 4px; padding: .15rem .6rem; font-size: .8rem; cursor: pointer; transition: border-color .15s, color .15s; }
.footer-arrepentimiento-btn:hover { border-color: var(--brand); color: var(--brand); }
.legal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.6); z-index: 1090; display: flex; align-items: center; justify-content: center; padding: 1rem; opacity: 0; visibility: hidden; transition: opacity .2s, visibility .2s; }
.legal-overlay.open { opacity: 1; visibility: visible; }
.legal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 500px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 24px 64px rgba(0,0,0,.22); }
.legal-box-header { display: flex; align-items: center; justify-content: space-between; padding: .85rem 1rem; border-bottom: 1px solid #e2e8f0; font-size: .9rem; font-weight: 700; color: #1e293b; gap: .5rem; }
.legal-close-btn { background: none; border: none; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #94a3b8; cursor: pointer; flex-shrink: 0; transition: background .12s, color .12s; }
.legal-close-btn:hover { background: #f1f5f9; color: #1e293b; }
.legal-box-body { padding: 1rem; overflow-y: auto; white-space: pre-wrap; font-size: .875rem; color: #374151; line-height: 1.6; }
#arrepentimientoOverlay .legal-box-header { background: #fef2f2; border-bottom-color: #fecaca; }
#arrepentimientoOverlay .legal-box-body { white-space: normal; }
.arr-icon { width: 26px; height: 26px; background: #fee2e2; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: #dc2626; font-size: .85rem; flex-shrink: 0; }
.arr-aviso { font-size: .78rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; padding: .55rem .75rem; margin-bottom: .85rem; line-height: 1.5; }
.arr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .45rem .7rem; }
@media (max-width: 420px) { .arr-grid { grid-template-columns: 1fr; } }
.arr-field-full { grid-column: 1 / -1; }
.arr-label { display: block; font-size: .72rem; font-weight: 600; color: #475569; margin-bottom: .2rem; }
.arr-footer { display: flex; justify-content: flex-end; align-items: center; gap: .5rem; margin-top: .85rem; padding-top: .75rem; border-top: 1px solid #f1f5f9; }
.arr-ok { text-align: center; padding: 1.75rem 0 1rem; }
.arr-ok .bi-check-circle-fill { font-size: 2.25rem; }
.footer-social { display: flex; gap: .55rem; flex-wrap: wrap; margin-top: .75rem; }
.footer-social-btn {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,.08);
    display: flex; align-items: center; justify-content: center;
    color: #94a3b8; font-size: 1.15rem; text-decoration: none;
    transition: background .15s, color .15s;
}
.footer-social-btn:hover { background: var(--brand); color: #fff; }
</style>
</head>
<body>

<!-- ── Pantalla de carga inicial ── -->
<div id="pageLoader">
    <div class="pl-spinner"></div>
    <div class="pl-texto">Cargando tienda...</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     BANNER DE ESTADO DE PAGO
═══════════════════════════════════════════════════════════ -->
<?php if ($status === 'approved'): ?>
<div class="payment-banner approved">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <span>¡Pago confirmado! Tu pedido fue registrado exitosamente<?= $idVenta ? " (#$idVenta)" : '' ?>. En breve recibirás un email de confirmación.</span>
</div>
<?php elseif ($status === 'failure'): ?>
<div class="payment-banner failure">
    <i class="bi bi-x-circle-fill fs-5"></i>
    <span>El pago no pudo procesarse. Por favor intentá nuevamente o elegí otro medio de pago.</span>
</div>
<?php elseif ($status === 'pending'): ?>
<div class="payment-banner pending">
    <i class="bi bi-clock-fill fs-5"></i>
    <span>Pago pendiente de acreditación. Te notificaremos cuando sea confirmado.</span>
</div>
<?php elseif ($status === 'pedido_ok'): ?>
<div class="payment-banner approved">
    <i class="bi bi-bag-check-fill fs-5"></i>
    <span>¡Pedido registrado<?= $idVenta ? " (#$idVenta)" : '' ?>! Nos comunicaremos para coordinar la entrega y el pago.</span>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════════════════════ -->
<nav class="navbar-tienda">
    <div class="d-flex align-items-center gap-3" style="max-width:1280px;margin:0 auto;width:100%">
        <!-- Logo + nombre -->
        <a href="?comercio=<?= urlencode($comercio) ?><?= isset($_GET['modo']) ? '&modo=' . urlencode($_GET['modo']) : '' ?>" class="d-flex align-items-center gap-2 text-decoration-none flex-shrink-0">
            <img id="navLogo" src="" alt="Logo" class="brand-logo d-none">
            <span id="navNombre" class="brand-name">Cargando...</span>
        </a>

        <!-- Buscador -->
        <div class="search-box flex-grow-1">
            <i class="bi bi-search search-icon"></i>
            <!-- type=search + enterkeyhint: el teclado del celular muestra la lupa.
                 name/autocomplete/data-*: evitan que el gestor de contraseñas lo
                 tome como campo de usuario del login -->
            <input type="search" id="searchInput" name="busqueda" class="form-control"
                   placeholder="Buscar productos..." autocomplete="off" spellcheck="false"
                   inputmode="search" enterkeyhint="search"
                   data-form-type="other" data-lpignore="true" data-1p-ignore data-bwignore>
            <div class="ac-dropdown" id="acDropdown"></div>
        </div>

        <!-- Login cliente -->
        <div class="flex-shrink-0" id="authNav">
            <button class="btn-auth" id="btnLoginNav" onclick="openLoginModal()">
                <i class="bi bi-person-circle"></i>
                <span class="d-none d-sm-inline ms-1">Ingresar</span>
            </button>
            <div id="authUserNav" class="d-none align-items-center gap-2">
                <span class="auth-user-name d-none d-sm-inline" id="authUserName"></span>
                <button class="btn-auth btn-auth-sm" onclick="logoutCliente()" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Carrito -->
        <button class="btn-cart flex-shrink-0" onclick="openCart()">
            <i class="bi bi-cart3"></i>
            <span class="d-none d-sm-inline ms-1">Carrito</span>
            <span id="cartBadge" class="cart-badge d-none">0</span>
        </button>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     MODAL LOGIN CLIENTE
═══════════════════════════════════════════════════════════ -->
<div class="login-overlay" id="loginOverlay" onclick="closeLoginModal(event)">
    <div class="login-modal" onclick="event.stopPropagation()">
        <button class="login-close" onclick="closeLoginModal()">&times;</button>
        <div class="text-center mb-4">
            <i class="bi bi-person-circle" style="font-size:2.5rem;color:var(--brand)"></i>
            <h5 class="fw-bold mt-2 mb-0">Ingresar a la tienda</h5>
            <p class="text-muted small mt-1">Ingresá para ver tus precios especiales</p>
        </div>
        <div id="loginError" class="alert alert-danger py-2 small d-none"></div>
        <!-- Los campos van dentro de un form para que el navegador acote el
             autocompletado de credenciales acá y no al buscador de la navbar -->
        <form onsubmit="submitLogin();return false">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" class="form-control" id="loginEmail" name="email" placeholder="tu@email.com" autocomplete="email">
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold">Contraseña</label>
                <input type="password" class="form-control" id="loginPass" name="password" placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btnLoginSubmit">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
            </button>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ -->
<div class="hero d-none" id="heroSection">
    <div class="hero-eyebrow" id="heroEyebrow"></div>
    <p id="heroDesc"></p>
</div>

<!-- ═══════════════════════════════════════════════════════════
     SELECTOR DE CATEGORÍAS + NAV RÁPIDA
═══════════════════════════════════════════════════════════ -->
<div class="cat-selector-bar" id="catSelectorBar">
    <div class="cat-selector-bar-inner">

        <button class="cat-trigger" id="catTrigger" onclick="toggleCatPanel(event)">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            Categorías
            <i class="bi bi-chevron-down chev"></i>
        </button>

    </div>
    <div class="cat-active-path d-none" id="catActivePath"
         style="padding:.3rem 1rem .35rem;font-size:.83rem;border-top:1px solid #f1f5f9;height: 3rem;"></div>
    <!-- Panel doble (inline, empuja el contenido) -->
    <div class="cat-panel-wrap" id="catPanelWrap">
        <div class="cat-panel">
            <div class="cat-panel-parents" id="catPanelParents">
                <div class="cp-item hover" data-cat="0" onclick="selectCatPanel(0,null,null)">
                    Todos los productos
                </div>
            </div>
            <div class="cat-panel-subs" id="catPanelSubs">
                <div class="cp-empty"><i class="bi bi-arrow-left me-1"></i>Elegí una categoría</div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CARRUSEL BANNERS
═══════════════════════════════════════════════════════════ -->
<div style="padding: .75rem .75rem 0; display:none" id="carouselBannersWrap">
<div id="carouselBanners" class="carousel slide" data-bs-ride="carousel" style="border-radius:12px;overflow:hidden">
    <div class="carousel-indicators" id="carouselIndicators"></div>
    <div class="carousel-inner" id="carouselInner"></div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanners" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselBanners" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>
</div>

<!-- Categorías destacadas (oculto hasta que loadCategorias detecte) -->
<div class="container my-3 d-none" id="categoriasDestacadasWrap">
    <div class="cat-dest-shell" id="categoriasDestacadasShell">
        <button type="button" class="cat-dest-nav prev" id="catDestPrev" aria-label="Categorías anteriores" disabled>
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="d-flex flex-nowrap gap-2 pb-1" id="categoriasDestacadasRow"
             style="overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch"></div>
        <button type="button" class="cat-dest-nav next" id="catDestNext" aria-label="Categorías siguientes" disabled>
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Cuotas sin interés por banco (oculto hasta que loadComercio lo active) -->
<div class="cuotas-section d-none" id="cuotasSection">
    <div class="cuotas-inner">
        <button class="cuotas-arrow" onclick="document.getElementById('cuotasTrack').scrollBy({left:-220,behavior:'smooth'})">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="cuotas-track" id="cuotasTrack"></div>
        <button class="cuotas-arrow" onclick="document.getElementById('cuotasTrack').scrollBy({left:220,behavior:'smooth'})">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Barra de envío (oculta hasta que loadComercio la active) -->
<div class="envio-bar d-none" id="envioBar">
    <i class="bi bi-truck me-2"></i><span id="envioText"></span>
</div>

<!-- Banner medios de pago con descuento/recargo (oculto hasta que loadComercio detecte descuentos) -->
<div id="fpMethodsBanner" style="display:none"></div>

<!-- Secciones de descuentos activos (oculto hasta que loadDescuentos detecte) -->
<div class="container my-4 d-none" id="descuentosSectionsWrap"></div>

<!-- IDs legacy para compatibilidad con el JS original -->
<div id="mobileMarcasFiltros" style="display:none"></div>
<div id="mobileAtribFiltros"  style="display:none"></div>
<div id="mobileCats"          style="display:none"><div id="mobileCatsParents"></div><div id="mobileCatsSubs"></div></div>
<div id="sidebarCats"         style="display:none"></div>
<div id="marcasFiltrosWrap"   style="display:none"></div>
<div id="atribFiltrosWrap"    style="display:none"></div>

<!-- ═══════════════════════════════════════════════════════════
     LAYOUT WEB4: SIDEBAR FILTROS (desktop) + CONTENIDO
═══════════════════════════════════════════════════════════ -->
<div class="w4-layout sin-filtros">

    <!-- SIDEBAR: visible sólo con categoría, búsqueda o descuento activo -->
    <aside class="w4-sidebar" id="filtrosWrap">
        <!-- Título desktop + reinicio de filtros -->
        <div class="w4-sidebar-head">
            <div class="w4-sidebar-title">Filtros</div>
            <button type="button" class="btn-reset-filtros" id="btnResetFiltros" onclick="resetFiltros()">
                <i class="bi bi-arrow-counterclockwise"></i> Reiniciar
            </button>
        </div>
        <!-- Contenido de filtros -->
        <div class="filtros-panel" id="filtrosPanel">
            <div class="filtros-panel-inner">
                <div class="w4-filtro-bloque d-none" id="catFiltrosInline"></div>
                <div id="marcasFiltrosInline" style="display:flex;flex-direction:column;align-items:flex-start;gap:.15rem"></div>
                <div id="atribFiltrosInline" style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem"></div>
                <div class="w4-filtro-bloque">
                    <div class="filtro-label" style="margin-bottom:.4rem">Precio</div>
                    <div class="price-slider-wrap">
                        <div class="price-slider-labels">
                            <span id="precioMinLabel">$0</span>
                            <span id="precioMaxLabel">—</span>
                        </div>
                        <div class="price-slider-track">
                            <div class="price-slider-bar"></div>
                            <div class="price-slider-range" id="priceSliderRange"></div>
                            <input type="range" id="sliderPrecioMin" min="0" max="1000000" step="50000" value="0"
                                   oninput="onPriceSliderInput(event)">
                            <input type="range" id="sliderPrecioMax" min="0" max="1000000" step="50000" value="1000000"
                                   oninput="onPriceSliderInput(event)">
                        </div>
                        <button class="btn-clear-price d-none" id="btnClearPrice" onclick="clearPriceFilter()">
                            × Quitar filtro de precio
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO: toolbar + productos + paginación -->
    <div class="w4-main">
        <div class="tienda-layout">
            <div class="toolbar">
                <span class="toolbar-label" id="toolbarLabel">Cargando productos...</span>
                <select id="sortSelect" class="form-select form-select-sm" style="width:auto;min-width:170px">
                    <option value="nombre_asc"  selected>Nombre A → Z</option>
                    <option value="nombre_desc">Nombre Z → A</option>
                    <option value="nuevo_desc">Más recientes primero</option>
                    <option value="actualizado_desc">Lo último</option>
                    <option value="precio_asc">Precio menor → mayor</option>
                    <option value="precio_desc">Precio mayor → menor</option>
                </select>
            </div>
            <div class="products-grid" id="productsGrid">
                <!-- Skeletons -->
                <?php for ($i = 0; $i < 8; $i++): ?>
                <div class="product-card-skeleton">
                    <div class="skeleton" style="aspect-ratio:1"></div>
                    <div style="padding:.75rem">
                        <div class="skeleton mb-2" style="height:12px;width:60%"></div>
                        <div class="skeleton mb-1" style="height:14px"></div>
                        <div class="skeleton mb-3" style="height:14px;width:80%"></div>
                        <div class="skeleton" style="height:36px"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <div id="pagination" class="pagination-wrap"></div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     CART DRAWER
═══════════════════════════════════════════════════════════ -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
        <span><i class="bi bi-cart3 me-2"></i>Tu carrito</span>
        <button class="btn-remove" onclick="closeCart()" title="Cerrar">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>
    <div class="cart-body" id="cartBody">
        <div class="cart-empty">
            <i class="bi bi-cart-x"></i>
            Tu carrito está vacío
        </div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display:none">
        <div class="cart-total">
            <span>Total</span>
            <span id="cartTotal">$0</span>
        </div>
        <div id="cartPromoBadge" style="display:none;padding:5px 12px;font-size:.78rem;color:#0dcaf0;background:rgba(13,202,240,.1);border:1px solid rgba(13,202,240,.3);border-radius:6px;margin:0 0 8px;text-align:center;line-height:1.4"></div>
        <button class="btn-checkout" onclick="openCheckout()">
            <i class="bi bi-bag-check me-2"></i>Iniciar compra
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CHECKOUT MODAL
═══════════════════════════════════════════════════════════ -->
<div class="checkout-modal" id="checkoutModal">
    <div id="checkoutBackdrop"></div>
    <div class="checkout-box">
        <div class="checkout-box-header">
            <h2 class="checkout-title mb-0">Finalizar compra</h2>
            <button class="btn-remove" onclick="closeCheckout()"><i class="bi bi-x-lg fs-5"></i></button>
        </div>
        <div class="checkout-box-body">

        <!-- Resumen de la orden -->
        <div class="order-summary" id="orderSummary"></div>

        <!-- Formulario de datos -->
        <form id="checkoutForm" onsubmit="submitCheckout(event)">
            <!-- Selector de cliente (solo modo=comercio) -->
            <div class="mb-3" id="ckClienteWrap" style="display:none">
                <label class="form-label form-label-req">Cliente</label>
                <select class="form-select" id="ckClienteSelect" onchange="onSelectCliente(this)">
                    <option value="">— Seleccioná un cliente —</option>
                </select>
            </div>
            <!-- Datos del cliente (ocultos en modo=comercio cuando se selecciona cliente) -->
            <div id="ckNombreEmailWrap">
            <div class="mb-3">
                <label class="form-label form-label-req">Nombre completo</label>
                <input type="text" class="form-control" id="ckNombre"
                       placeholder="Juan Pérez" required autocomplete="name">
            </div>
            <div class="mb-3">
                <label class="form-label form-label-req">Email</label>
                <input type="email" class="form-control" id="ckEmail"
                       placeholder="tu@email.com" required autocomplete="email">
            </div>
            </div>
            <div class="mb-3" id="ckTelefonoWrap">
                <label class="form-label" id="ckTelefonoLabel">Teléfono</label>
                <input type="tel" class="form-control" id="ckTelefono"
                       placeholder="+54 11 1234-5678" autocomplete="tel">
            </div>

            <!-- Datos de envío (modo controlado por comercio: opcional/obligatorio/no_pedir) -->
            <div class="mb-4" id="envioWrap">
                <div id="envioOblHdr" style="display:none;font-size:.875rem;font-weight:600;color:#475569;margin-bottom:.6rem">
                    <i class="bi bi-geo-alt-fill" style="color:var(--brand)"></i> Datos de envío
                </div>
                <button type="button" id="btnToggleEnvio"
                        onclick="toggleEnvioSection()"
                        style="width:100%;background:#f6f8fb;border:1px dashed #cbd5e1;border-radius:8px;padding:.55rem .9rem;font-size:.875rem;color:#475569;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:background .15s">
                    <i class="bi bi-geo-alt-fill" style="color:var(--brand)"></i>
                    <span>Agregar datos de envío</span>
                    <span style="font-size:.78rem;color:#94a3b8">(opcional)</span>
                    <i class="bi bi-chevron-down" id="iconEnvio" style="margin-left:auto;font-size:.75rem;transition:transform .2s"></i>
                </button>
                <div id="envioSection" style="display:none;margin-top:.75rem">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.85rem">Provincia</label>
                        <select class="form-select form-select-sm" id="ckProvincia" autocomplete="address-level1">
                            <option value="">— Seleccioná una provincia —</option>
                            <option>Buenos Aires</option>
                            <option>Ciudad Autónoma de Buenos Aires</option>
                            <option>Catamarca</option>
                            <option>Chaco</option>
                            <option>Chubut</option>
                            <option>Córdoba</option>
                            <option>Corrientes</option>
                            <option>Entre Ríos</option>
                            <option>Formosa</option>
                            <option>Jujuy</option>
                            <option>La Pampa</option>
                            <option>La Rioja</option>
                            <option>Mendoza</option>
                            <option>Misiones</option>
                            <option>Neuquén</option>
                            <option>Río Negro</option>
                            <option>Salta</option>
                            <option>San Juan</option>
                            <option>San Luis</option>
                            <option>Santa Cruz</option>
                            <option>Santa Fe</option>
                            <option>Santiago del Estero</option>
                            <option>Tierra del Fuego</option>
                            <option>Tucumán</option>
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 100px;gap:.5rem;margin-bottom:.5rem">
                        <div>
                            <label class="form-label" style="font-size:.85rem">Localidad</label>
                            <input type="text" class="form-control form-control-sm" id="ckLocalidad"
                                   placeholder="Mar del Plata" autocomplete="address-level2">
                        </div>
                        <div>
                            <label class="form-label" style="font-size:.85rem">Cód. postal</label>
                            <input type="text" class="form-control form-control-sm" id="ckCP"
                                   placeholder="7600" autocomplete="postal-code" maxlength="10">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:.85rem">Dirección</label>
                        <input type="text" class="form-control form-control-sm" id="ckDireccion"
                               placeholder="Calle 123, Piso 2, Dpto B" autocomplete="street-address">
                    </div>
                </div>
            </div>

            <!-- Cotización de envío (visible solo si hay carriers activos con CP origen) -->
            <div id="ckCotizarEnvioWrap" style="display:none;margin-bottom:1rem">
                <div style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:10px;padding:.85rem 1rem">
                    <div style="font-size:.875rem;font-weight:600;color:#1e40af;margin-bottom:.6rem">
                        <i class="bi bi-truck me-1"></i>Cotizar envío
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:flex-end">
                        <div style="flex:1">
                            <label style="font-size:.8rem;color:#475569;display:block;margin-bottom:.25rem">Código postal de destino</label>
                            <input type="text" id="ckCPEnvio" class="form-control form-control-sm"
                                   placeholder="Ej: 7600" maxlength="10" pattern="[0-9]*"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="btnCotizarEnvio"
                                onclick="cotizarEnvioCheckout()" style="white-space:nowrap">
                            <i class="bi bi-search me-1"></i>Cotizar
                        </button>
                    </div>
                    <div id="ckCotizarEnvioResult" style="margin-top:.65rem;display:none"></div>
                </div>
            </div>

            <!-- Sección MP (oculta si el comercio no tiene MP configurado) -->
            <div id="ckMpSection" style="display:none">
                <div class="small text-muted mb-3">
                    <i class="bi bi-shield-lock me-1"></i>
                    Serás redirigido a MercadoPago para completar el pago de forma segura.
                </div>
                <div class="fp-disc-info" id="fpDiscMercadopago"></div>
                <button type="submit" class="btn-mp" id="btnPagar">
                    <i class="bi bi-credit-card"></i>
                    Pagar con MercadoPago
                </button>
                <div class="checkout-divider"><span>o</span></div>
            </div>

            <!-- Sección Nave (oculta hasta que loadComercio la active) -->
            <div id="ckNaveSection" style="display:none">
                <div class="checkout-divider"><span>o</span></div>
                <div class="small text-muted mb-2">
                    <i class="bi bi-shield-lock me-1"></i>
                    Serás redirigido a Nave para pagar con QR, tarjeta o billetera.
                </div>
                <div class="fp-disc-info" id="fpDiscNave"></div>
                <button type="button" class="btn-nave" id="btnNave" onclick="submitCheckout(null,'nave')">
                    <i class="bi bi-qr-code"></i>
                    Pagar con Nave
                </button>
            </div>

            <!-- Sección GoCuotas (oculta hasta que loadComercio la active) -->
            <div id="ckGocuotasSection" style="display:none">
                <div class="checkout-divider"><span>o</span></div>
                <div class="small text-muted mb-2">
                    <i class="bi bi-shield-lock me-1"></i>
                    Serás redirigido a GoCuotas para pagar en cuotas sin tarjeta de crédito.
                </div>
                <div class="fp-disc-info" id="fpDiscGocuotas"></div>
                <button type="button" class="btn-gocuotas" id="btnGocuotas" onclick="submitCheckout(null,'gocuotas')">
                    <i class="bi bi-credit-card-2-front"></i>
                    Pagar con GoCuotas
                </button>
            </div>

            <!-- Sección Payway (oculta hasta que loadComercio la active) -->
            <div id="ckPaywaySection" style="display:none">
                <div class="checkout-divider"><span>o</span></div>
                <div class="small text-muted mb-2">
                    <i class="bi bi-shield-lock me-1"></i>
                    Pagá con tarjeta de crédito o débito. Tus datos están protegidos por Payway.
                </div>
                <div class="fp-disc-info" id="fpDiscPayway"></div>
                <div class="pw-card-form">
                    <form id="formPayway" autocomplete="off" onsubmit="submitPayway(event)">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" id="pwCardNumber"
                                   data-decidir="card_number" placeholder="Número de tarjeta"
                                   maxlength="19" inputmode="numeric" autocomplete="cc-number">
                        </div>
                        <div class="pw-card-row mb-2">
                            <input type="text" class="form-control form-control-sm" id="pwExpMonth"
                                   data-decidir="card_expiration_month" placeholder="MM"
                                   maxlength="2" inputmode="numeric" style="max-width:64px">
                            <input type="text" class="form-control form-control-sm" id="pwExpYear"
                                   data-decidir="card_expiration_year" placeholder="AA"
                                   maxlength="2" inputmode="numeric" style="max-width:64px">
                            <input type="text" class="form-control form-control-sm" id="pwCvv"
                                   data-decidir="security_code" placeholder="CVV"
                                   maxlength="4" inputmode="numeric" style="max-width:72px">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" id="pwHolder"
                                   data-decidir="card_holder_name" placeholder="Nombre del titular (como figura en la tarjeta)"
                                   autocomplete="cc-name">
                        </div>
                        <div class="pw-card-row mb-3">
                            <input type="text" class="form-control form-control-sm" id="pwDocNumber"
                                   data-decidir="card_holder_doc_number" placeholder="DNI del titular"
                                   maxlength="15" inputmode="numeric">
                            <input type="hidden" data-decidir="card_holder_doc_type" value="dni">
                            <select class="form-select form-select-sm" id="pwCuotas" style="max-width:130px">
                                <option value="1">1 cuota</option>
                                <option value="3">3 cuotas</option>
                                <option value="6">6 cuotas</option>
                                <option value="12">12 cuotas</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-payway" id="btnPayway">
                            <i class="bi bi-credit-card-fill"></i>
                            Pagar con Payway
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sección Transferencia (oculta hasta que loadComercio la active) -->
            <div id="ckTransfSection" style="display:none">
                <div class="checkout-divider"><span>o</span></div>
                <div class="ck-transf-info">
                    <div class="fw-semibold mb-1"><i class="bi bi-bank me-1 text-success"></i>Transferencia bancaria</div>
                    <div class="font-monospace" id="ckTransfCBU" style="font-size:.93rem; word-break:break-all"></div>
                    <div class="text-muted mt-1" style="font-size:.82rem"><i class="bi bi-whatsapp me-1 text-success"></i>Enviá el comprobante por WhatsApp para confirmar tu pedido.</div>
                </div>
                <div class="fp-disc-info" id="fpDiscTransferencia"></div>
                <button type="button" class="btn-transf" id="btnTransf" onclick="submitPedidoSinPago('transferencia','btnTransf')">
                    <i class="bi bi-bank"></i>
                    Confirmar pedido (Transferencia)
                </button>
            </div>

            <!-- Sección Efectivo (oculta hasta que loadComercio la active) -->
            <div id="ckEfectivoSection" style="display:none">
                <div class="checkout-divider"><span>o</span></div>
                <div class="fp-disc-info" id="fpDiscEfectivo"></div>
                <button type="button" class="btn-efectivo" id="btnEfectivo" onclick="submitPedidoSinPago('efectivo','btnEfectivo')">
                    <i class="bi bi-cash-coin"></i>
                    Pagar en efectivo
                </button>
                <div class="text-muted mt-1" style="font-size:.82rem;text-align:center"><i class="bi bi-whatsapp me-1 text-success"></i>Coordinamos el pago por WhatsApp.</div>
            </div>

            <!-- Botón pedido sin pago (siempre visible) -->
            <button type="button" class="btn-pedido" id="btnPedido" onclick="submitPedidoSinPago('','btnPedido')">
                <i class="bi bi-bag-check"></i>
                Hacer pedido (coordinar pago)
            </button>
        </form>

        </div><!-- /checkout-box-body -->
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL DETALLE DE PRODUCTO
═══════════════════════════════════════════════════════════ -->
<div class="prod-modal-overlay" id="prodModalOverlay" onclick="closeProdModal()"></div>
<div class="prod-modal" id="prodModal">
    <div class="prod-modal-box">
        <div class="prod-modal-topbar">
            <button class="prod-modal-back" onclick="closeProdModal()">
                <i class="bi bi-arrow-left"></i> Volver a la tienda
            </button>
            <button class="prod-modal-close" onclick="closeProdModal()" title="Cerrar" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="pm-page">
        <div class="pm-layout">
        <div class="prod-modal-img-wrap" id="prodModalImgWrap">
            <img id="prodModalImg" src="" alt="" style="display:none">
            <div class="prod-modal-placeholder" id="prodModalPlaceholder">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
        <div class="pm-info">
            <div class="prod-modal-cat"  id="prodModalCat"></div>
            <div class="prod-modal-nombre" id="prodModalNombre"></div>
            <div class="prod-modal-precio" id="prodModalPrecio"></div>
            <div id="prodModalStockBadge"></div>
            <!-- Selector de variantes (visible solo si el producto tiene variantes) -->
            <div id="prodModalVariantes" style="display:none">
                <div style="font-size:.8rem;font-weight:600;color:#475569;margin-bottom:.4rem">Elegí una opción:</div>
                <div id="prodModalVariantesChips" style="display:flex;flex-wrap:wrap;gap:.4rem"></div>
            </div>
            <button class="btn-mp" id="prodModalBtnAdd" style="margin-top:0">
                <i class="bi bi-cart-plus"></i> Agregar al carrito
            </button>
            <div id="prodModalMediosPago" style="display:none"></div>

            <div id="prodModalDescWrap" style="display:none">
                <div class="pm-section-title">Descripción</div>
                <div class="prod-modal-desc" id="prodModalDesc"></div>
            </div>
            <div id="prodModalAtributos"></div>
        </div><!-- /pm-info -->
        </div><!-- /pm-layout -->

        <!-- Productos similares -->
        <div class="similares-section" id="similaresSection" style="display:none">
            <div class="similares-title">Productos similares</div>
            <div class="similares-shell" id="similaresShell">
                <button type="button" class="similares-nav prev" id="similaresPrev" aria-label="Anteriores" disabled>
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="similares-grid" id="similaresGrid"></div>
                <button type="button" class="similares-nav next" id="similaresNext" aria-label="Siguientes" disabled>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
        </div><!-- /pm-page -->
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CARRUSEL PUBLICIDADES (debajo de productos, arriba del footer)
═══════════════════════════════════════════════════════════ -->
<div class="container my-4" id="publicidadesWrap" style="display:none">
    <div id="carouselPublicidades" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators" id="carouselPubIndicators"></div>
        <div class="carousel-inner" id="carouselPubInner"></div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPublicidades" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselPublicidades" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     UBICACIÓN GOOGLE MAPS (oculta hasta que loadComercio la active)
═══════════════════════════════════════════════════════════ -->
<section id="ubicacionSection" class="container my-5 d-none">
    <h2 class="fw-bold text-center mb-4" style="font-size:1.4rem">Ubicación</h2>
    <div id="ubicacionMapWrap" class="ratio ratio-16x9 d-none" style="max-width:700px;margin:0 auto;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)">
        <iframe id="ubicacionMapIframe" src="" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="border:0"></iframe>
    </div>
    <p id="ubicacionDireccion" class="text-center text-muted mt-3 d-none" style="white-space:pre-line;max-width:700px;margin:0 auto"></p>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════ -->
<footer class="site-footer">
    <div class="footer-inner">

        <!-- Columna 1: Marca -->
        <div class="footer-col-brand">
            <img id="footerLogo" src="" alt="Logo" class="footer-logo d-none">
            <span id="footerNombre" class="footer-brand-name"></span>
            <p  id="footerDesc"   class="footer-desc d-none"></p>
        </div>

        <!-- Columna 2: Contacto -->
        <div>
            <div class="footer-section-title">Contacto</div>
            <div id="footerDireccion" class="footer-item d-none">
                <i class="bi bi-geo-alt-fill"></i><span></span>
            </div>
            <div id="footerTelefono" class="footer-item d-none">
                <i class="bi bi-telephone-fill"></i>
                <a id="footerTelefonoLink" href="#"><span></span></a>
            </div>
            <div id="footerEmail" class="footer-item d-none">
                <i class="bi bi-envelope-fill"></i>
                <a id="footerEmailLink" href="#"><span></span></a>
            </div>
        </div>

        <!-- Columna 3: Legal -->
        <div>
            <div class="footer-section-title">Información</div>
            <div id="footerCuit" class="footer-item d-none">
                <i class="bi bi-card-text"></i><span></span>
            </div>
            <div id="footerCondIva" class="footer-item d-none">
                <i class="bi bi-receipt"></i><span></span>
            </div>
        </div>

        <!-- Columna 4: Redes sociales (oculta hasta que loadComercio la active) -->
        <div id="footerRedesCol" class="d-none">
            <div class="footer-section-title">Seguinos</div>
            <div class="footer-social" id="footerRedes"></div>
        </div>

    </div>
    <div class="footer-bottom">
        <span id="footerCopyright"></span>
        <div id="footerDataFiscal" class="d-none" style="line-height:1"></div>
        <style>#footerDataFiscal img { max-width:60px; height:auto; }</style>
        <div id="footerLegal" class="d-none footer-legal-links">
            <a href="#" class="d-none footer-legal-link" data-legal="tienda_terminos_condiciones"  data-titulo="Términos y Condiciones"  data-icono="bi-file-text">Términos y Condiciones</a>
            <a href="#" class="d-none footer-legal-link" data-legal="tienda_politicas_garantia"    data-titulo="Políticas de Garantía"   data-icono="bi-shield-check">Políticas de Garantía</a>
            <a href="#" class="d-none footer-legal-link" data-legal="tienda_politicas_privacidad"  data-titulo="Políticas de Privacidad" data-icono="bi-lock">Políticas de Privacidad</a>
            <a href="#" class="d-none footer-legal-link" data-legal="tienda_terminos_promociones"  data-titulo="Términos y Promociones"  data-icono="bi-tag">Términos y Promociones</a>
            <button type="button" id="footerArrepentimientoBtn" class="d-none footer-arrepentimiento-btn">Botón de arrepentimiento</button>
        </div>
        <span class="footer-powered">Tienda impulsada por <strong>VentaWeb</strong></span>
    </div>
</footer>

<!-- ═══════════════════════════════════════════════════════════
     MODAL LEGALES (reutilizable para los 4 textos)
═══════════════════════════════════════════════════════════ -->
<div class="legal-overlay" id="legalModal">
    <div class="legal-box">
        <div class="legal-box-header">
            <span id="legalModalTitle"></span>
            <button class="legal-close-btn" id="legalModalCloseBtn" aria-label="Cerrar">&times;</button>
        </div>
        <div class="legal-box-body" id="legalModalBody"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL ARREPENTIMIENTO
═══════════════════════════════════════════════════════════ -->
<div class="legal-overlay" id="arrepentimientoOverlay">
    <div class="legal-box">
        <div class="legal-box-header">
            <div style="display:flex;align-items:center;gap:.55rem">
                <span class="arr-icon"><i class="bi bi-arrow-counterclockwise"></i></span>
                Solicitud de arrepentimiento
            </div>
            <button class="legal-close-btn" id="arrepentimientoCloseBtn" aria-label="Cerrar">&times;</button>
        </div>
        <div class="legal-box-body">
            <p class="arr-aviso">
                <i class="bi bi-info-circle me-1"></i>
                Conforme al art. 34 de la Ley 24.240, podés revocar tu compra dentro de los <strong>10 días hábiles</strong> desde la recepción del producto o la contratación del servicio.
            </p>
            <div id="arrepentimientoForm">
                <div class="arr-grid">
                    <div>
                        <label class="arr-label">Nombre y apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="arrNombre" placeholder="Tu nombre completo">
                    </div>
                    <div>
                        <label class="arr-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-sm" id="arrEmail" placeholder="tu@email.com">
                    </div>
                    <div>
                        <label class="arr-label">Teléfono <span class="text-muted" style="font-weight:400">(opcional)</span></label>
                        <input type="tel" class="form-control form-control-sm" id="arrTelefono" placeholder="Ej: 11 1234-5678">
                    </div>
                    <div>
                        <label class="arr-label">N° de pedido <span class="text-muted" style="font-weight:400">(opcional)</span></label>
                        <input type="text" class="form-control form-control-sm" id="arrNroPedido" placeholder="Ej: 1042">
                    </div>
                    <div class="arr-field-full">
                        <label class="arr-label">Motivo <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="arrMotivo" rows="2" placeholder="Describí el motivo de tu solicitud..."></textarea>
                    </div>
                </div>
                <div class="arr-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="arrepentimientoCancelBtn">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-danger" id="arrEnviarBtn">
                        <i class="bi bi-send me-1"></i>Enviar solicitud
                    </button>
                </div>
            </div>
            <div id="arrepentimientoOk" class="d-none arr-ok">
                <i class="bi bi-check-circle-fill text-success"></i>
                <p class="fw-semibold mt-2 mb-1">¡Solicitud enviada!</p>
                <p class="text-muted small mb-0">El comercio se comunicará con vos a la brevedad.</p>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     WHATSAPP FLOATING BUTTON
═══════════════════════════════════════════════════════════ -->
<a href="#" id="waFloatBtn" target="_blank" rel="noopener" class="wa-float d-none" title="Contactar por WhatsApp">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.557 4.118 1.529 5.845L.057 23.457a.5.5 0 0 0 .614.614l5.612-1.472A11.942 11.942 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.806 9.806 0 0 1-5.006-1.374l-.358-.214-3.722.976.993-3.625-.234-.374A9.818 9.818 0 0 1 2.182 12C2.182 6.57 6.57 2.182 12 2.182c5.43 0 9.818 4.388 9.818 9.818 0 5.43-4.388 9.818-9.818 9.818z"/>
    </svg>
</a>

<!-- ═══════════════════════════════════════════════════════════
     TOAST CONTAINER
═══════════════════════════════════════════════════════════ -->
<div class="toast-container-custom" id="toastContainer"></div>

<!-- ═══════════════════════════════════════════════════════════
     LOADING OVERLAY
═══════════════════════════════════════════════════════════ -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="small text-muted">Procesando...</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
/* ── Configuración ──────────────────────────────────────── */
const API_URL    = <?= json_encode($ERP_API) ?>;
const COMERCIO   = <?= json_encode($comercio) ?>;
const CART_KEY   = 'cart_'    + COMERCIO;
const CLIENT_KEY = 'cliente_' + COMERCIO;
const STATUS     = <?= json_encode($status) ?>;
/* Modo ficha: la página se sirve desde producto.php y arranca con el detalle
   del producto abierto. La tienda de abajo se carga recién al cerrarlo. */
const MODO_FICHA = <?= $VW_FICHA ? 'true' : 'false' ?>;

/* URLs de navegación. Se arman a mano (y no desde location.href) para no
   arrastrar el status/external_reference del retorno de pago a la ficha.
   La base sale de location.pathname: así funciona igual si el archivo se
   sirve como webemma.php, como index.php o desde la raíz de un subdominio. */
const BASE_URL = location.pathname;
function urlTienda()      { return BASE_URL + '?comercio=' + encodeURIComponent(COMERCIO) + (modoComercio ? '&modo=comercio' : ''); }
function urlProducto(id)  { return BASE_URL + '?comercio=' + encodeURIComponent(COMERCIO) + '&p=' + id + (modoComercio ? '&modo=comercio' : ''); }

/* ── Estado de la app ───────────────────────────────────── */
let currentCat        = 0;
let currentPage       = 1;
let currentQ          = '';
let currentSort       = 'nombre_asc';
let currentAttrFiltros = {};   // {nombre: valor} — filtros activos por atributo
let currentCatSchema  = [];    // schema de atributos de la categoría activa
let currentMarcas        = [];    // array de id_marca seleccionados
let marcasConocidas      = {};    // id_marca => nombre, acumulado para poder elegir varias
let currentDescuento     = 0;     // sección de descuento activa (botón "Ver todos")
let ocultarFiltrosMarcas = false; // configuración del comercio
let marcasFiltroAbierto  = false; // en mobile el bloque de marcas arranca cerrado
let ocultarSinStock      = false; // configuración del comercio
let searchTimer       = null;
let tiendaTieneMp     = false;
let tiendaWhatsapp      = '';
let decidirInstance     = null;
let descuentosFormaPago  = {};
let promoDescuentoActual = null; // null | { descuento: float, nombres: string }
let promoCheckTimer;
let tieneVariantes      = false;
let checkoutEnvioDatos  = 'opcional';
let checkoutTelefono    = 'opcional';
let envioModo           = 'referencia';   // 'referencia' | 'suma_total'
let envioCarriers       = [];             // carriers activos del comercio: ['oca','andreani',...]
let envioCpOrigen       = '';             // CP de origen del comercio
let envioCostoSeleccionado = null;        // {carrier, nombre, precio, dias} o null
const modoComercio      = new URLSearchParams(location.search).get('modo') === 'comercio';
let clientesComercio    = [];
let selectedIdCliente   = null;
const productMap      = {};
const globalCatMap    = {};    // id_categoria → objeto categoría (incluye atributos_schema)

/* ── Sesión cliente ──────────────────────────────────────── */
function getClienteSession() {
    try { return JSON.parse(localStorage.getItem(CLIENT_KEY)) || null; } catch { return null; }
}
/* Sufijo de query con la lista de precios del cliente logueado. Va en toda
   consulta de productos: sin esto un mayorista ve el precio minorista. */
function paramsListaPrecio() {
    const cli = getClienteSession();
    if (!cli) return '';
    let qs = '';
    if (cli.id_lista_precio) qs += `&id_lista_precio=${cli.id_lista_precio}`;
    if (cli.tipo_precio && cli.tipo_precio !== 'minorista') qs += `&tipo_precio=${encodeURIComponent(cli.tipo_precio)}`;
    return qs;
}
function setClienteSession(data) { localStorage.setItem(CLIENT_KEY, JSON.stringify(data)); }
function clearClienteSession()   { localStorage.removeItem(CLIENT_KEY); }

function updateNavAuth() {
    if (modoComercio) return; // en modo comercio no hay login de cliente
    const cli = getClienteSession();
    if (cli) {
        document.getElementById('btnLoginNav').classList.add('d-none');
        const userDiv = document.getElementById('authUserNav');
        userDiv.classList.remove('d-none');
        userDiv.classList.add('d-flex');
        document.getElementById('authUserName').textContent = cli.nombre;
    } else {
        document.getElementById('btnLoginNav').classList.remove('d-none');
        document.getElementById('authUserNav').classList.add('d-none');
        document.getElementById('authUserNav').classList.remove('d-flex');
    }
}

function openLoginModal() {
    document.getElementById('loginEmail').value = '';
    document.getElementById('loginPass').value  = '';
    document.getElementById('loginError').classList.add('d-none');
    document.getElementById('loginOverlay').classList.add('open');
    setTimeout(() => document.getElementById('loginEmail').focus(), 100);
}
function closeLoginModal(e) {
    if (e && e.target !== document.getElementById('loginOverlay')) return;
    document.getElementById('loginOverlay').classList.remove('open');
}

async function submitLogin() {
    const email = document.getElementById('loginEmail').value.trim();
    const pass  = document.getElementById('loginPass').value;
    const errEl = document.getElementById('loginError');
    const btn   = document.getElementById('btnLoginSubmit');

    if (!email || !pass) { errEl.textContent = 'Completá email y contraseña.'; errEl.classList.remove('d-none'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';

    try {
        const res  = await fetch(`${API_URL}?action=login_cliente`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: COMERCIO, email, password: pass }),
        });
        const data = await res.json();
        if (!data.ok) {
            errEl.textContent = data.msg || 'Error al ingresar.';
            errEl.classList.remove('d-none');
        } else {
            setClienteSession({ id_cliente: data.id_cliente, nombre: data.nombre, email: data.email || '', id_lista_precio: data.id_lista_precio, tipo_precio: data.tipo_precio || 'minorista' });
            document.getElementById('loginOverlay').classList.remove('open');
            updateNavAuth();
            currentPage = 1;
            loadProductos(); // recargar con precios de su lista
        }
    } catch (e) {
        errEl.textContent = 'Error de conexión.';
        errEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Ingresar';
    }
}

function logoutCliente() {
    clearClienteSession();
    updateNavAuth();
    currentPage = 1;
    loadProductos(); // recargar con precios normales
}

// Enter en los campos del login
document.addEventListener('keydown', e => {
    if (e.key === 'Enter' && document.getElementById('loginOverlay').classList.contains('open')) {
        submitLogin();
    }
    if (e.key === 'Escape' && document.getElementById('loginOverlay').classList.contains('open')) {
        document.getElementById('loginOverlay').classList.remove('open');
    }
});

/* ═══════════════════════════════════════════════════════════
   INICIO
═══════════════════════════════════════════════════════════ */
function ocultarPageLoader() {
    const loader = document.getElementById('pageLoader');
    if (!loader) return;
    loader.classList.add('oculto');
    setTimeout(() => loader.remove(), 320);
}

/* En modo ficha la tienda de abajo arranca vacía. Se llena la primera vez que
   hace falta: al cerrar el detalle, al buscar o al filtrar por categoría. */
let _tiendaCargada = !MODO_FICHA;
function cargarTiendaSiHaceFalta(conGrilla = true) {
    if (_tiendaCargada) return;
    _tiendaCargada = true;          // antes de los fetch: evita reentradas
    fetchPrecioMax(0);
    loadDescuentos();
    // conGrilla=false cuando quien llama ya va a pedir la grilla con sus
    // propios filtros: así no se dispara un loadProductos de más.
    if (conGrilla) loadProductos();
}

document.addEventListener('DOMContentLoaded', async () => {
    if (modoComercio) {
        clearClienteSession();
        document.getElementById('authNav').style.display = 'none';
        document.getElementById('loginOverlay').style.display = 'none';
    }
    updateCartBadge();
    updateNavAuth();
    await loadComercio();
    loadAutocomplete(); // en paralelo, sin await — no bloquea la carga

    // Ocultar pantalla de carga — ya tenemos colores, logo y nombre.
    // En modo ficha se mantiene hasta que el detalle esté armado (lo saca
    // openProdModal): si no, se ve un flash del fondo vacío.
    if (!MODO_FICHA) ocultarPageLoader();

    await loadCategorias();

    // En modo ficha el detalle tapa toda la pantalla debajo del navbar: la
    // grilla y las secciones de la home se cargan al cerrarlo.
    if (!MODO_FICHA) {
        loadDescuentos();

        // Deep-link: ?cat=ID filtra por categoría al cargar
        const deepCatId = parseInt(new URLSearchParams(location.search).get('cat') || '0');
        if (deepCatId > 0 && globalCatMap[deepCatId]) {
            const deepCat    = globalCatMap[deepCatId];
            const deepParent = deepCat.id_categoria_padre
                ? { id: deepCat.id_categoria_padre, nombre: globalCatMap[deepCat.id_categoria_padre]?.nombre || '' }
                : null;
            // scroll=false: al entrar por ?cat=ID la página arranca arriba
            selectCatPanel(deepCatId, deepCat.nombre, deepParent, false); // llama filterCat → loadProductos internamente
        } else {
            fetchPrecioMax(0); // carga máximo global al inicio
            await loadProductos();
        }
    }

    // Deep-link: ?p=ID abre el modal del producto directamente
    const deepProdId = parseInt(new URLSearchParams(location.search).get('p') || '0');
    if (deepProdId > 0) {
        // true: la URL ya trae el ?p=, no hay que agregar otra entrada al historial
        if (productMap[deepProdId]) {
            openProdModal(deepProdId, true);
        } else {
            loadSingleProduct(deepProdId, true); // fetchea y abre
        }
    }

    // Limpiar carrito si el pago fue aprobado o el pedido fue registrado
    if (STATUS === 'approved' || STATUS === 'pedido_ok') {
        localStorage.removeItem(CART_KEY);
        updateCartBadge();
    }

    // Cerrar checkout al hacer clic en el fondo oscuro
    document.getElementById('checkoutBackdrop').addEventListener('click', closeCheckout);

    // ── Autocomplete ─────────────────────────────────────────
    initAutocomplete();

    // Buscador con debounce (carga la grilla)
    const inputBuscar = document.getElementById('searchInput');
    const buscar = () => {
        closeProdModal(false, true);   // si estaba viendo un producto, vuelve al listado
        // La búsqueda es sobre todo el catálogo: si había una categoría
        // filtrando, se saca para no esconder resultados de otras
        if (inputBuscar.value.trim() && currentCat) limpiarCategoriaActiva();
        currentQ    = inputBuscar.value.trim();
        currentPage = 1;
        currentDescuento = 0;
        document.body.classList.toggle('busqueda-activa', currentQ !== '');
        loadProductos();
    };
    inputBuscar.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(buscar, 380);
    });
    // Enter / lupa del teclado: busca ya y cierra el teclado
    inputBuscar.addEventListener('keydown', e => {
        if (e.key !== 'Enter') return;
        clearTimeout(searchTimer);
        buscar();
        inputBuscar.blur();
    });
    // Evento search: la "x" nativa del type=search limpia la búsqueda
    inputBuscar.addEventListener('search', () => { clearTimeout(searchTimer); buscar(); });

    // Ordenamiento
    document.getElementById('sortSelect').addEventListener('change', e => {
        currentSort = e.target.value;
        currentPage = 1;
        loadProductos();
    });

    // Sincronizar CP de datos de envío → campo cotizador
    document.getElementById('ckCP').addEventListener('input', e => {
        const envio = document.getElementById('ckCPEnvio');
        if (envio) envio.value = e.target.value;
    });

    // Clic en categoría padre del breadcrumb → filtrar por ella
    document.getElementById('catActivePath').addEventListener('click', e => {
        const btn = e.target.closest('.path-crumb-btn');
        if (!btn) return;
        const cat = globalCatMap[parseInt(btn.dataset.parentCat)];
        if (cat) selectCatPanel(parseInt(btn.dataset.parentCat), cat.nombre, null);
    });

    // Delegación de clicks en sub-items del panel de categorías
    document.getElementById('catPanelSubs').addEventListener('click', e => {
        const el = e.target.closest('.cp-sub-item');
        if (!el) return;
        const catId      = parseInt(el.dataset.cat);
        const nombre     = el.dataset.nombre     || '';
        const parentId   = el.dataset.parentId   ? parseInt(el.dataset.parentId) : null;
        const parentNom  = el.dataset.parentNombre || '';
        selectCatPanel(catId, nombre, parentId ? {id: parentId, nombre: parentNom} : null);
        scrollAProductos(false);
    });
});

/* ═══════════════════════════════════════════════════════════
   CARGAR DATOS DEL COMERCIO
═══════════════════════════════════════════════════════════ */
/* ═══════════════════════════════════════════════════════════
   AUTOCOMPLETE
═══════════════════════════════════════════════════════════ */
let _acData      = [];   // [{id, nombre, codigo}] — cargado una vez
let _acIndex     = -1;   // ítem activo con teclado
const AC_MIN     = 2;    // mínimo de caracteres para activar
const AC_MAX     = 8;    // máximo de sugerencias visibles

async function loadAutocomplete() {
    try {
        const res  = await fetch(`${API_URL}?action=autocomplete&codigo=${encodeURIComponent(COMERCIO)}`);
        const data = await res.json();
        if (data.ok) _acData = data.productos || [];
    } catch (e) { /* silencioso — el buscador normal sigue funcionando */ }
}

function initAutocomplete() {
    const input    = document.getElementById('searchInput');
    const dropdown = document.getElementById('acDropdown');

    function escRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

    function highlight(texto, q) {
        const re = new RegExp('(' + escRe(q) + ')', 'gi');
        return texto.replace(re, '<mark>$1</mark>');
    }

    function positionDropdown() {
        if (window.innerWidth < 768) {
            const rect = input.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + 4) + 'px';
        } else {
            dropdown.style.top = '';
        }
    }

    function normAc(s) {
        return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function showDropdown(q) {
        if (!q || q.length < AC_MIN || !_acData.length) { hideDropdown(); return; }
        const words = q.trim().split(/\s+/).filter(w => w.length > 0).map(normAc);
        const matches = _acData.filter(p => {
            const nom = normAc(p.nombre);
            const cod = normAc(p.codigo || '');
            return words.every(w => nom.includes(w) || cod.includes(w));
        });
        if (!matches.length) { hideDropdown(); return; }

        const items  = matches.slice(0, AC_MAX);
        const extra  = matches.length - items.length;
        let html = items.map((p, i) =>
            `<div class="ac-item" data-idx="${i}" data-nombre="${p.nombre.replace(/"/g,'&quot;')}">
                ${highlight(p.nombre, q)}
                ${p.codigo ? `<span class="ac-item-codigo">${p.codigo}</span>` : ''}
             </div>`
        ).join('');
        if (extra > 0) {
            html += `<div class="ac-footer">+${extra} resultado${extra > 1 ? 's' : ''} más — seguí escribiendo</div>`;
        }
        dropdown.innerHTML = html;
        positionDropdown();
        dropdown.classList.add('open');
        _acIndex = -1;

        dropdown.querySelectorAll('.ac-item').forEach(el => {
            el.addEventListener('mousedown', ev => {
                ev.preventDefault(); // evita blur antes del click
                selectAcItem(el.dataset.nombre);
            });
        });
    }

    function hideDropdown() {
        dropdown.classList.remove('open');
        dropdown.innerHTML = '';
        _acIndex = -1;
    }

    function selectAcItem(nombre) {
        input.value = nombre;
        hideDropdown();
        if (currentCat) limpiarCategoriaActiva();   // buscar es sobre todo el catálogo
        currentQ    = nombre;
        currentPage = 1;
        document.body.classList.add('busqueda-activa');
        loadProductos();
    }

    function updateActiveClass() {
        dropdown.querySelectorAll('.ac-item').forEach((el, i) => {
            el.classList.toggle('ac-active', i === _acIndex);
        });
    }

    input.addEventListener('input', e => {
        showDropdown(e.target.value.trim());
    });

    input.addEventListener('keydown', e => {
        const items = dropdown.querySelectorAll('.ac-item');
        if (!dropdown.classList.contains('open') || !items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            _acIndex = Math.min(_acIndex + 1, items.length - 1);
            updateActiveClass();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            _acIndex = Math.max(_acIndex - 1, -1);
            updateActiveClass();
        } else if (e.key === 'Enter' && _acIndex >= 0) {
            e.preventDefault();
            e.stopImmediatePropagation(); // no dispara el debounce
            selectAcItem(items[_acIndex].dataset.nombre);
        } else if (e.key === 'Escape') {
            hideDropdown();
        }
    });

    input.addEventListener('blur', () => {
        // Pequeño delay para que el mousedown del item se procese antes
        setTimeout(hideDropdown, 150);
    });

    // Cerrar si se hace clic fuera
    document.addEventListener('click', e => {
        if (!input.closest('.search-box').contains(e.target)) hideDropdown();
    });
}

async function loadComercio() {
    try {
        const res  = await fetch(`${API_URL}?action=comercio&codigo=${encodeURIComponent(COMERCIO)}`);
        const data = await res.json();
        if (!data.ok) { showFatalError(data.msg); return; }

        // Mostrar botón MP solo si el comercio tiene token configurado
        if (data.tiene_mp) {
            tiendaTieneMp = true;
            document.getElementById('ckMpSection').style.display = '';
        }

        // Mostrar botón Nave si el comercio tiene Nave configurado
        if (data.tiene_nave) {
            document.getElementById('ckNaveSection').style.display = '';
        }

        // Mostrar botón GoCuotas si el comercio lo tiene configurado
        if (data.tiene_gocuotas) {
            document.getElementById('ckGocuotasSection').style.display = '';
        }

        // Inicializar Payway si el comercio lo tiene configurado
        if (data.tiene_payway && data.payway_public_key && typeof Decidir !== 'undefined') {
            const pwUrl = data.payway_sandbox
                ? 'https://developers.decidir.com/api/v2'
                : 'https://ventasonline.payway.com.ar/api/v2';
            decidirInstance = new Decidir(pwUrl);
            decidirInstance.setPublishableKey(data.payway_public_key);
            decidirInstance.setTimeout(5000);
            document.getElementById('ckPaywaySection').style.display = '';
        }

        // Variantes disponibles en la tienda
        if (data.tiene_variantes) {
            tieneVariantes = true;
        }

        // Aplicar fuente de Google Fonts
        if (data.tienda_fuente) {
            const gfKey = data.tienda_fuente.replace(/ /g, '+');
            const lnk = document.createElement('link');
            lnk.rel  = 'stylesheet';
            lnk.href = `https://fonts.googleapis.com/css2?family=${gfKey}:wght@400;500;600;700&display=swap`;
            document.head.appendChild(lnk);
            document.body.style.fontFamily = `'${data.tienda_fuente}', sans-serif`;
        }

        // Aplicar color de tema
        const color = data.tienda_color || '#4361ee';
        document.documentElement.style.setProperty('--brand', color);
        document.documentElement.style.setProperty('--brand-dark', shadeColor(color, -15));
        document.documentElement.style.setProperty('--brand-light', shadeColor(color, 92));

        // Título y meta
        document.title = data.nombre_fantasia + ' — Tienda';

        // Navbar
        const navNombre = document.getElementById('navNombre');
        navNombre.textContent = data.nombre_fantasia;
        if (data.logo_url) {
            const img = document.getElementById('navLogo');
            img.src = data.logo_url;
            img.classList.remove('d-none');
            navNombre.style.display = 'none'; // ocultar texto si hay logo

            // Favicon dinámico con el logo del comercio
            document.querySelectorAll('link[rel="icon"], link[rel="apple-touch-icon"]').forEach(el => {
                el.href = data.logo_url;
            });
        }

        // Configuración: ocultar filtros de marca y sin stock
        ocultarFiltrosMarcas = data.ocultar_filtros_marcas === true;
        ocultarSinStock      = data.ocultar_sin_stock      === true;

        // Hero — visible sólo si el toggle está activo y hay descripción
        const heroVisible = data.tienda_hero_visible !== false;
        if (heroVisible && data.tienda_descripcion) {
            const hero    = document.getElementById('heroSection');
            const eyebrow = document.getElementById('heroEyebrow');
            const desc    = document.getElementById('heroDesc');
            desc.textContent = data.tienda_descripcion;
            if (data.tienda_hero_mostrar_nombre !== false) {
                eyebrow.textContent = data.nombre_fantasia || '';
            } else {
                eyebrow.remove();
            }
            hero.classList.remove('d-none');
        }

        // Footer - marca
        document.getElementById('footerNombre').textContent = data.nombre_fantasia;
        document.getElementById('footerCopyright').textContent =
            '© ' + new Date().getFullYear() + ' ' + data.nombre_fantasia + '. Todos los derechos reservados.';
        if (data.logo_url) {
            const fl = document.getElementById('footerLogo');
            fl.src = data.logo_url;
            fl.classList.remove('d-none');
        }
        if (data.tienda_descripcion) {
            const fd = document.getElementById('footerDesc');
            fd.textContent = data.tienda_descripcion;
            fd.classList.remove('d-none');
        }

        // Footer - contacto
        if (data.direccion || data.ciudad || data.provincia) {
            const addr = [data.direccion, data.ciudad, data.provincia].filter(Boolean).join(', ');
            const el = document.getElementById('footerDireccion');
            el.querySelector('span').textContent = addr;
            el.classList.remove('d-none');
        }
        if (data.telefono) {
            const el = document.getElementById('footerTelefono');
            el.querySelector('span').textContent = data.telefono;
            document.getElementById('footerTelefonoLink').href = 'tel:' + data.telefono.replace(/\s/g, '');
            el.classList.remove('d-none');
        }
        if (data.email) {
            const el = document.getElementById('footerEmail');
            el.querySelector('span').textContent = data.email;
            document.getElementById('footerEmailLink').href = 'mailto:' + data.email;
            el.classList.remove('d-none');
        }

        // Footer - legal
        if (data.cuit) {
            const el = document.getElementById('footerCuit');
            el.querySelector('span').textContent = 'CUIT: ' + data.cuit;
            el.classList.remove('d-none');
        }
        if (data.condicion_iva_label) {
            const el = document.getElementById('footerCondIva');
            el.querySelector('span').textContent = data.condicion_iva_label;
            el.classList.remove('d-none');
        }

        // Redes sociales en footer
        const redesDef = [
            { key: 'red_instagram', icon: 'bi-instagram',  label: 'Instagram' },
            { key: 'red_facebook',  icon: 'bi-facebook',   label: 'Facebook'  },
            { key: 'red_tiktok',    icon: 'bi-tiktok',     label: 'TikTok'    },
            { key: 'red_youtube',   icon: 'bi-youtube',    label: 'YouTube'   },
            { key: 'red_x',         icon: 'bi-twitter-x',  label: 'X'         },
        ];
        const redesHtml = redesDef
            .filter(r => data[r.key])
            .map(r => `<a href="${escHtml(data[r.key])}" target="_blank" rel="noopener"
                          class="footer-social-btn" title="${r.label}">
                          <i class="bi ${r.icon}"></i>
                       </a>`)
            .join('');
        if (redesHtml) {
            document.getElementById('footerRedes').innerHTML = redesHtml;
            document.getElementById('footerRedesCol').classList.remove('d-none');
        }

        // Descuentos por forma de pago
        descuentosFormaPago = data.descuentos_forma_pago || {};
        renderFpMethodsBanner();
        const fpDiscEls = {
            mercadopago:  'fpDiscMercadopago',
            nave:         'fpDiscNave',
            gocuotas:     'fpDiscGocuotas',
            payway:       'fpDiscPayway',
            transferencia:'fpDiscTransferencia',
            efectivo:     'fpDiscEfectivo',
        };
        Object.entries(fpDiscEls).forEach(function([fp, elId]) {
            const el  = document.getElementById(elId);
            if (!el) return;
            const dsc = descuentosFormaPago[fp];
            if (!dsc) return;
            const esRecargo = dsc.direccion === 'recargo';
            el.innerHTML = '<i class="bi bi-' + (esRecargo ? 'exclamation-circle' : 'tag-fill') + ' me-1"></i>'
                + dsc.label + ' pagando con este método';
            el.className = 'fp-disc-info ' + (esRecargo ? 'fp-recargo' : 'fp-descuento');
            el.style.display = '';
        });

        // Google Maps embed + dirección
        if (data.tienda_maps_embed || data.tienda_maps_direccion) {
            document.getElementById('ubicacionSection').classList.remove('d-none');
            if (data.tienda_maps_embed) {
                document.getElementById('ubicacionMapIframe').src = data.tienda_maps_embed;
                document.getElementById('ubicacionMapWrap').classList.remove('d-none');
            }
            if (data.tienda_maps_direccion) {
                const dirEl = document.getElementById('ubicacionDireccion');
                dirEl.textContent = data.tienda_maps_direccion;
                dirEl.classList.remove('d-none');
            }
        }

        // Legales + Botón de arrepentimiento
        let hayLegal = false;
        document.querySelectorAll('.footer-legal-link').forEach(function(a) {
            const key = a.dataset.legal;
            if (data[key]) { a.dataset.texto = data[key]; a.classList.remove('d-none'); hayLegal = true; }
        });
        if (hayLegal || data.tienda_boton_arrepentimiento) {
            document.getElementById('footerLegal').classList.remove('d-none');
        }
        if (data.tienda_boton_arrepentimiento) {
            document.getElementById('footerArrepentimientoBtn').classList.remove('d-none');
        }

        // Cuotas sin interés por banco
        if (data.cuotas_bancos && data.cuotas_bancos.length) {
            document.getElementById('cuotasTrack').innerHTML = data.cuotas_bancos.map(b => {
                const logoHtml = escHtml(b.logo_text).replace(/\n/g, '<br>');
                const fsStyle  = b.font_size ? `font-size:${b.font_size}` : '';
                return `<div class="cuota-card">
                    <div class="cuota-card-box">
                        <div class="cuota-logo-wrap">
                            <span class="cuota-logo-text" style="color:${escHtml(b.color)};${fsStyle}">${logoHtml}</span>
                        </div>
                        <div class="cuota-desc"><strong>${b.cuotas}</strong>Sin Interés</div>
                    </div>
                    <div class="cuota-banco-nombre">${escHtml(b.label)}</div>
                </div>`;
            }).join('');
            document.getElementById('cuotasSection').classList.remove('d-none');
        }

        // Data Fiscal AFIP
        if (data.tienda_data_fiscal) {
            const dfEl = document.getElementById('footerDataFiscal');
            dfEl.innerHTML = data.tienda_data_fiscal;
            dfEl.classList.remove('d-none');
        }

        // WhatsApp floating button
        if (data.tienda_whatsapp) {
            tiendaWhatsapp = data.tienda_whatsapp;
            const waBtn = document.getElementById('waFloatBtn');
            waBtn.href = 'https://wa.me/' + data.tienda_whatsapp.replace(/[^0-9]/g, '');
            waBtn.classList.remove('d-none');
        }

        // Transferencia section
        if (data.tienda_cbu) {
            document.getElementById('ckTransfCBU').textContent = data.tienda_cbu;
            document.getElementById('ckTransfSection').style.display = '';
        }

        // Efectivo section
        if (data.tienda_efectivo) {
            document.getElementById('ckEfectivoSection').style.display = '';
        }

        // Barra de envío
        if (data.tienda_envio) {
            document.getElementById('envioText').textContent = data.tienda_envio;
            document.getElementById('envioBar').classList.remove('d-none');
        }

        // Datos de envío en checkout
        checkoutEnvioDatos = data.checkout_envio_datos || 'opcional';
        applyEnvioDatosMode(checkoutEnvioDatos);

        // Teléfono en checkout
        checkoutTelefono = data.checkout_telefono || 'opcional';
        applyTelefonoMode(checkoutTelefono);

        // Cotización de envíos
        envioModo     = data.envio_modo     || 'referencia';
        envioCarriers = data.envio_carriers || [];
        envioCpOrigen = data.envio_cp_origen || '';
        // Mostrar widget de cotización si hay carriers activos y CP origen configurado
        if (envioCarriers.length > 0 && envioCpOrigen) {
            document.getElementById('ckCotizarEnvioWrap').style.display = '';
        }

        // Orden por defecto del comercio
        if (data.tienda_orden_default) {
            currentSort = data.tienda_orden_default;
            const ss = document.getElementById('sortSelect');
            if (ss) ss.value = currentSort;
        }

        // Ocultar botón "Hacer pedido" genérico si hay métodos específicos configurados
        if (data.tienda_cbu || data.tienda_efectivo || data.tiene_nave || data.tiene_gocuotas || data.tiene_payway) {
            document.getElementById('btnPedido').style.display = 'none';
        }

        // Carrusel de banners
        const banners = data.banners || [];
        if (banners.length > 0) {
            const inner      = document.getElementById('carouselInner');
            const indicators = document.getElementById('carouselIndicators');
            banners.forEach((b, i) => {
                const imgTag = `<img src="${b.ruta}" class="d-block w-100" alt="Banner ${i+1}" style="max-height:420px;object-fit:cover">`;
                const item   = document.createElement('div');
                item.className = 'carousel-item' + (i === 0 ? ' active' : '');
                item.innerHTML = b.url ? `<a href="${b.url}">${imgTag}</a>` : imgTag;
                inner.appendChild(item);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.dataset.bsTarget  = '#carouselBanners';
                btn.dataset.bsSlideTo = i;
                if (i === 0) { btn.classList.add('active'); btn.setAttribute('aria-current', 'true'); }
                indicators.appendChild(btn);
            });
            document.getElementById('carouselBannersWrap').style.display = '';
        }

        // Carrusel de publicidades (debajo de productos)
        const publicidades = data.publicidades || [];
        if (publicidades.length > 0) {
            const pInner      = document.getElementById('carouselPubInner');
            const pIndicators = document.getElementById('carouselPubIndicators');
            publicidades.forEach((p, i) => {
                const imgTag = `<img src="${p.ruta}" class="d-block w-100" alt="Publicidad ${i+1}" style="max-height:420px;object-fit:cover">`;
                const item   = document.createElement('div');
                item.className = 'carousel-item' + (i === 0 ? ' active' : '');
                item.innerHTML = p.url ? `<a href="${p.url}">${imgTag}</a>` : imgTag;
                pInner.appendChild(item);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.dataset.bsTarget  = '#carouselPublicidades';
                btn.dataset.bsSlideTo = i;
                if (i === 0) { btn.classList.add('active'); btn.setAttribute('aria-current', 'true'); }
                pIndicators.appendChild(btn);
            });
            document.getElementById('publicidadesWrap').style.display = '';
        }

    } catch (e) {
        showFatalError('No se pudo conectar con la tienda.');
    }
}

/* ═══════════════════════════════════════════════════════════
   CATEGORÍAS
═══════════════════════════════════════════════════════════ */
async function loadCategorias() {
    try {
        const res  = await fetch(`${API_URL}?action=categorias&codigo=${encodeURIComponent(COMERCIO)}`);
        const data = await res.json();
        if (!data.ok) return;

        const cats   = data.categorias;
        const catMap = {};
        cats.forEach(c => { catMap[c.id_categoria] = c; globalCatMap[c.id_categoria] = c; });

        // Construir árbol recursivo para el sidebar (accordion: hijos ocultos por defecto)
        function renderNode(cat, level) {
            const children = cats.filter(c => parseInt(c.id_categoria_padre) === parseInt(cat.id_categoria));
            const hasChildren = children.length > 0;
            const pl = 14 + level * 14;
            const marker = level > 0 ? '<span class="cat-sub-marker">└</span>' : '';
            const chevron = hasChildren
                ? `<button class="cat-chevron" id="chev-${cat.id_categoria}"
                           onclick="event.stopPropagation();toggleCatSubs(${cat.id_categoria});return false;"
                           title="Expandir/colapsar subcategorías">
                       <i class="bi bi-chevron-right"></i>
                   </button>`
                : '';
            let html = `<a class="cat-item${level > 0 ? ' cat-sub' : ''}" data-cat="${cat.id_categoria}"
                           style="padding-left:${pl}px"
                           onclick="catClick(${cat.id_categoria});return false;" href="#">
                           <span class="cat-item-label">${marker}${escHtml(cat.nombre)}</span>
                           ${chevron}
                           <span class="cat-count">${cat.total_productos}</span>
                        </a>`;
            if (hasChildren) {
                html += `<div id="cat-ch-${cat.id_categoria}" style="display:none">`;
                children.forEach(child => { html += renderNode(child, level + 1); });
                html += `</div>`;
            }
            return html;
        }

        // Categorías raíz: sin padre o cuyo padre no está en la lista
        const roots = cats.filter(c => !c.id_categoria_padre || !catMap[c.id_categoria_padre]);

        const sidebar = document.getElementById('sidebarCats');
        const mobile  = document.getElementById('mobileCats');

        sidebar.innerHTML = '<a class="cat-item active" data-cat="0" onclick="filterCat(0);return false;" href="#">Todos</a>';

        roots.forEach(cat => {
            sidebar.innerHTML += renderNode(cat, 0);
        });

        // Mobile: armar mapa de hijos
        const childrenMap = {};
        cats.forEach(c => {
            const pid = parseInt(c.id_categoria_padre);
            if (pid && catMap[pid]) {
                if (!childrenMap[pid]) childrenMap[pid] = [];
                childrenMap[pid].push(c);
            }
        });
        window._mobileCatChildren = childrenMap;

        // Mobile: solo categorías padre en la primera fila
        const parentsEl = document.getElementById('mobileCatsParents');
        parentsEl.innerHTML = '<button class="chip-cat active" data-cat="0" onclick="filterCatMobile(0)">Todos</button>';
        roots.forEach(cat => {
            const hasSubs = (childrenMap[cat.id_categoria] || []).length > 0;
            parentsEl.innerHTML += `<button class="chip-cat" data-cat="${cat.id_categoria}"
                onclick="filterCatMobile(${cat.id_categoria})">
                ${escHtml(cat.nombre)}${hasSubs ? ' <i class="bi bi-chevron-down" style="font-size:.65rem;opacity:.6"></i>' : ''}
            </button>`;
        });

        // Categorías destacadas en home (tarjetas grandes debajo del carrusel)
        const destacadas = data.categorias_destacadas || [];
        if (destacadas.length) {
            const row  = document.getElementById('categoriasDestacadasRow');
            const wrap = document.getElementById('categoriasDestacadasWrap');
            row.innerHTML = '';
            destacadas.forEach(c => {
                const col = document.createElement('div');
                col.className = 'cat-dest-card';
                col.style.cssText = 'flex:0 0 auto;width:34vw;max-width:34vw';
                col.innerHTML = `
                  <a href="#" onclick="selectCatPanel(${c.id_categoria},'${escHtml(c.nombre).replace(/'/g,"\\'")}',null);scrollAProductos();return false;"
                     class="d-block position-relative rounded overflow-hidden shadow-sm" style="aspect-ratio:3/1">
                    <img src="${c.imagen_url}" class="w-100 h-100" style="object-fit:cover" alt="${escHtml(c.nombre)}">
                  </a>`;
                row.appendChild(col);
            });
            wrap.classList.remove('d-none');

            // Desktop: navegación con flechas (desliza varias tarjetas por click).
            // Mobile: se mantiene el auto-scroll infinito táctil.
            const esDesktop = window.matchMedia('(min-width: 768px)').matches;
            if (esDesktop && destacadas.length > 1) {
                initCatDestFlechas(row);
            } else if (destacadas.length > 1) {
                row.style.scrollSnapType = 'none';
                // Duplicar tarjetas para loop infinito
                const origCards = [...row.children];
                const origScrollW = row.scrollWidth;
                origCards.forEach(c => row.appendChild(c.cloneNode(true)));
                const gap = 8; // gap-2 = 0.5rem
                const loopAt = origScrollW + gap;

                let rafId;
                function autoScroll() {
                    row.scrollLeft += 0.45;
                    if (row.scrollLeft >= loopAt) row.scrollLeft -= loopAt;
                    rafId = requestAnimationFrame(autoScroll);
                }
                rafId = requestAnimationFrame(autoScroll);

                // Pausar al tocar/hover, reanudar al soltar
                const pause  = () => cancelAnimationFrame(rafId);
                const resume = () => { rafId = requestAnimationFrame(autoScroll); };
                row.addEventListener('touchstart',  pause,  {passive:true});
                row.addEventListener('touchend',    resume, {passive:true});
                row.addEventListener('mouseenter',  pause);
                row.addEventListener('mouseleave',  resume);
            }
        }

        // ── web2: construir panel doble de categorías ──
        window._catChildrenMap = childrenMap;
        window._catRoots       = roots;
        buildCatPanel(roots, childrenMap);
        renderCatFiltros();

    } catch (e) { /* silencioso */ }
}

/* Mobile: lleva la vista al listado de productos. Sin esto, al tocar una
   categoría destacada parece que no pasó nada porque el listado queda fuera
   de pantalla. Descuenta las barras sticky para no tapar el encabezado. */
function scrollAProductos(soloMobile = true) {
    // Las categorías destacadas solo bajan en mobile; el panel de categorías
    // de la navbar baja siempre (soloMobile = false)
    if (soloMobile && window.matchMedia('(min-width: 768px)').matches) return;
    const destino = document.querySelector('.w4-layout') || document.getElementById('productsGrid');
    if (!destino) return;
    const navbar = document.querySelector('.navbar-tienda');
    const barra  = document.getElementById('catSelectorBar');
    const offset = (navbar ? navbar.offsetHeight : 0) + (barra ? barra.offsetHeight : 0) + 8;
    const top    = destino.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
}

/* Igual que scrollAProductos pero apunta a la grilla, salteando los filtros
   (en mobile quedan arriba de los productos) */
function scrollAGrilla() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    const navbar = document.querySelector('.navbar-tienda');
    const barra  = document.getElementById('catSelectorBar');
    const offset = (navbar ? navbar.offsetHeight : 0) + (barra ? barra.offsetHeight : 0) + 8;
    const top    = grid.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
}

/* Categorías destacadas en desktop: flechas que deslizan una tanda completa
   de tarjetas por click. En mobile no se usa (queda el auto-scroll táctil). */
function initCatDestFlechas(row) {
    const shell = document.getElementById('categoriasDestacadasShell');
    const prev  = document.getElementById('catDestPrev');
    const next  = document.getElementById('catDestNext');
    if (!shell || !prev || !next) return;

    row.style.scrollSnapType = 'none';

    // Desliza el ancho visible menos una tarjeta asomada, para no perder contexto
    const pasoScroll = () => {
        const card = row.querySelector('.cat-dest-card');
        const solape = card ? card.offsetWidth * 0.5 : 0;
        return Math.max(row.clientWidth - solape, row.clientWidth * 0.5);
    };

    const sincronizar = () => {
        const hayOverflow = row.scrollWidth - row.clientWidth > 4;
        shell.classList.toggle('has-nav', hayOverflow);
        prev.disabled = !hayOverflow || row.scrollLeft <= 2;
        next.disabled = !hayOverflow || row.scrollLeft >= row.scrollWidth - row.clientWidth - 2;
    };

    prev.addEventListener('click', () => row.scrollBy({ left: -pasoScroll(), behavior: 'smooth' }));
    next.addEventListener('click', () => row.scrollBy({ left:  pasoScroll(), behavior: 'smooth' }));
    row.addEventListener('scroll', sincronizar, { passive: true });
    window.addEventListener('resize', sincronizar);

    sincronizar();
    // Las imágenes pueden cambiar el ancho al cargar
    setTimeout(sincronizar, 400);
}

// ── Panel doble de categorías (web2) ──────────────────────
let _catPanelOpen = false;

function buildCatPanel(roots, childrenMap) {
    const parEl = document.getElementById('catPanelParents');
    if (!parEl) return;
    let html = `<div class="cp-item" data-cat="0"
                     onclick="selectCatPanel(0,null,null)"
                     onmouseenter="hoverParent(0)">Todos los productos</div>`;
    roots.forEach(cat => {
        const hasSubs = (childrenMap[cat.id_categoria] || []).length > 0;
        const n = escHtml(cat.nombre);
        html += `<div class="cp-item" data-cat="${cat.id_categoria}"
                      onmouseenter="hoverParent(${cat.id_categoria})"
                      onclick="clickParent(${cat.id_categoria})">
                    <span>${n}</span>
                    ${hasSubs ? '<i class="bi bi-chevron-right cp-arr"></i>' : ''}
                 </div>`;
    });
    parEl.innerHTML = html;
}

function hoverParent(catId) {
    document.querySelectorAll('#catPanelParents .cp-item').forEach(el => {
        el.classList.toggle('hover', parseInt(el.dataset.cat) === catId);
    });
    const subsEl = document.getElementById('catPanelSubs');
    if (!subsEl) return;
    if (catId === 0) {
        subsEl.innerHTML = '<div class="cp-empty"><i class="bi bi-arrow-left me-1"></i>Elegí una categoría</div>';
        return;
    }
    const cat      = globalCatMap[catId];
    const children = (window._catChildrenMap || {})[catId] || [];
    if (!children.length) {
        subsEl.innerHTML = '<div class="cp-empty"><i class="bi bi-check me-1"></i>Sin subcategorías</div>';
        return;
    }
    const parentName = escHtml(cat ? cat.nombre : '');
    let html = `<div class="cp-subs-hdr">${parentName}</div>`;
    children.forEach(c => {
        html += `<div class="cp-sub-item"
                      data-cat="${c.id_categoria}"
                      data-nombre="${escHtml(c.nombre)}"
                      data-parent-id="${catId}"
                      data-parent-nombre="${escHtml(cat ? cat.nombre : '')}">
                    ${escHtml(c.nombre)}
                 </div>`;
    });
    subsEl.innerHTML = html;
}

function clickParent(catId) {
    const children = (window._catChildrenMap || {})[catId] || [];
    const cat = globalCatMap[catId];
    if (!children.length) {
        selectCatPanel(catId, cat ? cat.nombre : '', null);
        scrollAProductos(false);
    } else if (window.innerWidth < 768) {
        // Mobile: mostrar subs para que el usuario elija
        hoverParent(catId);
    } else {
        // Desktop con subcategorías: filtrar por categoría padre (incluye todas las subs)
        selectCatPanel(catId, cat ? cat.nombre : '', null);
        scrollAProductos(false);
    }
}

function selectCatPanel(catId, nombre, parent, scroll = true) {
    closeProdModal(false, true);   // si estaba viendo un producto, vuelve al listado
    // Blanquear buscador al cambiar de categoría
    const si = document.getElementById('searchInput');
    if (si && si.value) {
        si.value = '';
        currentQ = '';
    }
    document.body.classList.remove('busqueda-activa');
    closeCatPanel();
    // Marcar link activo en la nav rápida
    document.querySelectorAll('#catQuicknav .cat-qlink:not(.disabled)').forEach(function(a) {
        const fn = a.getAttribute('onclick') || '';
        const m  = fn.match(/selectCatPanel\((\d+)/);
        a.classList.toggle('active', m && parseInt(m[1]) === catId);
    });
    const pathEl = document.getElementById('catActivePath');
    if (catId === 0) {
        pathEl.innerHTML = '';
        pathEl.classList.add('d-none');
    } else {
        let crumb = '';
        if (parent) crumb += `<button class="path-crumb path-crumb-btn" data-parent-cat="${parent.id}" title="Filtrar por ${escHtml(parent.nombre)}">${escHtml(parent.nombre)}</button><i class="bi bi-chevron-right path-sep"></i>`;
        crumb += `<span class="path-crumb">${escHtml(nombre || '')}</span>`;
        crumb += `<button class="btn-clear-cat ms-1" onclick="selectCatPanel(0,null,null)" title="Quitar filtro"><i class="bi bi-x-lg"></i></button>`;
        pathEl.innerHTML = crumb;
        pathEl.classList.remove('d-none');
    }
    filterCat(catId, scroll);
}

/* Bloque de categorías del sidebar de filtros: muestra las subcategorías de la
   categoría activa (o las hermanas, si la activa ya es una hoja), igual que el
   panel de cat-selector-bar. Sin categoría muestra las raíces. */
function renderCatFiltros() {
    const wrap = document.getElementById('catFiltrosInline');
    if (!wrap) return;
    const childrenMap = window._catChildrenMap || {};
    const cat    = currentCat ? globalCatMap[currentCat] : null;
    const padreId = cat && cat.id_categoria_padre && globalCatMap[cat.id_categoria_padre]
        ? parseInt(cat.id_categoria_padre) : 0;
    // Si la categoría activa tiene hijas se listan esas; si es hoja, sus hermanas
    const baseId = (childrenMap[currentCat] || []).length ? currentCat : padreId;
    const items  = baseId ? (childrenMap[baseId] || []) : (window._catRoots || []);
    if (!items.length) { wrap.innerHTML = ''; wrap.classList.add('d-none'); return; }

    const base = baseId ? globalCatMap[baseId] : null;
    let html = `<div class="filtro-label" style="margin-bottom:.4rem">${base ? escHtml(base.nombre) : 'Categorías'}</div>
                <div class="cat-filtro-chips">`;
    if (base) {
        html += `<button type="button" class="chip-cat${currentCat === baseId ? ' active' : ''}"
                         onclick="selectCatFiltro(${baseId})">Ver todo</button>`;
    }
    items.forEach(c => {
        const id = parseInt(c.id_categoria);
        html += `<button type="button" class="chip-cat${id === currentCat ? ' active' : ''}"
                         onclick="selectCatFiltro(${id})">${escHtml(c.nombre)}</button>`;
    });
    wrap.innerHTML = html + '</div>';
    wrap.classList.remove('d-none');
}

/* Click en un chip de categoría del sidebar: arma el breadcrumb con el padre
   (si lo tiene) y filtra sin volver a scrollear la página. */
function selectCatFiltro(catId) {
    const cat   = globalCatMap[catId];
    const pid   = cat && cat.id_categoria_padre ? parseInt(cat.id_categoria_padre) : 0;
    const padre = pid && globalCatMap[pid] ? { id: pid, nombre: globalCatMap[pid].nombre } : null;
    selectCatPanel(catId, cat ? cat.nombre : '', padre, false);
}

function toggleFiltros() {
    const panel = document.getElementById('filtrosPanel');
    const btn   = document.getElementById('filtrosToggleBtn');
    const open  = panel.classList.toggle('open');
    btn.classList.toggle('open', open);
}

function toggleCatPanel(e) {
    e && e.stopPropagation();
    const wrap = document.getElementById('catPanelWrap');
    const trigger = document.getElementById('catTrigger');
    if (wrap.classList.contains('open')) {
        closeCatPanel();
    } else {
        wrap.classList.add('open');
        trigger.classList.add('open');
        setTimeout(() => document.addEventListener('click', _closePanelOutside), 0);
    }
}
function _closePanelOutside(e) {
    const bar = document.getElementById('catSelectorBar');
    if (!bar || !bar.contains(e.target)) {
        closeCatPanel();
    } else {
        setTimeout(() => document.addEventListener('click', _closePanelOutside), 0);
    }
}
function closeCatPanel() {
    document.getElementById('catPanelWrap')?.classList.remove('open');
    document.getElementById('catTrigger')?.classList.remove('open');
}

// ── Filtro de precio (dual range slider) ──────────────────
let currentPrecioMin  = '';
let currentPrecioMax  = '';
let precioMaxGlobal   = 1000000;
let precioSliderTimer = null;

function updatePriceSliderUI() {
    const slMin  = document.getElementById('sliderPrecioMin');
    const slMax  = document.getElementById('sliderPrecioMax');
    const range  = document.getElementById('priceSliderRange');
    const lblMin = document.getElementById('precioMinLabel');
    const lblMax = document.getElementById('precioMaxLabel');
    if (!slMin || !slMax || !range) return;
    const maxAttr = parseInt(slMin.getAttribute('max')) || 1;
    const vMin = parseInt(slMin.value);
    const vMax = parseInt(slMax.value);
    range.style.left  = (vMin / maxAttr * 100) + '%';
    range.style.right = (100 - vMax / maxAttr * 100) + '%';
    lblMin.textContent = '$' + vMin.toLocaleString('es-AR');
    lblMax.textContent = '$' + vMax.toLocaleString('es-AR');
    // El input de máximo va encima; con los dos pulgares juntos a la derecha el
    // de mínimo queda inalcanzable si no se lo sube de capa.
    slMax.style.zIndex = 4;
    slMin.style.zIndex = vMin > maxAttr * 0.6 ? 5 : 3;
    const active = vMin > 0 || vMax < maxAttr;
    document.getElementById('btnClearPrice')?.classList.toggle('d-none', !active);
}

function onPriceSliderInput(e) {
    const slMin = document.getElementById('sliderPrecioMin');
    const slMax = document.getElementById('sliderPrecioMax');
    if (!slMin || !slMax) return;
    // Evitar cruce
    if (parseInt(slMin.value) > parseInt(slMax.value)) {
        if (e.target === slMin) slMin.value = slMax.value;
        else                    slMax.value = slMin.value;
    }
    updatePriceSliderUI();
    clearTimeout(precioSliderTimer);
    precioSliderTimer = setTimeout(applyPriceFilter, 400);
}

function applyPriceFilter() {
    const slMin = document.getElementById('sliderPrecioMin');
    const slMax = document.getElementById('sliderPrecioMax');
    if (!slMin || !slMax) return;
    const maxAttr = parseInt(slMin.getAttribute('max')) || precioMaxGlobal;
    const vMin = parseInt(slMin.value);
    const vMax = parseInt(slMax.value);
    currentPrecioMin = vMin > 0        ? String(vMin) : '';
    currentPrecioMax = vMax < maxAttr  ? String(vMax) : '';
    currentPage = 1;
    loadProductos();
}

function clearPriceFilter() {
    const slMin = document.getElementById('sliderPrecioMin');
    const slMax = document.getElementById('sliderPrecioMax');
    if (!slMin || !slMax) return;
    slMin.value = 0;
    slMax.value = slMax.getAttribute('max');
    currentPrecioMin = '';
    currentPrecioMax = '';
    updatePriceSliderUI();
    currentPage = 1;
    loadProductos();
}

/* Vuelve marcas, atributos y precio a su estado inicial. La categoría se
   mantiene: para salir de ella está el × del breadcrumb. */
function resetFiltros() {
    currentAttrFiltros = {};
    currentMarcas      = [];
    currentPrecioMin   = '';
    currentPrecioMax   = '';
    const slMin = document.getElementById('sliderPrecioMin');
    const slMax = document.getElementById('sliderPrecioMax');
    if (slMin && slMax) {
        slMin.value = 0;
        slMax.value = slMax.getAttribute('max');
        updatePriceSliderUI();
    }
    renderAtribFiltros();
    currentPage = 1;
    loadProductos();
}

/* Sin filtros aplicados no hay nada que reiniciar */
function updateResetFiltrosState() {
    const btn = document.getElementById('btnResetFiltros');
    if (!btn) return;
    btn.disabled = !(currentMarcas.length
        || Object.keys(currentAttrFiltros).length
        || currentPrecioMin || currentPrecioMax);
}

/* ~100 posiciones por slider, redondeadas a un valor "lindo". Con el step fijo
   de 50.000 y un máximo de 50.000 el control tenía dos posiciones y no filtraba
   nada intermedio. */
function nicePriceStep(max) {
    const raw = Math.max(1, max / 100);
    const mag = Math.pow(10, Math.floor(Math.log10(raw)));
    return Math.max(1, Math.round(raw / mag) * mag);
}

async function fetchPrecioMax(cat) {
    try {
        const catParam = cat ? `&categoria=${cat}` : '';
        const res  = await fetch(`${API_URL}?action=precio_max&codigo=${encodeURIComponent(COMERCIO)}${catParam}`);
        const data = await res.json();
        if (!data.ok || !data.max) return;
        precioMaxGlobal = data.max;
        const slMin = document.getElementById('sliderPrecioMin');
        const slMax = document.getElementById('sliderPrecioMax');
        if (!slMin || !slMax) return;
        const step = nicePriceStep(data.max);
        slMin.setAttribute('max', data.max);
        slMax.setAttribute('max', data.max);
        slMin.setAttribute('step', step);
        slMax.setAttribute('step', step);
        // Reset a rango completo al cambiar de categoría
        slMin.value = 0;
        slMax.value = data.max;
        currentPrecioMin = '';
        currentPrecioMax = '';
        updatePriceSliderUI();
    } catch(e) {}
}

function filterCatMobile(catId) {
    const subsEl   = document.getElementById('mobileCatsSubs');
    const children = catId ? (window._mobileCatChildren || {})[catId] || [] : [];

    if (children.length > 0) {
        // Mostrar sub-chips
        subsEl.innerHTML = children.map(c =>
            `<button class="chip-cat chip-sub" data-cat="${c.id_categoria}"
                     onclick="filterCat(${c.id_categoria})">
                 ${escHtml(c.nombre)}
             </button>`
        ).join('');
        subsEl.classList.remove('d-none');
    } else {
        // Sin hijos: ocultar segunda fila
        subsEl.innerHTML = '';
        subsEl.classList.add('d-none');
    }

    filterCat(catId);
}

/* ═══════════════════════════════════════════════════════════
   ENVÍO — CHECKOUT
═══════════════════════════════════════════════════════════ */
function toggleEnvioSection() {
    const sec  = document.getElementById('envioSection');
    const icon = document.getElementById('iconEnvio');
    const open = sec.style.display !== 'none';
    sec.style.display = open ? 'none' : 'block';
    icon.style.transform = open ? '' : 'rotate(180deg)';
}

function applyTelefonoMode(mode) {
    const wrap  = document.getElementById('ckTelefonoWrap');
    const label = document.getElementById('ckTelefonoLabel');
    const input = document.getElementById('ckTelefono');
    if (!wrap) return;
    if (mode === 'obligatorio') {
        input.required = true;
        label.innerHTML = 'Teléfono <span style="color:var(--brand)">*</span>';
    } else {
        input.required = false;
        label.textContent = 'Teléfono';
    }
}

function applyEnvioDatosMode(mode) {
    const wrap = document.getElementById('envioWrap');
    const btn  = document.getElementById('btnToggleEnvio');
    const hdr  = document.getElementById('envioOblHdr');
    const sec  = document.getElementById('envioSection');
    const loc  = document.getElementById('ckLocalidad');
    const dir  = document.getElementById('ckDireccion');
    if (!wrap) return;
    if (mode === 'no_pedir') {
        wrap.style.display = 'none';
        if (loc) loc.required = false;
        if (dir) dir.required = false;
    } else if (mode === 'obligatorio') {
        wrap.style.display = '';
        if (btn) btn.style.display = 'none';
        if (hdr) hdr.style.display = '';
        sec.style.display = 'block';
        if (loc) loc.required = true;
        if (dir) dir.required = true;
    } else {
        // opcional (default)
        wrap.style.display = '';
        if (btn) btn.style.display = '';
        if (hdr) hdr.style.display = 'none';
        sec.style.display = 'none';
        if (loc) loc.required = false;
        if (dir) dir.required = false;
    }
}

function getEnvio() {
    return {
        provincia: document.getElementById('ckProvincia')?.value  || '',
        localidad: document.getElementById('ckLocalidad')?.value.trim() || '',
        cp:        document.getElementById('ckCP')?.value.trim()        || '',
        direccion: document.getElementById('ckDireccion')?.value.trim() || '',
        costo_envio:   envioCostoSeleccionado ? envioCostoSeleccionado.precio  : null,
        carrier_envio: envioCostoSeleccionado ? envioCostoSeleccionado.carrier : null,
        envio_modo:    envioModo,
    };
}

/* ── Cotización de envío en checkout ─────────────────────── */
async function cotizarEnvioCheckout() {
    const cp  = document.getElementById('ckCPEnvio')?.value.trim() || '';
    if (!cp || cp.length < 4) {
        showToast('Ingresá un código postal válido (4 dígitos).', 'error');
        return;
    }

    const btn    = document.getElementById('btnCotizarEnvio');
    const result = document.getElementById('ckCotizarEnvioResult');
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cotizando...';
    result.style.display = 'none';

    try {
        const res  = await fetch(`${API_URL}?action=cotizar_envio&codigo=${encodeURIComponent(COMERCIO)}&cp=${encodeURIComponent(cp)}`);
        const data = await res.json();

        if (!data.ok || !data.cotizaciones) {
            result.innerHTML = `<div style="color:#dc2626;font-size:.82rem"><i class="bi bi-x-circle me-1"></i>${data.msg || 'No se pudo cotizar.'}</div>`;
            result.style.display = '';
            return;
        }

        const cots = data.cotizaciones;
        if (!cots.length) {
            result.innerHTML = `<div style="color:#6b7280;font-size:.82rem"><i class="bi bi-info-circle me-1"></i>Sin cotizaciones disponibles para ese CP.</div>`;
            result.style.display = '';
            return;
        }

        // Mostrar opciones de carriers
        let html = '<div style="display:flex;flex-direction:column;gap:.4rem">';
        cots.forEach((c, i) => {
            const hasPrecio = c.precio !== null && c.precio > 0;
            const precioStr = hasPrecio ? formatPrice(c.precio) : '—';
            const diasStr   = c.dias    ? ` · ${c.dias} días hábiles` : '';
            const errorStr  = c.error && !hasPrecio ? `<span style="color:#dc2626;font-size:.75rem"> (${c.error})</span>` : '';

            if (hasPrecio) {
                // Opción seleccionable
                const checked = (envioCostoSeleccionado && envioCostoSeleccionado.carrier === c.carrier) ? 'checked' : (i === 0 ? 'checked' : '');
                html += `
                <label style="display:flex;align-items:center;gap:.6rem;padding:.45rem .6rem;background:#fff;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-size:.85rem" class="cotiz-opt">
                    <input type="radio" name="cotiz_carrier" value="${i}" ${checked}
                           onchange="seleccionarCotizacion(${i},'${escJs(c.carrier)}','${escJs(c.nombre)}',${c.precio},${c.dias||0})"
                           style="flex-shrink:0">
                    <i class="bi bi-truck" style="color:#3b82f6"></i>
                    <span><strong>${escHtml(c.nombre)}</strong>${diasStr}</span>
                    <span style="margin-left:auto;font-weight:700;color:#1e40af">${precioStr}</span>
                </label>`;
                if (checked) seleccionarCotizacion(i, c.carrier, c.nombre, c.precio, c.dias || 0);
            } else {
                html += `
                <div style="display:flex;align-items:center;gap:.6rem;padding:.45rem .6rem;background:#fafafa;border:1px solid #f1f5f9;border-radius:8px;font-size:.85rem;color:#94a3b8">
                    <i class="bi bi-truck"></i>
                    <span>${escHtml(c.nombre)}${errorStr}</span>
                    <span style="margin-left:auto">Sin tarifa</span>
                </div>`;
            }
        });
        html += '</div>';

        if (envioModo === 'referencia') {
            html += `<div style="font-size:.75rem;color:#6b7280;margin-top:.4rem"><i class="bi bi-info-circle me-1"></i>El costo de envío es orientativo y no se suma al total del pedido.</div>`;
        } else {
            html += `<div style="font-size:.75rem;color:#1e40af;margin-top:.4rem"><i class="bi bi-info-circle me-1"></i>El envío seleccionado se sumará al total del pedido.</div>`;
        }

        result.innerHTML = html;
        result.style.display = '';
        // Copiar CP al campo de la sección de envío también
        const ckCp = document.getElementById('ckCP');
        if (ckCp && !ckCp.value) ckCp.value = cp;

        // Actualizar resumen si está abierto
        updateOrderSummaryShipping();

    } catch (e) {
        result.innerHTML = `<div style="color:#dc2626;font-size:.82rem"><i class="bi bi-x-circle me-1"></i>Error al cotizar.</div>`;
        result.style.display = '';
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-search me-1"></i>Cotizar';
    }
}

function seleccionarCotizacion(idx, carrier, nombre, precio, dias) {
    envioCostoSeleccionado = { carrier, nombre, precio: parseFloat(precio), dias };
    updateOrderSummaryShipping();
}

function updateOrderSummaryShipping() {
    // Actualizar la línea de envío en el resumen de orden
    const existing = document.getElementById('orderSummaryEnvio');
    if (!envioCostoSeleccionado || !envioCostoSeleccionado.precio) {
        if (existing) existing.remove();
        return;
    }
    const precioStr = formatPrice(envioCostoSeleccionado.precio);
    const label = envioModo === 'suma_total'
        ? `Envío (${envioCostoSeleccionado.nombre})`
        : `Envío ref. (${envioCostoSeleccionado.nombre})`;
    const style = envioModo === 'suma_total'
        ? 'display:flex;justify-content:space-between;font-size:.88rem;padding:.2rem 0;color:#1e40af'
        : 'display:flex;justify-content:space-between;font-size:.88rem;padding:.2rem 0;color:#64748b';

    if (existing) {
        existing.innerHTML = `<span>${escHtml(label)}</span><span>${precioStr}</span>`;
    } else {
        // Insertar antes del total
        const summary = document.getElementById('orderSummary');
        const totalRow = summary?.querySelector('.order-summary-total');
        if (!summary || !totalRow) return;
        const div = document.createElement('div');
        div.id = 'orderSummaryEnvio';
        div.style.cssText = style;
        div.className = 'order-summary-item';
        div.innerHTML = `<span>${escHtml(label)}</span><span>${precioStr}</span>`;
        summary.insertBefore(div, totalRow);
    }

    // Si es suma_total, actualizar el total mostrado
    if (envioModo === 'suma_total') {
        const cart     = getCart();
        const subtotal = cartSubtotal(cart);
        const total    = subtotal + envioCostoSeleccionado.precio;
        const totalRow = document.querySelector('#orderSummary .order-summary-total span:last-child');
        if (totalRow) totalRow.textContent = formatPrice(total);
    }
}

function escJs(s) { return String(s).replace(/'/g, "\\'"); }

// Clic en una categoría del sidebar: filtra productos y auto-expande hijos si los tiene
function catClick(catId) {
    filterCat(catId);
    const el = document.getElementById('cat-ch-' + catId);
    if (el && el.style.display === 'none') {
        el.style.display = 'block';
        const chev = document.getElementById('chev-' + catId);
        if (chev) chev.classList.add('open');
    }
}

// Solo expande/colapsa sin cambiar el filtro de productos
function toggleCatSubs(catId) {
    const el   = document.getElementById('cat-ch-' + catId);
    const chev = document.getElementById('chev-' + catId);
    if (!el) return;
    const isOpen = el.style.display !== 'none';
    el.style.display = isOpen ? 'none' : 'block';
    if (chev) chev.classList.toggle('open', !isOpen);
}

/* Saca la categoría activa (y sus filtros dependientes) sin recargar la
   grilla: quien llama decide cuándo pedir los productos */
function limpiarCategoriaActiva() {
    currentCat         = 0;
    currentAttrFiltros = {};
    currentMarcas      = [];
    currentCatSchema   = [];
    renderAtribFiltros();
    renderCatFiltros();
    fetchPrecioMax(0);

    document.querySelectorAll('[data-cat]').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('#catQuicknav .cat-qlink').forEach(a => a.classList.remove('active'));
    const pathEl = document.getElementById('catActivePath');
    if (pathEl) { pathEl.innerHTML = ''; pathEl.classList.add('d-none'); }

    const url = new URL(location.href);
    url.searchParams.delete('cat');
    history.replaceState({ cat: 0 }, '', url.toString());
}

function filterCat(catId, scroll = true) {
    currentCat         = catId;
    currentPage        = 1;
    currentAttrFiltros = {};
    currentMarcas      = [];
    currentDescuento   = 0;
    // El rango de precio es propio de cada categoría: si no se limpia acá, la
    // grilla se pide con el rango anterior (fetchPrecioMax lo resetea recién
    // cuando responde, después de loadProductos).
    currentPrecioMin   = '';
    currentPrecioMax   = '';
    document.querySelectorAll('[data-cat]').forEach(el => {
        el.classList.toggle('active', parseInt(el.dataset.cat) === catId);
    });
    // Cargar schema de atributos de la categoría seleccionada
    const cat = catId ? globalCatMap[catId] : null;
    currentCatSchema = (cat && cat.atributos_schema && cat.atributos_schema.length) ? cat.atributos_schema : [];
    renderAtribFiltros();
    renderCatFiltros();
    fetchPrecioMax(catId); // actualiza el máximo del slider según la categoría

    // Actualizar URL con la categoría activa
    const urlCat = new URL(location.href);
    if (catId) {
        urlCat.searchParams.set('cat', catId);
    } else {
        urlCat.searchParams.delete('cat');
    }
    history.pushState({ cat: catId }, '', urlCat.toString());

    loadProductos();
    // Bajar a la grilla: sin esto el cambio de categoría pasa desapercibido
    if (scroll) scrollAProductos(false);
}

function renderAtribFiltros() {
    const wrap   = document.getElementById('atribFiltrosWrap');
    const wrapM  = document.getElementById('mobileAtribFiltros');
    const wrapI  = document.getElementById('atribFiltrosInline');

    if (!currentCatSchema.length) {
        wrap.style.display = 'none'; wrap.innerHTML = '';
        wrapM.style.display = 'none'; wrapM.innerHTML = '';
        if (wrapI) wrapI.innerHTML = '';
        return;
    }

    function buildHtml(prefix) {
        let html = '';
        currentCatSchema.forEach(function(attr) {
            const val = currentAttrFiltros[attr.nombre] || '';
            const safeNombre = escHtml(attr.nombre);
            const inputId = prefix + attr.nombre.replace(/\s+/g,'_');
            html += '<div class="atrib-filtro-item"><label for="' + inputId + '">' + safeNombre;
            if (attr.unidad) html += ' <span style="color:#94a3b8">(' + escHtml(attr.unidad) + ')</span>';
            html += '</label>';
            const jNombre = JSON.stringify(attr.nombre).replace(/"/g, '&quot;');
            if (attr.tipo === 'lista' && attr.opciones && attr.opciones.length) {
                html += '<select id="' + inputId + '" class="form-select form-select-sm" onchange="applyAtribFiltro(' + jNombre + ', this.value)">';
                html += '<option value="">— Todos —</option>';
                attr.opciones.forEach(function(o) {
                    html += '<option value="' + escHtml(o) + '"' + (val === o ? ' selected' : '') + '>' + escHtml(o) + '</option>';
                });
                html += '</select>';
            } else {
                html += '<input type="' + (attr.tipo === 'numero' ? 'number' : 'text') + '" id="' + inputId + '" class="form-control form-control-sm"'
                    + ' value="' + escHtml(val) + '" placeholder="Cualquiera"'
                    + ' oninput="applyAtribFiltro(' + jNombre + ', this.value)">';
            }
            html += '</div>';
        });
        return html;
    }

    wrap.style.display  = ''; wrap.innerHTML       = buildHtml('af_');
    wrapM.style.display = 'block'; wrapM.innerHTML = buildHtml('maf_');
    if (wrapI) wrapI.innerHTML = buildHtml('af_');
}

function applyAtribFiltro(nombre, valor) {
    if (valor === '') {
        delete currentAttrFiltros[nombre];
    } else {
        currentAttrFiltros[nombre] = valor;
    }
    currentPage = 1;
    loadProductos();
}

function renderMarcasFiltros(marcasMap) {
    const wrap  = document.getElementById('marcasFiltrosWrap');
    const wrapM = document.getElementById('mobileMarcasFiltros');
    const wrapI = document.getElementById('marcasFiltrosInline');
    const ids   = Object.keys(marcasMap);

    if (ocultarFiltrosMarcas || ids.length === 0) {
        wrap.style.display  = 'none'; wrap.innerHTML  = '';
        wrapM.style.display = 'none'; wrapM.innerHTML = '';
        if (wrapI) wrapI.innerHTML = '';
        return;
    }

    function buildChips() {
        // En mobile el bloque arranca colapsado: con muchas marcas los chips
        // empujaban toda la grilla hacia abajo
        const sel  = currentMarcas.length ? ` <span class="marcas-sel">(${currentMarcas.length})</span>` : '';
        let html = `<div class="marcas-filtro-bloque${marcasFiltroAbierto ? ' open' : ''}">
            <button type="button" class="marcas-toggle" onclick="toggleMarcasFiltro(this)">
                <span class="marcas-filtro-label">Marca${sel}</span>
                <i class="bi bi-chevron-down chev"></i>
            </button>
            <div class="marcas-chips">`;
        ids.forEach(function(id) {
            const active = currentMarcas.indexOf(parseInt(id)) !== -1;
            const icon   = active
                ? `<i class="bi bi-x-circle-fill" style="font-size:.72rem;opacity:.9"></i>`
                : '';
            html += `<button type="button" class="chip-marca${active ? ' active' : ''}"
                             onclick="toggleMarca(${id})" title="${active ? 'Quitar filtro' : 'Filtrar por esta marca'}">
                         ${escHtml(marcasMap[id])}${icon}
                     </button>`;
        });
        html += '</div></div>';
        return html;
    }

    wrap.style.display  = ''; wrap.innerHTML  = buildChips();
    wrapM.style.display = 'block'; wrapM.innerHTML = buildChips();
    if (wrapI) wrapI.innerHTML = buildChips();
}

function toggleMarcasFiltro(btn) {
    marcasFiltroAbierto = !btn.closest('.marcas-filtro-bloque').classList.contains('open');
    // Los chips se renderizan en varios contenedores: se abren/cierran todos
    document.querySelectorAll('.marcas-filtro-bloque')
            .forEach(b => b.classList.toggle('open', marcasFiltroAbierto));
}

function toggleMarca(id) {
    id = parseInt(id);
    const idx = currentMarcas.indexOf(id);
    if (idx === -1) {
        currentMarcas.push(id);
    } else {
        currentMarcas.splice(idx, 1);
    }
    currentPage = 1;
    loadProductos();
    scrollAGrilla();   // volver al primer producto del resultado filtrado
}

/* ═══════════════════════════════════════════════════════════
   PRODUCTOS
═══════════════════════════════════════════════════════════ */
/* Los filtros (incluido el de precio) sólo se muestran cuando hay una
   categoría, búsqueda o sección de descuento activa */
function updateFiltrosVisibility() {
    const layout = document.querySelector('.w4-layout');
    if (!layout) return;
    const activo = !!(currentCat || (currentQ && currentQ.trim()) || currentDescuento);
    layout.classList.toggle('sin-filtros', !activo);
}

async function loadProductos(page = currentPage) {
    // Cualquier carga de grilla implica que la tienda ya se está mostrando
    cargarTiendaSiHaceFalta(false);
    updateFiltrosVisibility();
    updateResetFiltrosState();
    const pageChanged = page !== currentPage;
    currentPage = page;
    const grid = document.getElementById('productsGrid');
    if (pageChanged) {
        const nav    = document.querySelector('.navbar-tienda');
        const offset = nav ? nav.offsetHeight + 12 : 70;
        const top    = grid.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    }

    // Skeletons mientras carga
    grid.innerHTML = Array(8).fill(`
        <div class="product-card-skeleton">
            <div class="skeleton" style="aspect-ratio:1"></div>
            <div style="padding:.75rem">
                <div class="skeleton mb-2" style="height:12px;width:60%"></div>
                <div class="skeleton mb-1" style="height:14px"></div>
                <div class="skeleton mb-3" style="height:14px;width:80%"></div>
                <div class="skeleton" style="height:36px"></div>
            </div>
        </div>`).join('');

    try {
        let url = `${API_URL}?action=productos&codigo=${encodeURIComponent(COMERCIO)}&page=${page}&limit=20&sort=${currentSort}`;
        if (currentCat)      url += `&categoria=${currentCat}`;
        if (currentQ)        url += `&q=${encodeURIComponent(currentQ)}`;
        if (currentPrecioMin) url += `&precio_min=${encodeURIComponent(currentPrecioMin)}`;
        if (currentPrecioMax) url += `&precio_max=${encodeURIComponent(currentPrecioMax)}`;
        Object.entries(currentAttrFiltros).forEach(function([k, v]) {
            if (v !== '') url += '&attr[' + encodeURIComponent(k) + ']=' + encodeURIComponent(v);
        });
        currentMarcas.forEach(id => { url += `&marcas[]=${id}`; });
        if (currentDescuento) url += `&id_descuento=${currentDescuento}`;
        url += paramsListaPrecio();

        const res  = await fetch(url);
        const data = await res.json();
        if (!data.ok) { grid.innerHTML = `<p class="text-muted">${escHtml(data.msg)}</p>`; return; }

        // Marcas disponibles. Si el backend manda el facet (lista completa,
        // independiente del filtro de marca), se usa tal cual. Si no, se acumulan
        // las marcas ya vistas mientras haya filtro activo: sin acumular, al
        // filtrar por una marca la respuesta solo trae esa y no se puede sumar otra.
        if (Array.isArray(data.marcas) && data.marcas.length) {
            marcasConocidas = {};
            data.marcas.forEach(m => { marcasConocidas[m.id] = m.nombre; });
        } else {
            if (currentMarcas.length === 0) marcasConocidas = {};
            data.items.forEach(p => {
                if (p.id_marca) marcasConocidas[p.id_marca] = p.marca_nombre;
            });
        }
        renderMarcasFiltros(marcasConocidas);

        const label = document.getElementById('toolbarLabel');
        if (data.total === 0) {
            label.textContent = 'Sin resultados';
            grid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:#94a3b8">
                    <i class="bi bi-search" style="font-size:2.5rem;display:block;margin-bottom:.75rem"></i>
                    <p>No encontramos productos con ese criterio.</p>
                </div>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        label.textContent = `${data.total} producto${data.total !== 1 ? 's' : ''}`;

        data.items.forEach(p => { productMap[p.id] = p; });

        grid.innerHTML = data.items.map(renderProductCard).join('');

        lazyLoadImgs(grid);

        renderPagination(data.paginas, data.page);
    } catch (e) {
        grid.innerHTML = '<p class="text-danger">Error al cargar productos. Intentá recargar la página.</p>';
    }
}

function renderProductCard(p) {
    const imgHtml = p.imagen_url
        ? `<img data-src="${escHtml(p.imagen_url)}" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                alt="${escHtml(p.nombre)}" class="product-img" loading="lazy">`
        : `<div class="product-img-placeholder"><i class="bi bi-box-seam"></i></div>`;

    const price = formatPrice(p.precio_final);
    const dscStyle = badgeDescuentoStyle(p.descuento_color);
    const priceHtml = p.descuento_label
        ? (p.descuento_direccion === 'recargo'
            ? `${price}<span class="badge-recargo"${dscStyle}>${escHtml(p.descuento_label)}</span>`
            : `<span class="price-original">${formatPrice(p.precio_sin_descuento)}</span>${price}<span class="badge-descuento"${dscStyle}>${escHtml(p.descuento_label)}</span>`)
        : price;

    // El producto abre su propia página (producto.php), que reusa esta misma
    // tienda en "modo ficha": link real, compartible y abrible en otra pestaña.
    const prodUrl = urlProducto(p.id);
    return `
    <div class="product-card">
        <a href="${prodUrl}" style="display:block;text-decoration:none;color:inherit">${imgHtml}</a>
        <a href="${prodUrl}" class="product-body" style="text-decoration:none;color:inherit;display:block">
            <div class="product-cat">${escHtml(p.categoria_nombre)}</div>
            <div class="product-name" title="${escHtml(p.nombre)}">${escHtml(p.nombre)}</div>
            <div class="product-price">${priceHtml}
                <small>${escHtml(p.unidad_medida || 'unidad')}</small>
            </div>
        </a>
        <div class="product-footer">
            ${p.variantes && p.variantes.length > 0
                ? `<button class="btn-add" onclick="location.href='${prodUrl}'"
                           ${!p.stock_disponible ? 'disabled' : ''}>
                       ${p.stock_disponible
                           ? '<i class="bi bi-tags"></i> Ver opciones'
                           : '<i class="bi bi-slash-circle"></i> Sin stock'}
                   </button>`
                : `<button class="btn-add" onclick="addToCart(${p.id},'${jsAttrStr(p.nombre)}',${p.precio_final},'${jsAttrStr(p.imagen_url || '')}',null,null,${p.stock??0},'${jsAttrStr(p.unidad_medida || '')}')"
                           ${!p.stock_disponible ? 'disabled' : ''}>
                       ${p.stock_disponible
                           ? '<i class="bi bi-plus-lg"></i> Agregar'
                           : '<i class="bi bi-slash-circle"></i> Sin stock'}
                   </button>`
            }
        </div>
    </div>`;
}

// Lazy-load de imágenes con data-src dentro de un scope
function lazyLoadImgs(scope) {
    const imgs = scope.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.src = e.target.dataset.src;
                    obs.unobserve(e.target);
                }
            });
        }, { rootMargin: '100px' });
        imgs.forEach(img => io.observe(img));
    } else {
        imgs.forEach(img => img.src = img.dataset.src);
    }
}

// ── Secciones de descuentos en la home ──────────────────────
async function loadDescuentos() {
    const wrap = document.getElementById('descuentosSectionsWrap');
    if (!wrap) return;
    try {
        const res  = await fetch(`${API_URL}?action=descuentos_web&codigo=${encodeURIComponent(COMERCIO)}`);
        const data = await res.json();
        if (!data.ok || !Array.isArray(data.descuentos) || !data.descuentos.length) return;

        const ROW_LIMIT = 12;

        // Traer productos de cada descuento en paralelo
        const secciones = await Promise.all(data.descuentos.map(async d => {
            try {
                const r = await fetch(`${API_URL}?action=productos&codigo=${encodeURIComponent(COMERCIO)}&id_descuento=${d.id_descuento}&limit=${ROW_LIMIT}`);
                const pd = await r.json();
                return { d, items: (pd.ok && pd.items) ? pd.items : [] };
            } catch (e) { return { d, items: [] }; }
        }));

        let html = '';
        secciones.forEach(({ d, items }) => {
            if (!items.length) return;
            items.forEach(p => { productMap[p.id] = p; });
            const esRecargo = d.direccion === 'recargo';
            const eyebrow   = esRecargo ? 'Recargo' : 'Ofertas';
            const eyeIcon   = esRecargo ? 'bi-arrow-up-circle' : 'bi-lightning-charge-fill';
            const verTodos  = d.cant > items.length
                ? `<button class="dsc-section-vertodos" onclick="verTodosDescuento(${d.id_descuento})">Ver todos (${d.cant}) <i class="bi bi-arrow-right"></i></button>`
                : '';
            // Color propio del descuento (título, flechas, botón "ver todos").
            // Sobre fondo blanco un color muy claro no se lee: se oscurece.
            let colorSec = /^#[0-9a-f]{6}$/i.test(d.color || '') ? d.color : '';
            if (colorSec && lumaHex(colorSec) > .75) colorSec = shadeColor(colorSec, -45);
            const styleSec = colorSec ? ` style="--dsc-main:${colorSec}"` : '';
            html += `
            <section class="dsc-section${esRecargo ? ' dsc-section--recargo' : ''}"${styleSec}>
                <div class="dsc-section-head">
                    <div class="dsc-section-titles">
                        <span class="dsc-section-eyebrow"><i class="bi ${eyeIcon}"></i> ${eyebrow}</span>
                        <h2 class="dsc-section-title" title="${escHtml(d.nombre)}">${escHtml(d.nombre)}</h2>
                    </div>
                    ${verTodos}
                </div>
                <div class="dsc-section-shell">
                    <button type="button" class="dsc-section-nav prev" aria-label="Anteriores" disabled>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="dsc-section-row">${items.map(renderProductCard).join('')}</div>
                    <button type="button" class="dsc-section-nav next" aria-label="Siguientes" disabled>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </section>`;
        });

        if (!html) return;
        wrap.innerHTML = html;
        wrap.classList.remove('d-none');
        lazyLoadImgs(wrap);
        wrap.querySelectorAll('.dsc-section-shell').forEach(initDscFlechas);
    } catch (e) { /* silencioso: la home funciona sin secciones */ }
}

/* Flechas laterales del carrusel de cada sección de descuentos (reemplazan
   la barra de scroll horizontal) */
function initDscFlechas(shell) {
    const row  = shell.querySelector('.dsc-section-row');
    const prev = shell.querySelector('.dsc-section-nav.prev');
    const next = shell.querySelector('.dsc-section-nav.next');
    if (!row || !prev || !next) return;

    // Desliza el ancho visible dejando una tarjeta asomada, para no perder contexto
    const pasoScroll = () => {
        const card   = row.querySelector('.product-card');
        const solape = card ? card.offsetWidth * 0.6 : 0;
        return Math.max(row.clientWidth - solape, row.clientWidth * 0.5);
    };
    const sincronizar = () => {
        const hayOverflow = row.scrollWidth - row.clientWidth > 4;
        prev.disabled = !hayOverflow || row.scrollLeft <= 2;
        next.disabled = !hayOverflow || row.scrollLeft >= row.scrollWidth - row.clientWidth - 2;
    };
    prev.addEventListener('click', () => row.scrollBy({ left: -pasoScroll(), behavior: 'smooth' }));
    next.addEventListener('click', () => row.scrollBy({ left:  pasoScroll(), behavior: 'smooth' }));
    row.addEventListener('scroll', sincronizar, { passive: true });
    window.addEventListener('resize', sincronizar);

    sincronizar();
    setTimeout(sincronizar, 400);   // las imágenes cambian el ancho al cargar
}

// "Ver todos" de una sección → filtra la grilla principal por ese descuento
function verTodosDescuento(id) {
    currentCat         = 0;
    currentQ           = '';
    currentPage        = 1;
    currentAttrFiltros = {};
    currentMarcas      = [];
    currentDescuento   = id;
    const si = document.getElementById('searchInput'); if (si) si.value = '';
    document.body.classList.remove('busqueda-activa');
    document.querySelectorAll('[data-cat]').forEach(el => el.classList.remove('active'));
    loadProductos(1);
    const layout = document.querySelector('.w4-layout') || document.getElementById('productsGrid');
    if (layout) layout.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function renderPagination(total, current) {
    const pag = document.getElementById('pagination');
    if (total <= 1) { pag.innerHTML = ''; return; }
    let html = '';
    const prev = current > 1;
    const next = current < total;
    html += `<button class="page-btn" onclick="loadProductos(${current-1})" ${!prev?'disabled':''}><i class="bi bi-chevron-left"></i></button>`;
    const from = Math.max(1, current - 2);
    const to   = Math.min(total, current + 2);
    if (from > 1) html += `<button class="page-btn" onclick="loadProductos(1)">1</button>${from>2?'<span style="padding:0 4px">…</span>':''}`;
    for (let i = from; i <= to; i++) {
        html += `<button class="page-btn ${i===current?'active':''}" onclick="loadProductos(${i})">${i}</button>`;
    }
    if (to < total) html += `${to<total-1?'<span style="padding:0 4px">…</span>':''}<button class="page-btn" onclick="loadProductos(${total})">${total}</button>`;
    html += `<button class="page-btn" onclick="loadProductos(${current+1})" ${!next?'disabled':''}><i class="bi bi-chevron-right"></i></button>`;
    pag.innerHTML = html;
}

/* ═══════════════════════════════════════════════════════════
   CARRITO (localStorage)
═══════════════════════════════════════════════════════════ */
function getCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; } catch { return []; }
}
function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartBadge();
}

/* Pesables (kg / gramo): el precio del producto es por unidad de medida, así que
   `cantidad` se guarda en esa misma unidad (0,5 para un kg, 500 para un gramo) y
   sólo la interfaz habla en gramos. Sin esto el comprador tenía que sumar de a 1,
   es decir kilo por kilo o gramo por gramo. */
function esPesable(unidad) { return unidad === 'kg' || unidad === 'gramo'; }
// El comprador escribe el peso en la unidad del producto: un producto en kg se pide
// en kilos (1 = 1 kg, 0,5 = medio) y uno en gramo se pide en gramos (500 = 500 g).
function pesoInicial(unidad) { return unidad === 'kg' ? 1 : 500; }
// step="any" en kg: con un paso fijo Chrome marca 0,75 como stepMismatch y pinta el
// campo en rojo aunque el peso sea válido.
function pesoPaso(unidad)    { return unidad === 'kg' ? 'any' : '1'; }
function redondearPeso(v, unidad) {
    return unidad === 'kg' ? Math.round(v * 1000) / 1000 : Math.round(v);
}
function cantTxt(item) {
    if (!esPesable(item.unidad)) return String(item.cantidad);
    const n = item.unidad === 'kg'
        ? String(item.cantidad.toFixed(3)).replace(/0+$/, '').replace(/\.$/, '').replace('.', ',')
        : String(item.cantidad);
    return n + ' ' + (item.unidad === 'kg' ? 'kg' : 'g');
}

function addToCart(id, nombre, precio, imagen, idVariante, varLabel, stock, unidad) {
    const cartKey  = idVariante ? String(id) + '_v' + idVariante : String(id);
    const cart     = getCart();
    const idx      = cart.findIndex(i => (i.cart_key || String(i.id)) === cartKey);
    const maxStock = stock || 0;
    const paso     = esPesable(unidad) ? pesoInicial(unidad) : 1;
    if (idx >= 0) {
        if (maxStock > 0 && cart[idx].cantidad >= maxStock) {
            showToast('No hay más stock disponible', 'error');
            return;
        }
        let nueva = cart[idx].cantidad + paso;
        if (maxStock > 0 && nueva > maxStock) nueva = maxStock;
        cart[idx].cantidad = Math.round(nueva * 1000) / 1000;
    } else {
        const inicial = maxStock > 0 ? Math.min(paso, maxStock) : paso;
        cart.push({ cart_key: cartKey, id, id_variante: idVariante || null,
                    variante_label: varLabel || null, nombre, precio, imagen,
                    cantidad: Math.round(inicial * 1000) / 1000, stock: maxStock,
                    unidad: unidad || null });
    }
    saveCart(cart);
    showToast(`"${nombre}" agregado al carrito`);
    renderCartDrawer();
}

function updateQuantity(cartKey, delta) {
    const cart = getCart();
    const idx  = cart.findIndex(i => (i.cart_key || String(i.id)) === String(cartKey));
    if (idx < 0) return;
    const newQty   = cart[idx].cantidad + delta;
    const maxStock = cart[idx].stock || 0;
    if (newQty < 1) return;
    if (maxStock > 0 && newQty > maxStock) return;
    cart[idx].cantidad = newQty;
    saveCart(cart);
    renderCartDrawer();
}

/* Pesables: el comprador escribe el peso en la unidad del producto. */
function setPeso(cartKey, valor) {
    const cart = getCart();
    const idx  = cart.findIndex(i => (i.cart_key || String(i.id)) === String(cartKey));
    if (idx < 0) return;
    const item = cart[idx];
    let cant   = redondearPeso(parseFloat(String(valor).replace(',', '.')), item.unidad);
    if (!(cant > 0)) { renderCartDrawer(); return; }   // vacío o ≤ 0 → vuelve al peso anterior
    const maxStock = item.stock || 0;
    if (maxStock > 0 && cant > maxStock) {
        cant = maxStock;
        showToast('Sólo quedan ' + cantTxt({ cantidad: maxStock, unidad: item.unidad }), 'error');
    }
    item.cantidad = cant;
    saveCart(cart);
    renderCartDrawer();
}

function removeItem(cartKey) {
    saveCart(getCart().filter(i => (i.cart_key || String(i.id)) !== String(cartKey)));
    renderCartDrawer();
}

function updateCartBadge() {
    const cart  = getCart();
    const total = cart.reduce((s, i) => s + (esPesable(i.unidad) ? 1 : i.cantidad), 0);
    const badge = document.getElementById('cartBadge');
    if (total > 0) {
        badge.textContent = total > 99 ? '99+' : total;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function cartSubtotal(cart) {
    return cart.reduce((s, i) => s + i.precio * i.cantidad, 0);
}

const FP_ICONS = { efectivo:'bi-cash-coin', transferencia:'bi-bank', mercadopago:'bi-credit-card', nave:'bi-qr-code', gocuotas:'bi-credit-card-2-front' };
const FP_NAMES = { efectivo:'Efectivo', transferencia:'Transferencia', mercadopago:'MercadoPago', nave:'Nave', gocuotas:'GoCuotas' };

function applyFpDesc(precio, dsc) {
    if (!dsc) return precio;
    if (dsc.tipo === 'porcentaje') {
        const f = dsc.direccion === 'recargo' ? 1 + dsc.valor / 100 : 1 - dsc.valor / 100;
        return Math.round(precio * f * 100) / 100;
    }
    return dsc.direccion === 'recargo' ? precio + dsc.valor : Math.max(0, precio - dsc.valor);
}

function renderFpMethodsBanner() {
    const entries = Object.entries(descuentosFormaPago);
    const el = document.getElementById('fpMethodsBanner');
    if (!entries.length || !el) return;
    let html = '<span class="fp-banner-label"><i class="bi bi-tags me-1"></i>Beneficios por medio de pago:</span>';
    entries.forEach(([fp, dsc]) => {
        const esR = dsc.direccion === 'recargo';
        html += `<span class="fp-banner-pill ${esR ? 'fp-banner-pill-recargo' : 'fp-banner-pill-desc'}">` +
            `<i class="bi ${FP_ICONS[fp] || 'bi-credit-card'} me-1"></i>${FP_NAMES[fp] || fp} <strong>${dsc.label}</strong></span>`;
    });
    el.innerHTML = html;
    el.style.display = '';
}

function renderProdModalMediosPago(precioFinal) {
    const el      = document.getElementById('prodModalMediosPago');
    const entries = Object.entries(descuentosFormaPago);
    if (!entries.length || !precioFinal || !el) { if (el) el.style.display = 'none'; return; }
    let html = '<div class="prod-modal-medios-pago"><div class="prod-modal-medios-titulo"><i class="bi bi-tags me-1"></i>Precio por medio de pago</div>';
    entries.forEach(([fp, dsc]) => {
        const esR   = dsc.direccion === 'recargo';
        const precio = applyFpDesc(precioFinal, dsc);
        html += `<div class="prod-modal-medio-row">` +
            `<span class="prod-modal-medio-name"><i class="bi ${FP_ICONS[fp] || 'bi-credit-card'} me-1"></i>${FP_NAMES[fp] || fp}</span>` +
            `<span class="prod-modal-medio-precio">${formatPrice(precio)}</span>` +
            `<span class="prod-modal-medio-badge ${esR ? 'badge-recargo' : 'badge-descuento'}">${dsc.label}</span>` +
            `</div>`;
    });
    html += '</div>';
    el.innerHTML = html;
    el.style.display = '';
}

function getCartTotalConEnvio() {
    let total = cartSubtotal(getCart());
    if (envioModo === 'suma_total' && envioCostoSeleccionado && envioCostoSeleccionado.precio > 0) {
        total += envioCostoSeleccionado.precio;
    }
    if (promoDescuentoActual && promoDescuentoActual.descuento > 0) {
        total = Math.max(0, total - promoDescuentoActual.descuento);
    }
    return total;
}

/* ═══════════════════════════════════════════════════════════
   PROMOS / COMBOS
═══════════════════════════════════════════════════════════ */
function schedulePromoCheck() {
    clearTimeout(promoCheckTimer);
    promoCheckTimer = setTimeout(checkPromoCombo, 400);
}

async function checkPromoCombo() {
    const cart = getCart();
    if (!cart.length) { promoDescuentoActual = null; updatePromoDisplay(); return; }
    const items = cart
        .filter(i => i.id > 0)
        .map(i => ({ id_producto: i.id, cantidad: i.cantidad, precio_unit: i.precio }));
    if (!items.length) { promoDescuentoActual = null; updatePromoDisplay(); return; }
    try {
        const fd = new FormData();
        fd.append('action', 'check_promos');
        fd.append('codigo', COMERCIO);
        fd.append('canal', 'web');
        fd.append('items', JSON.stringify(items));
        const res  = await fetch(`${API_URL}?action=check_promos`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok && data.data && data.data.length) {
            const total   = data.data.reduce((s, p) => s + p.monto_descuento, 0);
            const nombres = data.data.map(p => p.nombre + (p.veces > 1 ? ' \u00d7' + p.veces : '')).join(', ');
            promoDescuentoActual = { descuento: total, nombres };
        } else {
            promoDescuentoActual = null;
        }
    } catch (e) {
        promoDescuentoActual = null;
    }
    updatePromoDisplay();
}

function updatePromoDisplay() {
    const badge    = document.getElementById('cartPromoBadge');
    const totalEl  = document.getElementById('cartTotal');
    const subtotal = cartSubtotal(getCart());
    if (promoDescuentoActual && promoDescuentoActual.descuento > 0) {
        if (badge) {
            badge.style.display = '';
            badge.innerHTML = '<i class="bi bi-gift me-1"></i><strong>' +
                escHtml(promoDescuentoActual.nombres) + '</strong> \u2014 ' +
                formatPrice(promoDescuentoActual.descuento) + ' OFF';
        }
        if (totalEl) totalEl.textContent = formatPrice(Math.max(0, subtotal - promoDescuentoActual.descuento));
    } else {
        if (badge)   badge.style.display = 'none';
        if (totalEl) totalEl.textContent  = formatPrice(subtotal);
    }
}

function getDiscountedTotal(payMethod) {
    const dsc   = descuentosFormaPago[payMethod];
    const total = getCartTotalConEnvio();
    if (!dsc || !total) return null;
    if (dsc.tipo === 'porcentaje') {
        const factor = dsc.direccion === 'recargo' ? 1 + dsc.valor / 100 : 1 - dsc.valor / 100;
        return Math.round(total * factor * 100) / 100;
    } else {
        return dsc.direccion === 'recargo'
            ? total + dsc.valor
            : Math.max(0, total - dsc.valor);
    }
}

/* ═══════════════════════════════════════════════════════════
   CART DRAWER
═══════════════════════════════════════════════════════════ */
function openCart() {
    renderCartDrawer();
    document.getElementById('cartDrawer').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    document.getElementById('cartDrawer').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

function renderCartDrawer() {
    const cart   = getCart();
    const body   = document.getElementById('cartBody');
    const footer = document.getElementById('cartFooter');

    if (cart.length === 0) {
        body.innerHTML = `<div class="cart-empty"><i class="bi bi-cart-x"></i>Tu carrito está vacío</div>`;
        footer.style.display = 'none';
        updateCartBadge();
        return;
    }

    body.innerHTML = cart.map(item => {
        const ck = escHtml(item.cart_key || String(item.id));
        return `
        <div class="cart-item">
            ${item.imagen
                ? `<img src="${escHtml(item.imagen)}" alt="" class="cart-item-img">`
                : `<div class="cart-item-img d-flex align-items-center justify-content-center" style="background:#f0f2f5;border-radius:8px"><i class="bi bi-box-seam text-muted"></i></div>`
            }
            <div class="cart-item-info">
                <div class="cart-item-name">${escHtml(item.nombre)}</div>
                ${item.variante_label ? `<div style="font-size:.72rem;color:var(--brand);font-weight:600"><i class="bi bi-tag-fill me-1"></i>${escHtml(item.variante_label)}</div>` : ''}
                <div class="cart-item-price">${formatPrice(item.precio)} ${esPesable(item.unidad) ? '/ ' + escHtml(item.unidad === 'kg' ? 'kg' : 'g') : 'c/u'}</div>
                <div class="qty-control">
                    ${esPesable(item.unidad)
                        ? `<input type="number" class="qty-peso" min="${item.unidad === 'kg' ? '0.001' : '1'}"
                                  step="${pesoPaso(item.unidad)}" inputmode="decimal"
                                  value="${item.cantidad}"
                                  onchange="setPeso('${ck}', this.value)">
                           <span class="qty-peso-uni">${item.unidad === 'kg' ? 'kg' : 'g'}</span>`
                        : `<button class="qty-btn" onclick="updateQuantity('${ck}',-1)">−</button>
                           <span class="qty-val">${item.cantidad}</span>
                           <button class="qty-btn" onclick="updateQuantity('${ck}',1)" ${item.stock > 0 && item.cantidad >= item.stock ? 'disabled' : ''}>+</button>`
                    }
                    <span class="cart-item-subtotal ms-2">${formatPrice(item.precio * item.cantidad)}</span>
                </div>
            </div>
            <button class="btn-remove" onclick="removeItem('${ck}')" title="Quitar">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    }).join('');

    footer.style.display = '';
    updateCartBadge();
    updatePromoDisplay();   // muestra dato actual inmediatamente
    schedulePromoCheck();   // refresca desde la API
}

/* ═══════════════════════════════════════════════════════════
   MODO COMERCIO — helpers
═══════════════════════════════════════════════════════════ */
async function loadClientesComercio() {
    if (clientesComercio.length) return; // ya cargados
    try {
        const res  = await fetch(`${API_URL}?action=clientes&codigo=${encodeURIComponent(COMERCIO)}`);
        const data = await res.json();
        if (data.ok) {
            clientesComercio = data.clientes || [];
            const sel = document.getElementById('ckClienteSelect');
            clientesComercio.forEach(c => {
                const opt = document.createElement('option');
                opt.value       = c.id_cliente;
                opt.textContent = c.nombre + (c.telefono ? ' — ' + c.telefono : '');
                opt.dataset.nombre   = c.nombre;
                opt.dataset.email    = c.email;
                opt.dataset.telefono = c.telefono;
                sel.appendChild(opt);
            });
        }
    } catch (err) { console.warn('loadClientesComercio:', err); }
}

function onSelectCliente(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        selectedIdCliente = null;
        document.getElementById('ckNombreEmailWrap').style.display = '';
        document.getElementById('ckNombre').required = true;
        document.getElementById('ckEmail').required  = true;
        return;
    }
    selectedIdCliente = parseInt(opt.value, 10);
    // Rellenar campos ocultos por si los necesita el submit sin pago
    document.getElementById('ckNombre').value = opt.dataset.nombre  || '';
    document.getElementById('ckEmail').value  = opt.dataset.email   || '';
    document.getElementById('ckTelefono').value = opt.dataset.telefono || '';
    // Ocultar bloque nombre/email (el comercio ya eligió el cliente)
    document.getElementById('ckNombreEmailWrap').style.display = 'none';
    document.getElementById('ckNombre').required = false;
    document.getElementById('ckEmail').required  = false;
}

/* ═══════════════════════════════════════════════════════════
   CHECKOUT MODAL
═══════════════════════════════════════════════════════════ */
function openCheckout() {
    const cart = getCart();
    if (cart.length === 0) { showToast('Tu carrito está vacío', 'error'); return; }

    // Resetear cotización de envío
    envioCostoSeleccionado = null;
    const ckCPEnvio = document.getElementById('ckCPEnvio');
    if (ckCPEnvio) ckCPEnvio.value = '';
    const cotizResult = document.getElementById('ckCotizarEnvioResult');
    if (cotizResult) cotizResult.style.display = 'none';

    // Renderizar resumen
    let html = cart.map(i => `
        <div class="order-summary-item">
            <span>${escHtml(i.nombre)} × ${cantTxt(i)}</span>
            <span>${formatPrice(i.precio * i.cantidad)}</span>
        </div>`).join('');
    if (promoDescuentoActual && promoDescuentoActual.descuento > 0) {
        html += `<div class="order-summary-item" style="color:#0dcaf0">
            <span><i class="bi bi-gift me-1"></i>${escHtml(promoDescuentoActual.nombres)}</span>
            <span>- ${formatPrice(promoDescuentoActual.descuento)}</span>
        </div>`;
    }
    html += `<div class="order-summary-total"><span>Total</span><span>${formatPrice(getCartTotalConEnvio())}</span></div>`;
    document.getElementById('orderSummary').innerHTML = html;

    // Mostrar total con descuento en cada botón de pago
    [
        { key: 'efectivo',      btnId: 'btnEfectivo', baseHtml: '<i class="bi bi-cash-coin"></i> Pagar en efectivo' },
        { key: 'transferencia', btnId: 'btnTransf',   baseHtml: '<i class="bi bi-bank"></i> Confirmar pedido (Transferencia)' },
        { key: 'gocuotas',     btnId: 'btnGocuotas', baseHtml: '<i class="bi bi-credit-card-2-front"></i> Pagar con GoCuotas' },
        { key: 'mercadopago',  btnId: 'btnPagar',    baseHtml: '<i class="bi bi-credit-card"></i> Pagar con MercadoPago' },
        { key: 'nave',         btnId: 'btnNave',     baseHtml: '<i class="bi bi-qr-code"></i> Pagar con Nave' },
    ].forEach(function({ key, btnId, baseHtml }) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        const tot = getDiscountedTotal(key);
        btn.innerHTML = tot !== null
            ? baseHtml + ` <span style="margin-left:.4rem;font-weight:800">${formatPrice(tot)}</span>`
            : baseHtml;
    });

    document.getElementById('checkoutForm').reset();
    selectedIdCliente = null;

    if (modoComercio) {
        // Mostrar selector de cliente, ocultar nombre/email
        document.getElementById('ckClienteWrap').style.display = '';
        document.getElementById('ckNombreEmailWrap').style.display = 'none';
        document.getElementById('ckNombre').required = false;
        document.getElementById('ckEmail').required  = false;
        loadClientesComercio();
    } else {
        document.getElementById('ckClienteWrap').style.display = 'none';
        document.getElementById('ckNombreEmailWrap').style.display = '';
        document.getElementById('ckNombre').required = true;
        document.getElementById('ckEmail').required  = true;
        // Pre-llenar datos del cliente logueado y bloquear el email
        const cli      = getClienteSession();
        const emailEl  = document.getElementById('ckEmail');
        const nombreEl = document.getElementById('ckNombre');
        if (cli && cli.email) {
            emailEl.value    = cli.email;
            emailEl.readOnly = true;
            emailEl.classList.add('bg-light');
            if (cli.nombre) nombreEl.value = cli.nombre;
        } else {
            emailEl.readOnly = false;
            emailEl.classList.remove('bg-light');
        }
    }

    document.getElementById('checkoutModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (!modoComercio) {
        const cli      = getClienteSession();
        const nombreEl = document.getElementById('ckNombre');
        setTimeout(() => (cli && cli.email ? document.getElementById('ckTelefono') : nombreEl).focus(), 300);
    }
}

function closeCheckout() {
    document.getElementById('checkoutModal').classList.remove('open');
    document.body.style.overflow = '';
}

function submitPayway(e) {
    e.preventDefault();

    const nombre   = document.getElementById('ckNombre').value.trim();
    const email    = document.getElementById('ckEmail').value.trim();
    const telefono = document.getElementById('ckTelefono')?.value.trim() || '';
    const cart     = getCart();

    if (modoComercio && !selectedIdCliente) { showToast('Seleccioná un cliente.', 'error'); return; }
    if (!modoComercio && (!nombre || !email)) { showToast('Completá nombre y email.', 'error'); return; }
    if (!cart.length) { showToast('Tu carrito está vacío.', 'error'); return; }
    if (!decidirInstance) { showToast('Payway no está disponible.', 'error'); return; }

    const cardRaw = document.getElementById('pwCardNumber').value.replace(/\D/g, '');
    if (!cardRaw) { showToast('Ingresá el número de tarjeta.', 'error'); return; }

    const btn = document.getElementById('btnPayway');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    showLoading(true);

    // Detectar marca de tarjeta por BIN
    let paymentMethodId = 1; // VISA por defecto
    if (/^5[1-5]/.test(cardRaw) || /^2[2-7]/.test(cardRaw)) paymentMethodId = 2; // Mastercard
    else if (/^3[47]/.test(cardRaw)) paymentMethodId = 3; // Amex

    const bin = cardRaw.substring(0, 6);

    decidirInstance.createToken(document.getElementById('formPayway'), function(status, response) {
        if (status !== 200 && status !== 201) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card-fill"></i> Pagar con Payway';
            showLoading(false);
            const errMsg = (response && (response.message || response.error)) || 'Error al procesar la tarjeta. Verificá los datos ingresados.';
            showToast(errMsg, 'error');
            return;
        }

        const pwToken = response.token;
        const cuotas  = parseInt(document.getElementById('pwCuotas').value, 10) || 1;
        const baseUrl = window.location.href.split('?')[0];
        const clientePayload = modoComercio
            ? { id_cliente: selectedIdCliente, nombre, email, telefono }
            : { nombre, email, telefono };

        const payload = {
            comercio:                 COMERCIO,
            items:                    cart.map(i => ({ id_producto: i.id, id_variante: i.id_variante || null, cantidad: i.cantidad })),
            cliente:                  clientePayload,
            envio:                    getEnvio(),
            payment_method:           'payway',
            payway_token:             pwToken,
            payway_bin:               bin,
            payway_payment_method_id: paymentMethodId,
            payway_installments:      cuotas,
            back_urls: {
                success: `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=approved&external_reference=PREF_ID`,
                failure: `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=failure`,
                pending: `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=pending`,
            },
        };

        fetch(`${API_URL}?action=checkout`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok && (data.redirect_url || data.init_point)) {
                localStorage.removeItem(CART_KEY);
                updateCartBadge();
                window.location.href = data.redirect_url || data.init_point;
            } else {
                showToast(data.msg || 'Pago rechazado. Verificá los datos de tu tarjeta.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-credit-card-fill"></i> Pagar con Payway';
                showLoading(false);
            }
        })
        .catch(() => {
            showToast('Error de conexión. Intentá nuevamente.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card-fill"></i> Pagar con Payway';
            showLoading(false);
        });
    });
}

async function submitCheckout(e, paymentMethod = 'mercadopago') {
    if (e) e.preventDefault();

    const nombre   = document.getElementById('ckNombre').value.trim();
    const email    = document.getElementById('ckEmail').value.trim();
    const telefono = document.getElementById('ckTelefono').value.trim();
    const cart     = getCart();

    if (modoComercio && !selectedIdCliente) { showToast('Seleccioná un cliente.', 'error'); return; }
    if (!modoComercio && (!nombre || !email)) { showToast('Completá nombre y email.', 'error'); return; }
    if (!modoComercio && checkoutTelefono === 'obligatorio' && !telefono) { showToast('El teléfono es obligatorio.', 'error'); return; }
    if (!cart.length) { showToast('Tu carrito está vacío.',  'error'); return; }

    const btnId  = paymentMethod === 'nave' ? 'btnNave'
                 : paymentMethod === 'gocuotas' ? 'btnGocuotas'
                 : 'btnPagar';
    const btnLbl = paymentMethod === 'nave'
        ? '<i class="bi bi-qr-code"></i> Pagar con Nave'
        : paymentMethod === 'gocuotas'
        ? '<i class="bi bi-credit-card-2-front"></i> Pagar con GoCuotas'
        : '<i class="bi bi-credit-card"></i> Pagar con MercadoPago';
    const btn = document.getElementById(btnId);
    btn.disabled    = true;
    btn.textContent = 'Procesando...';
    showLoading(true);

    const baseUrl = window.location.href.split('?')[0];
    const clientePayload = modoComercio
        ? { id_cliente: selectedIdCliente, nombre, email, telefono }
        : { nombre, email, telefono };
    const payload = {
        comercio:       COMERCIO,
        items:          cart.map(i => ({ id_producto: i.id, id_variante: i.id_variante || null, cantidad: i.cantidad })),
        cliente:        clientePayload,
        envio:          getEnvio(),
        payment_method: paymentMethod,
        back_urls: {
            success: `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=approved&external_reference=PREF_ID`,
            failure: `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=failure`,
            pending: `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=pending`,
        },
    };

    try {
        const res  = await fetch(`${API_URL}?action=checkout`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.ok && (data.redirect_url || data.init_point)) {
            window.location.href = data.redirect_url || data.init_point;
        } else {
            showToast(data.msg || 'Error al iniciar el pago.', 'error');
            btn.disabled  = false;
            btn.innerHTML = btnLbl;
            showLoading(false);
        }
    } catch (err) {
        showToast('Error de conexión. Intentá nuevamente.', 'error');
        btn.disabled  = false;
        btn.innerHTML = btnLbl;
        showLoading(false);
    }
}

/* ═══════════════════════════════════════════════════════════
   UTILIDADES UI
═══════════════════════════════════════════════════════════ */
function showToast(msg, type = '') {
    const container = document.getElementById('toastContainer');
    const el = document.createElement('div');
    el.className = `toast-msg${type ? ' ' + type : ''}`;
    el.textContent = msg;
    container.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(8px)'; el.style.transition = 'all .3s'; setTimeout(() => el.remove(), 300); }, 2800);
}

function showLoading(show) {
    document.getElementById('loadingOverlay').classList.toggle('show', show);
}

function showFatalError(msg) {
    document.body.innerHTML = `
        <div style="display:grid;place-items:center;height:100vh;font-family:sans-serif">
            <div style="text-align:center;max-width:400px;padding:2rem">
                <div style="font-size:3rem;margin-bottom:1rem">🛑</div>
                <h2 style="margin-bottom:.5rem">Tienda no disponible</h2>
                <p style="color:#64748b">${escHtml(msg)}</p>
            </div>
        </div>`;
}

function formatPrice(n) {
    return '$ ' + Math.round(Number(n)).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Modales legales (políticas + arrepentimiento) ────────── */
(function() {
    function openOverlay(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
    function closeOverlay(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

    // Legales — modal reutilizable
    document.querySelectorAll('.footer-legal-link').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('legalModalTitle').innerHTML =
                '<i class="bi ' + this.dataset.icono + ' me-2"></i>' + this.dataset.titulo;
            document.getElementById('legalModalBody').textContent = this.dataset.texto || '';
            openOverlay('legalModal');
        });
    });
    document.getElementById('legalModalCloseBtn').addEventListener('click', () => closeOverlay('legalModal'));
    document.getElementById('legalModal').addEventListener('click', function(e) {
        if (e.target === this) closeOverlay('legalModal');
    });

    // Arrepentimiento — abrir
    document.getElementById('footerArrepentimientoBtn').addEventListener('click', function() {
        document.getElementById('arrepentimientoForm').classList.remove('d-none');
        document.getElementById('arrepentimientoOk').classList.add('d-none');
        ['arrNombre','arrEmail','arrTelefono','arrNroPedido','arrMotivo'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
        });
        openOverlay('arrepentimientoOverlay');
    });
    document.getElementById('arrepentimientoCloseBtn').addEventListener('click', () => closeOverlay('arrepentimientoOverlay'));
    document.getElementById('arrepentimientoCancelBtn').addEventListener('click', () => closeOverlay('arrepentimientoOverlay'));
    document.getElementById('arrepentimientoOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeOverlay('arrepentimientoOverlay');
    });

    // Arrepentimiento — enviar
    document.getElementById('arrEnviarBtn').addEventListener('click', async function() {
        const nombre  = document.getElementById('arrNombre').value.trim();
        const email   = document.getElementById('arrEmail').value.trim();
        const telefono= document.getElementById('arrTelefono').value.trim();
        const nroPed  = document.getElementById('arrNroPedido').value.trim();
        const motivo  = document.getElementById('arrMotivo').value.trim();

        if (!nombre)  { alert('El nombre es obligatorio.'); return; }
        if (!email)   { alert('El email es obligatorio.'); return; }
        if (!motivo)  { alert('El motivo es obligatorio.'); return; }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

        try {
            const res  = await fetch(`${API_URL}?action=arrepentimiento`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ comercio: COMERCIO, nombre, email, telefono, nro_pedido: nroPed, motivo })
            });
            const data = await res.json();
            if (!data.ok) { alert(data.msg || 'Error al enviar. Intentá más tarde.'); return; }
            document.getElementById('arrepentimientoForm').classList.add('d-none');
            document.getElementById('arrepentimientoOk').classList.remove('d-none');
        } catch (err) {
            alert('Error de conexión. Intentá más tarde.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i>Enviar solicitud';
        }
    });
})();

// Luminancia relativa aproximada del hex (0 = negro, 1 = blanco).
function lumaHex(hex) {
    const n = parseInt(hex.slice(1), 16);
    return (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 0xff) + 0.114 * (n & 0xff)) / 255;
}

// Color propio del descuento para el badge "% OFF". Vacío = color por defecto del CSS.
// Si el color es muy claro (ej: blanco) el texto pasa a oscuro y se agrega borde,
// si no el badge queda invisible sobre la tarjeta blanca.
function badgeDescuentoStyle(color) {
    if (!/^#[0-9a-fA-F]{6}$/.test(color || '')) return '';
    const claro = lumaHex(color) > .75;
    return ` style="background:${color};color:${claro ? '#1f2937' : '#fff'}${claro ? ';border:1px solid rgba(15,23,42,.25)' : ''}"`;
}
// Para strings JS dentro de atributos HTML (ej: onclick="f('...')"):
// escapa \ y ' para JS, y " para el atributo HTML externo.
function jsAttrStr(s) {
    return String(s || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

function shadeColor(hex, pct) {
    const num = parseInt(hex.replace('#',''), 16);
    const r = Math.min(255, Math.max(0, (num >> 16) + pct * 2.55 | 0));
    const g = Math.min(255, Math.max(0, ((num >> 8) & 0xff) + pct * 2.55 | 0));
    const b = Math.min(255, Math.max(0, (num & 0xff) + pct * 2.55 | 0));
    return '#' + [r,g,b].map(v => v.toString(16).padStart(2,'0')).join('');
}

/* ═══════════════════════════════════════════════════════════
   MODAL DETALLE DE PRODUCTO
═══════════════════════════════════════════════════════════ */
let _prodModalActual = 0;   // id del producto abierto, para seguir el historial

function openProdModal(id, desdeHistorial) {
    const p = productMap[id];
    if (!p) { loadSingleProduct(id, desdeHistorial); return; }   // todavía no está cacheado
    _prodModalActual = id;

    const img         = document.getElementById('prodModalImg');
    const placeholder = document.getElementById('prodModalPlaceholder');
    const imgWrap     = document.getElementById('prodModalImgWrap');

    // Limpiar carousel anterior si existe
    const oldCarousel = imgWrap.querySelector('.pm-carousel');
    if (oldCarousel) oldCarousel.remove();
    img.style.display = 'none';
    placeholder.style.display = 'none';

    // Armar lista de imágenes: principal + adicionales (sin duplicados)
    const allImgs = [];
    if (p.imagen_url) allImgs.push(p.imagen_url);
    (p.imagenes || []).forEach(u => { if (allImgs.indexOf(u) === -1) allImgs.push(u); });

    if (allImgs.length === 0) {
        placeholder.style.display = '';
    } else if (allImgs.length === 1) {
        img.src = allImgs[0];
        img.alt = p.nombre;
        img.style.display = '';
    } else {
        // Carousel
        let cidx = 0;
        const carousel = document.createElement('div');
        carousel.className = 'pm-carousel';

        const carImg = document.createElement('img');
        carImg.src = allImgs[0];
        carImg.alt = p.nombre;
        carousel.appendChild(carImg);

        const btnPrev = document.createElement('button');
        btnPrev.className = 'pm-carousel-btn pm-prev';
        btnPrev.innerHTML = '<i class="bi bi-chevron-left"></i>';
        carousel.appendChild(btnPrev);

        const btnNext = document.createElement('button');
        btnNext.className = 'pm-carousel-btn pm-next';
        btnNext.innerHTML = '<i class="bi bi-chevron-right"></i>';
        carousel.appendChild(btnNext);

        const dotsWrap = document.createElement('div');
        dotsWrap.className = 'pm-carousel-dots';
        allImgs.forEach(function(_, i) {
            const dot = document.createElement('button');
            dot.className = 'pm-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', function() { goTo(i); });
            dotsWrap.appendChild(dot);
        });
        carousel.appendChild(dotsWrap);
        imgWrap.appendChild(carousel);

        function goTo(i) {
            cidx = (i + allImgs.length) % allImgs.length;
            carImg.src = allImgs[cidx];
            dotsWrap.querySelectorAll('.pm-dot').forEach(function(d, j) {
                d.classList.toggle('active', j === cidx);
            });
        }
        btnPrev.addEventListener('click', function() { goTo(cidx - 1); });
        btnNext.addEventListener('click', function() { goTo(cidx + 1); });
    }

    const catEl = document.getElementById('prodModalCat');
    catEl.textContent = p.categoria_nombre || '';
    if (p.marca_nombre && !ocultarFiltrosMarcas) {
        catEl.innerHTML = escHtml(p.categoria_nombre || '') +
            `<span style="margin-left:.5rem;font-size:.7rem;background:var(--brand-light);color:var(--brand);
                          padding:.1rem .5rem;border-radius:999px;font-weight:600">
                <i class="bi bi-bookmark-fill" style="font-size:.65rem"></i> ${escHtml(p.marca_nombre)}
             </span>`;
    }
    document.getElementById('prodModalNombre').textContent = p.nombre;
    {
        const dscStyleModal = badgeDescuentoStyle(p.descuento_color);
        const modalPriceHtml = p.descuento_label
            ? (p.descuento_direccion === 'recargo'
                ? `${formatPrice(p.precio_final)}<span class="badge-recargo"${dscStyleModal}>${escHtml(p.descuento_label)}</span> <small>/ ${escHtml(p.unidad_medida || 'unidad')}</small>`
                : `<span class="price-original">${formatPrice(p.precio_sin_descuento)}</span>${formatPrice(p.precio_final)}<span class="badge-descuento"${dscStyleModal}>${escHtml(p.descuento_label)}</span> <small>/ ${escHtml(p.unidad_medida || 'unidad')}</small>`)
            : `${formatPrice(p.precio_final)} <small>/ ${escHtml(p.unidad_medida || 'unidad')}</small>`;
        document.getElementById('prodModalPrecio').innerHTML = modalPriceHtml;
        renderProdModalMediosPago(p.precio_final);
    }

    const descEl   = document.getElementById('prodModalDesc');
    const descWrap = document.getElementById('prodModalDescWrap');
    if (p.descripcion) {
        descEl.textContent     = p.descripcion;
        descWrap.style.display = '';
    } else {
        descWrap.style.display = 'none';
    }

    // Atributos adicionales del producto
    const atribEl = document.getElementById('prodModalAtributos');
    const atribs  = p.atributos && typeof p.atributos === 'object' ? Object.entries(p.atributos) : [];
    if (atribs.length) {
        atribEl.innerHTML = '<table>' + atribs.map(function([k, v]) {
            return '<tr><td>' + escHtml(k) + '</td><td>' + escHtml(String(v)) + '</td></tr>';
        }).join('') + '</table>';
        atribEl.style.display = '';
    } else {
        atribEl.innerHTML = '';
        atribEl.style.display = 'none';
    }

    const stockEl = document.getElementById('prodModalStockBadge');
    stockEl.innerHTML = p.stock_disponible
        ? '<span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.8rem;background:#dcfce7;color:#15803d;padding:.25rem .65rem;border-radius:999px;font-weight:600"><i class="bi bi-check-circle-fill"></i>En stock</span>'
        : '<span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.8rem;background:#fee2e2;color:#b91c1c;padding:.25rem .65rem;border-radius:999px;font-weight:600"><i class="bi bi-slash-circle-fill"></i>Sin stock</span>';

    const btn           = document.getElementById('prodModalBtnAdd');
    const varWrap       = document.getElementById('prodModalVariantes');
    const varChipsWrap  = document.getElementById('prodModalVariantesChips');

    if (p.variantes && p.variantes.length > 0) {
        // Mostrar selector de variantes
        varWrap.style.display = '';
        let selectedVar = null;

        // Renderizar chips
        function renderVarChips() {
            varChipsWrap.innerHTML = p.variantes
                .filter(v => !ocultarSinStock || v.stock_disponible)
                .map(v => {
                    const sinStock = !v.stock_disponible;
                    const active   = selectedVar && selectedVar.id_variante === v.id_variante;
                    return `<button type="button"
                                style="padding:.25rem .6rem;font-size:.78rem;border-radius:999px;border:1.5px solid ${active ? 'var(--brand)' : '#cbd5e1'};
                                       background:${active ? 'var(--brand)' : '#fff'};color:${active ? '#fff' : '#374151'};
                                       cursor:${sinStock ? 'default' : 'pointer'};opacity:${sinStock ? '.45' : '1'}"
                                ${sinStock ? 'disabled' : ''}
                                onclick="selectVariante(${v.id_variante})">
                                ${escHtml(v.label || '?')}
                            </button>`;
                }).join('');
        }

        window.selectVariante = function(idV) {
            selectedVar = p.variantes.find(v => v.id_variante === idV) || null;
            renderVarChips();
            // Actualizar precio mostrado
            if (selectedVar) {
                document.getElementById('prodModalPrecio').innerHTML =
                    formatPrice(selectedVar.precio_final) + ' <small>/ ' + escHtml(p.unidad_medida || 'unidad') + '</small>';
                renderProdModalMediosPago(selectedVar.precio_final);
            }
            // Activar/desactivar botón
            btn.disabled  = !selectedVar || !selectedVar.stock_disponible;
            btn.innerHTML = (selectedVar && !selectedVar.stock_disponible)
                ? '<i class="bi bi-slash-circle"></i> Sin stock'
                : '<i class="bi bi-cart-plus"></i> Agregar al carrito';
        };

        renderVarChips();
        btn.disabled  = true;  // requiere selección
        btn.innerHTML = '<i class="bi bi-tags"></i> Elegí una opción';
        btn.onclick   = () => {
            if (!selectedVar) return;
            const nombre = p.nombre + ' · ' + (selectedVar.label || '');
            addToCart(p.id, nombre, selectedVar.precio_final, p.imagen_url || '',
                      selectedVar.id_variante, selectedVar.label, selectedVar.stock ?? 0,
                      p.unidad_medida || '');
            closeProdModal();
        };
    } else {
        varWrap.style.display = 'none';
        if (p.stock_disponible) {
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar al carrito';
            btn.onclick   = () => {
                addToCart(p.id, p.nombre, p.precio_final, p.imagen_url || '', null, null, p.stock ?? 0, p.unidad_medida || '');
                closeProdModal();
            };
        } else {
            btn.disabled  = true;
            btn.innerHTML = '<i class="bi bi-slash-circle"></i> Sin stock';
            btn.onclick   = null;
        }
    }

    loadSimilares(p.id_categoria, p.id);

    document.getElementById('prodModalOverlay').classList.add('open');
    document.getElementById('prodModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    ajustarAlturaNavbar();
    // Al saltar de un similar a otro, arrancar el detalle desde arriba
    document.querySelector('.prod-modal-box').scrollTop = 0;

    // Actualizar URL con el id del producto (deep-link compartible).
    // Si viene del botón atrás del navegador la URL ya es la correcta.
    if (!desdeHistorial) {
        const urlP = new URL(location.href);
        urlP.searchParams.set('p', id);
        history.pushState({ prodId: id }, '', urlP.toString());
    }

    // En modo ficha la pantalla de carga espera a que el detalle esté armado
    if (MODO_FICHA) ocultarPageLoader();
}

/* El modal de producto arranca debajo de la navbar; su alto puede variar según
   el ancho, así que se mide y se guarda en --nav-h */
function ajustarAlturaNavbar() {
    const nav = document.querySelector('.navbar-tienda');
    if (nav) document.documentElement.style.setProperty('--nav-h', nav.offsetHeight + 'px');
}
window.addEventListener('resize', ajustarAlturaNavbar);

/* sinGrilla: quien llama ya va a pedir la grilla con sus propios filtros
   (buscador, categoría), así que acá no hay que cargarla de nuevo. */
function closeProdModal(desdeHistorial, sinGrilla) {
    if (!document.getElementById('prodModal').classList.contains('open')) return;
    document.getElementById('prodModalOverlay').classList.remove('open');
    document.getElementById('prodModal').classList.remove('open');
    document.body.style.overflow = '';
    _prodModalActual = 0;

    // En modo ficha atrás de la ficha no había tienda: se carga ahora y la URL
    // deja de ser producto.php para que recargar muestre el listado.
    if (MODO_FICHA) {
        cargarTiendaSiHaceFalta(!sinGrilla);
        if (!desdeHistorial) history.replaceState({}, '', urlTienda());
        return;
    }

    // Quitar el parámetro ?p= de la URL (si viene del atrás del navegador, ya no está)
    if (!desdeHistorial) {
        const urlC = new URL(location.href);
        urlC.searchParams.delete('p');
        history.pushState({}, '', urlC.toString());
    }
}

/* Atrás/adelante del navegador: la vista sigue al ?p= de la URL. Sin esto se
   quedaba el mismo producto abierto aunque el link cambiara. */
window.addEventListener('popstate', () => {
    const pid = parseInt(new URLSearchParams(location.search).get('p') || '0');
    if (pid) {
        if (pid !== _prodModalActual) openProdModal(pid, true);
    } else {
        closeProdModal(true);
    }
});

/* Productos similares: otros de la misma categoría, dentro del modal */
async function loadSimilares(idCat, excludeId) {
    const section = document.getElementById('similaresSection');
    const grid    = document.getElementById('similaresGrid');
    section.style.display = 'none';
    grid.innerHTML = '';
    if (!idCat) return;
    try {
        const res  = await fetch(`${API_URL}?action=productos&codigo=${encodeURIComponent(COMERCIO)}&categoria=${idCat}&limit=25${paramsListaPrecio()}`);
        const data = await res.json();
        if (!data.ok || !data.items || !data.items.length) return;
        const items = data.items.filter(p => p.id !== excludeId).slice(0, 24);
        if (!items.length) return;
        items.forEach(p => { productMap[p.id] = p; });
        grid.innerHTML = items.map(p => {
            const imgHtml = p.imagen_url
                ? `<img src="${escHtml(p.imagen_url)}" alt="${escHtml(p.nombre)}">`
                : `<i class="bi bi-box-seam" style="font-size:2.5rem;color:#cbd5e1"></i>`;
            return `<a href="${urlProducto(p.id)}" class="similares-card">
                <div class="similares-card-img">${imgHtml}</div>
                <div class="similares-card-body">
                    <div class="similares-card-name">${escHtml(p.nombre)}</div>
                    <div class="similares-card-price">${formatPrice(p.precio_final)}</div>
                </div>
            </a>`;
        }).join('');
        section.style.display = '';
        initSimilaresFlechas();
    } catch (e) { /* silencioso: el producto se ve igual sin similares */ }
}

/* Flechas del carrusel de similares. Los botones son fijos en el DOM, así que
   los listeners se registran una sola vez y después solo se resincronizan. */
let _simSincronizar = null;
function initSimilaresFlechas() {
    const row  = document.getElementById('similaresGrid');
    const prev = document.getElementById('similaresPrev');
    const next = document.getElementById('similaresNext');
    if (!row || !prev || !next) return;

    if (!_simSincronizar) {
        // Desliza el ancho visible menos una tarjeta asomada, para no perder contexto
        const pasoScroll = () => {
            const card = row.querySelector('.similares-card');
            const solape = card ? card.offsetWidth * 0.6 : 0;
            return Math.max(row.clientWidth - solape, row.clientWidth * 0.5);
        };
        _simSincronizar = () => {
            const hayOverflow = row.scrollWidth - row.clientWidth > 4;
            prev.disabled = !hayOverflow || row.scrollLeft <= 2;
            next.disabled = !hayOverflow || row.scrollLeft >= row.scrollWidth - row.clientWidth - 2;
        };
        prev.addEventListener('click', () => row.scrollBy({ left: -pasoScroll(), behavior: 'smooth' }));
        next.addEventListener('click', () => row.scrollBy({ left:  pasoScroll(), behavior: 'smooth' }));
        row.addEventListener('scroll', _simSincronizar, { passive: true });
        window.addEventListener('resize', _simSincronizar);
    }

    row.scrollLeft = 0;
    _simSincronizar();
    setTimeout(_simSincronizar, 400);   // las imágenes cambian el ancho al cargar
}

// Carga un producto por ID desde la API y abre su modal
async function loadSingleProduct(id, desdeHistorial) {
    try {
        const res  = await fetch(`${API_URL}?action=productos&codigo=${encodeURIComponent(COMERCIO)}&id_producto=${id}&limit=1${paramsListaPrecio()}`);
        const data = await res.json();
        if (data.ok && data.items && data.items.length > 0) {
            const p = data.items[0];
            productMap[p.id] = p;
            openProdModal(p.id, desdeHistorial);
            return;
        }
    } catch(e) {}
    // El producto no existe o falló la API. En modo ficha no hay nada más en la
    // pantalla: se muestra la tienda para no dejar la pantalla de carga colgada.
    if (MODO_FICHA) {
        ocultarPageLoader();
        cargarTiendaSiHaceFalta();
        history.replaceState({}, '', urlTienda());
        showToast('No encontramos ese producto.', 'error');
    }
}

/* ═══════════════════════════════════════════════════════════
   PEDIDO SIN PAGO
═══════════════════════════════════════════════════════════ */
async function submitPedidoSinPago(payMethod, btnId) {
    const nombre   = document.getElementById('ckNombre').value.trim();
    const email    = document.getElementById('ckEmail').value.trim();
    const telefono = document.getElementById('ckTelefono').value.trim();
    const cart     = getCart();

    if (modoComercio && !selectedIdCliente) { showToast('Seleccioná un cliente.', 'error'); return; }
    if (!modoComercio && (!nombre || !email)) { showToast('Completá nombre y email.', 'error'); return; }
    if (!modoComercio && checkoutTelefono === 'obligatorio' && !telefono) { showToast('El teléfono es obligatorio.', 'error'); return; }
    if (!cart.length) { showToast('Tu carrito está vacío.',  'error'); return; }

    const btn = document.getElementById(btnId || 'btnPedido');
    const originalHtml = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando pedido...';
    showLoading(true);

    // Abrir ventana en blanco ANTES del await: iOS Safari solo permite window.open()
    // en respuesta síncrona a un gesto del usuario. Después del await ya la bloquea.
    let waWindow = null;
    if (tiendaWhatsapp && (payMethod === 'transferencia' || payMethod === 'efectivo')) {
        waWindow = window.open('', '_blank');
    }

    const clientePayload = modoComercio
        ? { id_cliente: selectedIdCliente, nombre, email, telefono }
        : { nombre, email, telefono };
    const payload = {
        comercio:   COMERCIO,
        items:      cart.map(i => ({ id_producto: i.id, id_variante: i.id_variante || null, cantidad: i.cantidad })),
        cliente:    clientePayload,
        envio:      getEnvio(),
        forma_pago: payMethod || 'pedido_web',
    };

    try {
        const res  = await fetch(`${API_URL}?action=pedido`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.ok) {
            localStorage.removeItem(CART_KEY);
            updateCartBadge();
            closeCheckout();
            showLoading(false);
            // Redirigir la ventana pre-abierta a WhatsApp (funciona en iOS y Android)
            if (waWindow) {
                const textos = {
                    transferencia: `Hola! Acabo de realizar el pedido #${data.id_venta} por transferencia. Adjunto comprobante.`,
                    efectivo:      `Hola! Acabo de realizar el pedido #${data.id_venta} por efectivo.`,
                };
                const msg = encodeURIComponent(textos[payMethod]);
                waWindow.location.href = 'https://wa.me/' + tiendaWhatsapp.replace(/[^0-9]/g,'') + '?text=' + msg;
            }
            const baseUrl = window.location.href.split('?')[0];
            window.location.href = `${baseUrl}?comercio=${encodeURIComponent(COMERCIO)}&status=pedido_ok&external_reference=${data.id_venta}`;
        } else {
            if (waWindow) waWindow.close();
            showToast(data.msg || 'Error al registrar el pedido.', 'error');
            btn.disabled  = false;
            btn.innerHTML = originalHtml;
            showLoading(false);
        }
    } catch (err) {
        if (waWindow) waWindow.close();
        showToast('Error de conexión. Intentá nuevamente.', 'error');
        btn.disabled  = false;
        btn.innerHTML = originalHtml;
        showLoading(false);
    }
}

// Cerrar modales con Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeProdModal();
        closeCheckout();
        closeCart();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Payway JS SDK (Decidir) — se inicializa solo cuando el comercio tiene Payway activo -->
<script src="https://ventasonline.payway.com.ar/static/v2.6.4/decidir.js"></script>
</body>
</html>
