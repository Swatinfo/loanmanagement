@extends('layouts.app')
@section('title', 'Valuation — SHF')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white shf-page-title"><svg class="shf-header-icon" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg> Valuation — {{ $loan->loan_number }}</h2>
        <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg
                class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg> Back</a>
    </div>
@endsection

@php $v = $valuations->first(); @endphp

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5 shf-max-w-xl">

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php $isValuationLocked = !in_array($loan->status, ['active', 'on_hold']); @endphp
            @if ($isValuationLocked)
                <div class="alert alert-{{ $loan->status === 'rejected' ? 'danger' : 'success' }} mb-3">
                    <strong>Loan {{ ucfirst($loan->status) }}.</strong> Valuation details are read-only.
                </div>
            @endif

            <form method="POST" action="{{ route('loans.valuation.store', $loan) }}">
                @csrf
                <input type="hidden" name="valuation_type" value="property">
                <fieldset {{ $isValuationLocked ? 'disabled' : '' }}>

                <div class="shf-section mb-4">
                    <div class="shf-section-header"><span class="shf-section-title">Property Details</span></div>
                    <div class="shf-section-body">
                        <div class="row g-3">

                            {{-- Property Type --}}
                            <div class="col-sm-6">
                                <label class="shf-form-label">Property Type <span class="text-danger">*</span></label>
                                <select name="property_type" class="shf-input w-100" required>
                                    <option value="">-- Select --</option>
                                    @foreach (\App\Models\ValuationDetail::PROPERTY_TYPES as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ old('property_type', $v?->property_type) === $key ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Valuation Date --}}
                            <div class="col-sm-6">
                                <label class="shf-form-label">Valuation Date <span class="text-danger">*</span></label>
                                <input type="text" name="valuation_date" class="shf-input shf-datepicker-custom"
                                    data-min-date="{{ $v?->created_at ? $v->created_at->format('d/m/Y') : now()->subDays(3)->format('d/m/Y') }}"
                                    data-max-date="{{ $v?->created_at ? now()->format('d/m/Y') : now()->addDay()->format('d/m/Y') }}"
                                    value="{{ old('valuation_date', $v?->valuation_date?->format('d/m/Y') ?? now()->format('d/m/Y')) }}"
                                    required>
                            </div>

                            {{-- Address --}}
                            <div class="col-12">
                                <label class="shf-form-label">Property Address</label>
                                <textarea name="property_address" class="shf-input w-100" rows="2">{{ old('property_address', $v?->property_address) }}</textarea>
                            </div>

                            {{-- Landmark --}}
                            <div class="col-sm-6">
                                <label class="shf-form-label">Landmark <span class="text-danger">*</span></label>
                                <input type="text" name="landmark" class="shf-input w-100"
                                    value="{{ old('landmark', $v?->landmark) }}" placeholder="e.g. Near SBI Bank, Ring Road" required>
                            </div>

                            {{-- Latitude / Longitude --}}
                            <div class="col-sm-6">
                                <label class="shf-form-label">Latitude</label>
                                <input type="text" name="latitude" id="valLatitude" class="shf-input w-100"
                                    value="{{ old('latitude', $v?->latitude) }}" placeholder="e.g. 22.3039">
                            </div>
                            <div class="col-sm-6">
                                <label class="shf-form-label">Longitude</label>
                                <input type="text" name="longitude" id="valLongitude" class="shf-input w-100"
                                    value="{{ old('longitude', $v?->longitude) }}" placeholder="e.g. 70.8022">
                            </div>

                            {{-- Map preview --}}
                            <div class="col-12" id="mapPreview"
                                style="{{ old('latitude', $v?->latitude) && old('longitude', $v?->longitude) ? '' : 'display:none;' }}">
                                <div class="border rounded overflow-hidden" style="height: 200px;">
                                    <iframe id="mapFrame" width="100%" height="200" style="border:0;" loading="lazy"
                                        allowfullscreen
                                        src="{{ old('latitude', $v?->latitude) && old('longitude', $v?->longitude) ? 'https://maps.google.com/maps?q=' . old('latitude', $v?->latitude) . ',' . old('longitude', $v?->longitude) . '&z=15&output=embed' : '' }}">
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
                                <input type="number" name="land_area" id="landArea" class="shf-input w-100"
                                    value="{{ old('land_area', $v?->land_area) }}" placeholder="e.g. 1200" step="0.01" min="0" required>
                            </div>
                            <div class="col-sm-4">
                                <label class="shf-form-label">Land Rate (sq ft) <span class="text-danger">*</span></label>
                                <div class="input-group"><span class="input-group-text">₹</span>
                                    <input type="number" name="land_rate" id="landRate" class="shf-input w-100"
                                        value="{{ old('land_rate', $v?->land_rate) }}" min="0" step="0.01"
                                        required>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="shf-form-label">Land Valuation</label>
                                <div class="input-group"><span class="input-group-text">₹</span>
                                    <input type="text" id="landValuation" class="shf-input w-100"
                                        value="{{ $v?->land_valuation ? number_format($v->land_valuation) : '' }}"
                                        readonly class="shf-input-readonly">
                                </div>
                                <div class="shf-text-xs text-muted mt-1" id="landValWords"></div>
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
                                <input type="number" name="construction_area" id="constructionArea"
                                    class="shf-input w-100"
                                    value="{{ old('construction_area', $v?->construction_area) }}"
                                    placeholder="e.g. 800" step="0.01" min="0">
                            </div>
                            <div class="col-sm-4">
                                <label class="shf-form-label">Construction Rate (sq ft)</label>
                                <div class="input-group"><span class="input-group-text">₹</span>
                                    <input type="number" name="construction_rate" id="constructionRate"
                                        class="shf-input w-100"
                                        value="{{ old('construction_rate', $v?->construction_rate) }}" min="0"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="shf-form-label">Construction Valuation</label>
                                <div class="input-group"><span class="input-group-text">₹</span>
                                    <input type="text" id="constructionValuation" class="shf-input w-100"
                                        value="{{ $v?->construction_valuation ? number_format($v->construction_valuation) : '' }}"
                                        readonly class="shf-input-readonly">
                                </div>
                                <div class="shf-text-xs text-muted mt-1" id="constValWords"></div>
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
                                    <input type="text" id="finalValuation" class="shf-input w-100 fw-bold"
                                        value="{{ $v?->final_valuation ? number_format($v->final_valuation) : '' }}"
                                        readonly style="background:#f0fdf4;font-size:1.1rem;">
                                </div>
                                <div class="shf-text-xs text-muted mt-1" id="finalValWords"></div>
                            </div>
                            <div class="col-sm-3">
                                <label class="shf-form-label">Valuator Name <span class="text-danger">*</span></label>
                                <input type="text" name="valuator_name" class="shf-input w-100"
                                    value="{{ old('valuator_name', $v?->valuator_name) }}" required>
                            </div>
                            <div class="col-sm-3">
                                <label class="shf-form-label">Report Number</label>
                                <input type="text" name="valuator_report_number" class="shf-input w-100"
                                    value="{{ old('valuator_report_number', $v?->valuator_report_number) }}">
                            </div>
                            <div class="col-12">
                                <label class="shf-form-label">Notes</label>
                                <textarea name="notes" class="shf-input w-100" rows="2">{{ old('notes', $v?->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                </fieldset>
                <div class="shf-form-actions d-flex justify-content-end gap-3 mb-4">
                    <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline"><svg class="shf-icon-md"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg> {{ $isValuationLocked ? 'Back' : 'Cancel' }}</a>
                    @if(!$isValuationLocked)
                        <button type="submit" class="btn-accent" style="padding: 10px 24px;"><svg class="shf-icon-md"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg> Save Valuation</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('.shf-datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });
            $('.shf-datepicker-custom').each(function() {
                var opts = { format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true };
                if ($(this).data('min-date')) opts.startDate = $(this).data('min-date');
                if ($(this).data('max-date')) opts.endDate = $(this).data('max-date');
                $(this).datepicker(opts);
            });

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

                $('#landValuation').val(landVal ? '₹ ' + SHF.formatIndianNumber(landVal) : '');
                $('#constructionValuation').val(constVal ? '₹ ' + SHF.formatIndianNumber(constVal) : '');
                $('#finalValuation').val(finalVal ? '₹ ' + SHF.formatIndianNumber(finalVal) : '');

                $('#landValWords').text(landVal ? SHF.bilingualAmountWords(landVal) : '');
                $('#constValWords').text(constVal ? SHF.bilingualAmountWords(constVal) : '');
                $('#finalValWords').text(finalVal ? SHF.bilingualAmountWords(finalVal) : '');
            }

            $('#landArea, #landRate, #constructionArea, #constructionRate').on('input change', calculateValuations);
            calculateValuations(); // Init on load

            // Map preview from lat/lng
            function updateMap() {
                var lat = $('#valLatitude').val();
                var lng = $('#valLongitude').val();
                if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                    $('#mapFrame').attr('src', 'https://maps.google.com/maps?q=' + lat + ',' + lng +
                        '&z=15&output=embed');
                    $('#mapPreview').show();
                } else {
                    $('#mapPreview').hide();
                }
            }

            $('#valLatitude, #valLongitude').on('change blur', updateMap);

            // Client-side validation
            $('form').on('submit', function(e) {
                var valid = SHF.validateForm($(this), {
                    property_type:          { required: true, label: 'Property Type' },
                    valuation_date:         { required: true, dateFormat: 'd/m/Y', label: 'Valuation Date' },
                    property_address:       { maxlength: 1000, label: 'Property Address' },
                    landmark:               { required: true, maxlength: 255, label: 'Landmark' },
                    latitude:               { maxlength: 50, label: 'Latitude' },
                    longitude:              { maxlength: 50, label: 'Longitude' },
                    land_area:              { required: true, numeric: true, min: 0, label: 'Land Area' },
                    land_rate:              { required: true, numeric: true, min: 0, label: 'Land Rate' },
                    construction_area:      { numeric: true, min: 0, label: 'Construction Area' },
                    construction_rate:      { numeric: true, min: 0, label: 'Construction Rate' },
                    valuator_name:          { required: true, maxlength: 255, label: 'Valuator Name' },
                    valuator_report_number: { maxlength: 100, label: 'Report Number' },
                    notes:                  { maxlength: 5000, label: 'Notes' }
                });
                if (!valid) e.preventDefault();
            });
        });
    </script>
@endpush
