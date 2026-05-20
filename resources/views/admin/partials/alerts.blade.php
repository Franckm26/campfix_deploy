@if(isset($trendAlerts) && $trendAlerts->count() > 0)
<div class="analytics-card" id="alertsContainerWrap">
    @include('admin.partials.alerts', ['trendAlerts' => $trendAlerts])
</div>
@endif
