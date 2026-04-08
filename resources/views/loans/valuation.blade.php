@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;"><svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> Valuation — {{ $loan->loan_number }}</h2>
        <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@php $v = $valuations->first(); @endphp

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 48rem;">

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('loans.valuation.store', $loan) }}">
            @csrf
            <input type="hidden" name="valuation_type" value="property">

            <div class="shf-section mb-4">
                <div class="shf-section-header"><span class="shf-section-title">Property Details</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">

                        {{-- Property Type --}}
                        <div class="col-sm-6">
                            <label class="shf-form-label">Property Type <span class="text-danger">*</span></label>
                            <select name="property_type" class="shf-input w-100" required>
                                <option value="">-- Select --</option>
                                @foreach(\App\Models\ValuationDetail::PROPERTY_TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ old('property_type', $v?->property_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Valuation Date --}}
                        <div class="col-sm-6">
                            <label class="shf-form-label">Valuation Date <span class="text-danger">*</span></label>
                            <input type="text" name="valuation_date" class="shf-input shf-datepicker" value="{{ old('valuation_date', $v?->valuation_date?->format('d/m/Y') ?? now()->format('d/m/Y')) }}" required>
                        </div>

                        {{-- Address --}}
                        <div class="col-12">
                            <label class="shf-form-label">Property Address</label>
                            <textarea name="property_address" class="shf-input w-100" rows="2">{{ old('property_address', $v?->property_address) }}</textarea>
                        </div>

                        {{-- Latitude / Longitude --}}
                        <div class="col-sm-6">
                            <label class="shf-form-label">Latitude</label>
                            <input type="text" name="latitude" id="valLatitude" class="shf-input w-100" value="{{ old('latitude', $v?->latitude) }}" placeholder="e.g. 22.3039">
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Longitude</label>
                            <input type="text" name="longitude" id="valLongitude" class="shf-input w-100" value="{{ old('longitude', $v?->longitude) }}" placeholder="e.g. 70.8022">
                        </div>

                        {{-- Map preview --}}
                        <div class="col-12" id="mapPreview" style="{{ (old('latitude', $v?->latitude) && old('longitude', $v?->longitude)) ? '' : 'display:none;' }}">
                            <div class="border rounded overflow-hidden" style="height: 200px;">
                                <iframe id="mapFrame" width="100%" height="200" style="border:0;" loading="lazy" allowfullscreen
                                    src="{{ (old('latitude', $v?->latitude) && old('longitude', $v?->longitude)) ? 'https://maps.google.com/maps?q=' . old('latitude', $v?->latitude) . ',' . old('longitude', $v?->longitude) . '&z=15&output=embed' : '' }}">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shf-section mb-4">
                <div class="shf-section-header"><span class="shf-section-title">Land Valuation</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="shf-form-label">Land Area (sq ft) <span class="text-danger">*</span></label>
                            <input type="text" name="land_area" id="landArea" class="shf-input w-100" value="{{ old('land_area', $v?->land_area) }}" placeholder="e.g. 1200" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="shf-form-label">Land Rate (per sq ft) <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text">₹</span>
                                <input type="number" name="land_rate" id="landRate" class="shf-input w-100" value="{{ old('land_rate', $v?->land_rate) }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="shf-form-label">Land Valuation</label>
                            <div class="input-group"><span class="input-group-text">₹</span>
                                <input type="text" id="landValuation" class="shf-input w-100" value="{{ $v?->land_valuation ? number_format($v->land_valuation) : '' }}" readonly style="background:#f8f9fa;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shf-section mb-4">
                <div class="shf-section-header"><span class="shf-section-title">Construction Valuation</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="shf-form-label">Construction Area (sq ft)</label>
                            <input type="text" name="construction_area" id="constructionArea" class="shf-input w-100" value="{{ old('construction_area', $v?->construction_area) }}" placeholder="e.g. 800">
                        </div>
                        <div class="col-sm-4">
                            <label class="shf-form-label">Construction Rate (per sq ft)</label>
                            <div class="input-group"><span class="input-group-text">₹</span>
                                <input type="number" name="construction_rate" id="constructionRate" class="shf-input w-100" value="{{ old('construction_rate', $v?->construction_rate) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="shf-form-label">Construction Valuation</label>
                            <div class="input-group"><span class="input-group-text">₹</span>
                                <input type="text" id="constructionValuation" class="shf-input w-100" value="{{ $v?->construction_valuation ? number_format($v->construction_valuation) : '' }}" readonly style="background:#f8f9fa;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shf-section mb-4">
                <div class="shf-section-header"><span class="shf-section-title">Final Valuation</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="shf-form-label fw-bold">Total Valuation Amount</label>
                            <div class="input-group input-group-lg"><span class="input-group-text">₹</span>
                                <input type="text" id="finalValuation" class="shf-input w-100 fw-bold" value="{{ $v?->final_valuation ? number_format($v->final_valuation) : '' }}" readonly style="background:#f0fdf4;font-size:1.1rem;">
                            </div>
                            <small class="text-muted">Land Valuation + Construction Valuation</small>
                        </div>
                        <div class="col-sm-3">
                            <label class="shf-form-label">Valuator Name <span class="text-danger">*</span></label>
                            <input type="text" name="valuator_name" class="shf-input w-100" value="{{ old('valuator_name', $v?->valuator_name) }}" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="shf-form-label">Report Number</label>
                            <input type="text" name="valuator_report_number" class="shf-input w-100" value="{{ old('valuator_report_number', $v?->valuator_report_number) }}">
                        </div>
                        <div class="col-12">
                            <label class="shf-form-label">Notes</label>
                            <textarea name="notes" class="shf-input w-100" rows="2">{{ old('notes', $v?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mb-4">
                <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                <button type="submit" class="btn-accent" style="padding: 10px 24px;"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Valuation</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('.shf-datepicker').datepicker({ format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true });

    // Auto-calculate valuations
    function parseNum(val) {
        return parseFloat(String(val).replace(/[^0-9.]/g, '')) || 0;
    }

    function calculateValuations() {
        var landArea = parseNum($('#landArea').val());
        var landRate = parseNum($('#landRate').val());
        var landVal = Math.round(landArea * landRate);

        var constArea = parseNum($('#constructionArea').val());
        var constRate = parseNum($('#constructionRate').val());
        var constVal = Math.round(constArea * constRate);

        var finalVal = landVal + constVal;

        $('#landValuation').val(landVal ? landVal.toLocaleString('en-IN') : '');
        $('#constructionValuation').val(constVal ? constVal.toLocaleString('en-IN') : '');
        $('#finalValuation').val(finalVal ? finalVal.toLocaleString('en-IN') : '');
    }

    $('#landArea, #landRate, #constructionArea, #constructionRate').on('input change', calculateValuations);

    // Map preview from lat/lng
    function updateMap() {
        var lat = $('#valLatitude').val();
        var lng = $('#valLongitude').val();
        if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
            $('#mapFrame').attr('src', 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=15&output=embed');
            $('#mapPreview').show();
        } else {
            $('#mapPreview').hide();
        }
    }

    $('#valLatitude, #valLongitude').on('change blur', updateMap);
});
</script>
@endpush
