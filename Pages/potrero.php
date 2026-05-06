<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroControl | Potreros Y Mangas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
/* ═══════════════════════════════════════════════
   AgroControl — potrero.css (inline)
   ═══════════════════════════════════════════════ */

/* ── TOKENS OSCURO ── */
:root {
  --bg-base:         #080B07;
  --bg-surface:      #0F1410;
  --bg-elevated:     #161C15;
  --bg-card:         #1A2119;
  --bg-hover:        #202820;
  --bg-input:        #131812;
  --border-hairline: rgba(255,255,255,0.045);
  --border-subtle:   rgba(255,255,255,0.07);
  --border-mid:      rgba(255,255,255,0.11);
  --border-strong:   rgba(255,255,255,0.18);
  --text-primary:    #DDE8D9;
  --text-secondary:  #7A9474;
  --text-tertiary:   #485644;
  --text-muted:      #2E3B2C;
  --accent:          #5BAF59;
  --accent-bright:   #74C472;
  --accent-dim:      rgba(91,175,89,0.18);
  --accent-glow:     rgba(91,175,89,0.09);
  --accent-text:     #A0D49E;
  --accent-border:   rgba(91,175,89,0.22);
  --danger:          #C9524F;
  --danger-bright:   #E06866;
  --danger-dim:      rgba(201,82,79,0.10);
  --danger-text:     #EFA0A0;
  --danger-border:   rgba(201,82,79,0.22);
  --amber:           #C4883A;
  --amber-dim:       rgba(196,136,58,0.10);
  --amber-text:      #E8BC7A;
  --amber-border:    rgba(196,136,58,0.22);
  --blue:            #5190C8;
  --blue-dim:        rgba(81,144,200,0.10);
  --blue-text:       #94C0E8;
  --blue-border:     rgba(81,144,200,0.22);
  --r-xs:4px; --r-sm:7px; --r-md:10px; --r-lg:14px; --r-xl:18px;
  --sidebar-w: 218px;
  --font:  'Plus Jakarta Sans', sans-serif;
  --mono:  'DM Mono', monospace;
  --ease:  cubic-bezier(0.4,0,0.2,1);
  --spring:cubic-bezier(0.34,1.56,0.64,1);
}

html.light {
  --bg-base:         #F0F4EE;
  --bg-surface:      #FFFFFF;
  --bg-elevated:     #F5F8F3;
  --bg-card:         #FFFFFF;
  --bg-hover:        #EAF0E7;
  --bg-input:        #F3F7F1;
  --border-hairline: rgba(0,0,0,0.07);
  --border-subtle:   rgba(0,0,0,0.09);
  --border-mid:      rgba(0,0,0,0.13);
  --border-strong:   rgba(0,0,0,0.20);
  --text-primary:    #1A2418;
  --text-secondary:  #3D6038;
  --text-tertiary:   #6A8E65;
  --text-muted:      #A4BCA0;
  --accent:          #3D9C3A;
  --accent-bright:   #52B84F;
  --accent-dim:      rgba(61,156,58,0.12);
  --accent-glow:     rgba(61,156,58,0.07);
  --accent-text:     #1E5C1C;
  --accent-border:   rgba(61,156,58,0.28);
  --danger:          #C9524F;
  --danger-dim:      rgba(201,82,79,0.08);
  --danger-text:     #8B1F1C;
  --danger-border:   rgba(201,82,79,0.22);
  --amber:           #A06B1A;
  --amber-dim:       rgba(160,107,26,0.08);
  --amber-text:      #6B420A;
  --amber-border:    rgba(160,107,26,0.24);
  --blue:            #2E72B8;
  --blue-dim:        rgba(46,114,184,0.08);
  --blue-text:       #1A4E8A;
  --blue-border:     rgba(46,114,184,0.24);
}

*, *::before, *::after {
  box-sizing: border-box; margin: 0; padding: 0;
  transition-property: background-color, border-color, color, box-shadow;
  transition-duration: .22s; transition-timing-function: var(--ease);
}
a, button, .menu a, .theme-toggle, .btn-icon-edit,
.btn-icon-del, .btn-modal-cancel, .btn-modal-submit { transition: all .15s var(--ease) !important; }

html, body { height: 100%; }
body {
  font-family: var(--font);
  background: var(--bg-base);
  color: var(--text-primary);
  font-size: 14px; line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  display: flex;
}

/* ══════════════════════════
   SIDEBAR
   ══════════════════════════ */
aside {
  width: var(--sidebar-w); height: 100vh;
  background: var(--bg-surface);
  border-right: 1px solid var(--border-hairline);
  position: fixed; left: 0; top: 0;
  display: flex; flex-direction: column; overflow: hidden;
}
aside::after {
  content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 1px;
  background: linear-gradient(to bottom, transparent 0%, var(--border-subtle) 20%, var(--border-subtle) 80%, transparent 100%);
  pointer-events: none;
}
.sidebar { display: flex; flex-direction: column; height: 100%; }
.logo { padding: 18px 16px 16px; border-bottom: 1px solid var(--border-hairline); display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.logo-icon { width: 28px; height: 28px; background: var(--accent); border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 0 1px rgba(91,175,89,0.3), inset 0 1px 0 rgba(255,255,255,0.15); }
.logo-icon svg { width: 14px; height: 14px; }
.logo-name { font-size: 12.5px; font-weight: 700; color: var(--text-primary); display: block; }
.logo-name span { color: var(--accent-text); }
.logo-sub { font-size: 9.5px; color: var(--text-tertiary); letter-spacing: 0.6px; text-transform: uppercase; }
.nav-section { padding: 12px 8px 4px; }
.menu-label { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.3px; color: var(--text-muted); padding: 0 8px 7px; font-weight: 600; }
.menu { display: flex; flex-direction: column; }
.menu a { display: flex; align-items: center; gap: 8px; padding: 7px 9px; border-radius: var(--r-sm); font-size: 12.5px; font-weight: 400; color: var(--text-tertiary); text-decoration: none; margin-bottom: 1px; position: relative; }
.menu a:hover { background: var(--bg-hover); color: var(--text-secondary); }
.menu a.active { background: var(--accent-dim); color: var(--accent-text); font-weight: 500; }
.menu a.active::before { content: ''; position: absolute; left: 0; top: 22%; bottom: 22%; width: 2px; background: var(--accent); border-radius: 0 2px 2px 0; }
.menu a.logout-link:hover { color: var(--danger-text); background: var(--danger-dim); }
.nav-icon { width: 14px; height: 14px; flex-shrink: 0; opacity: .6; }
.menu a.active .nav-icon { opacity: 1; }
.nav-divider { height: 1px; background: var(--border-hairline); margin: 6px 8px; }

/* ══════════════════════════
   MAIN LAYOUT
   ══════════════════════════ */
.main {
  margin-left: var(--sidebar-w);
  width: calc(100% - var(--sidebar-w));
  min-height: 100vh;
  display: flex; flex-direction: column;
  background: var(--bg-base);
}

/* TOPBAR */
.topbar {
  background: var(--bg-surface);
  border-bottom: 1px solid var(--border-hairline);
  padding: 11px 26px;
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0; position: sticky; top: 0; z-index: 10;
}
.topbar-left { display: flex; flex-direction: column; gap: 1px; }
.topbar-breadcrumb { font-size: 10.5px; color: var(--text-muted); }
.topbar-breadcrumb span { color: var(--text-tertiary); }
.topbar-title { font-size: 14px; font-weight: 600; color: var(--text-primary); letter-spacing: -.2px; }
.topbar-right { display: flex; align-items: center; gap: 8px; }

.theme-toggle {
  width: 32px; height: 32px; border-radius: var(--r-sm);
  background: var(--bg-elevated); border: 1px solid var(--border-subtle);
  color: var(--text-tertiary); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  position: relative; flex-shrink: 0;
}
.theme-toggle:hover { background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-mid); }
.theme-toggle:active { transform: scale(.95); }
.icon-moon, .icon-sun { width: 14px; height: 14px; position: absolute; transition: opacity .2s var(--ease), transform .25s var(--spring) !important; }
html:not(.light) .icon-moon { opacity: 1; transform: scale(1) rotate(0deg); }
html:not(.light) .icon-sun  { opacity: 0; transform: scale(.5) rotate(30deg); }
html.light .icon-moon { opacity: 0; transform: scale(.5) rotate(-30deg); }
html.light .icon-sun  { opacity: 1; transform: scale(1) rotate(0deg); }

.btn-primary {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 14px; border-radius: var(--r-md);
  background: var(--accent); border: 1px solid rgba(91,175,89,.45);
  box-shadow: 0 1px 2px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.12);
  color: #061006; font-family: var(--font); font-size: 12.5px; font-weight: 700;
  cursor: pointer; white-space: nowrap; line-height: 1;
}
.btn-primary svg { width: 12px; height: 12px; }
.btn-primary:hover { background: var(--accent-bright); }
.btn-primary:active { transform: scale(.97); }

/* ══════════════════════════
   STATS GRID
   ══════════════════════════ */
.stats-grid {
  display: grid; grid-template-columns: repeat(4,1fr);
  gap: 12px; padding: 22px 26px 0;
}
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border-hairline);
  border-radius: var(--r-lg); padding: 15px 17px;
  display: flex; flex-direction: column; gap: 10px;
  position: relative; overflow: hidden;
}
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(to right, transparent, var(--border-subtle), transparent); }
.stat-card:hover { border-color: var(--border-subtle); }
.stat-top { display: flex; align-items: center; justify-content: space-between; }
.stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: .8px; color: var(--text-tertiary); font-weight: 600; }
.stat-icon { width: 22px; height: 22px; border-radius: var(--r-xs); display: flex; align-items: center; justify-content: center; }
.si-green { background: var(--accent-dim); border: 1px solid var(--accent-border); }
.si-blue  { background: var(--blue-dim);   border: 1px solid var(--blue-border); }
.si-amber { background: var(--amber-dim);  border: 1px solid var(--amber-border); }
.si-danger{ background: var(--danger-dim); border: 1px solid var(--danger-border); }
.stat-icon svg { width: 11px; height: 11px; }
.si-green svg { color: var(--accent-text); }
.si-blue  svg { color: var(--blue-text); }
.si-amber svg { color: var(--amber-text); }
.si-danger svg { color: var(--danger-text); }
.stat-value { font-size: 28px; font-weight: 700; color: var(--text-primary); line-height: 1; letter-spacing: -1px; }
.stat-meta { font-size: 10.5px; color: var(--text-muted); }

/* ══════════════════════════
   TABLE CONTAINER
   ══════════════════════════ */
.contenedorTabla {
  margin: 18px 26px 26px;
  background: var(--bg-card); border: 1px solid var(--border-hairline);
  border-radius: var(--r-lg);
  overflow: hidden;
  max-height: 420px;
  overflow-y: auto;
  overflow-x: hidden;
}
.contenedorTabla::-webkit-scrollbar { width: 3px; }
.contenedorTabla::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 4px; }

