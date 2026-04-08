@extends('layouts.app')

@section('header')
    <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
        <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Quotation Settings
    </h2>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            <!-- Tab Navigation (SHF dark tabs) -->
            <div class="shf-tabs">
                @php
                    $tabs = [
                        'company' => ['label' => 'Company', 'perm' => 'edit_company_info'],
                        'charges' => ['label' => 'IOM Stamp Paper', 'perm' => 'edit_charges'],
                        'bank-charges' => ['label' => 'Bank Charges', 'perm' => 'edit_charges'],
                        'gst' => ['label' => 'GST', 'perm' => 'edit_gst'],
                        'services' => ['label' => 'Services', 'perm' => 'edit_services'],
                        'tenures' => ['label' => 'Tenures', 'perm' => 'edit_tenures'],
                        'documents' => ['label' => 'Documents', 'perm' => 'edit_documents'],
                        'permissions' => ['label' => 'Permissions', 'perm' => 'manage_permissions'],
                    ];
                    $activeTab = request('tab', 'company');
                @endphp
                @foreach($tabs as $key => $info)
                    <button class="shf-tab{{ $activeTab === $key ? ' active' : '' }}" data-tab="{{ $key }}">
                        {{ $info['label'] }}
                    </button>
                @endforeach
            </div>

            <!-- Tab Content -->
            <div class="shf-card" style="border-top-left-radius: 0; border-top-right-radius: 0;">

                {{-- Company Details --}}
                <div class="settings-tab-pane p-4" id="tab-company"{!! $activeTab !== 'company' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.company') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Company Name</label>
                                <input type="text" name="companyName" class="shf-input" value="{{ $config['companyName'] }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Email</label>
                                <input type="email" name="companyEmail" class="shf-input" value="{{ $config['companyEmail'] }}" required>
                            </div>
                            <div class="col-12">
                                <label class="shf-form-label d-block mb-1">Address</label>
                                <input type="text" name="companyAddress" class="shf-input" value="{{ $config['companyAddress'] }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Phone</label>
                                <input type="text" name="companyPhone" class="shf-input" value="{{ $config['companyPhone'] }}" required>
                            </div>
                        </div>
                        @if(auth()->user()->hasPermission('edit_company_info'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Company Details
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Banks --}}
                <div class="settings-tab-pane p-4" id="tab-banks"{!! $activeTab !== 'banks' ? ' style="display:none;"' : '' !!}>
                    <p class="small mb-3" style="color: #6b7280;">Banks available for quotation selection. Managed in <a href="{{ route('loan-settings.index') }}#banks">Loan Settings → Banks</a>.</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($loanBanks as $bankName)
                            <span class="shf-badge shf-badge-blue" style="font-size:0.8rem;padding:4px 12px;">{{ $bankName }}</span>
                        @endforeach
                        @if(empty($loanBanks))
                            <small class="text-muted">No banks configured. Add banks in Loan Settings.</small>
                        @endif
                    </div>
                </div>

                {{-- IOM Stamp Paper Charges --}}
                <div class="settings-tab-pane p-4" id="tab-charges"{!! $activeTab !== 'charges' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.charges') }}">
                        @csrf
                        <p class="small mb-3" style="color: #6b7280;">IOM Stamp Paper charge structure.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="shf-form-label d-block mb-1">Threshold Amount</label>
                                <input type="number" name="iomCharges[thresholdAmount]" class="shf-input" value="{{ $config['iomCharges']['thresholdAmount'] }}" required>
                                <p class="mt-1" style="font-size:0.75rem;color:#9ca3af;">Loan amount up to which fixed charge applies</p>
                            </div>
                            <div class="col-md-4">
                                <label class="shf-form-label d-block mb-1">Fixed Charge (up to threshold)</label>
                                <input type="number" name="iomCharges[fixedCharge]" class="shf-input" value="{{ $config['iomCharges']['fixedCharge'] }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="shf-form-label d-block mb-1">Percentage (above threshold)</label>
                                <input type="number" name="iomCharges[percentageAbove]" step="0.01" class="shf-input" value="{{ $config['iomCharges']['percentageAbove'] }}" required>
                                <p class="mt-1" style="font-size:0.75rem;color:#9ca3af;">e.g. 0.35 means 0.35% of loan amount</p>
                            </div>
                        </div>
                        @if(auth()->user()->hasPermission('edit_charges'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save IOM Stamp Paper Charges
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Bank Charges --}}
                <div class="settings-tab-pane p-4" id="tab-bank-charges"{!! $activeTab !== 'bank-charges' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.bank-charges') }}" id="bankChargesForm">
                        @csrf
                        <p class="small mb-3" style="color: #6b7280;">Default charges per bank (used when generating quotations).</p>

                        <div class="table-responsive">
                            <table class="table table-hover small">
                                <thead>
                                    <tr>
                                        <th>Bank</th>
                                        <th>PF Charge (%)</th>
                                        <th>Admin Charges</th>
                                        <th>Stamp Paper & Notary</th>
                                        <th>Registration Fee</th>
                                        <th>Advocate Fees</th>
                                        <th>TC Report Charges</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bankChargesBody"></tbody>
                            </table>
                        </div>

                        @if(auth()->user()->hasPermission('edit_charges'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Bank Charges
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- GST --}}
                <div class="settings-tab-pane p-4" id="tab-gst"{!! $activeTab !== 'gst' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.gst') }}">
                        @csrf
                        <div style="max-width: 20rem;">
                            <label class="shf-form-label d-block mb-1">GST Percentage (%)</label>
                            <input type="number" name="gstPercent" step="0.01" class="shf-input" value="{{ $config['gstPercent'] }}" required>
                            <p class="mt-1" style="font-size:0.75rem;color:#9ca3af;">Applied on PF & Admin charges. e.g. 18 for 18%</p>
                        </div>
                        @if(auth()->user()->hasPermission('edit_gst'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save GST
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Services --}}
                <div class="settings-tab-pane p-4" id="tab-services"{!! $activeTab !== 'services' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.services') }}">
                        @csrf
                        <div>
                            <label class="shf-form-label d-block mb-1">Our Services (shown in PDF footer)</label>
                            <textarea name="ourServices" rows="4" class="shf-input" style="resize: vertical; min-height: 80px;">{{ $config['ourServices'] }}</textarea>
                            <p class="mt-1" style="font-size:0.75rem;color:#9ca3af;">Comma-separated list of services displayed in every PDF footer.</p>
                        </div>
                        @if(auth()->user()->hasPermission('edit_services'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Services
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Tenures --}}
                <div class="settings-tab-pane p-4" id="tab-tenures"{!! $activeTab !== 'tenures' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.tenures') }}" id="tenuresForm">
                        @csrf
                        <p class="small mb-3" style="color: #6b7280;">EMI tenure options (in years) for quotation generation.</p>

                        <div class="d-flex flex-wrap gap-2 mb-3" id="tenureTagsContainer"></div>

                        <div class="d-flex align-items-center gap-2" style="max-width: 20rem;">
                            <input type="number" id="newTenureInput" min="1" max="50" placeholder="e.g. 25" class="shf-input flex-grow-1">
                            <button type="button" id="addTenureBtn" class="btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                        </div>

                        @if(auth()->user()->hasPermission('edit_tenures'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Tenures
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Documents --}}
                <div class="settings-tab-pane p-4" id="tab-documents"{!! $activeTab !== 'documents' ? ' style="display:none;"' : '' !!}>
                    <form method="POST" action="{{ route('settings.documents') }}" id="documentsForm">
                        @csrf
                        <p class="small mb-3" style="color: #6b7280;">Required documents per business type (bilingual: English + Gujarati).</p>

                        <!-- Sub-tabs for document types -->
                        <div class="d-flex gap-1 mb-3" style="border-bottom: 1px solid var(--border);">
                            @foreach(['proprietor' => 'Proprietor', 'partnership_llp' => 'Partnership / LLP', 'pvt_ltd' => 'PVT LTD', 'salaried' => 'Salaried'] as $docType => $docLabel)
                                <button type="button" class="doc-sub-tab small fw-semibold" data-doc-type="{{ $docType }}" style="padding:8px 12px;border:none;border-bottom:2px solid transparent;background:transparent;color:#6b7280;border-radius:4px 4px 0 0;cursor:pointer;">
                                    {{ $docLabel }}
                                </button>
                            @endforeach
                        </div>

                        @foreach(['proprietor', 'partnership_llp', 'pvt_ltd', 'salaried'] as $docType)
                            <div class="doc-type-pane" id="docPane-{{ $docType }}" style="display:none;">
                                <div class="d-flex flex-column gap-2 mb-3" id="docList-{{ $docType }}"></div>

                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" class="shf-input flex-grow-1 newDocEn" placeholder="Document name (English)">
                                    <input type="text" class="shf-input flex-grow-1 newDocGu" placeholder="દસ્તાવેજ નામ (Gujarati)">
                                    <button type="button" class="btn-accent-sm addDocBtn" data-doc-type="{{ $docType }}"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                                </div>
                            </div>
                        @endforeach

                        @if(auth()->user()->hasPermission('edit_documents'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Documents
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Permissions Tab --}}
                @if(auth()->user()->hasPermission('manage_permissions'))
                    <div class="settings-tab-pane p-4" id="tab-permissions"{!! $activeTab !== 'permissions' ? ' style="display:none;"' : '' !!}>
                        <p class="text-muted mb-3">Manage role-based permission assignments. <a href="{{ route('permissions.index') }}" class="btn-accent-sm">
                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Open Permission Manager
                        </a></p>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(function () {
        // ============================================================
        //  TAB SWITCHING
        // ============================================================
        $('.shf-tabs').on('click', '.shf-tab', function () {
            var tab = $(this).data('tab');
            $('.shf-tab').removeClass('active');
            $(this).addClass('active');
            $('.settings-tab-pane').hide();
            $('#tab-' + tab).show();
            history.replaceState(null, '', '#' + tab);
        });
        // Restore tab from URL hash on page load
        var hash = window.location.hash.substring(1);
        if (hash && $('#tab-' + hash).length) {
            $('.shf-tab').removeClass('active');
            $('.shf-tab[data-tab="' + hash + '"]').addClass('active');
            $('.settings-tab-pane').hide();
            $('#tab-' + hash).show();
        }

        // ============================================================
        //  TENURES MANAGER
        // ============================================================
        var tenures = @json($config['tenures'] ?? []);

        function renderTenureTags() {
            var html = '';
            $.each(tenures, function (idx, t) {
                html += '<span class="shf-tag" style="background:#f0fdf4;border-color:#86efac;color:#16a34a;">' +
                    '<span>' + t + ' Years</span>' +
                    '<input type="hidden" name="tenures[]" value="' + t + '">' +
                    '<button type="button" class="shf-tag-remove removeTenureBtn" data-idx="' + idx + '" style="background:#16a34a;">&times;</button>' +
                    '</span>';
            });
            $('#tenureTagsContainer').html(html);
        }
        renderTenureTags();

        $('#addTenureBtn').on('click', function () {
            var val = parseInt($('#newTenureInput').val());
            if (val && val > 0 && $.inArray(val, tenures) === -1) {
                tenures.push(val);
                tenures.sort(function (a, b) { return a - b; });
                renderTenureTags();
                $('#newTenureInput').val('');
            }
        });
        $('#newTenureInput').on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); $('#addTenureBtn').click(); }
        });
        $(document).on('click', '.removeTenureBtn', function () {
            tenures.splice($(this).data('idx'), 1);
            renderTenureTags();
        });
        // Auto-add pending tenure on form submit
        $('#tenuresForm').on('submit', function () {
            var val = parseInt($('#newTenureInput').val());
            if (val && val > 0 && $.inArray(val, tenures) === -1) {
                tenures.push(val);
                tenures.sort(function (a, b) { return a - b; });
                renderTenureTags();
            }
        });

        // ============================================================
        //  BANK CHARGES MANAGER
        // ============================================================
        // Bank charges: one row per DB bank, pre-filled from saved charges
        var savedCharges = {};
        @json($bankCharges->map(fn($c) => $c->toArray())->values()).forEach(function(c) {
            savedCharges[c.bank_name] = c;
        });
        var loanBanks = @json($loanBanks);

        function renderChargeRows() {
            var html = '';
            loanBanks.forEach(function(bankName, idx) {
                var c = savedCharges[bankName] || {};
                html += '<tr>' +
                    '<td><strong style="font-size:0.8rem;">' + bankName + '</strong>' +
                    '<input type="hidden" name="charges[' + idx + '][bank_name]" value="' + bankName + '">' +
                    '</td>' +
                    '<td><input type="number" name="charges[' + idx + '][pf]" value="' + (c.pf || 0) + '" step="0.01" class="shf-input small" style="width:6rem;"></td>' +
                    '<td><input type="number" name="charges[' + idx + '][admin]" value="' + (c.admin || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                    '<td><input type="number" name="charges[' + idx + '][stamp_notary]" value="' + (c.stamp_notary || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                    '<td><input type="number" name="charges[' + idx + '][registration_fee]" value="' + (c.registration_fee || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                    '<td><input type="number" name="charges[' + idx + '][advocate]" value="' + (c.advocate || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                    '<td><input type="number" name="charges[' + idx + '][tc]" value="' + (c.tc || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                    '<td>' +
                    '<input type="hidden" name="charges[' + idx + '][extra1_name]" value="' + (c.extra1_name || '') + '">' +
                    '<input type="hidden" name="charges[' + idx + '][extra1_amt]" value="' + (c.extra1_amt || 0) + '">' +
                    '<input type="hidden" name="charges[' + idx + '][extra2_name]" value="' + (c.extra2_name || '') + '">' +
                    '<input type="hidden" name="charges[' + idx + '][extra2_amt]" value="' + (c.extra2_amt || 0) + '">' +
                    '</td></tr>';
            });
            $('#bankChargesBody').html(html);
        }
        renderChargeRows();

        // ============================================================
        //  DOCUMENTS MANAGER
        // ============================================================
        var docs = {
            proprietor: { en: @json($config['documents_en']['proprietor'] ?? []), gu: @json($config['documents_gu']['proprietor'] ?? []) },
            partnership_llp: { en: @json($config['documents_en']['partnership_llp'] ?? []), gu: @json($config['documents_gu']['partnership_llp'] ?? []) },
            pvt_ltd: { en: @json($config['documents_en']['pvt_ltd'] ?? []), gu: @json($config['documents_gu']['pvt_ltd'] ?? []) },
            salaried: { en: @json($config['documents_en']['salaried'] ?? []), gu: @json($config['documents_gu']['salaried'] ?? []) }
        };
        var currentDocTab = 'proprietor';

        var sortableInstances = {};

        function renderDocList(type) {
            var html = '';
            $.each(docs[type].en, function (idx, enVal) {
                var guVal = docs[type].gu[idx] || '';
                html += '<div class="doc-sortable-item d-flex align-items-center gap-2 p-3 rounded" style="background:var(--bg);border:1px solid var(--border);">' +
                    '<span class="doc-drag-handle" style="cursor:grab;color:#9ca3af;font-size:1rem;padding:0 4px;" title="Drag to reorder">⠿</span>' +
                    '<span class="fw-bold" style="font-size:0.75rem;color:rgba(255,255,255,0.85);width:1.5rem;">' + (idx + 1) + '.</span>' +
                    '<input type="text" name="documents_en[' + type + '][]" value="' + $('<span>').text(enVal).html() + '" class="shf-input flex-grow-1 small" placeholder="English">' +
                    '<input type="text" name="documents_gu[' + type + '][]" value="' + $('<span>').text(guVal).html() + '" class="shf-input flex-grow-1 small" placeholder="Gujarati">' +
                    '<button type="button" class="btn-accent-sm removeDocBtn" data-type="' + type + '" data-idx="' + idx + '" style="background:linear-gradient(135deg,#dc2626,#ef4444);"><svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Remove</button>' +
                    '</div>';
            });
            $('#docList-' + type).html(html);
            initSortable(type);
        }

        function initSortable(type) {
            var el = document.getElementById('docList-' + type);
            if (!el) return;
            if (sortableInstances[type]) sortableInstances[type].destroy();
            sortableInstances[type] = new Sortable(el, {
                handle: '.doc-drag-handle',
                animation: 150,
                ghostClass: 'doc-sortable-ghost',
                onEnd: function () {
                    // Read new order from DOM inputs
                    var newEn = [], newGu = [];
                    $('#docList-' + type).find('.doc-sortable-item').each(function () {
                        var inputs = $(this).find('input[type="text"]');
                        newEn.push(inputs.eq(0).val());
                        newGu.push(inputs.eq(1).val());
                    });
                    docs[type].en = newEn;
                    docs[type].gu = newGu;
                    renderDocList(type);
                }
            });
        }

        function switchDocTab(type) {
            currentDocTab = type;
            $('.doc-sub-tab').css({ 'border-bottom-color': 'transparent', 'color': '#6b7280', 'background': 'transparent' });
            $('.doc-sub-tab[data-doc-type="' + type + '"]').css({ 'border-bottom-color': '#f15a29', 'color': '#f15a29', 'background': 'rgba(241,90,41,0.05)' });
            $('.doc-type-pane').hide();
            $('#docPane-' + type).show();
            renderDocList(type);
        }
        // Render ALL doc types on load so form always submits all types
        $.each(['proprietor', 'partnership_llp', 'pvt_ltd', 'salaried'], function (_, t) { renderDocList(t); });
        switchDocTab('proprietor');

        $(document).on('click', '.doc-sub-tab', function () {
            switchDocTab($(this).data('doc-type'));
        });

        $(document).on('click', '.addDocBtn', function () {
            var type = $(this).data('doc-type');
            var $pane = $('#docPane-' + type);
            var enVal = $.trim($pane.find('.newDocEn').val());
            var guVal = $.trim($pane.find('.newDocGu').val());
            if (enVal) {
                docs[type].en.push(enVal);
                docs[type].gu.push(guVal || enVal);
                $pane.find('.newDocEn').val('');
                $pane.find('.newDocGu').val('');
                renderDocList(type);
            }
        });

        $(document).on('click', '.removeDocBtn', function () {
            var type = $(this).data('type');
            var idx = $(this).data('idx');
            docs[type].en.splice(idx, 1);
            docs[type].gu.splice(idx, 1);
            renderDocList(type);
        });

        // Auto-add any pending document input values on form submit
        $('#documentsForm').on('submit', function () {
            $.each(['proprietor', 'partnership_llp', 'pvt_ltd', 'salaried'], function (_, type) {
                var $pane = $('#docPane-' + type);
                var enVal = $.trim($pane.find('.newDocEn').val());
                if (enVal) {
                    var guVal = $.trim($pane.find('.newDocGu').val());
                    docs[type].en.push(enVal);
                    docs[type].gu.push(guVal || enVal);
                    renderDocList(type);
                }
            });
        });
    });

    // Reset settings confirmation
    $('#formResetSettings').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Reset ALL settings?',
            text: 'This will reset all settings to their defaults. This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reset all',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    </script>
    @endpush
@endsection
