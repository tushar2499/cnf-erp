@push('styles')
<style>
.report-table th, .report-table td { font-size: .8rem; padding: .35rem .5rem; vertical-align: middle; }
.report-table thead th { background: #e9ecef; font-weight: 600; }
.report-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
.report-table tr.group-row td { background: #f3f6f9; font-weight: 600; }
.report-table tr.subgroup-row td { background: #fafbfc; font-weight: 600; }
.report-table tr.total-row td { font-weight: 700; border-top: 2px solid #6c757d; }
.report-title { text-align: center; margin-bottom: 1rem; }
.report-title h5 { margin: 0; font-weight: 700; }
.report-title .period { font-size: .85rem; color: #6c757d; }
.signatures { margin-top: 4rem; }
.sign-line { border-top: 1px solid #6c757d; margin: 0 1.5rem; padding-top: .3rem; }
@media print {
    .top-navbar, .sidebar, .sidebar-backdrop, .no-print, .dataTables_filter, .dataTables_length, .dt-buttons { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card, .report-card { border: 0 !important; box-shadow: none !important; }
    body { background: #fff !important; }
    .report-table th, .report-table td { font-size: 10px; }
}
</style>
@endpush