.table-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; border-bottom: 1px solid var(--border-hairline);
  position: sticky; top: 0; z-index: 3;
  background: var(--bg-card);
}
.tt-left { display: flex; align-items: center; gap: 10px; }
.table-heading { font-size: 12.5px; font-weight: 600; color: var(--text-primary); }
.count-pill { font-size: 10.5px; font-weight: 500; background: var(--bg-elevated); color: var(--text-tertiary); border: 1px solid var(--border-hairline); padding: 2px 8px; border-radius: 20px; }

table { width: 100%; border-collapse: collapse; background: transparent; }
thead th { padding: 9px 16px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); font-weight: 600; background: var(--bg-elevated); border-bottom: 1px solid var(--border-hairline); position: sticky; top: 0; z-index: 2; }
thead th:last-child { text-align: right; }
tbody tr { border-bottom: 1px solid var(--border-hairline); }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(255,255,255,.015); }
html.light tbody tr:hover { background: rgba(0,0,0,.015); }
tbody td { padding: 11px 16px; vertical-align: middle; }
tbody td:last-child { text-align: right; }

.potrero-cell { display: flex; align-items: center; gap: 9px; }
.potrero-avatar { width: 28px; height: 28px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 10.5px; font-weight: 700; font-family: var(--mono); background: var(--accent-glow); border: 1px solid var(--accent-border); color: var(--accent-text); flex-shrink: 0; }
.potrero-name { font-size: 12.5px; font-weight: 500; color: var(--text-primary); }

.mono-val { font-family: var(--mono); font-size: 12.5px; font-weight: 600; color: var(--text-primary); }
.unit { font-size: 10.5px; font-weight: 400; color: var(--text-tertiary); margin-left: 2px; }
.capacidad-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.capacidad-main,
.capacidad-sub {
  display: inline-flex;
  align-items: baseline;
  gap: 3px;
}
.capacidad-sub .mono-val {
  font-size: 11px;
  color: var(--blue-text);
}
.capacidad-sub .unit {
  color: var(--text-muted);
}

.pasto-tag { font-size: 11.5px; color: var(--text-secondary); }
.fecha-tag { font-family: var(--mono); font-size: 11.5px; color: var(--text-tertiary); }

.badge-mangas { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; background: var(--blue-dim); color: var(--blue-text); border: 1px solid var(--blue-border); }
.badge-sin    { font-size: 10.5px; color: var(--text-muted); }

.actions-cell { display: flex; gap: 5px; justify-content: flex-end; }
.btn-icon-edit, .btn-icon-del {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 10px; border-radius: var(--r-xs);
  font-family: var(--font); font-size: 11.5px; font-weight: 500;
  text-decoration: none; cursor: pointer; border: 1px solid transparent; background: none;
}
.btn-icon-edit svg, .btn-icon-del svg { width: 11px; height: 11px; }
.btn-icon-edit { background: var(--accent-glow); border-color: var(--accent-border); color: var(--accent-text); }
.btn-icon-edit:hover { background: var(--accent-dim); border-color: rgba(91,175,89,.4); }
.btn-icon-edit:active { transform: scale(.96); }
.btn-icon-del { background: var(--danger-dim); border-color: var(--danger-border); color: var(--danger-text); }
.btn-icon-del:hover { background: rgba(201,82,79,.16); border-color: rgba(224,92,92,.38); }
.btn-icon-del:active { transform: scale(.96); }

/* ══════════════════════════
   MODAL BASE
   ══════════════════════════ */
.modalOverlay { display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.72); justify-content: center; align-items: center; padding: 20px; overflow-y: auto; }
.modalOverlay.activo { display: flex; }
.contenedorModal1 {
  background: var(--bg-surface); border: 1px solid var(--border-mid);
  border-radius: var(--r-xl); width: 100%; max-width: 480px; overflow: hidden;
  position: relative; animation: entrarModal .25s var(--spring); margin: auto;
}
.contenedorModal1::before { content: ''; position: absolute; top: 0; left: 10%; right: 10%; height: 1px; background: linear-gradient(to right, transparent, var(--border-strong), transparent); pointer-events: none; }
@keyframes entrarModal { from { transform: translateY(14px) scale(.97); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

.modal-header { padding: 22px 24px 0; display: flex; align-items: flex-start; justify-content: space-between; }
.modal-header-info { display: flex; flex-direction: column; gap: 3px; }
.modal-eyebrow { display: inline-flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--accent-text); margin-bottom: 2px; }
.eyebrow-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--accent); }
.modal-eyebrow-edit { color: var(--amber-text); }
.eyebrow-dot-edit { background: var(--amber); }
.modal-header h2 { font-size: 15.5px; font-weight: 700; color: var(--text-primary); letter-spacing: -.3px; }
.modal-subtitle { font-size: 11.5px; color: var(--text-tertiary); margin-top: 1px; }
.btn-cerrar { width: 26px; height: 26px; border-radius: var(--r-sm); background: var(--bg-elevated); border: 1px solid var(--border-subtle); color: var(--text-tertiary); cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
.btn-cerrar:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-mid); }
.btn-cerrar svg { width: 11px; height: 11px; }
.modal-divider { height: 1px; background: var(--border-hairline); margin: 18px 0 0; }

/* MODAL BODY */
.modal-body { padding: 18px 24px; display: flex; flex-direction: column; gap: 14px; max-height: 60vh; overflow-y: auto; }
.modal-body::-webkit-scrollbar { width: 3px; }
.modal-body::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 4px; }
.field-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 11px; font-weight: 600; color: var(--text-secondary); display: flex; align-items: center; gap: 3px; }
.field-req { color: var(--accent); font-size: 13px; line-height: 1; }

.contenedorModal1 input[type="text"],
.contenedorModal1 input[type="number"],
.contenedorModal1 input[type="date"],
.contenedorModal1 select {
  background: var(--bg-input); border: 1px solid var(--border-subtle);
  border-radius: var(--r-sm); padding: 8px 11px;
  font-family: var(--font); font-size: 12.5px; color: var(--text-primary);
  outline: none; width: 100%; caret-color: var(--accent);
  -webkit-appearance: none; font-weight: 400;
}
.contenedorModal1 input::placeholder { color: var(--text-muted); }
.contenedorModal1 input:hover, .contenedorModal1 select:hover { border-color: var(--border-mid); }
.contenedorModal1 input:focus, .contenedorModal1 select:focus { border-color: var(--accent-border); box-shadow: 0 0 0 3px var(--accent-glow); }
.contenedorModal1 select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23485644' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; cursor: pointer;
}
.contenedorModal1 select option { background: var(--bg-elevated); color: var(--text-primary); }

/* Checkbox */
.checkbox-wrap { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.checkbox-wrap input[type="checkbox"] { display: none; }
.checkbox-box { width: 16px; height: 16px; border-radius: var(--r-xs); border: 1.5px solid var(--border-mid); background: var(--bg-input); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .14s var(--ease); }
.checkbox-wrap:has(input:checked) .checkbox-box { background: var(--accent); border-color: var(--accent); }
.checkbox-wrap:has(input:checked) .checkbox-box::after {
  content: ''; width: 8px; height: 8px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23061006' stroke-width='3' stroke-linecap='round'%3E%3Cpolyline points='20,6 9,17 4,12'/%3E%3C/svg%3E");
  background-size: contain; background-repeat: no-repeat;
}
.checkbox-label { font-size: 12.5px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; }

/* Panel mangas */
.mangas-panel { background: var(--bg-elevated); border: 1px solid var(--border-subtle); border-radius: var(--r-md); padding: 14px; display: flex; flex-direction: column; gap: 12px; }
.mangas-panel-head { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 600; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: .6px; }
.mangas-panel-head svg { width: 13px; height: 13px; color: var(--blue-text); }
.field-hint-block { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text-muted); }
.field-hint-block svg { width: 12px; height: 12px; flex-shrink: 0; }
.cap-manga-box { background: var(--blue-dim); border: 1px solid var(--blue-border); border-radius: var(--r-sm); padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; }
.cap-manga-label { font-size: 10.5px; color: var(--blue-text); }
.cap-manga-val { font-size: 18px; font-weight: 700; color: var(--blue-text); font-family: var(--mono); }
.cap-manga-sub { font-size: 10px; color: var(--blue-text); opacity: .7; }

/* Capacidad total */
.cap-total-box { background: var(--accent-glow); border: 1px solid var(--accent-border); border-radius: var(--r-md); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; }
.cap-total-label { font-size: 10.5px; color: var(--accent-text); margin-bottom: 4px; }
.cap-total-val { font-size: 22px; font-weight: 700; color: var(--accent-text); font-family: var(--mono); letter-spacing: -.5px; }
.cap-total-icon { width: 32px; height: 32px; background: var(--accent-dim); border: 1px solid var(--accent-border); border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; }
.cap-total-icon svg { width: 15px; height: 15px; color: var(--accent-text); }

/* MODAL FOOTER */
.modal-footer { padding: 13px 24px 20px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-hairline); }
.modal-hint { font-size: 10.5px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.modal-hint svg { width: 11px; height: 11px; }
.modal-footer-btns { display: flex; gap: 7px; }
.btn-modal-cancel { padding: 8px 16px; border-radius: var(--r-md); background: var(--bg-elevated); border: 1px solid var(--border-subtle); color: var(--text-tertiary); font-family: var(--font); font-size: 12.5px; font-weight: 500; cursor: pointer; }
.btn-modal-cancel:hover { background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-mid); }
.btn-modal-submit { padding: 8px 18px; border-radius: var(--r-md); background: var(--accent); border: 1px solid rgba(91,175,89,.5); box-shadow: 0 1px 2px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.10); color: #061006; font-family: var(--font); font-size: 12.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-modal-submit svg { width: 12px; height: 12px; }
.btn-modal-submit:hover { background: var(--accent-bright); }
.btn-modal-submit:active { transform: scale(.97); }
.btn-modal-edit { background: var(--amber) !important; border-color: rgba(196,136,58,.5) !important; color: #1a0a00 !important; }
.btn-modal-edit:hover { background: #D4A050 !important; }

.btn-secondary {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 13px; border-radius: var(--r-md);
  background: var(--bg-elevated); border: 1px solid var(--border-subtle);
  color: var(--text-tertiary); font-family: var(--font); font-size: 12.5px; font-weight: 500;
  cursor: pointer; white-space: nowrap; line-height: 1;
}
.btn-secondary svg { width: 12px; height: 12px; }
.btn-secondary:hover { background: var(--bg-hover); color: var(--text-secondary); border-color: var(--border-mid); }
.btn-secondary:active { transform: scale(.97); }
.btn-blue-s { background: var(--blue-dim); border-color: var(--blue-border); color: var(--blue-text); }
.btn-blue-s:hover { background: rgba(81,144,200,0.18); color: var(--blue-text); border-color: rgba(81,144,200,.38); }

/* ══════════════════════════
   OCUPACIÓN DE POTREROS
   ══════════════════════════ */
.seccion-ocupacion { margin: 0 26px 0; display: flex; flex-direction: column; gap: 0; }
.seccion-titulo { display: flex; align-items: center; justify-content: space-between; padding: 18px 0 10px; }
.seccion-titulo-left { display: flex; align-items: center; gap: 10px; }
.seccion-titulo h3 { font-size: 12.5px; font-weight: 600; color: var(--text-primary); }

.ocupacion-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; padding-bottom: 4px; }
.ocupacion-card { background: var(--bg-card); border: 1px solid var(--border-hairline); border-radius: var(--r-lg); overflow: hidden; animation: fadeUp .28s var(--ease) both; }
.ocupacion-card:hover { border-color: var(--border-subtle); }
.oc-header { padding: 12px 14px 10px; border-bottom: 1px solid var(--border-hairline); display: flex; align-items: center; justify-content: space-between; }
.oc-nombre { font-size: 12.5px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 7px; }
.oc-avatar { width: 22px; height: 22px; border-radius: var(--r-xs); background: var(--accent-glow); border: 1px solid var(--accent-border); color: var(--accent-text); font-size: 9px; font-weight: 700; font-family: var(--mono); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.oc-cap-badge { font-size: 10px; font-weight: 600; font-family: var(--mono); padding: 2px 7px; border-radius: 20px; }
.oc-cap-ok   { background: var(--accent-glow); border: 1px solid var(--accent-border); color: var(--accent-text); }
.oc-cap-warn { background: var(--amber-dim);   border: 1px solid var(--amber-border);  color: var(--amber-text); }
.oc-cap-full { background: var(--danger-dim);  border: 1px solid var(--danger-border); color: var(--danger-text); }

.oc-barra-wrap { padding: 10px 14px 6px; }
.oc-barra-labels { display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted); margin-bottom: 5px; }
.oc-barra-bg { height: 4px; background: var(--bg-elevated); border-radius: 4px; overflow: hidden; }
.oc-barra-fill { height: 100%; border-radius: 4px; transition: width .5s var(--ease); }
.fill-ok   { background: var(--accent); }
.fill-warn { background: var(--amber); }
.fill-full { background: var(--danger); }

.oc-vacas { padding: 6px 14px 12px; display: flex; flex-direction: column; gap: 4px; }
.oc-vaca-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 6px 8px; border-radius: var(--r-sm);
  background: var(--bg-elevated); border: 1px solid var(--border-hairline);
}
.oc-vaca-info { display: flex; align-items: center; gap: 7px; }
.oc-vaca-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }
.oc-vaca-nombre { font-size: 12px; font-weight: 500; color: var(--text-primary); }
.oc-vaca-manga { font-size: 10.5px; color: var(--blue-text); background: var(--blue-dim); border: 1px solid var(--blue-border); padding: 1px 6px; border-radius: 10px; }
.oc-vaca-actions { display: flex; gap: 4px; }

