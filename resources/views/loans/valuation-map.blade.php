@extends('layouts.app')
@section('title', 'Valuation (Map) — SHF')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
@endpush

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white shf-page-title"><svg class="shf-header-icon" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg> Valuation (Map) — {{ $loan->loan_number }}</h2>
        <div class="d-flex gap-2">
            {{-- <a href="{{ route('loans.valuation', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white">
                <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg> Classic View
            </a> --}}
            <a href="{{ route('loans.stages', $loan) }}"
                class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg class="shf-icon-sm" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg> Back</a>
        </div>
    </div>
@endsection

@php $v = $valuations->first(); @endphp

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
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
                                    value="{{ old('landmark', $v?->landmark) }}"
                                    placeholder="e.g. Near SBI Bank, Ring Road" required>
                            </div>

                            {{-- Search Location --}}
                            <div class="col-sm-6">
                                <label class="shf-form-label">Search Location</label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <input type="text" id="locationSearch" class="shf-input w-100"
                                            placeholder="Search place, area, city..." autocomplete="off">
                                        <button type="button" id="btnSearch" class="btn-accent-outline btn-accent-sm"
                                            title="Search">
                                            <svg class="shf-icon-2xs" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="searchResults"
                                        class="position-absolute w-100 bg-white border rounded shadow-sm d-none"
                                        style="z-index:1000; max-height:200px; overflow-y:auto; top:100%;"></div>
                                </div>
                            </div>

                            {{-- Paste Coordinates --}}
                            <div class="col-sm-6">
                                <label class="shf-form-label">Paste Coordinates / Google Maps Link</label>
                                <div class="input-group">
                                    <input type="text" id="coordPaste" class="shf-input w-100"
                                        placeholder="e.g. 22.3039, 70.8022 or Google Maps URL">
                                    <button type="button" id="btnParseCoords" class="btn-accent-outline btn-accent-sm"
                                        title="Parse">
                                        <svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Latitude / Longitude --}}
                            <div class="col-sm-3">
                                <label class="shf-form-label">Latitude</label>
                                <input type="text" name="latitude" id="valLatitude" class="shf-input w-100"
                                    value="{{ old('latitude', $v?->latitude) }}" placeholder="Auto-filled" readonly>
                            </div>
                            <div class="col-sm-3">
                                <label class="shf-form-label">Longitude</label>
                                <input type="text" name="longitude" id="valLongitude" class="shf-input w-100"
                                    value="{{ old('longitude', $v?->longitude) }}" placeholder="Auto-filled" readonly>
                            </div>

                            {{-- Leaflet Map --}}
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="shf-form-label mb-0">
                                        <svg class="shf-icon-sm text-muted" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Click map to set location
                                    </label>
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <small id="coordDisplay" class="text-muted shf-text-xs"></small>
                                        <button type="button" id="btnCopyLocation"
                                            class="btn-accent-outline btn-accent-sm d-none" title="Copy coordinates">
                                            <svg class="shf-icon-2xs" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                            </svg>
                                            Copy
                                        </button>
                                        <button type="button" id="btnMyLocation"
                                            class="btn-accent-outline btn-accent-sm" title="Use my GPS location">
                                            <svg class="shf-icon-2xs" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            My Location
                                        </button>
                                    </div>
                                </div>
                                <div id="leafletMap"
                                    style="height: 300px; border-radius: 8px; border: 1px solid #dee2e6; z-index: 1;">
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
                                    value="{{ old('land_area', $v?->land_area) }}" placeholder="e.g. 1200"
                                    step="0.01" min="0" required>
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
                                    value="{{ old('construction_area', $v?->construction_area) }}" placeholder="e.g. 800"
                                    step="0.01" min="0">
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

                <div class="shf-form-actions d-flex justify-content-end gap-3 mb-4">
                    <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline"><svg class="shf-icon-md"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg> Cancel</a>
                    <button type="submit" class="btn-accent" style="padding: 10px 24px;"><svg class="shf-icon-md"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg> Save Valuation</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
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

            // ---- Leaflet Map ----
            // Fix Leaflet default icon paths for local vendor files
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: '{{ asset('vendor/leaflet/images/marker-icon-2x.png') }}',
                iconUrl: '{{ asset('vendor/leaflet/images/marker-icon.png') }}',
                shadowUrl: '{{ asset('vendor/leaflet/images/marker-shadow.png') }}'
            });

            // Default center: Rajkot, Gujarat
            var defaultLat = 22.3039;
            var defaultLng = 70.8022;
            var defaultZoom = 13;

            var initLat = parseFloat($('#valLatitude').val()) || defaultLat;
            var initLng = parseFloat($('#valLongitude').val()) || defaultLng;
            var hasInitCoords = $('#valLatitude').val() && $('#valLongitude').val();

            var map = L.map('leafletMap').setView([initLat, initLng], hasInitCoords ? 16 : defaultZoom);

            // Tile layers
            var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19
            });
            var satelliteLayer = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: '&copy; Esri',
                    maxZoom: 19
                });
            var hybridLabels = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    pane: 'overlayPane'
                });

            // Satellite + labels as default
            var satelliteHybrid = L.layerGroup([satelliteLayer, hybridLabels]);
            satelliteHybrid.addTo(map);

            // Layer switcher
            L.control.layers({
                'Satellite': satelliteHybrid,
                'Street': streetLayer
            }, null, {
                position: 'topright'
            }).addTo(map);

            var marker = null;
            var geocodeTimer = null;

            function reverseGeocode(lat, lng) {
                // Debounce to avoid spamming the API
                clearTimeout(geocodeTimer);
                geocodeTimer = setTimeout(function() {
                    $.getJSON('{{ route('api.reverse-geocode') }}', {
                            lat: lat,
                            lng: lng
                        })
                        .done(function(data) {
                            if (data.landmark) {
                                $('[name="landmark"]').val(data.landmark);
                            }
                            if (data.address) {
                                $('[name="property_address"]').val(data.address);
                            }
                        });
                }, 600);
            }

            function setMarker(lat, lng) {
                lat = parseFloat(lat);
                lng = parseFloat(lng);
                if (isNaN(lat) || isNaN(lng)) return;

                $('#valLatitude').val(lat.toFixed(6));
                $('#valLongitude').val(lng.toFixed(6));
                $('#coordDisplay').text(lat.toFixed(6) + ', ' + lng.toFixed(6));
                $('#btnCopyLocation').removeClass('d-none');

                // Reverse geocode to auto-fill landmark + address
                reverseGeocode(lat, lng);

                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);
                    marker.on('dragend', function(e) {
                        var pos = e.target.getLatLng();
                        setMarker(pos.lat, pos.lng);
                    });
                }
                map.setView([lat, lng], 19);
            }

            // Init marker if coordinates exist
            if (hasInitCoords) {
                setMarker(initLat, initLng);
            }

            // Click map to set marker
            map.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
            });

            // Parse coordinates from paste field
            function parseCoordinates(input) {
                input = input.trim();
                // Try "lat, lng" format
                var commaMatch = input.match(/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/);
                if (commaMatch) return {
                    lat: commaMatch[1],
                    lng: commaMatch[2]
                };

                // Try Google Maps URL formats
                // https://www.google.com/maps?q=22.3039,70.8022
                var qMatch = input.match(/[?&]q=(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/);
                if (qMatch) return {
                    lat: qMatch[1],
                    lng: qMatch[2]
                };

                // https://www.google.com/maps/@22.3039,70.8022,15z
                var atMatch = input.match(/@(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/);
                if (atMatch) return {
                    lat: atMatch[1],
                    lng: atMatch[2]
                };

                // https://maps.google.com/maps/place/.../@22.3039,70.8022
                var placeMatch = input.match(/place\/.*\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
                if (placeMatch) return {
                    lat: placeMatch[1],
                    lng: placeMatch[2]
                };

                // Google Maps short link with coordinates in path
                var pathMatch = input.match(/(-?\d{1,3}\.\d{3,})\s*,\s*(-?\d{1,3}\.\d{3,})/);
                if (pathMatch) return {
                    lat: pathMatch[1],
                    lng: pathMatch[2]
                };

                return null;
            }

            $('#btnParseCoords, #coordPaste').on('click keyup', function(e) {
                if (e.type === 'keyup' && e.which !== 13) return; // Only Enter key
                var val = $('#coordPaste').val();
                if (!val) return;
                var coords = parseCoordinates(val);
                if (coords) {
                    setMarker(coords.lat, coords.lng);
                    $('#coordPaste').val(coords.lat + ', ' + coords.lng).removeClass('is-invalid');
                } else {
                    Swal.fire('Error',
                        'Could not parse coordinates. Try "22.3039, 70.8022" or a Google Maps link.',
                        'error');
                    $('#coordPaste').addClass('is-invalid');
                }
            });

            // Copy Location to clipboard
            $('#btnCopyLocation').on('click', function() {
                var lat = $('#valLatitude').val();
                var lng = $('#valLongitude').val();
                if (!lat || !lng) return;
                var text = lat + ', ' + lng;
                navigator.clipboard.writeText(text).then(function() {
                    var $btn = $('#btnCopyLocation');
                    $btn.html(
                        '<svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!'
                        );
                    setTimeout(function() {
                        $btn.html(
                            '<svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg> Copy'
                            );
                    }, 1500);
                });
            });

            // Use My Location (GPS)
            $('#btnMyLocation').on('click', function() {
                var $btn = $(this);
                if (!navigator.geolocation) {
                    Swal.fire('Error', 'GPS is not supported by your browser.', 'error');
                    return;
                }
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Locating...');
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        setMarker(pos.coords.latitude, pos.coords.longitude);
                        $btn.prop('disabled', false).html(
                            '<svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> My Location'
                            );
                    },
                    function(err) {
                        Swal.fire('Error', 'Could not get your location: ' + err.message, 'error');
                        $btn.prop('disabled', false).html(
                            '<svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> My Location'
                            );
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    }
                );
            });

            // ---- Location Search ----
            var searchTimer = null;

            function doSearch() {
                var q = $('#locationSearch').val().trim();
                if (q.length < 3) {
                    $('#searchResults').addClass('d-none').empty();
                    return;
                }
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    $.getJSON('{{ route('api.search-location') }}', {
                        q: q
                    }).fail(function() {
                        $('#searchResults').html(
                            '<div class="p-2 text-muted shf-text-xs">Search failed. Try again.</div>'
                            ).removeClass('d-none');
                    }).done(function(data) {
                        var $results = $('#searchResults').empty();
                        if (!data.results || !data.results.length) {
                            $results.html(
                                '<div class="p-2 text-muted shf-text-xs">No results found</div>'
                                ).removeClass('d-none');
                            return;
                        }
                        data.results.forEach(function(r) {
                            var $item = $(
                                    '<div class="p-2 border-bottom shf-text-xs" style="cursor:pointer;"></div>'
                                    )
                                .text(r.name)
                                .on('click', function() {
                                    setMarker(r.lat, r.lng);
                                    $('#locationSearch').val(r.name);
                                    $results.addClass('d-none');
                                });
                            $item.on('mouseenter', function() {
                                $(this).css('background', '#f0f0f0');
                            });
                            $item.on('mouseleave', function() {
                                $(this).css('background', '');
                            });
                            $results.append($item);
                        });
                        $results.removeClass('d-none');
                    });
                }, 400);
            }

            $('#locationSearch').on('input', doSearch);
            $('#locationSearch').on('keydown', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    doSearch();
                }
            });
            $('#btnSearch').on('click', doSearch);
            // Hide results on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#locationSearch, #searchResults, #btnSearch').length) {
                    $('#searchResults').addClass('d-none');
                }
            });

            // ---- Valuation Calculations ----
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

            // ---- Client-side validation ----
            $('form').on('submit', function(e) {
                var valid = SHF.validateForm($(this), {
                    property_type: {
                        required: true,
                        label: 'Property Type'
                    },
                    valuation_date: {
                        required: true,
                        dateFormat: 'd/m/Y',
                        label: 'Valuation Date'
                    },
                    property_address: {
                        maxlength: 1000,
                        label: 'Property Address'
                    },
                    landmark: {
                        required: true,
                        maxlength: 255,
                        label: 'Landmark'
                    },
                    land_area: {
                        required: true,
                        numeric: true,
                        min: 0,
                        label: 'Land Area'
                    },
                    land_rate: {
                        required: true,
                        numeric: true,
                        min: 0,
                        label: 'Land Rate'
                    },
                    construction_area: {
                        numeric: true,
                        min: 0,
                        label: 'Construction Area'
                    },
                    construction_rate: {
                        numeric: true,
                        min: 0,
                        label: 'Construction Rate'
                    },
                    valuator_name: {
                        required: true,
                        maxlength: 255,
                        label: 'Valuator Name'
                    },
                    valuator_report_number: {
                        maxlength: 100,
                        label: 'Report Number'
                    },
                    notes: {
                        maxlength: 5000,
                        label: 'Notes'
                    }
                });
                if (!valid) e.preventDefault();
            });
        });
    </script>
@endpush
