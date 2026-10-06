@extends('layouts.app')

@section('title', 'التقييم اليومي للجهات')
@section('page-title', 'التقييم اليومي للجهات')

@section('content')
    @livewire('evaluations.daily-evaluation-form')
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<style>
    .flatpickr-input.input,
    input.flatpickr-input {
        display: block !important;
        width: 130px !important;
        color: #111827 !important;
        background: #fff !important;
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
<script>
    function initEvalDatePicker() {
        var el = document.getElementById('eval-date');
        if (!el) return;

        // لو flatpickr مربوط وما زال موجوداً في الصفحة، لا تفعل شيئاً
        if (el._flatpickr) {
            var alt = el._flatpickr.altInput;
            if (alt && document.body.contains(alt)) return;
            el._flatpickr.destroy();
        }

        flatpickr(el, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            locale: 'ar',
            defaultDate: el.value || 'today',
            onChange: function (selectedDates, dateStr) {
                if (!dateStr) return;
                Livewire.first().set('date', dateStr);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initEvalDatePicker);
    document.addEventListener('livewire:initialized', function () {   // Livewire v3
        initEvalDatePicker();
        Livewire.hook('commit', function (ctx) {
            ctx.succeed(function () { setTimeout(initEvalDatePicker, 0); });
        });
    });
    document.addEventListener('livewire:load', function () {          // Livewire v2
        initEvalDatePicker();
        Livewire.hook('message.processed', function () {
            setTimeout(initEvalDatePicker, 0);
        });
    });
</script>
@endpush