.btn-mover {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 8px; border-radius: var(--r-xs);
  background: var(--amber-dim); border: 1px solid var(--amber-border);
  color: var(--amber-text); font-size: 10.5px; font-weight: 500;
  font-family: var(--font); cursor: pointer;
}
.btn-mover svg { width: 9px; height: 9px; }
.btn-mover:hover { background: rgba(196,136,58,.18); }

/* ── BOTÓN VER (hoja de vida) ── */
.btn-ver {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 8px; border-radius: var(--r-xs);
  background: var(--accent-glow); border: 1px solid var(--accent-border);
  color: var(--accent-text); font-size: 10.5px; font-weight: 500;
  font-family: var(--font); cursor: pointer;
}
.btn-ver svg { width: 9px; height: 9px; }
.btn-ver:hover { background: var(--accent-dim); }

.oc-empty { padding: 14px; font-size: 11.5px; color: var(--text-muted); text-align: center; }

/* ══════════════════════════
   HISTORIAL TABLE
   ══════════════════════════ */
.hist-table-wrap { max-height: 55vh; overflow-y: auto; }
.hist-table-wrap::-webkit-scrollbar { width: 3px; }
.hist-table-wrap::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 4px; }
.hist-empty { padding: 32px; text-align: center; font-size: 12px; color: var(--text-muted); }
.badge-motivo { font-size: 10.5px; padding: 2px 8px; border-radius: 20px; background: var(--bg-elevated); border: 1px solid var(--border-hairline); color: var(--text-tertiary); }
.dias-val { font-family: var(--mono); font-size: 11.5px; color: var(--text-secondary); }
.estado-activo { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600; color: var(--accent-text); }
.estado-activo::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: var(--accent); display: inline-block; }
.estado-salida { font-size: 10.5px; color: var(--text-muted); font-family: var(--mono); }
.modal-wide { max-width: 700px !important; }

/* ══════════════════════════
   MODAL RESUMEN
   ══════════════════════════ */
.resumen-check { width: 48px; height: 48px; border-radius: 50%; background: var(--accent-dim); border: 2px solid var(--accent-border); display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; }
.resumen-check svg { width: 22px; height: 22px; color: var(--accent); }
.resumen-titulo { text-align: center; font-size: 15.5px; font-weight: 700; color: var(--text-primary); letter-spacing: -.2px; }
.resumen-sub { text-align: center; font-size: 11.5px; color: var(--text-tertiary); margin-bottom: 6px; }
.resumen-card { background: var(--bg-elevated); border: 1px solid var(--border-subtle); border-radius: var(--r-md); padding: 14px 16px; display: flex; flex-direction: column; gap: 9px; }
.resumen-row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; padding: 2px 0; }
.resumen-row + .resumen-row { border-top: 1px solid var(--border-hairline); padding-top: 9px; }
.resumen-key { color: var(--text-tertiary); font-weight: 500; display: flex; align-items: center; gap: 6px; }
.resumen-key svg { width: 11px; height: 11px; flex-shrink: 0; }
.resumen-val { color: var(--text-primary); font-weight: 600; }

.toast { position: fixed; top: 20px; right: 20px; z-index: 2000; display: flex; align-items: center; gap: 8px; background: var(--bg-card); border: 1px solid var(--accent-border); color: var(--accent-text); padding: 11px 16px; border-radius: var(--r-md); font-size: 13px; font-weight: 500; box-shadow: 0 4px 16px rgba(0,0,0,0.3); opacity: 0; transform: translateY(-10px); pointer-events: none; transition: opacity .22s var(--ease), transform .22s var(--ease) !important; }
.toast svg { width: 14px; height: 14px; color: var(--accent); flex-shrink: 0; }
.toast.show { opacity: 1; transform: translateY(0); }

/* ══════════════════════════
   HOJA DE VIDA — TABS
   ══════════════════════════ */
.hv-tabs { display: flex; gap: 4px; padding: 0 24px; border-bottom: 1px solid var(--border-hairline); }
.hv-tab {
  padding: 9px 14px; font-size: 12px; font-weight: 500;
  color: var(--text-tertiary); cursor: pointer; border-bottom: 2px solid transparent;
  margin-bottom: -1px; background: none; border-top: none; border-left: none; border-right: none;
  font-family: var(--font);
}
.hv-tab.activo { color: var(--accent-text); border-bottom-color: var(--accent); }
.hv-tab:hover:not(.activo) { color: var(--text-secondary); }
.hv-tab-panel { display: none; flex-direction: column; gap: 10px; }
.hv-tab-panel.activo { display: flex; }

/* Perfil vaca */
.hv-perfil { display: flex; align-items: flex-start; gap: 14px; padding: 4px 0 6px; }
.hv-avatar { width: 52px; height: 52px; border-radius: var(--r-lg); background: var(--accent-glow); border: 1px solid var(--accent-border); color: var(--accent-text); font-size: 15px; font-weight: 700; font-family: var(--mono); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.hv-nombre { font-size: 15px; font-weight: 700; color: var(--text-primary); letter-spacing: -.2px; }
.hv-codigo { font-size: 11px; color: var(--text-tertiary); font-family: var(--mono); }
.hv-estado-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; margin-top: 4px; }
.hp-prod { background: var(--accent-glow); border: 1px solid var(--accent-border); color: var(--accent-text); }
.hp-seco { background: var(--blue-dim);   border: 1px solid var(--blue-border);   color: var(--blue-text); }
.hp-enra { background: var(--amber-dim);  border: 1px solid var(--amber-border);  color: var(--amber-text); }
.hp-dot  { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

/* Stats hoja */
.hv-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.hv-stat { background: var(--bg-elevated); border: 1px solid var(--border-hairline); border-radius: var(--r-md); padding: 10px 12px; text-align: center; }
.hv-stat-val { font-size: 20px; font-weight: 700; color: var(--text-primary); font-family: var(--mono); letter-spacing: -.5px; }
.hv-stat-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); margin-top: 2px; }

/* Datos generales */
.hv-datos { background: var(--bg-elevated); border: 1px solid var(--border-hairline); border-radius: var(--r-md); overflow: hidden; }
.hv-dato-row { display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; border-bottom: 1px solid var(--border-hairline); font-size: 12px; }
.hv-dato-row:last-child { border-bottom: none; }
.hv-dato-lbl { color: var(--text-tertiary); display: flex; align-items: center; gap: 7px; }
.hv-dato-lbl svg { width: 11px; height: 11px; flex-shrink: 0; }
.hv-dato-val { color: var(--text-primary); font-weight: 500; text-align: right; }

/* Trayectoria timeline */
.hv-timeline { display: flex; flex-direction: column; }
.hv-tl-item { display: flex; gap: 12px; padding-bottom: 14px; }
.hv-tl-item:last-child { padding-bottom: 0; }
.hv-tl-left { display: flex; flex-direction: column; align-items: center; }
.hv-tl-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); border: 2px solid var(--bg-elevated); flex-shrink: 0; margin-top: 3px; }
.hv-tl-dot.activo { background: var(--accent-bright); box-shadow: 0 0 0 3px var(--accent-glow); }
.hv-tl-line { width: 1px; flex: 1; background: var(--border-hairline); margin-top: 4px; }
.hv-tl-item:last-child .hv-tl-line { display: none; }
.hv-tl-content { flex: 1; padding-bottom: 2px; }
.hv-tl-potrero { font-size: 12.5px; font-weight: 500; color: var(--text-primary); }
.hv-tl-meta { font-size: 11px; color: var(--text-tertiary); margin-top: 2px; }
.hv-tl-badge { font-size: 10px; padding: 1px 7px; border-radius: 10px; margin-left: 5px; }
.hv-tl-activo { background: var(--accent-glow); color: var(--accent-text); border: 1px solid var(--accent-border); }
.hv-tl-dias { font-family: var(--mono); font-size: 10.5px; color: var(--text-muted); margin-top: 3px; }

