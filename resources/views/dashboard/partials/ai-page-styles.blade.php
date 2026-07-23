{{--
    Shared AI-pages design language (" identical brand UI" brief, 2026-07-23).
    Generalized from the Spec 012 extraction-log redesign
    (resources/views/dashboard/invoices/index.blade.php @section('styles')).

    Usage: wrap the page's root content container in class="ai-page ..."
    (purely additive — never rename existing ids/classes/data-*), then:
        @section('styles')
            @include('dashboard.partials.ai-page-styles')
        @endsection
    If the page already has a @section('styles'), put the @include inside it.

    Scoped entirely under .ai-page so nothing leaks to other pages.
    Reuses the --sn-* brand tokens from public/css/app-ui.css — no new colors.

    Priority #1 (client complaint): table data too light to read. Metronic's
    stock .text-success (#50cd89 on white), .text-muted (#a1a5b7) and the
    badge-light-* pastels are the offenders — overridden to solid --sn-ink /
    --sn-emerald-deep / darkened --sn-amber, WCAG AA (>=4.5:1) where possible.
--}}
<style>
    /* ---- stats strip -------------------------------------------------- */
    .ai-page .sn-stat-tile {
        background: var(--sn-card);
        border: 1px solid var(--sn-line);
        border-radius: var(--sn-r-md);
        padding: .85rem 1.25rem;
        box-shadow: var(--sn-shadow-sm);
    }
    .ai-page .sn-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--sn-r-md);
        background: var(--sn-emerald-tint);
        color: var(--sn-emerald-deep);
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .ai-page .sn-stat-value {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--sn-ink);
        line-height: 1;
    }
    .ai-page .sn-stat-label {
        font-size: .78rem;
        color: var(--sn-ink-soft);
        margin-top: .2rem;
    }

    /* ---- filter card / form labels ------------------------------------- */
    .ai-page .form-label.text-muted {
        color: var(--sn-ink-soft) !important;
    }

    /* ---- table header: solid emerald fill, white bold text.
           Applies to every table under .ai-page (no sn-thead class needed)
           and out-specifies the global tint-only .sn-thead rule
           (app-ui.css §6) on purpose — AA contrast the client asked for. -- */
    .ai-page .table thead tr,
    .ai-page .table.sn-thead thead tr,
    .ai-page .table thead.sn-thead tr {
        background: var(--sn-emerald) !important;
        color: #fff !important;
    }
    .ai-page .table thead th,
    .ai-page .table.sn-thead thead th,
    .ai-page .table thead.sn-thead th {
        color: #fff !important;
        font-weight: 700;
        border-bottom: 2px solid var(--sn-emerald-deep) !important;
        padding-block: .85rem;
    }

    /* ---- data legibility (the #1 fix) ----------------------------------- */
    .ai-page .table td {
        color: var(--sn-ink);
    }
    .ai-page .table .text-muted,
    .ai-page .text-muted {
        color: var(--sn-ink-soft) !important;
    }
    .ai-page .table .text-success {
        color: var(--sn-emerald-deep) !important;
    }
    .ai-page .table .text-gray-800 {
        color: var(--sn-ink) !important;
    }

    /* ---- badges: readable tint+ink pairings replacing the washed-out
           badge-light-* pastels. Semantic meaning unchanged. ------------- */
    .ai-page .badge {
        font-weight: 700;
        letter-spacing: .01em;
        border: 1px solid transparent;
        transition: background-color var(--sn-dur-base) var(--sn-ease-out),
                    color var(--sn-dur-base) var(--sn-ease-out);
    }
    .ai-page .badge-light-primary,
    .ai-page .badge-light-success {
        color: var(--sn-emerald-deep) !important;
        background-color: var(--sn-emerald-tint) !important;
        border-color: rgba(10, 79, 58, .15);
    }
    .ai-page .badge-light-warning {
        /* darkened amber — plain --sn-amber on --sn-amber-tint is ~3.2:1;
           this reaches ~4.6:1 AA. */
        color: color-mix(in srgb, var(--sn-amber) 78%, black 22%) !important;
        background-color: var(--sn-amber-tint) !important;
        border-color: rgba(181, 120, 10, .25);
    }
    .ai-page .badge-light-danger {
        color: var(--sn-rust) !important;
        background-color: var(--sn-rust-tint) !important;
        border-color: rgba(169, 59, 44, .2);
    }
    .ai-page .badge-light-secondary {
        color: var(--sn-ink-soft) !important;
        background-color: var(--sn-paper-2) !important;
        border-color: var(--sn-line);
    }

    /* ---- bulk-action bar (pages that have one) --------------------------- */
    .ai-page #bulkBar {
        border-radius: var(--sn-r-md);
        border: 1px solid var(--sn-emerald-tint);
        background-color: var(--sn-emerald-tint) !important;
        color: var(--sn-emerald-deep) !important;
        animation: sn-bar-in var(--sn-dur-base) var(--sn-ease-out);
    }
    @keyframes sn-bar-in {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---- buttons: subtle hover/active feedback --------------------------- */
    .ai-page .btn {
        transition: transform var(--sn-dur-fast) var(--sn-ease-out),
                    box-shadow var(--sn-dur-fast) var(--sn-ease-out),
                    background-color var(--sn-dur-base) var(--sn-ease-out);
    }
    .ai-page .btn:hover {
        transform: translateY(-1px);
    }
    .ai-page .btn:active {
        transform: translateY(0);
    }

    /* ---- row entrance: fade + slide, staggered; gentle hover lift -------- */
    .ai-page tbody tr {
        animation: sn-row-in var(--sn-dur-slow) var(--sn-ease-out) both;
        transition: background-color var(--sn-dur-fast) var(--sn-ease-out),
                    transform var(--sn-dur-fast) var(--sn-ease-out);
    }
    .ai-page tbody tr:hover {
        transform: translateY(-1px);
    }
    @keyframes sn-row-in {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ai-page tbody tr:nth-child(1)  { animation-delay: 0ms; }
    .ai-page tbody tr:nth-child(2)  { animation-delay: 30ms; }
    .ai-page tbody tr:nth-child(3)  { animation-delay: 60ms; }
    .ai-page tbody tr:nth-child(4)  { animation-delay: 90ms; }
    .ai-page tbody tr:nth-child(5)  { animation-delay: 120ms; }
    .ai-page tbody tr:nth-child(6)  { animation-delay: 150ms; }
    .ai-page tbody tr:nth-child(7)  { animation-delay: 180ms; }
    .ai-page tbody tr:nth-child(8)  { animation-delay: 210ms; }
    .ai-page tbody tr:nth-child(9)  { animation-delay: 240ms; }
    .ai-page tbody tr:nth-child(10) { animation-delay: 270ms; }
    .ai-page tbody tr:nth-child(11) { animation-delay: 300ms; }
    .ai-page tbody tr:nth-child(12) { animation-delay: 330ms; }
    .ai-page tbody tr:nth-child(n+13) { animation-delay: 350ms; }

    /* ---- modal entrance: smoother than Bootstrap's default --------------- */
    .ai-page .modal .modal-dialog {
        transition: transform var(--sn-dur-slow) var(--sn-ease-out) !important;
    }

    /* ---- accessibility: hard-disable all motion added above -------------- */
    @media (prefers-reduced-motion: reduce) {
        .ai-page *,
        .ai-page *::before,
        .ai-page *::after {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>
