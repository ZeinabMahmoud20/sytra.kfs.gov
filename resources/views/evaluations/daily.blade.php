@extends('layouts.app')

@section('content')
    @livewire('evaluations.daily-evaluation-form')
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fp = flatpickr('#eval-date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            locale: 'ar',
            defaultDate: document.getElementById('eval-date').value,
            onChange: function (selectedDates, dateStr) {
                @this.set('date', dateStr);
            }
        });
    });
</script>
@endpush