/* Vacunas */
.hv-vacuna-row { background: var(--bg-elevated); border: 1px solid var(--border-hairline); border-radius: var(--r-sm); padding: 9px 12px; display: flex; align-items: center; justify-content: space-between; }
.hv-vac-nombre { font-size: 12px; font-weight: 500; color: var(--text-primary); }
.hv-vac-fecha  { font-size: 10.5px; color: var(--text-tertiary); font-family: var(--mono); margin-top: 2px; }
.badge-vac-ok   { background: var(--accent-glow); color: var(--accent-text); border: 1px solid var(--accent-border); font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.badge-vac-pend { background: var(--amber-dim);   color: var(--amber-text);   border: 1px solid var(--amber-border); font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.badge-vac-venc { background: var(--danger-dim);  color: var(--danger-text);  border: 1px solid var(--danger-border); font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.hv-empty { font-size: 12px; color: var(--text-muted); text-align: center; padding: 18px 0; }

/* Loading */
.hv-loading { display: flex; align-items: center; justify-content: center; padding: 40px; gap: 10px; color: var(--text-tertiary); font-size: 12.5px; }
.spinner { width: 18px; height: 18px; border: 2px solid var(--border-mid); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ══════════════════════════
   ANIMACIONES
   ══════════════════════════ */
@keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.stat-card { animation: fadeUp .28s var(--ease) both; }
.stat-card:nth-child(1) { animation-delay: .04s; }
.stat-card:nth-child(2) { animation-delay: .08s; }
.stat-card:nth-child(3) { animation-delay: .12s; }
.stat-card:nth-child(4) { animation-delay: .16s; }
.contenedorTabla { animation: fadeUp .28s .18s var(--ease) both; }

/* ══════════════════════════
   RESPONSIVE
   ══════════════════════════ */
@media (max-width: 960px) {
  aside { position: relative; width: 100%; height: auto; }
  .main { margin-left: 0; width: 100%; }
  .logo { padding: 14px 16px; }
  .nav-section { padding: 10px 12px 6px; }
  .menu { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
  .menu a { margin-bottom: 0; }
  .topbar { flex-wrap: wrap; gap: 10px; padding: 12px 16px; }
  .topbar-left, .topbar-right { width: 100%; }
  .topbar-right { justify-content: flex-start; flex-wrap: wrap; }
  .stats-grid { padding: 16px 16px 0; grid-template-columns: repeat(2,1fr); }
  .contenedorTabla { margin: 14px 16px; }
  .contenedorTabla { overflow-x: auto; }
  .contenedorTabla table { min-width: 960px; }
}
@media (max-width: 600px) {
  .menu { grid-template-columns: 1fr; }
  .stats-grid { grid-template-columns: 1fr; }
  .field-row-2 { grid-template-columns: 1fr; }
  .modal-footer { flex-direction: column; align-items: stretch; gap: 10px; }
  .modal-footer-btns { width: 100%; }
  .btn-modal-cancel, .btn-modal-submit { width: 100%; justify-content: center; }
}
    </style>
</head>

<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside>
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#061006" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2C6 2 3 7 3 12s3 10 9 10 9-5 9-10S18 2 12 2"/>
                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                    <circle cx="9" cy="9" r=".8" fill="#061006"/>
                    <circle cx="15" cy="9" r=".8" fill="#061006"/>
                </svg>
            </div>
            <div class="logo-text">
                <div class="logo-name">AGRO<span>CONTROL</span></div>
                <div class="logo-sub">Gestión Ganadera</div>
            </div>
        </div>

        <div class="nav-section">
            <div class="menu-label">Principal</div>
            <div class="menu">
                <a href="Dashboard.php">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                    Dashboard
                </a>
                <a href="Registro_Vacas.php">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <ellipse cx="12" cy="8" rx="7" ry="5"/>
                        <path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/>
                    </svg>
                    Gestión de Vacas
                </a>
                <a href="produccion_lechera.php">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                    Producción Lechera
                </a>
                <a href="potrero.php" class="active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                    </svg>
                    Potreros y Mangas
                </a>
                <a href="vacunaciones.html">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M19 3l2 2-7 7"/><path d="M17 5l2 2"/><path d="M3 21l9-9"/><path d="M14.5 5.5l-11 11 4 4 11-11z"/></svg>
                    Vacunaciones
                </a>
            </div>
        </div>

        <div class="nav-divider"></div>

        <div class="nav-section">
            <div class="menu-label">Sistema</div>
            <div class="menu">
                <a href="logout.php" class="logout-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- ═══════════════ MAIN ═══════════════ -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-breadcrumb">Principal / <span>Potreros</span></div>
            <div class="topbar-title">Potreros y Mangas</div>
        </div>
        <div class="topbar-right">
            <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>
            <button class="btn-secondary" onclick="abrirModal('modalHistorial')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
                </svg>
                Historial
            </button>
            <button class="btn-secondary btn-blue-s" onclick="abrirModal('modalAsignar')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Asignar Vaca
            </button>
            <button class="btn-primary" onclick="abrirModal('modalCrear')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nueva Finca
            </button>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <?php
            include("../Config/conexion.php");
            $con = conexion();
            $uid = (int)$_SESSION['id'];
            $totalPotreros  = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM potreros WHERE usuario_id=$uid"))[0];
            $totalHa        = mysqli_fetch_row(mysqli_query($con, "SELECT SUM(hectareas) FROM potreros WHERE usuario_id=$uid"))[0] ?? 0;
            $totalMangas    = mysqli_fetch_row(mysqli_query($con, "SELECT SUM(num_mangas) FROM potreros WHERE tiene_mangas=1 AND usuario_id=$uid"))[0] ?? 0;
            $totalCapacidad = mysqli_fetch_row(mysqli_query($con, "SELECT SUM(capacidad_max) FROM potreros WHERE usuario_id=$uid"))[0] ?? 0;
        ?>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total Fincas</div>
                <div class="stat-icon si-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalPotreros ?></div>
            <div class="stat-meta">Fincas registradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Hectáreas Totales</div>
                <div class="stat-icon si-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <polygon points="1,6 1,22 8,18 16,22 23,18 23,2 16,6 8,2"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= number_format($totalHa, 1) ?></div>
            <div class="stat-meta">hectáreas en total</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total Mangas</div>
                <div class="stat-icon si-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalMangas ?></div>
            <div class="stat-meta">mangas registradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Capacidad Total</div>
                <div class="stat-icon si-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <ellipse cx="12" cy="8" rx="7" ry="5"/>
                        <path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $totalCapacidad ?></div>
            <div class="stat-meta">vacas en total</div>
        </div>
    </div>

    <!-- OCUPACIÓN DE POTREROS -->
    <?php
        $sqlPotreros = "SELECT p.*, COUNT(a.id) as vacas_asignadas
            FROM potreros p
            LEFT JOIN asignaciones a ON a.potrero_id = p.id AND a.fecha_salida IS NULL
            WHERE p.usuario_id = $uid
            GROUP BY p.id
            ORDER BY p.nombre ASC";
        $resPotreros = mysqli_query($con, $sqlPotreros);
        $potrerosList = [];
        while ($rp = mysqli_fetch_assoc($resPotreros)) $potrerosList[] = $rp;
    ?>
    <div class="seccion-ocupacion">
        <div class="seccion-titulo">
            <div class="seccion-titulo-left">
                <div class="table-heading">Ocupación actual</div>
                <div class="count-pill"><?= count($potrerosList) ?> potreros</div>
            </div>
        </div>
        <div class="ocupacion-grid">
        <?php foreach ($potrerosList as $pt):
            $asignadas  = (int)$pt['vacas_asignadas'];
            $capacidad  = (int)$pt['capacidad_max'];
            $pct        = $capacidad > 0 ? min(100, round($asignadas / $capacidad * 100)) : 0;
            $fillClass  = $pct >= 100 ? 'fill-full' : ($pct >= 75 ? 'fill-warn' : 'fill-ok');
            $badgeClass = $pct >= 100 ? 'oc-cap-full' : ($pct >= 75 ? 'oc-cap-warn' : 'oc-cap-ok');
            $initials   = strtoupper(substr($pt['nombre'], 0, 2));

            $sqlVacas = "SELECT v.id, v.nombre, v.codigo, v.raza, v.estado, a.id as asig_id, a.manga_num
                         FROM asignaciones a
                         JOIN vacas v ON v.id = a.vaca_id
                         WHERE a.potrero_id = {$pt['id']} AND a.fecha_salida IS NULL
                         ORDER BY v.nombre ASC";
            $resVacas = mysqli_query($con, $sqlVacas);
        ?>
        <div class="ocupacion-card">
            <div class="oc-header">
                <div class="oc-nombre">
                    <div class="oc-avatar"><?= $initials ?></div>
                    <?= htmlspecialchars($pt['nombre']) ?>
                </div>
                <span class="oc-cap-badge <?= $badgeClass ?>"><?= $asignadas ?>/<?= $capacidad ?></span>
            </div>
            <div class="oc-barra-wrap">
                <div class="oc-barra-labels">
                    <span><?= $asignadas ?> vacas asignadas</span>
                    <span><?= $pct ?>%</span>
                </div>
                <div class="oc-barra-bg">
                    <div class="oc-barra-fill <?= $fillClass ?>" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <div class="oc-vacas">
                <?php if (mysqli_num_rows($resVacas) === 0): ?>
                    <div class="oc-empty">Sin vacas asignadas</div>
                <?php else: while ($vaca = mysqli_fetch_assoc($resVacas)): ?>
                <div class="oc-vaca-row">
                    <div class="oc-vaca-info">
                        <div class="oc-vaca-dot"></div>
                        <span class="oc-vaca-nombre"><?= htmlspecialchars($vaca['nombre']) ?></span>
                        <?php if ($vaca['manga_num']): ?>
                            <span class="oc-vaca-manga">Manga <?= $vaca['manga_num'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="oc-vaca-actions">
                        <button class="btn-ver"
                            onclick="abrirHojaVida(<?= (int)$vaca['id'] ?>, '<?= htmlspecialchars($vaca['nombre'], ENT_QUOTES) ?>')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Ver
                        </button>
                        <button class="btn-mover"
                            onclick="abrirMover(<?= $vaca['asig_id'] ?>, '<?= htmlspecialchars($vaca['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pt['nombre'], ENT_QUOTES) ?>', <?= $pt['id'] ?>)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                            Mover
                        </button>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- TABLE POTREROS REGISTRADOS -->
    <div class="contenedorTabla">
        <div class="table-toolbar">
            <div class="tt-left">
                <div class="table-heading">Fincas Registradas</div>
                <div class="count-pill"><?= $totalPotreros ?> potreros</div>
            </div>
        </div>

        <?php if (isset($_GET['ok'])): ?>
        <div style="padding:10px 20px; font-size:12.5px; color:var(--accent-text); background:var(--accent-glow); border-bottom:1px solid var(--accent-border); display:flex; align-items:center; gap:6px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20,6 9,17 4,12"/></svg>
            <?= match($_GET['ok']) {
                'editado'    => 'Potrero actualizado correctamente',
                default      => 'Potrero registrado correctamente'
            } ?>
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Hectáreas</th>
                    <th>Tipo de Pasto</th>
                    <th>Mangas</th>
                    <th>Capacidad</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $m2PorVacaTabla = [
                        'brachiaria_humidicola' => 250,
                        'brachiaria_decumbens'  => 200,
                        'brachiaria_dictyoneura'=> 230,
                        'brachiaria_ruziziensis'=> 180,
                        'pasto_elefante'        => 100,
                        'pasto_para'            => 300,
                        'pasto_angola'          => 250,
                        'pasto_natural'         => 400
                    ];
                    $sql   = "SELECT * FROM potreros WHERE usuario_id=$uid ORDER BY fecha_registro DESC";
                    $query = mysqli_query($con, $sql);
                    while ($row = mysqli_fetch_assoc($query)):
                        $initials = strtoupper(substr($row['nombre'], 0, 2));
                        $capacidadGeneral = (int) $row['capacidad_max'];
                        $numMangas = (int) $row['num_mangas'];
                        $tamManga = isset($row['tamaño_manga']) ? (float) $row['tamaño_manga'] : 0;
                        if ($tamManga <= 0 && isset($row['tamaño_manga'])) $tamManga = (float) $row['tamaño_manga'];
                        $m2Necesarios = $m2PorVacaTabla[$row['tipo_pasto']] ?? 0;
                        $capacidadManga = ($row['tiene_mangas'] && $numMangas > 0)
                            ? (int) floor($tamManga / max($m2Necesarios, 1))
                            : 0;
                ?>
                <tr>
                    <td>
                        <div class="potrero-cell">
                            <div class="potrero-avatar"><?= $initials ?></div>
                            <div class="potrero-name"><?= htmlspecialchars($row['nombre']) ?></div>
                        </div>
                    </td>
                    <td><span class="mono-val"><?= $row['hectareas'] ?></span><span class="unit">ha</span></td>
                    <td><span class="pasto-tag"><?= ucwords(str_replace('_', ' ', $row['tipo_pasto'])) ?></span></td>
                    <td>
                        <?php if ($row['tiene_mangas']): ?>
                            <span class="badge-mangas">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                                </svg>
                                <?= $row['num_mangas'] ?> mangas
                            </span>
                        <?php else: ?>
                            <span class="badge-sin">Sin mangas</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="capacidad-cell">
                            <div class="capacidad-main">
                                <span class="mono-val"><?= $capacidadGeneral ?></span><span class="unit">vacas total</span>
                            </div>
                            <?php if ($row['tiene_mangas'] && $numMangas > 0): ?>
                                <div class="capacidad-sub">
                                    <span class="mono-val"><?= $capacidadManga ?></span><span class="unit">por manga</span>
                                </div>
                            <?php else: ?>
                                <div class="capacidad-sub">
                                    <span class="unit">Sin capacidad por manga</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><span class="fecha-tag"><?= date('d/m/Y', strtotime($row['fecha_registro'])) ?></span></td>
                    <td>
                        <div class="actions-cell">
                            <!-- ✅ BOTÓN EDITAR — abre modal en vez de redirigir -->
                            <button class="btn-icon-edit"
                                onclick="abrirEditar(
                                    <?= $row['id'] ?>,
                                    '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>',
                                    <?= $row['hectareas'] ?>,
                                    '<?= $row['tipo_pasto'] ?>',
                                    <?= $row['tiene_mangas'] ?>,
                                    <?= (int)$row['num_mangas'] ?>,
                                    <?= (float)$row['tamaño_manga'] ?>
                                )">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                    <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Editar
                            </button>
                            <a href="eliminarp.php?id=<?= $row['id'] ?>"
                               onclick="return confirm('¿Está seguro de eliminar este potrero?')"
                               class="btn-icon-del">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <polyline points="3,6 5,6 21,6"/>
                                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                </svg>
                                Eliminar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div><!-- /main -->


<!-- ═══════════════ MODAL CREAR POTRERO ═══════════════ -->
<div class="modalOverlay" id="modalCrear">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow"><div class="eyebrow-dot"></div>Nuevo registro</div>
                <h2>Crear Finca</h2>
                <div class="modal-subtitle">Complete los datos de la nueva finca</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalCrear')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="CrearPotrero.php" method="POST">
            <div class="modal-body">
                <div class="field-row-2">
                    <div class="field">
                        <label class="field-label" for="nombre">Nombre De La Ficna <span class="field-req">*</span></label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Finca La Pradera" required>
                    </div>
                    <div class="field">
                        <label class="field-label" for="hectareas">Hectáreas <span class="field-req">*</span></label>
                        <input type="number" id="hectareas" name="hectareas" placeholder="Ej: 10.5" step="0.01" min="0.01" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="tipo_pasto">Tipo de Pasto <span class="field-req">*</span></label>
                    <select name="tipo_pasto" id="tipo_pasto" required>
                        <option value="">-- Seleccione tipo de pasto --</option>
                        <option value="brachiaria_humidicola">Brachiaria Humidicola</option>
                        <option value="brachiaria_decumbens">Brachiaria Decumbens</option>
                        <option value="brachiaria_dictyoneura">Brachiaria Dictyoneura</option>
                        <option value="brachiaria_ruziziensis">Brachiaria Ruziziensis</option>
                        <option value="pasto_elefante">Pasto Elefante</option>
                        <option value="pasto_para">Pasto Pará</option>
                        <option value="pasto_angola">Pasto Angola</option>
                        <option value="pasto_natural">Pasto Natural</option>
                    </select>
                </div>
                <label class="checkbox-wrap">
                    <input type="checkbox" id="tiene_mangas" name="tiene_mangas" value="1">
                    <div class="checkbox-box"></div>
                    <span class="checkbox-label">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                        </svg>
                        La finca tiene mangas?
                    </span>
                </label>
                <div id="grupo_mangas" style="display:none">
                    <div class="mangas-panel">
                        <div class="mangas-panel-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                            </svg>
                            Configuración de mangas
                        </div>
                        <div class="field-row-2">
                            <div class="field">
                                <label class="field-label" for="num_mangas">Número de mangas</label>
                                <input type="number" id="num_mangas" name="num_mangas" placeholder="Ej: 3" min="1">
                            </div>
                            <div class="field">
                                <label class="field-label" for="tamaño_manga">Tamaño por manga (m²)</label>
                                <input type="number" id="tamaño_manga" name="tamaño_manga" placeholder="Ej: 1000" min="1">
                            </div>
                        </div>
                        <div class="field-hint-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            1 hectárea = 10.000 m²
                        </div>
                        <div class="cap-manga-box">
                            <div>
                                <div class="cap-manga-label">Capacidad por manga</div>
                                <div class="cap-manga-sub">calculado automáticamente</div>
                            </div>
                            <div class="cap-manga-val" id="capacidad_manga_texto">--</div>
                        </div>
                    </div>
                </div>
                <div class="cap-total-box">
                    <div>
                        <div class="cap-total-label">Capacidad máxima total</div>
                        <div class="cap-total-val" id="capacidad_texto">--</div>
                    </div>
                    <div class="cap-total-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <ellipse cx="12" cy="8" rx="7" ry="5"/>
                            <path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/>
                        </svg>
                    </div>
                </div>
            </div>
            <input type="hidden" id="capacidad_max" name="capacidad_max" value="0">
            <div class="modal-footer">
                <div class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Los campos con * son obligatorios
                </div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalCrear')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                        Guardar Finca
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ MODAL EDITAR POTRERO ═══════════════ -->
<div class="modalOverlay" id="modalEditar">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow modal-eyebrow-edit">
                    <div class="eyebrow-dot eyebrow-dot-edit"></div>
                    Editar registro
                </div>
                <h2>Editar Finca</h2>
                <div class="modal-subtitle">Modifica los datos de la finca</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalEditar')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="ActualizarPotrero.php" method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="field-row-2">
                    <div class="field">
                        <label class="field-label" for="edit_nombre">Nombre <span class="field-req">*</span></label>
                        <input type="text" id="edit_nombre" name="nombre" placeholder="Ej: Finca La Pradera" required>
                    </div>
                    <div class="field">
                        <label class="field-label" for="edit_hectareas">Hectáreas <span class="field-req">*</span></label>
                        <input type="number" id="edit_hectareas" name="hectareas" placeholder="Ej: 10.5" step="0.01" min="0.01" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="edit_tipo_pasto">Tipo de Pasto <span class="field-req">*</span></label>
                    <select name="tipo_pasto" id="edit_tipo_pasto" required>
                        <option value="">-- Seleccione tipo de pasto --</option>
                        <option value="brachiaria_humidicola">Brachiaria Humidicola</option>
                        <option value="brachiaria_decumbens">Brachiaria Decumbens</option>
                        <option value="brachiaria_dictyoneura">Brachiaria Dictyoneura</option>
                        <option value="brachiaria_ruziziensis">Brachiaria Ruziziensis</option>
                        <option value="pasto_elefante">Pasto Elefante</option>
                        <option value="pasto_para">Pasto Pará</option>
                        <option value="pasto_angola">Pasto Angola</option>
                        <option value="pasto_natural">Pasto Natural</option>
                    </select>
                </div>
                <label class="checkbox-wrap">
                    <input type="checkbox" id="edit_tiene_mangas" name="tiene_mangas" value="1">
                    <div class="checkbox-box"></div>
                    <span class="checkbox-label">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                        </svg>
                        La finca tiene mangas?
                    </span>
                </label>
                <div id="edit_grupo_mangas" style="display:none">
                    <div class="mangas-panel">
                        <div class="mangas-panel-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>
                            </svg>
                            Configuración de mangas
                        </div>
                        <div class="field-row-2">
                            <div class="field">
                                <label class="field-label" for="edit_num_mangas">Número de mangas</label>
                                <input type="number" id="edit_num_mangas" name="num_mangas" placeholder="Ej: 3" min="1">
                            </div>
                            <div class="field">
                                <label class="field-label" for="edit_tamaño_manga">Tamaño por manga (m²)</label>
                                <input type="number" id="edit_tamaño_manga" name="tamaño_manga" placeholder="Ej: 1000" min="1">
                            </div>
                        </div>
                        <div class="field-hint-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            1 hectárea = 10.000 m²
                        </div>
                        <div class="cap-manga-box">
                            <div>
                                <div class="cap-manga-label">Capacidad por manga</div>
                                <div class="cap-manga-sub">calculado automáticamente</div>
                            </div>
                            <div class="cap-manga-val" id="edit_capacidad_manga_texto">--</div>
                        </div>
                    </div>
                </div>
                <div class="cap-total-box">
                    <div>
                        <div class="cap-total-label">Capacidad máxima total</div>
                        <div class="cap-total-val" id="edit_capacidad_texto">--</div>
                    </div>
                    <div class="cap-total-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <ellipse cx="12" cy="8" rx="7" ry="5"/>
                            <path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/>
                        </svg>
                    </div>
                </div>
            </div>
            <input type="hidden" id="edit_capacidad_max" name="capacidad_max" value="0">
            <div class="modal-footer">
                <div class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Los campos con * son obligatorios
                </div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit btn-modal-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ MODAL ASIGNAR VACA ═══════════════ -->
<div class="modalOverlay" id="modalAsignar">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow" style="color:var(--blue-text)">
                    <div class="eyebrow-dot" style="background:var(--blue)"></div>
                    Asignación
                </div>
                <h2>Asignar Vaca a una Finca</h2>
                <div class="modal-subtitle">Selecciona la vaca y el destino</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalAsignar')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="AsignarVaca.php" method="POST">
            <div class="modal-body">
                <div class="field">
                    <label class="field-label" for="asig_vaca_id">Vaca <span class="field-req">*</span></label>
                    <select name="vaca_id" id="asig_vaca_id" required>
                        <option value="">-- Seleccione una vaca --</option>
                        <?php
                            $sqlVacasLibres = "SELECT v.id, v.nombre, v.codigo FROM vacas v
                                WHERE v.usuario_id = $uid
                                AND v.id NOT IN (
                                    SELECT vaca_id FROM asignaciones WHERE fecha_salida IS NULL
                                ) ORDER BY v.nombre ASC";
                            $resVL = mysqli_query($con, $sqlVacasLibres);
                            while ($vl = mysqli_fetch_assoc($resVL)):
                        ?>
                        <option value="<?= $vl['id'] ?>"><?= htmlspecialchars($vl['nombre']) ?> (<?= $vl['codigo'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="asig_potrero_id">Finca <span class="field-req">*</span></label>
                    <select name="potrero_id" id="asig_potrero_id" required onchange="cargarMangas(this.value)">
                        <option value="">-- Seleccione una finca --</option>
                        <?php
                        $m2PorVacaArr = [
                            'brachiaria_humidicola'=>250,'brachiaria_decumbens'=>200,
                            'brachiaria_dictyoneura'=>230,'brachiaria_ruziziensis'=>180,
                            'pasto_elefante'=>100,'pasto_para'=>300,
                            'pasto_angola'=>250,'pasto_natural'=>400
                        ];
                        foreach ($potrerosList as $pt):
                            $libre = (int)$pt['capacidad_max'] - (int)$pt['vacas_asignadas'];
                            $m2 = $m2PorVacaArr[$pt['tipo_pasto']] ?? 200;
                            $tamManga = (float)$pt['tamaño_manga'];
                            $numMgs   = (int)$pt['num_mangas'] > 0 ? (int)$pt['num_mangas'] : 1;
                            if ($pt['tiene_mangas'] && $tamManga > 0 && $m2 > 0) {
                                $capManga = (int)floor($tamManga / $m2);
                            } else {
                                $capManga = (int)floor((int)$pt['capacidad_max'] / $numMgs);
                            }
                            if ($capManga <= 0) $capManga = (int)$pt['capacidad_max'];
                            $vacasPorManga = [];
                            for ($mn = 1; $mn <= $numMgs; $mn++) {
                                $cnt = mysqli_fetch_row(mysqli_query($con,
                                    "SELECT COUNT(*) FROM asignaciones WHERE potrero_id={$pt['id']} AND manga_num=$mn AND fecha_salida IS NULL"
                                ))[0];
                                $vacasPorManga[$mn] = (int)$cnt;
                            }
                            $dataVacasManga = htmlspecialchars(json_encode($vacasPorManga));
                        ?>
                        <option value="<?= $pt['id'] ?>"
                            data-mangas="<?= $pt['tiene_mangas'] ? $pt['num_mangas'] : 0 ?>"
                            data-cap-manga="<?= $capManga ?>"
                            data-vacas-manga="<?= $dataVacasManga ?>">
                            <?= htmlspecialchars($pt['nombre']) ?> — <?= $libre ?> lugares disponibles
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" id="campo_manga" style="display:none">
                    <label class="field-label" for="asig_manga_num">Manga (opcional)</label>
                    <select name="manga_num" id="asig_manga_num">
                        <option value="">-- Sin manga específica --</option>
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="asig_usuario">Responsable <span class="field-req">*</span></label>
                    <input type="text" id="asig_usuario" name="usuario" placeholder="Nombre del encargado" required>
                </div>
                <div class="field">
                    <label class="field-label" for="asig_fecha">Fecha de entrada <span class="field-req">*</span></label>
                    <input type="date" id="asig_fecha" name="fecha_entrada" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    La vaca quedará activa en esa finca hasta que se registre su salida o movimiento a otra finca
                </div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalAsignar')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit" style="background:var(--blue);border-color:rgba(81,144,200,.5);color:#fff;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                        Confirmar Asignación
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ MODAL MOVER VACA ═══════════════ -->
<div class="modalOverlay" id="modalMover">
    <div class="contenedorModal1">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow modal-eyebrow-edit">
                    <div class="eyebrow-dot eyebrow-dot-edit"></div>
                    Movimiento
                </div>
                <h2>Mover Vaca de Finca</h2>
                <div class="modal-subtitle" id="mover_subtitulo">Selecciona el destino</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalMover')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <form action="MoverVaca.php" method="POST">
            <input type="hidden" name="asignacion_id" id="mover_asig_id">
            <input type="hidden" name="potrero_actual_id" id="mover_potrero_actual_id">
            <div class="modal-body">
                <div class="field">
                    <label class="field-label" for="mover_potrero">Nueva Finca <span class="field-req">*</span></label>
                    <select name="nuevo_potrero_id" id="mover_potrero" required onchange="cargarMangasMover(this.value)">
                        <option value="">-- Seleccione finca destino --</option>
                        <?php foreach ($potrerosList as $pt):
                            $libre = (int)$pt['capacidad_max'] - (int)$pt['vacas_asignadas'];
                        ?>
                        <option value="<?= $pt['id'] ?>" data-mangas="<?= $pt['tiene_mangas'] ? $pt['num_mangas'] : 0 ?>">
                            <?= htmlspecialchars($pt['nombre']) ?> — <?= $libre ?> lugares disponibles
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" id="campo_manga_mover" style="display:none">
                    <label class="field-label" for="mover_manga_num">Manga (opcional)</label>
                    <select name="manga_num" id="mover_manga_num">
                        <option value="">-- Sin manga específica --</option>
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="mover_usuario">Responsable <span class="field-req">*</span></label>
                    <input type="text" id="mover_usuario" name="usuario" placeholder="Nombre del encargado" required>
                </div>
                <div class="field">
                    <label class="field-label" for="mover_fecha">Fecha de salida <span class="field-req">*</span></label>
                    <input type="date" id="mover_fecha" name="fecha_salida" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Se registrará la salida de la finca
                </div>
                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalMover')">Cancelar</button>
                    <button type="submit" class="btn-modal-submit btn-modal-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        Confirmar Movimiento
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════ MODAL HISTORIAL ═══════════════ -->
<div class="modalOverlay" id="modalHistorial">
    <div class="contenedorModal1 modal-wide">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow"><div class="eyebrow-dot"></div>Registro</div>
                <h2>Historial de Asignaciones</h2>
                <div class="modal-subtitle">Todos los movimientos de vacas en fincas</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalHistorial')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <div class="hist-table-wrap">
            <?php
                $sqlHist = "SELECT a.*, v.nombre as vaca_nombre, v.codigo as vaca_codigo,
                            p.nombre as potrero_nombre,
                            DATEDIFF(COALESCE(a.fecha_salida, NOW()), a.fecha_entrada) as dias
                            FROM asignaciones a
                            JOIN vacas v ON v.id = a.vaca_id
                            JOIN potreros p ON p.id = a.potrero_id
                            ORDER BY a.fecha_entrada DESC
                            LIMIT 100";
                $resHist = mysqli_query($con, $sqlHist);
            ?>
            <?php if (!$resHist || mysqli_num_rows($resHist) === 0): ?>
                <div class="hist-empty">No hay registros de asignaciones aún.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Vaca</th><th>Finca</th><th>Entrada</th>
                        <th>Salida</th><th>Días</th><th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($h = mysqli_fetch_assoc($resHist)): ?>
                    <tr>
                        <td>
                            <div class="potrero-cell">
                                <div class="potrero-avatar"><?= strtoupper(substr($h['vaca_nombre'],0,2)) ?></div>
                                <div>
                                    <div class="potrero-name"><?= htmlspecialchars($h['vaca_nombre']) ?></div>
                                    <div style="font-size:10px;color:var(--text-muted)"><?= $h['vaca_codigo'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="pasto-tag"><?= htmlspecialchars($h['potrero_nombre']) ?><?= $h['manga_num'] ? ' · Manga '.$h['manga_num'] : '' ?></span></td>
                        <td><span class="fecha-tag"><?= date('d/m/Y', strtotime($h['fecha_entrada'])) ?></span></td>
                        <td>
                            <?php if ($h['fecha_salida']): ?>
                                <span class="estado-salida"><?= date('d/m/Y', strtotime($h['fecha_salida'])) ?></span>
                            <?php else: ?>
                                <span class="estado-activo">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="dias-val"><?= $h['dias'] ?>d</span></td>
                        <td><span class="fecha-tag"><?= htmlspecialchars($h['usuario'] ?? '—') ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <div class="modal-hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Últimos 100 movimientos
            </div>
            <div class="modal-footer-btns">
                <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalHistorial')">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL RESUMEN ═══════════════ -->
<div class="modalOverlay" id="modalResumen">
    <div class="contenedorModal1" style="max-width:400px">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow"><div class="eyebrow-dot"></div>Confirmación</div>
                <h2 id="resumen_titulo">Operación exitosa</h2>
                <div class="modal-subtitle" id="resumen_subtitulo"></div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalResumen')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>
        <div class="modal-body" style="gap:16px">
            <div style="display:flex;flex-direction:column;align-items:center;padding:4px 0 8px">
                <div class="resumen-check">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <polyline points="20,6 9,17 4,12"/>
                    </svg>
                </div>
                <div class="resumen-titulo" id="resumen_accion">Vaca asignada</div>
                <div class="resumen-sub" id="resumen_desc">El cambio se registró correctamente</div>
            </div>
            <div class="resumen-card">
                <div class="resumen-row">
                    <span class="resumen-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>Vaca</span>
                    <span class="resumen-val" id="res_vaca">—</span>
                </div>
                <div class="resumen-row">
                    <span class="resumen-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/></svg>Potrero</span>
                    <span class="resumen-val" id="res_potrero">—</span>
                </div>
                <div class="resumen-row" id="res_manga_row">
                    <span class="resumen-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>Manga</span>
                    <span class="resumen-val" id="res_manga">—</span>
                </div>
                <div class="resumen-row">
                    <span class="resumen-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Fecha</span>
                    <span class="resumen-val" id="res_fecha">—</span>
                </div>
                <div class="resumen-row">
                    <span class="resumen-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 20a6.5 6.5 0 0113 0"/></svg>Responsable</span>
                    <span class="resumen-val" id="res_usuario">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="justify-content:flex-end">
            <button type="button" class="btn-modal-submit" onclick="cerrarModal('modalResumen')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polyline points="20,6 9,17 4,12"/>
                </svg>
                Entendido
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL HOJA DE VIDA ═══════════════ -->
<div class="modalOverlay" id="modalHojaVida">
    <div class="contenedorModal1" style="max-width:540px">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-eyebrow"><div class="eyebrow-dot"></div>Hoja de Vida</div>
                <h2 id="hv-modal-titulo">Ficha del animal</h2>
                <div class="modal-subtitle" id="hv-modal-subtitulo">Cargando datos...</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal('modalHojaVida')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-divider"></div>

        <div class="hv-tabs">
            <button class="hv-tab activo" onclick="hvTab(this,'hv-panel-perfil')">Perfil</button>
            <button class="hv-tab"        onclick="hvTab(this,'hv-panel-trayectoria')">Trayectoria</button>
            <button class="hv-tab"        onclick="hvTab(this,'hv-panel-vacunas')">Vacunas</button>
        </div>

        <div class="modal-body" style="gap:12px; max-height:62vh;">

            <div class="hv-loading" id="hv-loading">
                <div class="spinner"></div>
                Cargando hoja de vida...
            </div>

            <div class="hv-tab-panel activo" id="hv-panel-perfil" style="display:none">
                <div class="hv-perfil">
                    <div class="hv-avatar" id="hv-avatar">—</div>
                    <div>
                        <div class="hv-nombre" id="hv-nombre">—</div>
                        <div class="hv-codigo" id="hv-codigo">—</div>
                        <div id="hv-estado-wrap"></div>
                    </div>
                </div>
                <div class="hv-stats">
                    <div class="hv-stat">
                        <div class="hv-stat-val" id="hv-partos">—</div>
                        <div class="hv-stat-lbl">Partos</div>
                    </div>
                    <div class="hv-stat">
                        <div class="hv-stat-val" id="hv-edad">—</div>
                        <div class="hv-stat-lbl">Edad (años)</div>
                    </div>
                    <div class="hv-stat">
                        <div class="hv-stat-val" id="hv-dias-finca">—</div>
                        <div class="hv-stat-lbl">Días en finca</div>
                    </div>
                </div>
                <div class="hv-datos">
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>Código</span>
                        <span class="hv-dato-val" id="hv-d-codigo">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="8" rx="7" ry="5"/><path d="M5 13c0 3.3 3.1 6 7 6s7-2.7 7-6"/></svg>Raza</span>
                        <span class="hv-dato-val" id="hv-d-raza">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a1 1 0 01-1 1H4a1 1 0 01-1-1z"/></svg>Ubicación actual</span>
                        <span class="hv-dato-val" id="hv-d-ubicacion">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Ingreso al potrero actual</span>
                        <span class="hv-dato-val" id="hv-d-ingreso">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>Manga asignada</span>
                        <span class="hv-dato-val" id="hv-d-manga">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>Estado productivo</span>
                        <span class="hv-dato-val" id="hv-d-estado">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 19V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M14 3v6h6"/></svg>Procedencia</span>
                        <span class="hv-dato-val" id="hv-d-descripcion">—</span>
                    </div>
                    <div class="hv-dato-row">
                        <span class="hv-dato-lbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>Vacunas base</span>
                        <span class="hv-dato-val" id="hv-d-vacunas-info">—</span>
                    </div>
                </div>
            </div>

            <div class="hv-tab-panel" id="hv-panel-trayectoria" style="display:none">
                <div style="font-size:11px;color:var(--text-muted);padding-bottom:4px;">Historial completo de movimientos entre potreros</div>
                <div class="hv-timeline" id="hv-timeline">
                    <div class="hv-empty">Sin registros de trayectoria</div>
                </div>
            </div>

            <div class="hv-tab-panel" id="hv-panel-vacunas" style="display:none">
                <div style="font-size:11px;color:var(--text-muted);padding-bottom:4px;">Registro de vacunaciones del animal</div>
                <div id="hv-vacunas-lista" style="display:flex;flex-direction:column;gap:6px;">
                    <div class="hv-empty">Sin registros de vacunación</div>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <div class="modal-hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="hv-footer-info">Datos en tiempo real</span>
            </div>
            <div class="modal-footer-btns">
                <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalHojaVida')">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <polyline points="20,6 9,17 4,12"/>
    </svg>
    <span id="toast-msg">Operación realizada</span>
</div>

<script>
/* ══════════════════════════════════════════════
   HOJA DE VIDA
   ══════════════════════════════════════════════ */
function hvTab(btn, panelId) {
    document.querySelectorAll('.hv-tab').forEach(t => t.classList.remove('activo'));
    document.querySelectorAll('.hv-tab-panel').forEach(p => { p.classList.remove('activo'); p.style.display = 'none'; });
    btn.classList.add('activo');
    const panel = document.getElementById(panelId);
    panel.classList.add('activo');
    panel.style.display = 'flex';
}

function estadoPillHV(estado) {
    const mapa = {
        'Producción':{ cls:'hp-prod', label:'Producción' }, 'produccion':{ cls:'hp-prod', label:'Producción' },
        'Secado':    { cls:'hp-seco', label:'Secado' },     'secado':    { cls:'hp-seco', label:'Secado' },
        'Enrazada':  { cls:'hp-enra', label:'Enrazada' },   'enrazada':  { cls:'hp-enra', label:'Enrazada' },
    };
    const e = mapa[estado] || { cls:'hp-prod', label: estado };
    return `<span class="hv-estado-pill ${e.cls}"><span class="hp-dot"></span>${e.label}</span>`;
}

function fmtFecha(f) {
    if (!f || f === '0000-00-00') return '—';
    const [y, m, d] = f.split('T')[0].split('-');
    return `${d}/${m}/${y}`;
}

function abrirHojaVida(vacaId, vacaNombre) {
    document.querySelectorAll('.hv-tab').forEach((t, i) => t.classList.toggle('activo', i === 0));
    document.querySelectorAll('.hv-tab-panel').forEach((p, i) => { p.classList.toggle('activo', i === 0); p.style.display = i === 0 ? 'flex' : 'none'; });
    document.getElementById('hv-loading').style.display      = 'flex';
    document.getElementById('hv-panel-perfil').style.display = 'none';
    document.getElementById('hv-modal-titulo').textContent   = vacaNombre;
    document.getElementById('hv-modal-subtitulo').textContent = 'Cargando información...';
    abrirModal('modalHojaVida');
    fetch(`getHojaVida.php?vaca_id=${vacaId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) { document.getElementById('hv-modal-subtitulo').textContent = data.error; document.getElementById('hv-loading').style.display = 'none'; return; }
            renderHojaVida(data);
        })
        .catch(() => { document.getElementById('hv-modal-subtitulo').textContent = 'Error de conexión al cargar los datos'; document.getElementById('hv-loading').style.display = 'none'; });
}

function renderHojaVida(data) {
    const v = data.vaca, ub = data.ubicacion, tr = data.trayectoria, vac = data.vacunaciones;
    document.getElementById('hv-loading').style.display      = 'none';
    document.getElementById('hv-panel-perfil').style.display = 'flex';
    const initials = (v.nombre || '??').substring(0, 2).toUpperCase();
    document.getElementById('hv-avatar').textContent          = initials;
    document.getElementById('hv-nombre').textContent          = v.nombre;
    document.getElementById('hv-codigo').textContent          = v.codigo;
    document.getElementById('hv-modal-titulo').textContent    = v.nombre;
    document.getElementById('hv-modal-subtitulo').textContent = v.raza + ' · ' + (ub ? ub.potrero_nombre : 'Sin potrero asignado');
    document.getElementById('hv-estado-wrap').innerHTML       = estadoPillHV(v.estado);
    document.getElementById('hv-partos').textContent = data.partos;
    document.getElementById('hv-edad').textContent   = v.edad || '—';
    let diasFinca = '—';
    if (tr.length > 0) { const primera = new Date(tr[0].fecha_entrada); diasFinca = Math.floor((new Date() - primera) / 86400000); }
    document.getElementById('hv-dias-finca').textContent = diasFinca;
    document.getElementById('hv-d-codigo').textContent   = v.codigo;
    document.getElementById('hv-d-raza').textContent     = v.raza;
    document.getElementById('hv-d-estado').innerHTML     = estadoPillHV(v.estado);
    document.getElementById('hv-d-descripcion').textContent = v.descripcion ? v.descripcion : 'Sin descripcion registrada';
    document.getElementById('hv-d-vacunas-info').textContent = v.vacunas_info ? v.vacunas_info : 'Sin vacunas base registradas';
    if (ub) {
        let ubicTxt = ub.potrero_nombre;
        if (ub.manga_num) ubicTxt += ' · Manga ' + ub.manga_num;
        document.getElementById('hv-d-ubicacion').textContent = ubicTxt;
        document.getElementById('hv-d-ingreso').textContent   = fmtFecha(ub.fecha_entrada);
        document.getElementById('hv-d-manga').textContent     = ub.manga_num ? 'Manga ' + ub.manga_num : 'Sin manga específica';
    } else {
        document.getElementById('hv-d-ubicacion').textContent = 'Sin potrero asignado';
        document.getElementById('hv-d-ingreso').textContent   = '—';
        document.getElementById('hv-d-manga').textContent     = '—';
    }
    document.getElementById('hv-footer-info').textContent = `${tr.length} movimiento${tr.length !== 1 ? 's' : ''} registrado${tr.length !== 1 ? 's' : ''}`;
    const tl = document.getElementById('hv-timeline');
    if (tr.length === 0) {
        tl.innerHTML = '<div class="hv-empty">Sin trayectoria registrada</div>';
    } else {
        tl.innerHTML = tr.map(t => {
            const esActivo = !t.fecha_salida;
            const badge = esActivo ? '<span class="hv-tl-badge hv-tl-activo">Actual</span>' : '';
            const manga = t.manga_num ? ` · Manga ${t.manga_num}` : '';
            const resp  = t.usuario   ? ` · ${t.usuario}` : '';
            const salida = t.fecha_salida ? ' → ' + fmtFecha(t.fecha_salida) : '';
            return `<div class="hv-tl-item"><div class="hv-tl-left"><div class="hv-tl-dot ${esActivo ? 'activo' : ''}"></div><div class="hv-tl-line"></div></div><div class="hv-tl-content"><div class="hv-tl-potrero">${t.potrero_nombre}${manga}${badge}</div><div class="hv-tl-meta">Entrada: ${fmtFecha(t.fecha_entrada)}${salida}${resp}</div><div class="hv-tl-dias">${t.dias} día${t.dias != 1 ? 's' : ''}</div></div></div>`;
        }).join('');
    }
    const vlista = document.getElementById('hv-vacunas-lista');
    const badgeCls = { aplicada:'badge-vac-ok', pendiente:'badge-vac-pend', vencida:'badge-vac-venc' };
    const badgeLbl = { aplicada:'Aplicada', pendiente:'Pendiente', vencida:'Vencida' };
    if (vac.length === 0) {
        vlista.innerHTML = '<div class="hv-empty">Sin registros de vacunación</div>';
    } else {
        vlista.innerHTML = vac.map(vc => `<div class="hv-vacuna-row"><div><div class="hv-vac-nombre">${vc.tipo_vacuna}</div><div class="hv-vac-fecha">${vc.fecha_aplicada ? 'Aplicada: ' + fmtFecha(vc.fecha_aplicada) : 'Programada: ' + fmtFecha(vc.fecha_programada)}${vc.dosis_ml ? ' · ' + vc.dosis_ml + ' ml' : ''}${vc.responsable ? ' · ' + vc.responsable : ''}</div></div><span class="${badgeCls[vc.estado] || 'badge-vac-pend'}">${badgeLbl[vc.estado] || vc.estado}</span></div>`).join('');
    }
}

/* ══════════════════════════════════════════════
   MANGAS
   ══════════════════════════════════════════════ */
function cargarMangas(potreroId) {
    const sel      = document.getElementById('asig_potrero_id');
    const opt      = sel.options[sel.selectedIndex];
    const nMangas  = parseInt(opt?.dataset?.mangas) || 0;
    const capManga = parseInt(opt?.dataset?.capManga) || 0;
    const vacasManga = JSON.parse(opt?.dataset?.vacasManga || '{}');
    const campo    = document.getElementById('campo_manga');
    const selM     = document.getElementById('asig_manga_num');
    if (nMangas > 0 && potreroId) {
        campo.style.display = 'flex';
        selM.innerHTML = '<option value="">-- Sin manga específica --</option>';
        for (let i = 1; i <= nMangas; i++) {
            const enManga = vacasManga[i] || 0;
            const libre   = capManga - enManga;
            const llena   = libre <= 0;
            const label   = llena ? `Manga ${i} — LLENA (${enManga}/${capManga})` : `Manga ${i} — ${libre} lugar${libre !== 1 ? 'es' : ''} disponible${libre !== 1 ? 's' : ''}`;
            selM.innerHTML += `<option value="${i}" ${llena ? 'disabled' : ''}>${label}</option>`;
        }
    } else {
        campo.style.display = 'none';
        selM.innerHTML = '<option value="">-- Sin manga específica --</option>';
    }
}

function cargarMangasMover(potreroId) {
    const sel     = document.getElementById('mover_potrero');
    const opt     = sel.options[sel.selectedIndex];
    const nMangas = parseInt(opt?.dataset?.mangas) || 0;
    const campo   = document.getElementById('campo_manga_mover');
    const selM    = document.getElementById('mover_manga_num');
    if (nMangas > 0 && potreroId) {
        campo.style.display = 'flex';
        selM.innerHTML = '<option value="">-- Sin manga específica --</option>';
        for (let i = 1; i <= nMangas; i++)
            selM.innerHTML += `<option value="${i}">Manga ${i}</option>`;
    } else {
        campo.style.display = 'none';
        selM.innerHTML = '<option value="">-- Sin manga específica --</option>';
    }
}

function abrirMover(asigId, vacaNombre, potreroActual, potreroActualId) {
    document.getElementById('mover_asig_id').value           = asigId;
    document.getElementById('mover_potrero_actual_id').value = potreroActualId;
    document.getElementById('mover_subtitulo').textContent   = `${vacaNombre} — actualmente en ${potreroActual}`;
    const sel = document.getElementById('mover_potrero');
    sel.value = potreroActualId;
    cargarMangasMover(potreroActualId);
    abrirModal('modalMover');
}

/* ══════════════════════════════════════════════
   MODAL EDITAR POTRERO
   ══════════════════════════════════════════════ */
function abrirEditar(id, nombre, hectareas, tipoPasto, tieneMangas, numMangas, tamManga) {
    document.getElementById('edit_id').value         = id;
    document.getElementById('edit_nombre').value     = nombre;
    document.getElementById('edit_hectareas').value  = hectareas;
    document.getElementById('edit_tipo_pasto').value = tipoPasto;
    const chk = document.getElementById('edit_tiene_mangas');
    chk.checked = tieneMangas == 1;
    document.getElementById('edit_grupo_mangas').style.display = tieneMangas ? 'block' : 'none';
    document.getElementById('edit_num_mangas').value   = numMangas || '';
    document.getElementById('edit_tamaño_manga').value = tamManga  || '';
    calcularCapacidadEdit();
    abrirModal('modalEditar');
}

function calcularCapacidadEdit() {
    const hectareas   = parseFloat(document.getElementById('edit_hectareas').value)    || 0;
    const tipoPasto   = document.getElementById('edit_tipo_pasto').value;
    const tieneMangas = document.getElementById('edit_tiene_mangas').checked;
    const numMangas   = parseInt(document.getElementById('edit_num_mangas').value)      || 0;
    const tamMangaM2  = parseFloat(document.getElementById('edit_tamaño_manga').value)  || 0;
    const capTexto    = document.getElementById('edit_capacidad_texto');
    const capMangaT   = document.getElementById('edit_capacidad_manga_texto');
    const capHidden   = document.getElementById('edit_capacidad_max');
    if (hectareas <= 0 || !tipoPasto) { capTexto.textContent = '--'; capMangaT.textContent = '--'; capHidden.value = 0; return; }
    const capacidadTotal = Math.floor(hectareas * factores[tipoPasto]);
    capTexto.textContent = capacidadTotal + ' vacas';
    capHidden.value      = capacidadTotal;
    if (tieneMangas && numMangas > 0 && tamMangaM2 > 0) {
        const capPorManga = Math.floor(tamMangaM2 / m2PorVaca[tipoPasto]);
        capMangaT.textContent = capPorManga + ' vacas';
    } else {
        capMangaT.textContent = '--';
    }
}

document.getElementById('edit_tiene_mangas').addEventListener('change', function() {
    document.getElementById('edit_grupo_mangas').style.display = this.checked ? 'block' : 'none';
    calcularCapacidadEdit();
});
['edit_hectareas','edit_tipo_pasto','edit_num_mangas','edit_tamaño_manga'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcularCapacidadEdit);
});

/* ══════════════════════════════════════════════
   MODAL RESUMEN — abrir desde URL params
   ══════════════════════════════════════════════ */
(function() {
    const p  = new URLSearchParams(window.location.search);
    const ok = p.get('ok');
    if (ok === 'asignacion' || ok === 'movimiento') {
        const vaca    = p.get('vaca')    || '—';
        const potrero = p.get('potrero') || '—';
        const manga   = p.get('manga')   || '';
        const fecha   = p.get('fecha')   || '—';
        const usuario = p.get('usuario') || '—';
        document.getElementById('res_vaca').textContent    = vaca;
        document.getElementById('res_potrero').textContent = potrero;
        document.getElementById('res_usuario').textContent = usuario;
        if (fecha && fecha !== '—') { const [y,m,d] = fecha.split('-'); document.getElementById('res_fecha').textContent = `${d}/${m}/${y}`; }
        if (manga) { document.getElementById('res_manga').textContent = 'Manga ' + manga; document.getElementById('res_manga_row').style.display = 'flex'; }
        else { document.getElementById('res_manga_row').style.display = 'none'; }
        document.getElementById('resumen_accion').textContent = ok === 'asignacion' ? '¡Vaca asignada!'    : '¡Vaca movida!';
        document.getElementById('resumen_desc').textContent   = ok === 'asignacion' ? 'Asignada correctamente' : 'Traslado registrado correctamente';
        document.getElementById('resumen_titulo').textContent = ok === 'asignacion' ? 'Asignación exitosa' : 'Movimiento exitoso';
        abrirModal('modalResumen');
        window.history.replaceState({}, '', 'potrero.php');
    }
})();

/* ══════════════════════════════════════════════
   MODALES — abrir / cerrar
   ══════════════════════════════════════════════ */
function abrirModal(id)  { document.getElementById(id).classList.add('activo'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }
document.querySelectorAll('.modalOverlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('activo'); });
});

/* ══════════════════════════════════════════════
   THEME TOGGLE
   ══════════════════════════════════════════════ */
const toggle = document.getElementById('themeToggle');
if (localStorage.getItem('theme') === 'light') document.documentElement.classList.add('light');
toggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('light');
    localStorage.setItem('theme', document.documentElement.classList.contains('light') ? 'light' : 'dark');
});

/* ══════════════════════════════════════════════
   CÁLCULO DE CAPACIDAD
   ══════════════════════════════════════════════ */
const m2PorVaca = {
    brachiaria_humidicola:250, brachiaria_decumbens:200,
    brachiaria_dictyoneura:230, brachiaria_ruziziensis:180,
    pasto_elefante:100, pasto_para:300, pasto_angola:250, pasto_natural:400
};
const factores = {
    brachiaria_humidicola:1.2, brachiaria_decumbens:1.5,
    brachiaria_dictyoneura:1.3, brachiaria_ruziziensis:1.8,
    pasto_elefante:4.5, pasto_para:1.0, pasto_angola:1.2, pasto_natural:0.8
};

const hectInput    = document.getElementById('hectareas');
const tipoPastoSel = document.getElementById('tipo_pasto');
const numMInput    = document.getElementById('num_mangas');
const tamMInput    = document.getElementById('tamaño_manga');
const capTexto     = document.getElementById('capacidad_texto');
const capManga     = document.getElementById('capacidad_manga_texto');
const capHidden    = document.getElementById('capacidad_max');
const chkMangas    = document.getElementById('tiene_mangas');
const grupoMangas  = document.getElementById('grupo_mangas');

function calcularCapacidad() {
    const hectareas   = parseFloat(hectInput.value)  || 0;
    const tipoPasto   = tipoPastoSel.value;
    const tieneMangas = chkMangas.checked;
    const numMangas   = parseInt(numMInput.value)     || 0;
    const tamMangaM2  = parseFloat(tamMInput.value)   || 0;
    if (hectareas <= 0 || !tipoPasto) { capTexto.textContent = '--'; capManga.textContent = '--'; capHidden.value = 0; return; }
    const capacidadTotal = Math.floor(hectareas * factores[tipoPasto]);
    capTexto.textContent = capacidadTotal + ' vacas';
    capHidden.value      = capacidadTotal;
    if (tieneMangas && numMangas > 0 && tamMangaM2 > 0) {
        const capPorManga = Math.floor(tamMangaM2 / m2PorVaca[tipoPasto]);
        const areaMangas  = numMangas * tamMangaM2;
        const areaTotalM2 = hectareas * 10000;
        capManga.textContent = capPorManga + (areaMangas > areaTotalM2 ? ' (área excede total)' : ' vacas');
    } else {
        capManga.textContent = '--';
    }
}

chkMangas.addEventListener('change', function() {
    grupoMangas.style.display = this.checked ? 'block' : 'none';
    if (!this.checked) { numMInput.value = ''; tamMInput.value = ''; capManga.textContent = '--'; }
    calcularCapacidad();
});
['hectareas','tipo_pasto','num_mangas','tamaño_manga'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcularCapacidad);
});

/* ══════════════════════════════════════════════
   TOAST
   ══════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.style.borderColor = type === 'error' ? 'var(--danger-border)' : 'var(--accent-border)';
    t.style.color       = type === 'error' ? 'var(--danger-text)'   : 'var(--accent-text)';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}
<?php if (isset($_GET['ok'])): ?>
showToast('✅ ' + <?= json_encode(match($_GET['ok']) {
    'asignacion' => 'Vaca asignada correctamente',
    'movimiento' => 'Vaca movida al nuevo potrero',
    'editado'    => 'Potrero actualizado correctamente',
    default      => 'Potrero registrado correctamente'
}) ?>);
<?php elseif (isset($_GET['error'])): ?>
showToast('⚠️ ' + <?= json_encode(match($_GET['error']) {
    'vaca_ya_asignada'         => 'Esta vaca ya tiene un potrero asignado',
    'potrero_lleno'            => 'El potrero destino está al máximo de capacidad',
    'manga_llena'              => 'Esta manga ya está al máximo de capacidad, selecciona otra manga',
    'potrero_no_encontrado'    => 'No se encontró el potrero seleccionado',
    'sin_cambio'               => 'La vaca ya está en ese potrero y manga',
    'asignacion_no_encontrada' => 'No se encontró la asignación',
    'datos_incompletos'        => 'Faltan datos obligatorios',
    default                    => 'Ocurrió un error. Intente de nuevo'
}) ?>, 'error');
<?php endif; ?>
</script>

</body>
</html>
