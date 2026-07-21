@extends('layouts.app')

@section('page_title')
<div class="analytics-page-title">
    <div>
        <h2><i class="fas fa-chart-line"></i> Reports Analytics</h2>
        <p>Decision support for maintenance planning and resource allocation</p>
    </div>
</div>

@endsection

@section('styles')
<style>
    .analytics-shell {
        --analytics-blue: #1769e0;
        --analytics-green: #148a58;
        --analytics-red: #d93645;
        --analytics-amber: #e99a00;
        --analytics-ink: #10233f;
        --analytics-muted: #66758a;
        --analytics-border: #dce2ea;
        display: grid;
        gap: 18px;
        padding: 20px;
        background: #f5f7fa;
        min-height: calc(100vh - 92px);
    }

    .analytics-page-title h2 {
        margin: 0;
        color: #10233f;
        font-size: 25px;
        font-weight: 700;
    }

    .analytics-page-title p {
        margin: 3px 0 0;
        color: #66758a;
        font-size: 14px;
    }

    .analytics-panel {
        background: #fff;
        border: 1px solid var(--analytics-border);
        border-radius: 8px;
        box-shadow: 0 2px 7px rgba(16, 35, 63, 0.05);
    }

    .analytics-filter {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(155px, .75fr) minmax(155px, .75fr) auto auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
    }

    .analytics-field label {
        display: block;
        margin-bottom: 5px;
        color: #45566d;
        font-size: 12px;
        font-weight: 700;
    }

    .analytics-field .form-control,
    .analytics-field .form-select {
        height: 42px;
        border-color: #ccd4df;
        border-radius: 6px;
    }

    .analytics-filter .btn {
        height: 42px;
        border-radius: 6px;
        font-weight: 600;
    }

    .analytics-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .analytics-kpi {
        position: relative;
        min-height: 132px;
        padding: 18px 20px;
        overflow: hidden;
    }

    .analytics-kpi::before {
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--kpi-color);
        content: '';
    }

    .analytics-kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .analytics-kpi-label {
        color: var(--analytics-muted);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .analytics-kpi-icon {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 50%;
        color: var(--kpi-color);
        background: color-mix(in srgb, var(--kpi-color) 12%, white);
    }

    .analytics-kpi-value {
        margin-top: 7px;
        color: var(--analytics-ink);
        font-size: 30px;
        font-weight: 750;
        line-height: 1.15;
    }

    .analytics-kpi-context {
        margin-top: 5px;
        color: var(--analytics-muted);
        font-size: 12px;
    }

    .analytics-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 17px 20px;
        border-bottom: 1px solid #e5e9ef;
    }

    .analytics-panel-header h3 {
        margin: 0;
        color: var(--analytics-ink);
        font-size: 17px;
        font-weight: 700;
    }

    .analytics-panel-header p {
        margin: 3px 0 0;
        color: var(--analytics-muted);
        font-size: 12px;
    }

    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chart-legend span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border: 1px solid #dce2ea;
        border-radius: 5px;
        color: #45566d;
        font-size: 11px;
        font-weight: 700;
    }

    .chart-legend i {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .analytics-header-tools,
    .analytics-chart-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    .analytics-chart-actions .btn,
    .analytics-header-tools .btn {
        min-height: 34px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 650;
    }

    .chart-selection {
        min-height: 48px;
        margin: 0 20px 17px;
        padding: 10px 13px;
        border: 1px solid #dce4ee;
        border-radius: 5px;
        color: #52647b;
        background: #f8fafc;
        font-size: 12px;
        line-height: 1.5;
    }

    .chart-selection strong {
        color: var(--analytics-ink);
    }

    .chart-selection a {
        margin-left: 6px;
        font-weight: 700;
        text-decoration: none;
    }

    .analytics-data-table {
        display: none;
        margin: 0 20px 20px;
        overflow-x: auto;
    }

    .analytics-data-table.is-visible {
        display: block;
    }

    .analytics-data-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .analytics-data-table th,
    .analytics-data-table td {
        padding: 9px 11px;
        border: 1px solid #e1e6ed;
        color: #45566d;
        font-size: 12px;
        text-align: left;
    }

    .analytics-data-table th {
        background: #f4f7fa;
        color: #283b55;
    }

    .location-tools {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(150px, .45fr) auto;
        gap: 10px;
        padding: 13px 16px;
        border-bottom: 1px solid #e5e9ef;
        background: #fafbfc;
    }

    .location-tools .form-control,
    .location-tools .form-select {
        height: 38px;
        border-radius: 5px;
        font-size: 13px;
    }

    .risk-sort {
        padding: 0;
        border: 0;
        color: inherit;
        background: transparent;
        font: inherit;
        text-transform: inherit;
    }

    .risk-sort:hover,
    .risk-sort:focus {
        color: var(--analytics-blue);
    }

    .analytics-summary-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 15px 0;
    }

    .analytics-summary-metric {
        padding: 12px;
        border: 1px solid #e0e6ed;
        border-radius: 6px;
        background: #f8fafc;
    }

    .analytics-summary-metric span {
        display: block;
        color: #6b7a8e;
        font-size: 11px;
        text-transform: uppercase;
    }

    .analytics-summary-metric strong {
        display: block;
        margin-top: 3px;
        font-size: 18px;
    }

    .analytics-chart-body {
        height: 350px;
        padding: 18px 20px 8px;
    }

    .analytics-chart-body canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .analytics-interpretation {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 0 20px 18px;
        padding: 13px 15px;
        border-left: 4px solid var(--interpretation-color);
        border-radius: 4px;
        background: #f7f9fc;
        color: #31445e;
        font-size: 13px;
    }

    .analytics-interpretation strong {
        color: var(--analytics-ink);
    }

    .analytics-interpretation .btn {
        flex: 0 0 auto;
        border-radius: 5px;
    }

    .analytics-grid-two {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 18px;
    }

    .analytics-grid-three {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .analytics-small-chart {
        height: 285px;
        padding: 17px 20px 20px;
    }

    .analytics-small-chart canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .category-selector {
        display: flex;
        gap: 7px;
        padding: 12px 16px;
        overflow-x: auto;
        border-bottom: 1px solid #e3e8ef;
        scrollbar-width: thin;
    }

    .category-selector button {
        flex: 0 0 auto;
        min-height: 36px;
        padding: 7px 13px;
        border: 1px solid #cad3df;
        border-radius: 5px;
        color: #455b76;
        background: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    .category-selector button.active {
        border-color: var(--analytics-blue);
        color: #fff;
        background: var(--analytics-blue);
    }

    .category-analytics-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 370px;
        min-height: 680px;
    }

    .category-main-panel {
        min-width: 0;
        padding: 16px;
    }

    .category-metric-tabs {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        border: 1px solid #dce2ea;
        border-radius: 7px;
        overflow: hidden;
    }

    .category-metric-tabs button,
    .category-metric-tabs > div {
        min-height: 78px;
        padding: 12px;
        border: 0;
        border-right: 1px solid #dce2ea;
        color: #63748a;
        background: #fff;
        text-align: center;
    }

    .category-metric-tabs > :last-child { border-right: 0; }
    .category-metric-tabs button { cursor: pointer; }
    .category-metric-tabs button.active { box-shadow: inset 0 -4px 0 var(--analytics-blue); background: #f7faff; }
    .category-metric-tabs span { display: block; font-size: 11px; font-weight: 700; }
    .category-metric-tabs strong { display: block; margin-top: 4px; color: var(--analytics-ink); font-size: 22px; }

    .category-trend-chart {
        height: 290px;
        padding: 18px 4px 10px;
    }

    .category-trend-chart canvas { width: 100% !important; height: 100% !important; }

    .category-ticket-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #e4e9ef;
    }

    .category-ticket-filters { display: flex; gap: 5px; }
    .category-ticket-filters button {
        padding: 6px 10px;
        border: 1px solid #ccd5df;
        border-radius: 4px;
        color: #566980;
        background: #fff;
        font-size: 11px;
        font-weight: 700;
    }
    .category-ticket-filters button.active { border-color: var(--analytics-blue); color: var(--analytics-blue); background: #f2f7ff; }
    .category-ticket-toolbar .form-control { width: min(250px, 45%); height: 34px; border-radius: 5px; font-size: 12px; }

    .category-ticket-list {
        display: grid;
        max-height: 330px;
        overflow-y: auto;
        border: 1px solid #e0e6ed;
        border-radius: 6px;
    }

    .category-ticket-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        padding: 11px 13px;
        border: 0;
        border-bottom: 1px solid #e8ecf1;
        color: inherit;
        background: #fff;
        font: inherit;
        text-align: left;
    }

    .category-ticket-row:last-child { border-bottom: 0; }
    .category-ticket-row:hover, .category-ticket-row.active { background: #f3f7fd; box-shadow: inset 3px 0 0 var(--analytics-blue); }
    .category-ticket-row h5 { margin: 0 0 3px; color: var(--analytics-ink); font-size: 13px; }
    .category-ticket-row p { margin: 0; color: #6c7c90; font-size: 11px; }
    .category-ticket-row-meta { display: flex; align-items: center; gap: 6px; }
    .category-status-badge { display: inline-flex; padding: 4px 7px; border-radius: 4px; color: #fff; font-size: 10px; font-weight: 800; white-space: nowrap; }

    .category-live-panel {
        min-width: 0;
        padding: 18px;
        border-left: 1px solid #dce2ea;
        background: #fbfcfd;
    }

    .category-live-heading {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 13px;
        border-bottom: 1px solid #dce2ea;
    }

    .category-live-heading h4 { margin: 0; color: var(--analytics-ink); font-size: 17px; }
    .category-live-heading span { color: #65768b; font-size: 11px; }
    .category-live-heading span i { display: inline-block; width: 8px; height: 8px; margin-right: 5px; border-radius: 50%; background: #10a6a6; }
    .category-ticket-id { padding: 4px 7px; border-radius: 4px; background: #eaf1fb; color: #1769e0 !important; font-weight: 700; }

    .category-empty-detail { padding: 90px 18px; color: #738197; text-align: center; }
    .category-empty-detail i { margin-bottom: 10px; font-size: 32px; }
    .category-empty-detail p { margin: 0; }

    .category-ticket-detail > h3 { margin: 18px 0 6px; color: var(--analytics-ink); font-size: 18px; }
    .category-ticket-detail > p { color: #617289; font-size: 12px; line-height: 1.55; }

    .category-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin: 14px 0;
    }

    .category-detail-grid > div { padding: 8px; border: 1px solid #e0e5eb; border-radius: 5px; background: #fff; }
    .category-detail-grid span { display: block; color: #748298; font-size: 9px; font-weight: 700; text-transform: uppercase; }
    .category-detail-grid strong { display: block; margin-top: 2px; color: #233953; font-size: 11px; overflow-wrap: anywhere; }

    .category-workflow {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin: 15px 0;
    }

    .category-workflow-step { position: relative; padding-top: 24px; color: #8794a6; font-size: 9px; font-weight: 700; text-align: center; }
    .category-workflow-step::before { position: absolute; top: 8px; left: 0; right: 0; height: 3px; background: #d8dfe7; content: ''; }
    .category-workflow-step:first-child::before { left: 50%; }
    .category-workflow-step:last-child::before { right: 50%; }
    .category-workflow-step i { position: absolute; z-index: 1; top: 2px; left: 50%; width: 15px; height: 15px; margin-left: -7px; border: 3px solid #d8dfe7; border-radius: 50%; background: #fff; }
    .category-workflow-step.done { color: #148a58; }
    .category-workflow-step.done::before { background: #148a58; }
    .category-workflow-step.done i { border-color: #148a58; background: #148a58; }
    .category-workflow-step.current { color: #1769e0; }
    .category-workflow-step.current i { border-color: #1769e0; }

    .category-assignment-control, .category-resolution-form, .category-resolution-history { margin: 13px 0; padding: 11px; border: 1px solid #dce3eb; border-radius: 6px; background: #fff; }
    .category-assignment-control label, .category-resolution-form label { margin-bottom: 4px; color: #43566e; font-size: 10px; font-weight: 700; }
    .category-resolution-history { color: #52647b; font-size: 11px; line-height: 1.55; }

    .category-workflow-actions { display: flex; gap: 7px; margin-top: 12px; }
    .category-workflow-actions .btn { flex: 1 1 auto; min-height: 39px; border-radius: 5px; font-size: 11px; font-weight: 700; }
    .category-action-message { min-height: 22px; margin-top: 8px; font-size: 11px; }

    .category-summary-metrics { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .category-summary-section { margin-top: 22px; }
    .category-summary-section h4 { margin: 0 0 10px; color: var(--analytics-ink); font-size: 15px; }
    .category-summary-section-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .category-summary-section-heading span { color: #6b7a8e; font-size: 11px; }
    .category-summary-chart { display: block; width: 100%; min-height: 260px; border: 1px solid #e0e6ed; object-fit: contain; background: #fff; }
    .category-summary-table-wrap { overflow-x: auto; border: 1px solid #dce2ea; }
    .category-summary-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .category-summary-table th, .category-summary-table td { padding: 9px; border-right: 1px solid #e1e6ec; border-bottom: 1px solid #e1e6ec; text-align: left; vertical-align: top; }
    .category-summary-table th { color: #455a73; background: #f4f7fa; white-space: nowrap; }
    .category-summary-table tr:last-child td { border-bottom: 0; }
    .category-summary-table th:last-child, .category-summary-table td:last-child { border-right: 0; }

    .decision-list {
        display: grid;
        gap: 10px;
        padding: 16px 20px 20px;
    }

    .decision-item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 11px;
        align-items: start;
        padding: 12px;
        border: 1px solid #e2e7ee;
        border-left: 4px solid var(--decision-color);
        border-radius: 6px;
        background: #fafbfc;
    }

    button.decision-item {
        width: 100%;
        color: inherit;
        font: inherit;
        text-align: left;
        cursor: pointer;
    }

    button.decision-item:hover,
    button.decision-item:focus {
        border-color: var(--decision-color);
        background: #f4f7fb;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--decision-color) 12%, transparent);
    }

    .decision-icon {
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        color: var(--decision-color);
    }

    .decision-item h4 {
        margin: 0 0 3px;
        color: var(--analytics-ink);
        font-size: 14px;
        font-weight: 700;
    }

    .decision-item p {
        margin: 0;
        color: var(--analytics-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .risk-table-wrap {
        overflow-x: auto;
    }

    .risk-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .risk-table th,
    .risk-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e9ef;
        color: #31445e;
        font-size: 13px;
        text-align: left;
        vertical-align: middle;
    }

    .risk-table th {
        color: #627187;
        background: #f8fafc;
        font-size: 11px;
        text-transform: uppercase;
    }

    .risk-table tr:last-child td {
        border-bottom: 0;
    }

    .risk-score {
        display: inline-flex;
        min-width: 42px;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 4px;
        color: #fff;
        font-weight: 700;
    }

    .risk-score.high { background: var(--analytics-red); }
    .risk-score.medium { background: var(--analytics-amber); }
    .risk-score.low { background: var(--analytics-green); }

    .location-detail-button {
        padding: 2px 0;
        border: 0;
        color: #2c4668;
        background: transparent;
        font: inherit;
        font-weight: 750;
        text-align: left;
        text-decoration: underline;
        text-decoration-color: #9ab2d0;
        text-underline-offset: 3px;
    }

    .location-detail-button:hover,
    .location-detail-button:focus {
        color: var(--analytics-blue);
        text-decoration-color: var(--analytics-blue);
    }

    .location-detail-metrics {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .location-detail-metric {
        padding: 11px;
        border: 1px solid #dfe5ec;
        border-radius: 6px;
        background: #f8fafc;
    }

    .location-detail-metric span {
        display: block;
        color: #6b7a8e;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .location-detail-metric strong {
        display: block;
        margin-top: 3px;
        color: var(--analytics-ink);
        font-size: 18px;
    }

    .location-breakdowns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 16px 0;
    }

    .location-breakdown {
        padding: 13px;
        border: 1px solid #dfe5ec;
        border-radius: 6px;
    }

    .location-breakdown h4,
    .location-reports h4 {
        margin: 0 0 10px;
        color: var(--analytics-ink);
        font-size: 14px;
    }

    .location-breakdown-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 0;
        border-bottom: 1px solid #edf0f4;
        color: #4d6078;
        font-size: 12px;
    }

    .location-breakdown-row:last-child { border-bottom: 0; }

    .location-reports {
        margin-top: 16px;
        overflow-x: auto;
    }

    .location-report-table {
        width: 100%;
        border-collapse: collapse;
    }

    .location-report-table th,
    .location-report-table td {
        padding: 8px 9px;
        border: 1px solid #e1e6ed;
        color: #45566d;
        font-size: 11px;
        text-align: left;
    }

    .location-report-table th { background: #f4f7fa; }

    .location-risk-callout {
        padding: 12px 14px;
        border-left: 4px solid var(--location-risk-color, #1769e0);
        border-radius: 4px;
        background: #f7f9fc;
        color: #42546c;
        font-size: 13px;
    }

    .empty-analytics {
        padding: 40px 20px;
        color: var(--analytics-muted);
        text-align: center;
    }

    .empty-analytics i {
        display: block;
        margin-bottom: 10px;
        color: #9aa7b8;
        font-size: 34px;
    }

    .executive-summary {
        color: #3c4d63;
        font-size: 14px;
        line-height: 1.7;
    }

    .executive-summary strong {
        color: #10233f;
    }

    .summary-callout {
        margin-top: 14px;
        padding: 13px 15px;
        border-left: 4px solid var(--analytics-blue);
        background: #f3f7fd;
    }

    .dss-action-list {
        margin: 0;
        padding-left: 20px;
        color: #45566d;
        font-size: 13px;
        line-height: 1.7;
    }

    .dss-report {
        color: #31445e;
        font-size: 13px;
        line-height: 1.65;
    }

    .dss-report-cover {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr) auto;
        gap: 16px;
        align-items: center;
        padding: 18px;
        border: 1px solid #dce3eb;
        border-top: 5px solid #1769e0;
        background: #f8fafc;
    }

    .dss-report-cover img { width: 64px; height: 64px; object-fit: contain; }
    .dss-report-cover span { color: #1769e0; font-size: 12px; font-weight: 800; text-transform: uppercase; }
    .dss-report-cover h1 { margin: 2px 0; color: #10233f; font-size: 22px; }
    .dss-report-cover p { margin: 0; color: #66758a; }
    .dss-report-cover dl { margin: 0; font-size: 11px; }
    .dss-report-cover dl div { display: grid; grid-template-columns: 78px 1fr; gap: 8px; }
    .dss-report-cover dt { color: #718096; }
    .dss-report-cover dd { margin: 0; color: #263a55; font-weight: 700; }

    .dss-report-section { padding: 18px 4px 4px; }
    .dss-report-section h2 { margin: 0 0 10px; color: #10233f; font-size: 17px; border-bottom: 1px solid #dfe5ec; padding-bottom: 7px; }
    .dss-report-section h3 { margin: 16px 0 8px; color: #213650; font-size: 14px; }
    .dss-report-section p { margin: 0 0 10px; }

    .dss-scorecards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 9px;
    }

    .dss-scorecards > div {
        padding: 10px;
        border: 1px solid #dfe5ec;
        border-radius: 5px;
        background: #f8fafc;
    }

    .dss-scorecards span, .dss-scorecards small { display: block; color: #6b7a8e; font-size: 10px; }
    .dss-scorecards strong { display: block; margin: 2px 0; color: #10233f; font-size: 17px; }

    .dss-insights { display: grid; gap: 7px; margin: 0; padding-left: 20px; }

    .dss-report-table { width: 100%; border-collapse: collapse; }
    .dss-report-table th, .dss-report-table td { padding: 7px 8px; border: 1px solid #dfe5ec; font-size: 10px; text-align: left; vertical-align: top; }
    .dss-report-table th { color: #243952; background: #f1f5f9; text-transform: uppercase; }


    @media (max-width: 1100px) {
        .analytics-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .analytics-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .analytics-grid-two, .analytics-grid-three { grid-template-columns: 1fr; }
        .category-analytics-layout { grid-template-columns: 1fr; }
        .category-live-panel { border-top: 1px solid #dce2ea; border-left: 0; }
    }

    @media (max-width: 650px) {
        .analytics-shell { padding: 12px; }
        .analytics-kpis, .analytics-filter { grid-template-columns: 1fr; }
        .analytics-chart-body { height: 290px; padding-inline: 10px; }
        .analytics-panel-header, .analytics-interpretation { align-items: flex-start; flex-direction: column; }
        .analytics-interpretation { margin-inline: 12px; }
        .analytics-header-tools, .analytics-chart-actions { justify-content: flex-start; }
        .location-tools, .analytics-summary-metrics { grid-template-columns: 1fr; }
        .location-detail-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .location-breakdowns { grid-template-columns: 1fr; }
        .dss-report-cover { grid-template-columns: 52px minmax(0, 1fr); }
        .dss-report-cover dl { grid-column: 1 / -1; }
        .dss-scorecards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .category-metric-tabs, .category-summary-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .category-ticket-toolbar { align-items: stretch; flex-direction: column; }
        .category-ticket-toolbar .form-control { width: 100%; }
    }

    @media print {
        body * { visibility: hidden; }
        #executiveSummaryModal, #executiveSummaryModal * { visibility: visible; }
        #executiveSummaryModal { position: absolute; inset: 0; display: block !important; }
        #executiveSummaryModal .modal-dialog { max-width: 100%; margin: 0; }
        #executiveSummaryModal .modal-content { border: 0; box-shadow: none; }
        #executiveSummaryModal .btn-close, #executiveSummaryModal .modal-footer { display: none; }
    }
</style>
@endsection

@section('content')
@php
    $trendDelta = (float) ($executiveSummary['trend_delta'] ?? 0);
    $topLocation = $executiveSummary['top_location'] ?? null;
    $topCategory = $executiveSummary['top_category'] ?? null;
    $trendInterpretationColor = $totalReports === 0 ? '#66758a' : ($trendDelta > 0 ? '#d93645' : '#148a58');
    $dominantStatus = $statusStats->first();
    $highRiskLocations = $locationStats->where('risk_score', '>=', 12);
    $mediumRiskLocations = $locationStats->whereBetween('risk_score', [6, 11]);
    $locationOpenWork = $locationStats->sum('open');
@endphp

<main class="analytics-shell">
    <form class="analytics-panel analytics-filter" method="GET" action="{{ route('admin.analytics') }}">
        <div class="analytics-field">
            <label for="location">Location</label>
            <select class="form-select" id="location" name="location">
                <option value="">All locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
                @endforeach
            </select>
        </div>
        <div class="analytics-field">
            <label for="date_from">From</label>
            <input class="form-control" id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
        </div>
        <div class="analytics-field">
            <label for="date_to">To</label>
            <input class="form-control" id="date_to" name="date_to" type="date" value="{{ request('date_to') }}">
        </div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.analytics') }}" title="Clear filters"><i class="fas fa-rotate-left"></i> Reset</a>
    </form>

    <section class="analytics-kpis" aria-label="Key performance indicators">
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #1769e0;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Total Reports</span>
                <span class="analytics-kpi-icon"><i class="fas fa-clipboard-list"></i></span>
            </div>
            <div class="analytics-kpi-value">{{ number_format($totalReports) }}</div>
            <div class="analytics-kpi-context">{{ number_format($hazardReports) }} marked as safety hazards</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #d93645;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Open Workload</span>
                <span class="analytics-kpi-icon"><i class="fas fa-triangle-exclamation"></i></span>
            </div>
            <div class="analytics-kpi-value">{{ number_format($openReports) }}</div>
            <div class="analytics-kpi-context">Reports still requiring action</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #148a58;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Resolution Rate</span>
                <span class="analytics-kpi-icon"><i class="fas fa-circle-check"></i></span>
            </div>
            <div class="analytics-kpi-value">{{ number_format($resolutionRate, 1) }}%</div>
            <div class="analytics-kpi-context">Target: {{ number_format($targetResolutionRate) }}%</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #e99a00;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Recorded Repair Cost</span>
                <span class="analytics-kpi-icon"><i class="fas fa-coins"></i></span>
            </div>
            <div class="analytics-kpi-value">PHP {{ number_format($totalCost, 2) }}</div>
            <div class="analytics-kpi-context">
                {{ $avgResolutionHours !== null ? number_format($avgResolutionHours, 1).' average hours to resolve' : 'No resolution-time data yet' }}
            </div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #148a58;">
            <div class="analytics-kpi-header"><span class="analytics-kpi-label">Resolved Reports</span><span class="analytics-kpi-icon"><i class="fas fa-check-double"></i></span></div>
            <div class="analytics-kpi-value">{{ number_format($resolvedReports) }}</div>
            <div class="analytics-kpi-context">{{ number_format($openReports) }} still open</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #7557c5;">
            <div class="analytics-kpi-header"><span class="analytics-kpi-label">Average Resolution</span><span class="analytics-kpi-icon"><i class="fas fa-stopwatch"></i></span></div>
            <div class="analytics-kpi-value">{{ $avgResolutionHours !== null ? number_format($avgResolutionHours, 1).' hrs' : 'N/A' }}</div>
            <div class="analytics-kpi-context">Compared with {{ $slaTargetHours }}-hour SLA</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #10a6a6;">
            <div class="analytics-kpi-header"><span class="analytics-kpi-label">Average Report Age</span><span class="analytics-kpi-icon"><i class="fas fa-calendar-day"></i></span></div>
            <div class="analytics-kpi-value">{{ number_format($avgReportAgeDays, 1) }} days</div>
            <div class="analytics-kpi-context">Oldest open report: {{ number_format($oldestOpenDays) }} days</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: {{ $slaCompliance !== null && $slaCompliance >= 85 ? '#148a58' : '#d93645' }};">
            <div class="analytics-kpi-header"><span class="analytics-kpi-label">SLA Compliance</span><span class="analytics-kpi-icon"><i class="fas fa-gauge-high"></i></span></div>
            <div class="analytics-kpi-value">{{ $slaCompliance !== null ? number_format($slaCompliance, 1).'%' : 'N/A' }}</div>
            <div class="analytics-kpi-context">{{ $slaTargetHours }}-hour completion benchmark</div>
        </article>
    </section>

    <section class="analytics-panel">
        <header class="analytics-panel-header">
            <div>
                <h3>Reports Trend</h3>
                <p>Historical report volume and completed work</p>
            </div>
            <div class="analytics-header-tools">
                <div class="chart-legend" aria-label="Chart legend">
                    <span><i style="background:#1769e0"></i> Reports</span>
                    <span><i style="background:#148a58"></i> Resolved</span>
                </div>
                <button class="btn btn-outline-secondary" type="button" data-toggle-table="trendData"><i class="fas fa-table"></i> Data</button>
                <button class="btn btn-outline-secondary" type="button" data-download-chart="reportsTrendChart" data-file-name="reports-trend"><i class="fas fa-download"></i></button>
            </div>
        </header>
        <div class="analytics-chart-body">
            <canvas id="reportsTrendChart" aria-label="Reports trend chart"></canvas>
        </div>
        <div class="analytics-interpretation" style="--interpretation-color: {{ $trendInterpretationColor }};">
            <div>
                <strong>System interpretation:</strong>
                @if($totalReports === 0)
                    There is not enough report data in this filter period to identify a trend.
                @elseif($trendDelta > 0)
                    Recent report volume increased by {{ number_format(abs($trendDelta), 1) }} reports per month compared with the earlier period. Review staffing and preventive maintenance before demand rises further.
                @elseif($trendDelta < 0)
                    Recent report volume decreased by {{ number_format(abs($trendDelta), 1) }} reports per month. Continue the current maintenance approach and verify that reporting remains accessible.
                @else
                    Report volume is stable. Focus decisions on unresolved hazards and the locations with the highest risk scores.
                @endif
            </div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#executiveSummaryModal">
                <i class="fas fa-file-lines"></i> Executive Summary
            </button>
        </div>
        <div class="analytics-data-table" id="trendData">
            <table><thead><tr><th>Month</th><th>Reports</th><th>Resolved</th><th>Completion Rate</th></tr></thead><tbody>
                @foreach($trendStats as $month)
                    <tr><td>{{ $month['label'] }}</td><td>{{ $month['reports'] }}</td><td>{{ $month['resolved'] }}</td><td>{{ $month['reports'] > 0 ? number_format(($month['resolved'] / $month['reports']) * 100, 1).'%' : 'No reports' }}</td></tr>
                @endforeach
            </tbody></table>
        </div>
    </section>

    <div class="analytics-grid-two">
        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Status Distribution</h3>
                    <p>Current workload by report status</p>
                </div>
                <div class="analytics-chart-actions">
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#statusSummaryModal"><i class="fas fa-file-lines"></i> Summary</button>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-table="statusData"><i class="fas fa-table"></i> Data</button>
                    <button class="btn btn-outline-secondary" type="button" data-download-chart="statusChart" data-file-name="status-distribution" title="Download chart"><i class="fas fa-download"></i></button>
                </div>
            </header>
            @if($statusStats->isNotEmpty())
                <div class="analytics-small-chart"><canvas id="statusChart" aria-label="Report status chart"></canvas></div>
                <div class="chart-selection" id="statusInsight"><strong>Explore:</strong> Click a status segment to see its share and open the matching report list.</div>
                <div class="analytics-data-table" id="statusData">
                    <table><thead><tr><th>Status</th><th>Reports</th><th>Avg Age</th><th>Avg Resolution</th><th>Oldest</th><th>Priority</th></tr></thead><tbody>
                        @foreach($statusStats as $status)
                            <tr><td>{{ $status['status'] }}</td><td>{{ number_format($status['count']) }}</td><td>{{ number_format($status['avg_age_days'], 1) }} days</td><td>{{ $status['avg_resolution_hours'] !== null ? number_format($status['avg_resolution_hours'], 1).' hrs' : 'N/A' }}</td><td>{{ $status['oldest_days'] }} days</td><td>{{ $status['priority'] }}</td></tr>
                        @endforeach
                    </tbody></table>
                </div>
            @else
                <div class="empty-analytics"><i class="fas fa-chart-pie"></i>No status data available.</div>
            @endif
        </section>

        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Decision Alerts</h3>
                    <p>Repeated repairs for the same issue in the same location that may justify replacement</p>
                </div>
            </header>
            <div class="decision-list">
                @forelse($decisionAlerts as $alert)
                    @php
                        $alertColor = match($alert['level']) {
                            'critical' => '#d93645',
                            'warning' => '#e99a00',
                            'success' => '#148a58',
                            default => '#1769e0',
                        };
                        $alertIcon = match($alert['level']) {
                            'critical' => 'fa-circle-exclamation',
                            'warning' => 'fa-triangle-exclamation',
                            'success' => 'fa-circle-check',
                            default => 'fa-circle-info',
                        };
                    @endphp
                    <button class="decision-item" type="button" style="--decision-color: {{ $alertColor }};" data-alert-key="{{ $alert['key'] }}" title="View supporting evidence">
                        <div class="decision-icon"><i class="fas {{ $alertIcon }}"></i></div>
                        <div><h4>{{ $alert['title'] }}</h4><p>{{ $alert['body'] }}</p></div>
                    </button>
                @empty
                    <div class="empty-analytics"><i class="fas fa-circle-info"></i>No decision alerts for this period.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="analytics-panel category-workspace">
        <header class="analytics-panel-header">
            <div><h3>Category Operations Analytics</h3><p>Select a category, inspect its trend and tickets, then manage the maintenance workflow</p></div>
            <div class="analytics-chart-actions">
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#categorySummaryModal"><i class="fas fa-file-lines"></i> Summary</button>
                <button class="btn btn-outline-secondary" type="button" data-download-chart="categoryTrendChart" data-file-name="category-trend" title="Download selected category chart"><i class="fas fa-download"></i></button>
            </div>
        </header>
        @if($categoryWorkspace->isNotEmpty())
            <nav class="category-selector" id="categorySelector" aria-label="Report categories"></nav>
            <div class="category-analytics-layout">
                <div class="category-main-panel">
                    <div class="category-metric-tabs" id="categoryMetricTabs">
                        <button type="button" data-category-metric="submitted" class="active"><span>Reports</span><strong id="categoryMetricReports">0</strong></button>
                        <button type="button" data-category-metric="resolved"><span>Resolved</span><strong id="categoryMetricResolved">0</strong></button>
                        <button type="button" data-category-metric="hazards"><span>Hazards</span><strong id="categoryMetricHazards">0</strong></button>
                        <div><span>Open</span><strong id="categoryMetricOpen">0</strong></div>
                    </div>
                    <div class="category-trend-chart"><canvas id="categoryTrendChart" aria-label="Selected category report trend"></canvas></div>
                    <div class="category-ticket-toolbar">
                        <div class="category-ticket-filters" id="categoryTicketFilters">
                            <button type="button" class="active" data-ticket-filter="all">All</button>
                            <button type="button" data-ticket-filter="open">Open</button>
                            <button type="button" data-ticket-filter="resolved">Resolved</button>
                        </div>
                        <input class="form-control" id="categoryTicketSearch" type="search" placeholder="Search tickets..." aria-label="Search category tickets">
                    </div>
                    <div class="category-ticket-list" id="categoryTicketList"></div>
                </div>
                <aside class="category-live-panel" id="categoryLivePanel">
                    <div class="category-live-heading"><div><h4>Ticket Details</h4><span><i></i> Updating live</span></div><span class="category-ticket-id" id="categoryDetailTicket">Select a ticket</span></div>
                    <div class="category-empty-detail" id="categoryEmptyDetail"><i class="fas fa-ticket"></i><p>Select a ticket from the list to view details and workflow actions.</p></div>
                    <div class="category-ticket-detail" id="categoryTicketDetail" hidden>
                        <h3 id="categoryDetailTitle"></h3>
                        <p id="categoryDetailDescription"></p>
                        <div class="category-detail-grid">
                            <div><span>Status</span><strong id="categoryDetailStatus"></strong></div>
                            <div><span>Location</span><strong id="categoryDetailLocation"></strong></div>
                            <div><span>Priority</span><strong id="categoryDetailPriority"></strong></div>
                            <div><span>Assigned To</span><strong id="categoryDetailAssignee"></strong></div>
                            <div><span>Submitted</span><strong id="categoryDetailCreated"></strong></div>
                            <div><span>Reported Count</span><strong id="categoryDetailCount"></strong></div>
                        </div>
                        <div class="category-workflow" id="categoryWorkflow"></div>
                        <div class="category-resolution-history" id="categoryResolutionHistory" hidden></div>
                        <div class="category-assignment-control" id="categoryAssignmentControl" hidden>
                            <label for="categoryStaffSelect" id="categoryStaffLabel">Assign to Maintenance Staff</label>
                            <select class="form-select" id="categoryStaffSelect"><option value="">Select staff...</option></select>
                        </div>
                        <div class="category-resolution-form" id="categoryResolutionForm" hidden>
                            <div class="row g-2"><div class="col-6"><label>Cost</label><input class="form-control" id="categoryResolutionCost" type="number" min="0" step="0.01" placeholder="0.00"></div><div class="col-6"><label>Damaged Part</label><input class="form-control" id="categoryDamagedPart" type="text"></div><div class="col-12"><label>Replaced With</label><input class="form-control" id="categoryReplacedPart" type="text"></div><div class="col-12"><label>Resolution Notes</label><textarea class="form-control" id="categoryResolutionNotes" rows="3"></textarea></div></div>
                        </div>
                        <div class="category-workflow-actions">
                            <button class="btn btn-primary" type="button" id="categoryPrimaryAction"></button>
                            <a class="btn btn-outline-secondary" id="categoryOpenReport" href="{{ route('admin.reports') }}"><i class="fas fa-arrow-up-right-from-square"></i> Full Report</a>
                        </div>
                        <div class="category-action-message" id="categoryActionMessage" role="status"></div>
                    </div>
                </aside>
            </div>
        @else
            <div class="empty-analytics"><i class="fas fa-chart-column"></i>No category data available.</div>
        @endif
    </section>

    <div class="analytics-grid-three">
        <section class="analytics-panel">
            <header class="analytics-panel-header"><div><h3>Priority Distribution</h3><p>Workload by reported priority</p></div><button class="btn btn-outline-secondary" type="button" data-download-chart="priorityChart" data-file-name="priority-distribution"><i class="fas fa-download"></i></button></header>
            <div class="analytics-small-chart"><canvas id="priorityChart" aria-label="Priority distribution chart"></canvas></div>
            <div class="chart-selection" id="priorityInsight"><strong>Explore:</strong> Click a priority segment to inspect its workload.</div>
        </section>
        <section class="analytics-panel">
            <header class="analytics-panel-header"><div><h3>Open Report Aging</h3><p>Backlog grouped by age</p></div><button class="btn btn-outline-secondary" type="button" data-download-chart="agingChart" data-file-name="report-aging"><i class="fas fa-download"></i></button></header>
            <div class="analytics-small-chart"><canvas id="agingChart" aria-label="Open report aging chart"></canvas></div>
            <div class="chart-selection" id="agingInsight"><strong>Decision use:</strong> Reports older than seven days should be reviewed for escalation.</div>
        </section>
        <section class="analytics-panel">
            <header class="analytics-panel-header"><div><h3>Cost by Category</h3><p>Recorded financial impact</p></div><button class="btn btn-outline-secondary" type="button" data-download-chart="costChart" data-file-name="category-cost"><i class="fas fa-download"></i></button></header>
            <div class="analytics-small-chart"><canvas id="costChart" aria-label="Category cost chart"></canvas></div>
            <div class="chart-selection" id="costInsight"><strong>Explore:</strong> Click a category to review its recorded cost.</div>
        </section>
    </div>

    <section class="analytics-panel">
        <header class="analytics-panel-header">
            <div>
                <h3>Location Risk Ranking</h3>
                <p>Risk score weighs unresolved reports, safety hazards, and recorded cost</p>
            </div>
            <div class="analytics-chart-actions">
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#locationSummaryModal"><i class="fas fa-file-lines"></i> Summary</button>
                <button class="btn btn-outline-secondary" id="exportRiskCsv" type="button"><i class="fas fa-file-csv"></i> Export CSV</button>
            </div>
        </header>
        @if($locationStats->isNotEmpty())
            <div class="location-tools">
                <input class="form-control" id="locationRiskSearch" type="search" placeholder="Search location..." aria-label="Search locations">
                <select class="form-select" id="locationRiskFilter" aria-label="Filter by risk">
                    <option value="all">All risk levels</option>
                    <option value="high">High risk</option>
                    <option value="medium">Medium risk</option>
                    <option value="low">Low risk</option>
                </select>
                <a class="btn btn-outline-primary" href="{{ route('admin.reports') }}"><i class="fas fa-list"></i> Open Reports</a>
            </div>
            <div class="risk-table-wrap">
                <table class="risk-table" id="locationRiskTable">
                    <thead><tr><th>Rank</th><th><button class="risk-sort" type="button" data-sort="location">Location <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="total">Total <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="open">Open <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="hazards">Hazards <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="cost">Cost <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="risk">Risk <i class="fas fa-sort"></i></button></th><th>Interpretation</th></tr></thead>
                    <tbody id="locationRiskBody">
                        @foreach($locationStats->take(10) as $index => $location)
                            @php $riskClass = $location['risk_score'] >= 12 ? 'high' : ($location['risk_score'] >= 6 ? 'medium' : 'low'); @endphp
                            <tr data-risk-level="{{ $riskClass }}" data-location="{{ strtolower($location['location']) }}" data-total="{{ $location['total'] }}" data-open="{{ $location['open'] }}" data-hazards="{{ $location['hazards'] }}" data-cost="{{ $location['cost'] }}" data-risk="{{ $location['risk_score'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td><button class="location-detail-button" type="button" data-location-detail="{{ $location['location'] }}" title="View {{ $location['location'] }} details">{{ $location['location'] }}</button></td>
                                <td>{{ number_format($location['total']) }}</td>
                                <td>{{ number_format($location['open']) }}</td>
                                <td>{{ number_format($location['hazards']) }}</td>
                                <td>PHP {{ number_format($location['cost'], 2) }}</td>
                                <td><span class="risk-score {{ $riskClass }}">{{ $location['risk_score'] }}</span></td>
                                <td>{{ $location['interpretation'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-analytics"><i class="fas fa-location-dot"></i>No location data available for this period.</div>
        @endif
    </section>
</main>

<div class="modal fade" id="executiveSummaryModal" tabindex="-1" aria-labelledby="executiveSummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title fs-5" id="executiveSummaryLabel"><i class="fas fa-wand-magic-sparkles text-primary"></i> Executive Maintenance Summary</h3>
                    <small class="text-muted">{{ $executiveSummary['period'] }}</small>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body executive-summary">@include('admin.partials.complete-dss-report')</div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="button" id="printDssReport"><i class="fas fa-file-pdf"></i> Print / Save PDF</button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.analytics-summaries')
@include('admin.partials.analytics-evidence-modals')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const historicalLabels = @json($trendStats->pluck('label')->values());
    const reportValues = @json($trendStats->pluck('reports')->values());
    const resolvedValues = @json($trendStats->pluck('resolved')->values());
    const totalReportCount = {{ (int) $totalReports }};
    const reportsUrl = @json(route('admin.reports'));
    const locationDetails = @json($locationStats->keyBy('location')->all());
    const decisionAlertDetails = @json($decisionAlerts->keyBy('key')->all());
    const categoryWorkspace = @json($categoryWorkspace->values());

    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#66758a';

    const trendCanvas = document.getElementById('reportsTrendChart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: historicalLabels,
                datasets: [
                    {
                        label: 'Reports',
                        data: reportValues,
                        borderColor: '#1769e0',
                        backgroundColor: 'rgba(23, 105, 224, .10)',
                        fill: true,
                        tension: .3,
                        pointRadius: 4,
                        borderWidth: 2
                    },
                    {
                        label: 'Resolved',
                        data: resolvedValues,
                        borderColor: '#148a58',
                        backgroundColor: 'transparent',
                        tension: .3,
                        pointRadius: 4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e7ebf0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($statusStats->pluck('status')->values()),
                datasets: [{
                    data: @json($statusStats->pluck('count')->values()),
                    backgroundColor: ['#1769e0', '#148a58', '#e99a00', '#d93645', '#7557c5', '#5f6f82'],
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                onClick: function (event, elements, chart) {
                    if (!elements.length) return;
                    const index = elements[0].index;
                    const status = chart.data.labels[index];
                    const count = Number(chart.data.datasets[0].data[index]);
                    const share = totalReportCount > 0 ? ((count / totalReportCount) * 100).toFixed(1) : '0.0';
                    const insight = document.getElementById('statusInsight');
                    const reportLink = String(status).toLowerCase() === 'resolved'
                        ? ' Resolved items are represented here for performance analysis.'
                        : ' <a href="' + reportsUrl + '?status=' + encodeURIComponent(status) + '">Open matching reports</a>';
                    insight.innerHTML = '<strong>' + escapeHtml(status) + ':</strong> ' + count.toLocaleString() + ' reports (' + share + '% of the selected workload).' + reportLink;
                }
            }
        });
    }

    function createInteractiveChart(canvasId, type, labels, values, colors, insightId, valueLabel, currency) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        return new Chart(canvas, {
            type,
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderColor: '#ffffff', borderWidth: 2, borderRadius: type === 'bar' ? 4 : 0 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: type !== 'bar', position: 'bottom', labels: { usePointStyle: true, padding: 12 } } },
                scales: type === 'bar' ? { y: { beginAtZero: true, ticks: { precision: currency ? undefined : 0 }, grid: { color: '#e7ebf0' } }, x: { grid: { display: false } } } : {},
                onClick: function (event, elements, chart) {
                    if (!elements.length || !insightId) return;
                    const index = elements[0].index;
                    const value = Number(chart.data.datasets[0].data[index]);
                    const formatted = currency ? 'PHP ' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : value.toLocaleString();
                    document.getElementById(insightId).innerHTML = '<strong>' + escapeHtml(chart.data.labels[index]) + ':</strong> ' + formatted + ' ' + escapeHtml(valueLabel) + '.';
                }
            }
        });
    }

    createInteractiveChart('priorityChart', 'doughnut', @json($priorityStats->pluck('priority')->values()), @json($priorityStats->pluck('count')->values()), ['#d93645', '#e99a00', '#1769e0', '#148a58', '#7557c5'], 'priorityInsight', 'reports', false);
    createInteractiveChart('agingChart', 'bar', @json($agingStats->pluck('bucket')->values()), @json($agingStats->pluck('count')->values()), ['#148a58', '#1769e0', '#e99a00', '#d93645'], 'agingInsight', 'open reports', false);
    createInteractiveChart('costChart', 'bar', @json($costStats->pluck('category')->values()), @json($costStats->pluck('cost')->values()), ['#1769e0', '#10a6a6', '#e99a00', '#7557c5', '#d93645', '#148a58'], 'costInsight', 'recorded cost', true);

    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = String(value);
        return node.innerHTML;
    }

    let activeCategoryIndex = 0;
    let activeCategoryMetric = 'submitted';
    let activeTicketFilter = 'all';
    let selectedCategoryTicketId = null;
    let categoryTrendChart = null;
    let resolutionFormOpen = false;

    function currentCategory() {
        return categoryWorkspace[activeCategoryIndex] || null;
    }

    function currentCategoryTicket() {
        const category = currentCategory();
        return category ? category.tickets.find(function (ticket) { return Number(ticket.id) === Number(selectedCategoryTicketId); }) : null;
    }

    function categoryStatusColor(status) {
        const normalized = String(status).toLowerCase();
        if (normalized === 'resolved') return '#148a58';
        if (normalized === 'in progress') return '#1769e0';
        if (normalized === 'assigned') return '#e99a00';
        return '#6b7a8e';
    }

    function renderCategorySelectors() {
        const selector = document.getElementById('categorySelector');
        if (!selector) return;
        selector.innerHTML = categoryWorkspace.map(function (category, index) {
            return '<button type="button" class="' + (index === activeCategoryIndex ? 'active' : '') + '" data-category-index="' + index + '">' + escapeHtml(category.name) + ' <span>(' + Number(category.stats.total).toLocaleString() + ')</span></button>';
        }).join('');
        selector.querySelectorAll('[data-category-index]').forEach(function (button) {
            button.addEventListener('click', function () {
                activeCategoryIndex = Number(button.dataset.categoryIndex);
                activeTicketFilter = 'all';
                selectedCategoryTicketId = null;
                resolutionFormOpen = false;
                document.getElementById('categoryActionMessage').textContent = '';
                document.querySelectorAll('[data-ticket-filter]').forEach(function (filter) { filter.classList.toggle('active', filter.dataset.ticketFilter === 'all'); });
                const search = document.getElementById('categoryTicketSearch');
                if (search) search.value = '';
                renderCategoryWorkspace();
            });
        });
    }

    function renderCategoryMetrics() {
        const category = currentCategory();
        if (!category) return;
        document.getElementById('categoryMetricReports').textContent = Number(category.stats.total).toLocaleString();
        document.getElementById('categoryMetricResolved').textContent = Number(category.stats.resolved).toLocaleString();
        document.getElementById('categoryMetricHazards').textContent = Number(category.stats.hazards).toLocaleString();
        document.getElementById('categoryMetricOpen').textContent = Number(category.stats.open).toLocaleString();
    }

    function renderCategoryTrend() {
        const category = currentCategory();
        const canvas = document.getElementById('categoryTrendChart');
        if (!category || !canvas) return;
        const metricLabels = { submitted: 'Reports submitted', resolved: 'Reports resolved', hazards: 'Safety hazards' };
        const metricColors = { submitted: '#1769e0', resolved: '#148a58', hazards: '#d93645' };
        if (categoryTrendChart) categoryTrendChart.destroy();
        categoryTrendChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: category.monthly.map(function (month) { return month.label; }),
                datasets: [{
                    label: category.name + ' - ' + metricLabels[activeCategoryMetric],
                    data: category.monthly.map(function (month) { return Number(month[activeCategoryMetric] || 0); }),
                    borderColor: metricColors[activeCategoryMetric],
                    backgroundColor: metricColors[activeCategoryMetric] + '1A',
                    fill: true,
                    tension: .32,
                    pointRadius: 4,
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: true, position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e7ebf0' } }, x: { grid: { display: false } } } }
        });
    }

    function categoryTicketMatches(ticket) {
        const search = (document.getElementById('categoryTicketSearch')?.value || '').trim().toLowerCase();
        const matchesFilter = activeTicketFilter === 'all'
            || (activeTicketFilter === 'resolved' && String(ticket.status).toLowerCase() === 'resolved')
            || (activeTicketFilter === 'open' && String(ticket.status).toLowerCase() !== 'resolved');
        const searchable = [ticket.ticket, ticket.title, ticket.location, ticket.status, ticket.assignee].join(' ').toLowerCase();
        return matchesFilter && (!search || searchable.includes(search));
    }

    function renderCategoryTickets() {
        const category = currentCategory();
        const list = document.getElementById('categoryTicketList');
        if (!category || !list) return;
        const statusOrder = { 'pending': 1, 'assigned': 2, 'in progress': 3, 'resolved': 4 };
        const tickets = category.tickets.filter(categoryTicketMatches).slice().sort(function (left, right) {
            const leftRank = statusOrder[String(left.status).toLowerCase()] || 5;
            const rightRank = statusOrder[String(right.status).toLowerCase()] || 5;
            return leftRank - rightRank || Number(right.id) - Number(left.id);
        });
        if (!tickets.some(function (ticket) { return Number(ticket.id) === Number(selectedCategoryTicketId); })) {
            selectedCategoryTicketId = tickets.length ? tickets[0].id : null;
        }
        list.innerHTML = tickets.length ? tickets.map(function (ticket) {
            return '<button type="button" class="category-ticket-row ' + (Number(ticket.id) === Number(selectedCategoryTicketId) ? 'active' : '') + '" data-category-ticket="' + ticket.id + '"><div><h5>' + escapeHtml(ticket.title) + '</h5><p>' + escapeHtml(ticket.ticket) + ' | ' + escapeHtml(ticket.location) + ' | ' + escapeHtml(ticket.assignee) + '</p></div><div class="category-ticket-row-meta"><span class="category-status-badge" style="background:' + categoryStatusColor(ticket.status) + '">' + escapeHtml(ticket.status) + '</span></div></button>';
        }).join('') : '<div class="empty-analytics"><i class="fas fa-ticket"></i>No tickets match this filter.</div>';
        list.querySelectorAll('[data-category-ticket]').forEach(function (button) {
            button.addEventListener('click', function () {
                selectedCategoryTicketId = Number(button.dataset.categoryTicket);
                resolutionFormOpen = false;
                document.getElementById('categoryActionMessage').textContent = '';
                renderCategoryTickets();
                renderCategoryTicketDetail();
            });
        });
        renderCategoryTicketDetail();
    }

    function renderCategoryWorkflow(ticket) {
        const statuses = ['Pending', 'Assigned', 'In Progress', 'Resolved'];
        const currentIndex = Math.max(0, statuses.findIndex(function (status) { return status.toLowerCase() === String(ticket.status).toLowerCase(); }));
        document.getElementById('categoryWorkflow').innerHTML = statuses.map(function (status, index) {
            const state = index < currentIndex ? 'done' : (index === currentIndex ? 'current' : '');
            return '<div class="category-workflow-step ' + state + '"><i></i>' + status + '</div>';
        }).join('');
    }

    async function loadCategoryStaff(category) {
        const select = document.getElementById('categoryStaffSelect');
        if (!select || select.dataset.loadedFor === category.name) return;
        select.innerHTML = '<option value="">Loading staff...</option>';
        try {
            const endpoint = category.is_technology ? '/admin/mis-users' : '/admin/maintenance-users';
            const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await response.json();
            if (currentCategory()?.name !== category.name) return;
            select.innerHTML = '<option value="">Select ' + escapeHtml(category.staff_label) + '...</option>' + (data.users || []).map(function (staff) { return '<option value="' + staff.id + '">' + escapeHtml(staff.name) + '</option>'; }).join('');
            select.dataset.loadedFor = category.name;
        } catch (error) {
            select.innerHTML = '<option value="">Unable to load staff</option>';
        }
    }

    function renderCategoryTicketDetail() {
        const category = currentCategory();
        const ticket = currentCategoryTicket();
        const empty = document.getElementById('categoryEmptyDetail');
        const detail = document.getElementById('categoryTicketDetail');
        if (!category || !ticket) {
            if (empty) empty.hidden = false;
            if (detail) detail.hidden = true;
            document.getElementById('categoryDetailTicket').textContent = 'Select a ticket';
            return;
        }
        empty.hidden = true;
        detail.hidden = false;
        document.getElementById('categoryDetailTicket').textContent = ticket.ticket;
        document.getElementById('categoryDetailTitle').textContent = ticket.title;
        document.getElementById('categoryDetailDescription').textContent = ticket.description;
        document.getElementById('categoryDetailStatus').textContent = ticket.status;
        document.getElementById('categoryDetailStatus').style.color = categoryStatusColor(ticket.status);
        document.getElementById('categoryDetailLocation').textContent = ticket.location;
        document.getElementById('categoryDetailPriority').textContent = ticket.is_hazard ? ticket.priority + ' - Safety Hazard' : ticket.priority;
        document.getElementById('categoryDetailAssignee').textContent = ticket.assignee;
        document.getElementById('categoryDetailCreated').textContent = ticket.created_at || 'Unknown';
        document.getElementById('categoryDetailCount').textContent = Number(ticket.report_count).toLocaleString();
        document.getElementById('categoryOpenReport').href = '/reports/' + ticket.id;
        renderCategoryWorkflow(ticket);

        const assignment = document.getElementById('categoryAssignmentControl');
        const resolution = document.getElementById('categoryResolutionForm');
        const history = document.getElementById('categoryResolutionHistory');
        const action = document.getElementById('categoryPrimaryAction');
        assignment.hidden = !ticket.can_assign;
        resolution.hidden = !(resolutionFormOpen && ticket.can_progress && ticket.status === 'In Progress');
        history.hidden = String(ticket.status).toLowerCase() !== 'resolved';
        history.innerHTML = history.hidden ? '' : '<strong>Resolution record</strong><br>Resolved: ' + escapeHtml(ticket.resolved_at || 'Date unavailable') + '<br>Cost: PHP ' + Number(ticket.cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '<br>Notes: ' + escapeHtml(ticket.resolution_notes || 'No notes provided');
        document.getElementById('categoryStaffLabel').textContent = 'Assign to ' + category.staff_label;

        action.hidden = false;
        action.disabled = false;
        if (ticket.can_assign) {
            action.innerHTML = '<i class="fas fa-user-plus"></i> Assign to ' + escapeHtml(category.staff_label);
            loadCategoryStaff(category);
        } else if (ticket.can_progress && ticket.status === 'Assigned') {
            action.innerHTML = '<i class="fas fa-play"></i> Start Progress';
        } else if (ticket.can_progress && ticket.status === 'In Progress') {
            action.innerHTML = resolutionFormOpen ? '<i class="fas fa-check"></i> Confirm Resolution' : '<i class="fas fa-flag-checkered"></i> Resolve Ticket';
        } else if (String(ticket.status).toLowerCase() === 'resolved') {
            action.innerHTML = '<i class="fas fa-circle-check"></i> Resolved';
            action.disabled = true;
        } else {
            action.innerHTML = '<i class="fas fa-lock"></i> Assigned Workflow';
            action.disabled = true;
        }
    }

    function renderCategoryWorkspace() {
        if (!categoryWorkspace.length) return;
        renderCategorySelectors();
        renderCategoryMetrics();
        renderCategoryTrend();
        renderCategoryTickets();
    }

    function renderCategorySummary() {
        const category = currentCategory();
        if (!category) return;
        const tickets = category.tickets || [];
        const total = Number(category.stats.total || 0);
        const resolved = Number(category.stats.resolved || 0);
        const open = Number(category.stats.open || 0);
        const hazards = Number(category.stats.hazards || 0);
        const resolutionRate = total > 0 ? (resolved / total) * 100 : 0;
        const recordedCost = tickets.reduce(function (sum, ticket) { return sum + Number(ticket.cost || 0); }, 0);
        const locationCounts = tickets.reduce(function (counts, ticket) {
            const location = ticket.location || 'Not specified';
            counts[location] = (counts[location] || 0) + Number(ticket.report_count || 1);
            return counts;
        }, {});
        const leadingLocation = Object.entries(locationCounts).sort(function (a, b) { return b[1] - a[1]; })[0];
        const title = category.name + ' Executive Summary';
        const metricLabel = { submitted: 'submitted reports', resolved: 'resolved reports', hazards: 'safety hazards' }[activeCategoryMetric];

        document.getElementById('categorySummaryTitle').textContent = title;
        document.getElementById('categorySummaryMetrics').innerHTML = [
            ['Category', category.name],
            ['Reports', total.toLocaleString()],
            ['Open', open.toLocaleString()],
            ['Resolved', resolved.toLocaleString() + ' (' + resolutionRate.toFixed(1) + '%)'],
            ['Hazards', hazards.toLocaleString()]
        ].map(function (metric) {
            return '<div class="analytics-summary-metric"><span>' + escapeHtml(metric[0]) + '</span><strong>' + escapeHtml(metric[1]) + '</strong></div>';
        }).join('');

        const locationText = leadingLocation
            ? escapeHtml(leadingLocation[0]) + ' contributes the largest share with ' + Number(leadingLocation[1]).toLocaleString() + ' report(s).'
            : 'No location concentration is available.';
        document.getElementById('categorySummaryInterpretation').innerHTML = '<strong>System interpretation:</strong> ' + escapeHtml(category.name) + ' has ' + total.toLocaleString() + ' report(s), with ' + open.toLocaleString() + ' still open and a ' + resolutionRate.toFixed(1) + '% resolution rate. ' + locationText + ' Recorded resolution cost is PHP ' + recordedCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '.';

        let decision = 'Continue monitoring this category and review recurring ticket causes before planning staff and materials.';
        if (hazards > 0) decision = 'Inspect the ' + hazards.toLocaleString() + ' hazard-related report(s) first, then assign routine work by age and operational impact.';
        else if (open > resolved) decision = 'Prioritize the open backlog, confirm ownership for unassigned tickets, and move assigned work through progress to resolution.';
        else if (open > 0) decision = 'Review the remaining open tickets and protect the current resolution performance with timely follow-up.';
        document.getElementById('categorySummaryDecision').innerHTML = '<strong>Decision:</strong> ' + escapeHtml(decision);

        document.getElementById('categorySummaryGraphTitle').textContent = 'Six-Month ' + category.name + ' Trend - ' + metricLabel;
        const summaryImage = document.getElementById('categorySummaryChartImage');
        if (categoryTrendChart) summaryImage.src = categoryTrendChart.toBase64Image('image/png', 1);
        summaryImage.alt = category.name + ' ' + metricLabel + ' trend';
        document.getElementById('categorySummaryTicketCount').textContent = tickets.length.toLocaleString() + ' ticket record(s)';
        document.getElementById('categorySummaryTickets').innerHTML = tickets.length ? tickets.map(function (ticket) {
            return '<tr><td><strong>' + escapeHtml(ticket.ticket) + '</strong></td><td>' + escapeHtml(ticket.title) + '</td><td>' + escapeHtml(ticket.location) + '</td><td>' + escapeHtml(ticket.status) + '</td><td>' + escapeHtml(ticket.assignee) + '</td><td>' + (ticket.is_hazard ? 'Yes' : 'No') + '</td><td>PHP ' + Number(ticket.cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td></tr>';
        }).join('') : '<tr><td colspan="7">No ticket evidence is available for this category.</td></tr>';
    }

    document.getElementById('categorySummaryModal')?.addEventListener('show.bs.modal', renderCategorySummary);

    async function categoryWorkflowRequest(url, options) {
        const message = document.getElementById('categoryActionMessage');
        const action = document.getElementById('categoryPrimaryAction');
        action.disabled = true;
        message.className = 'category-action-message text-primary';
        message.textContent = 'Saving workflow update...';
        try {
            const response = await fetch(url, options);
            const data = await response.json();
            if (!response.ok || data.success === false || data.error) throw new Error(data.error || data.message || 'Workflow update failed.');
            message.className = 'category-action-message text-success';
            message.textContent = data.message || 'Workflow updated successfully.';
            return data;
        } catch (error) {
            message.className = 'category-action-message text-danger';
            message.textContent = error.message;
            action.disabled = false;
            return null;
        }
    }

    document.querySelectorAll('[data-category-metric]').forEach(function (button) {
        button.addEventListener('click', function () {
            activeCategoryMetric = button.dataset.categoryMetric;
            document.querySelectorAll('[data-category-metric]').forEach(function (metric) { metric.classList.toggle('active', metric === button); });
            renderCategoryTrend();
        });
    });

    document.querySelectorAll('[data-ticket-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            activeTicketFilter = button.dataset.ticketFilter;
            document.querySelectorAll('[data-ticket-filter]').forEach(function (filter) { filter.classList.toggle('active', filter === button); });
            renderCategoryTickets();
        });
    });

    document.getElementById('categoryTicketSearch')?.addEventListener('input', renderCategoryTickets);
    document.getElementById('categoryPrimaryAction')?.addEventListener('click', async function () {
        const category = currentCategory();
        const ticket = currentCategoryTicket();
        if (!category || !ticket) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        if (ticket.can_assign) {
            const select = document.getElementById('categoryStaffSelect');
            if (!select.value) {
                document.getElementById('categoryActionMessage').className = 'category-action-message text-danger';
                document.getElementById('categoryActionMessage').textContent = 'Select a staff member before assigning.';
                return;
            }
            const form = new FormData();
            form.append('assigned_to', select.value);
            form.append('_token', csrf);
            const result = await categoryWorkflowRequest('/admin/report/' + ticket.id + '/assign', { method: 'POST', headers: { 'Accept': 'application/json' }, credentials: 'same-origin', body: form });
            if (!result) return;
            ticket.assigned_to = Number(select.value);
            ticket.assignee = select.options[select.selectedIndex].text;
            ticket.status = 'Assigned';
            ticket.can_assign = false;
            ticket.can_progress = Boolean(category.can_progress);
            renderCategoryTickets();
            renderCategoryTicketDetail();
            return;
        }

        if (ticket.can_progress && ticket.status === 'Assigned') {
            const result = await categoryWorkflowRequest('/reports/' + ticket.id + '/update-status', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify({ status: 'In Progress' }) });
            if (!result) return;
            ticket.status = 'In Progress';
            renderCategoryTickets();
            renderCategoryTicketDetail();
            return;
        }

        if (ticket.can_progress && ticket.status === 'In Progress' && !resolutionFormOpen) {
            resolutionFormOpen = true;
            renderCategoryTicketDetail();
            return;
        }

        if (ticket.can_progress && ticket.status === 'In Progress' && resolutionFormOpen) {
            const payload = {
                status: 'Resolved',
                cost: document.getElementById('categoryResolutionCost').value || null,
                damaged_part: document.getElementById('categoryDamagedPart').value.trim() || null,
                replaced_part: document.getElementById('categoryReplacedPart').value.trim() || null,
                resolution_notes: document.getElementById('categoryResolutionNotes').value.trim() || null
            };
            const result = await categoryWorkflowRequest('/reports/' + ticket.id + '/update-status', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(payload) });
            if (!result) return;
            ticket.status = 'Resolved';
            ticket.can_progress = false;
            ticket.cost = Number(payload.cost || 0);
            ticket.damaged_part = payload.damaged_part;
            ticket.replaced_part = payload.replaced_part;
            ticket.resolution_notes = payload.resolution_notes;
            ticket.resolved_at = new Date().toLocaleString();
            category.stats.open = Math.max(0, Number(category.stats.open) - Number(ticket.report_count || 1));
            category.stats.resolved = Number(category.stats.resolved) + Number(ticket.report_count || 1);
            if (category.monthly.length) category.monthly[category.monthly.length - 1].resolved = Number(category.monthly[category.monthly.length - 1].resolved) + Number(ticket.report_count || 1);
            resolutionFormOpen = false;
            renderCategoryMetrics();
            renderCategoryTrend();
            renderCategoryTickets();
            renderCategoryTicketDetail();
        }
    });

    renderCategoryWorkspace();

    function renderLocationBreakdown(targetId, values) {
        const target = document.getElementById(targetId);
        if (!target) return;
        const entries = Object.entries(values || {});
        target.innerHTML = entries.length
            ? entries.map(function (entry) {
                return '<div class="location-breakdown-row"><span>' + escapeHtml(entry[0]) + '</span><strong>' + Number(entry[1]).toLocaleString() + '</strong></div>';
            }).join('')
            : '<div class="text-muted small">No breakdown data available.</div>';
    }

    function renderEvidenceStats(targetId, values) {
        const target = document.getElementById(targetId);
        if (!target) return;
        target.innerHTML = Object.entries(values || {}).map(function (entry) {
            return '<div class="analytics-summary-metric"><span>' + escapeHtml(entry[0]) + '</span><strong>' + escapeHtml(entry[1]) + '</strong></div>';
        }).join('');
    }

    function renderEvidenceActions(targetId, actions) {
        const target = document.getElementById(targetId);
        if (!target) return;
        target.innerHTML = (actions || []).map(function (action) { return '<li>' + escapeHtml(action) + '</li>'; }).join('');
    }

    function renderEvidenceReports(targetId, reports) {
        const target = document.getElementById(targetId);
        if (!target) return;
        target.innerHTML = (reports || []).length
            ? reports.map(function (report) {
                return '<tr><td><a href="' + reportsUrl + '?search=' + encodeURIComponent(report.title) + '"><strong>' + escapeHtml(report.title) + '</strong></a></td><td>' + escapeHtml(report.location) + '</td><td>' + escapeHtml(report.status) + '</td><td>' + escapeHtml(report.priority) + '</td><td>' + Number(report.age_days).toLocaleString() + ' days</td><td>' + escapeHtml(report.assigned_to) + '</td></tr>';
            }).join('')
            : '<tr><td colspan="6" class="text-center text-muted">No related report records available.</td></tr>';
    }

    let alertTrendChart = null;
    document.querySelectorAll('[data-alert-key]').forEach(function (button) {
        button.addEventListener('click', function () {
            const alert = decisionAlertDetails[button.dataset.alertKey];
            if (!alert) return;
            document.getElementById('alertEvidenceTitle').textContent = alert.title;
            document.getElementById('alertEvidenceWhy').textContent = alert.why;
            document.getElementById('alertEvidenceImpact').textContent = alert.impact;
            document.getElementById('alertEvidencePriority').textContent = alert.priority;
            renderEvidenceStats('alertEvidenceStats', alert.stats);
            renderEvidenceActions('alertEvidenceActions', alert.actions);
            renderEvidenceReports('alertEvidenceReports', alert.related_reports);
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('alertEvidenceModal'));
            modal.show();
            setTimeout(function () {
                if (alertTrendChart) alertTrendChart.destroy();
                const issueTrend = Array.isArray(alert.trend) ? alert.trend : [];
                alertTrendChart = new Chart(document.getElementById('alertEvidenceChart'), {
                    type: 'line',
                    data: {
                        labels: issueTrend.map(function (item) { return item.label; }),
                        datasets: [
                            { label: 'Issue reports', data: issueTrend.map(function (item) { return Number(item.reports); }), borderColor: '#1769e0', backgroundColor: 'rgba(23,105,224,.10)', fill: true, tension: .3 },
                            { label: 'Completed repairs', data: issueTrend.map(function (item) { return Number(item.repairs); }), borderColor: '#148a58', tension: .3 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
            }, 150);
        });
    });

    document.querySelectorAll('[data-location-detail]').forEach(function (button) {
        button.addEventListener('click', function () {
            const location = locationDetails[button.dataset.locationDetail];
            if (!location) return;

            const riskLevel = Number(location.risk_score) >= 12 ? 'High' : (Number(location.risk_score) >= 6 ? 'Medium' : 'Low');
            const riskColor = riskLevel === 'High' ? '#d93645' : (riskLevel === 'Medium' ? '#e99a00' : '#148a58');
            document.getElementById('locationDetailName').textContent = location.location;
            document.getElementById('locationDetailTotal').textContent = Number(location.total).toLocaleString();
            document.getElementById('locationDetailOpen').textContent = Number(location.open).toLocaleString();
            document.getElementById('locationDetailResolved').textContent = Number(location.resolved).toLocaleString();
            document.getElementById('locationDetailHazards').textContent = Number(location.hazards).toLocaleString();
            document.getElementById('locationDetailRisk').textContent = Number(location.risk_score).toLocaleString();
            document.getElementById('locationDetailRate').textContent = Number(location.resolution_rate).toFixed(1) + '%';
            document.getElementById('locationDetailCost').textContent = 'PHP ' + Number(location.cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('locationDetailPriority').textContent = riskLevel;
            document.getElementById('locationDetailPriority').style.color = riskColor;
            document.getElementById('locationDetailInterpretation').textContent = location.interpretation;
            document.getElementById('locationDetailCallout').style.setProperty('--location-risk-color', riskColor);
            document.getElementById('locationDetailReportsLink').href = reportsUrl + '?search=' + encodeURIComponent(location.location);

            renderLocationBreakdown('locationStatusBreakdown', location.status_breakdown);
            renderLocationBreakdown('locationCategoryBreakdown', location.category_breakdown);

            const recentBody = document.getElementById('locationRecentReports');
            const reports = Array.isArray(location.recent_reports) ? location.recent_reports : [];
            recentBody.innerHTML = reports.length
                ? reports.map(function (report) {
                    return '<tr>' +
                        '<td>' + escapeHtml(report.date || 'Unknown') + '</td>' +
                        '<td><strong>' + escapeHtml(report.title) + '</strong></td>' +
                        '<td>' + escapeHtml(report.category) + '</td>' +
                        '<td>' + escapeHtml(report.status) + '</td>' +
                        '<td>' + escapeHtml(report.priority) + '</td>' +
                        '<td>' + (report.is_hazard ? '<span class="text-danger fw-bold">Yes</span>' : 'No') + '</td>' +
                        '<td>PHP ' + Number(report.cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '</tr>';
                }).join('')
                : '<tr><td colspan="7" class="text-center text-muted">No recent reports available.</td></tr>';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('locationDetailModal')).show();
        });
    });

    document.querySelectorAll('[data-toggle-table]').forEach(function (button) {
        button.addEventListener('click', function () {
            const table = document.getElementById(button.dataset.toggleTable);
            if (!table) return;
            const visible = table.classList.toggle('is-visible');
            button.innerHTML = visible ? '<i class="fas fa-eye-slash"></i> Hide Data' : '<i class="fas fa-table"></i> Data';
        });
    });

    document.querySelectorAll('[data-download-chart]').forEach(function (button) {
        button.addEventListener('click', function () {
            const canvas = document.getElementById(button.dataset.downloadChart);
            if (!canvas) return;
            const link = document.createElement('a');
            link.download = (button.dataset.fileName || 'analytics-chart') + '.png';
            link.href = canvas.toDataURL('image/png', 1);
            link.click();
        });
    });

    document.querySelectorAll('[data-print-summary]').forEach(function (button) {
        button.addEventListener('click', function () {
            const content = document.getElementById(button.dataset.printSummary);
            if (!content) return;
            const printWindow = window.open('', '_blank', 'width=900,height=700');
            if (!printWindow) return;
            const dynamicTitle = button.dataset.dynamicTitle ? document.getElementById(button.dataset.dynamicTitle)?.textContent : '';
            const printTitle = dynamicTitle || button.dataset.title;
            printWindow.document.write('<!doctype html><html><head><title>' + escapeHtml(printTitle) + '</title><style>body{font-family:Arial,sans-serif;color:#23344d;padding:36px;line-height:1.55}h1{font-size:22px}h4{margin:20px 0 8px}.analytics-summary-metrics{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}.analytics-summary-metric,.summary-callout{border:1px solid #dce2ea;padding:10px}.analytics-summary-metric span{display:block;color:#66758a;font-size:10px;text-transform:uppercase}.analytics-summary-metric strong{display:block;font-size:16px}.summary-callout{margin-top:14px;border-left:4px solid #1769e0}.category-summary-chart{display:block;width:100%;max-height:340px;object-fit:contain}.category-summary-section-heading{display:flex;justify-content:space-between;align-items:center}.category-summary-table-wrap{overflow:visible}.category-summary-table{width:100%;border-collapse:collapse;font-size:10px}.category-summary-table th,.category-summary-table td{padding:6px;border:1px solid #dce2ea;text-align:left;vertical-align:top}.category-summary-table th{background:#f3f6f9}@media print{body{padding:0}.category-summary-section{break-inside:avoid}.category-summary-table tr{break-inside:avoid}}</style></head><body><h1>' + escapeHtml(printTitle) + '</h1>' + content.innerHTML + '</body></html>');
            printWindow.document.close();
            let printed = false;
            const printDocument = function () {
                if (printed) return;
                printed = true;
                printWindow.focus();
                printWindow.print();
            };
            printWindow.onload = printDocument;
            setTimeout(printDocument, 500);
        });
    });

    const riskBody = document.getElementById('locationRiskBody');
    const riskSearch = document.getElementById('locationRiskSearch');
    const riskFilter = document.getElementById('locationRiskFilter');

    function filterRiskRows() {
        if (!riskBody) return;
        const search = (riskSearch ? riskSearch.value : '').trim().toLowerCase();
        const level = riskFilter ? riskFilter.value : 'all';
        let rank = 1;
        Array.from(riskBody.rows).forEach(function (row) {
            const matchesSearch = !search || row.dataset.location.includes(search);
            const matchesLevel = level === 'all' || row.dataset.riskLevel === level;
            row.hidden = !(matchesSearch && matchesLevel);
            if (!row.hidden) row.cells[0].textContent = rank++;
        });
    }

    if (riskSearch) riskSearch.addEventListener('input', filterRiskRows);
    if (riskFilter) riskFilter.addEventListener('change', filterRiskRows);

    const sortDirections = {};
    document.querySelectorAll('.risk-sort').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!riskBody) return;
            const key = button.dataset.sort;
            sortDirections[key] = sortDirections[key] === 'asc' ? 'desc' : 'asc';
            const direction = sortDirections[key] === 'asc' ? 1 : -1;
            const rows = Array.from(riskBody.rows);
            rows.sort(function (left, right) {
                if (key === 'location') return left.dataset.location.localeCompare(right.dataset.location) * direction;
                return (Number(left.dataset[key]) - Number(right.dataset[key])) * direction;
            });
            rows.forEach(function (row) { riskBody.appendChild(row); });
            filterRiskRows();
        });
    });

    const exportRiskButton = document.getElementById('exportRiskCsv');
    if (exportRiskButton) {
        exportRiskButton.addEventListener('click', function () {
            if (!riskBody) return;
            const rows = [['Rank', 'Location', 'Total', 'Open', 'Hazards', 'Cost', 'Risk', 'Interpretation']];
            Array.from(riskBody.rows).filter(function (row) { return !row.hidden; }).forEach(function (row) {
                rows.push(Array.from(row.cells).map(function (cell) { return cell.innerText.trim(); }));
            });
            const csv = rows.map(function (row) {
                return row.map(function (value) { return '"' + String(value).replace(/"/g, '""') + '"'; }).join(',');
            }).join('\r\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'location-risk-ranking.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    }

    const printDssButton = document.getElementById('printDssReport');
    if (printDssButton) {
        printDssButton.addEventListener('click', function () {
            const report = document.getElementById('dssExecutiveReportContent');
            if (!report) return;
            const printableCharts = ['reportsTrendChart', 'statusChart', 'categoryTrendChart', 'priorityChart', 'agingChart', 'costChart']
                .map(function (id) {
                    const canvas = document.getElementById(id);
                    if (!canvas) return '';
                    const title = canvas.getAttribute('aria-label') || 'Analytics chart';
                    return '<figure><h3>' + escapeHtml(title) + '</h3><img src="' + canvas.toDataURL('image/png', 1) + '" alt="' + escapeHtml(title) + '"></figure>';
                }).join('');
            const printWindow = window.open('', '_blank', 'width=1100,height=850');
            if (!printWindow) return;
            const generatedBy = @json(auth()->user()->name ?? 'CampFix Administrator');
            const period = @json($executiveSummary['period']);
            const logo = @json(asset('Campfix/Images/logo.png'));
            printWindow.document.write(`<!doctype html><html><head><title>CampFix Executive Maintenance Analytics Report</title><style>
                @page { size: A4 portrait; margin: 20mm 12mm 18mm; @bottom-center { content: "Page " counter(page) " of " counter(pages); } }
                *{box-sizing:border-box} body{margin:0;color:#263a55;font:10pt/1.48 Arial,sans-serif}.print-header{position:fixed;top:-15mm;left:0;right:0;height:11mm;display:flex;align-items:center;gap:8px;border-bottom:1px solid #b9c4d1;font-size:8pt}.print-header img{width:25px;height:25px;object-fit:contain}.print-header strong{color:#10233f}.print-footer{position:fixed;bottom:-13mm;left:0;right:0;height:9mm;display:flex;justify-content:space-between;border-top:1px solid #b9c4d1;padding-top:4px;color:#66758a;font-size:7pt}.dss-report-cover{display:grid;grid-template-columns:58px 1fr auto;gap:12px;align-items:center;padding:14px;border:1px solid #ccd5df;border-top:5px solid #1769e0}.dss-report-cover img{width:50px;height:50px;object-fit:contain}.dss-report-cover span{color:#1769e0;font-size:8pt;font-weight:bold;text-transform:uppercase}.dss-report-cover h1{margin:2px 0;color:#10233f;font-size:18pt}.dss-report-cover p,.dss-report-cover dl{margin:0}.dss-report-cover dl{font-size:7.5pt}.dss-report-cover dl div{display:grid;grid-template-columns:62px 1fr;gap:5px}.dss-report-cover dt{color:#66758a}.dss-report-cover dd{margin:0;font-weight:bold}.dss-report-section{padding-top:12px;break-inside:auto}.dss-report-section h2{margin:0 0 7px;padding-bottom:4px;border-bottom:1px solid #ccd5df;color:#10233f;font-size:12pt}.dss-report-section h3{font-size:10pt;margin:10px 0 5px}.dss-report-section p{margin:0 0 7px}.dss-scorecards{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}.dss-scorecards>div{padding:7px;border:1px solid #d8e0e8;background:#f7f9fb}.dss-scorecards span,.dss-scorecards small{display:block;color:#66758a;font-size:7pt}.dss-scorecards strong{display:block;color:#10233f;font-size:12pt}.dss-insights{margin:0;padding-left:18px}.dss-report-table{width:100%;border-collapse:collapse;font-size:7.2pt}.dss-report-table thead{display:table-header-group}.dss-report-table tr{break-inside:avoid}.dss-report-table th,.dss-report-table td{padding:4px 5px;border:1px solid #ccd5df;text-align:left;vertical-align:top}.dss-report-table th{background:#edf2f7;text-transform:uppercase}.dss-recommendation{margin-bottom:6px;padding:7px 9px;border-left:3px solid #1769e0;background:#f7f9fb;break-inside:avoid}.dss-recommendation p{margin:3px 0 0}.dss-page-break{break-before:page}.dss-print-charts{break-before:page}.dss-print-charts h2{font-size:12pt;border-bottom:1px solid #ccd5df}.dss-print-charts .charts{display:grid;grid-template-columns:1fr 1fr;gap:10px}.dss-print-charts figure{margin:0;break-inside:avoid;border:1px solid #d8e0e8;padding:6px}.dss-print-charts h3{margin:0 0 4px;font-size:9pt}.dss-print-charts img{display:block;width:100%;height:190px;object-fit:contain}
            </style></head><body><div class="print-header"><img src="${logo}"><div><strong>CampFix Decision Support System</strong><br>Executive Maintenance Analytics Report | ${escapeHtml(period)}</div></div><div class="print-footer"><span>${escapeHtml(window.location.origin)} | Confidential</span><span>Generated automatically by CampFix DSS | ${escapeHtml(generatedBy)}</span></div>${report.outerHTML}<section class="dss-print-charts"><h2>Appendix B. Analytics Visualizations</h2><div class="charts">${printableCharts}</div></section></body></html>`);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function () { printWindow.print(); }, 500);
        });
    }
});
</script>
@endsection
