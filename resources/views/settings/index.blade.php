@extends('layouts.app')
@section('title', 'Settings — SHF')

@section('header')
    <h2 class="font-display fw-semibold text-white shf-page-title">
        <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
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
                        'dvr' => ['label' => 'DVR', 'perm' => 'view_settings'],
                        'quotation-reasons' => ['label' => 'Quotation Reasons', 'perm' => 'view_settings'],
                        'permissions' => ['label' => 'Permissions', 'perm' => 'manage_permissions'],
                    ];
                    $activeTab = request('tab', 'company');
                @endphp
                @foreach ($tabs as $key => $info)
                    <button class="shf-tab{{ $activeTab === $key ? ' active' : '' }}" data-tab="{{ $key }}">
                        {{ $info['label'] }}
                    </button>
                @endforeach
            </div>

            <!-- Tab Content -->
            <div class="shf-card shf-section-no-top-radius">

                {{-- Company Details --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-company"{!! $activeTab !== 'company' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.company') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Company Name</label>
                                <input type="text" name="companyName" class="shf-input"
                                    value="{{ $config['companyName'] }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Email</label>
                                <input type="email" name="companyEmail" class="shf-input"
                                    value="{{ $config['companyEmail'] }}" required>
                            </div>
                            <div class="col-12">
                                <label class="shf-form-label d-block mb-1">Address</label>
                                <input type="text" name="companyAddress" class="shf-input"
                                    value="{{ $config['companyAddress'] }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Phone</label>
                                <input type="text" name="companyPhone" class="shf-input"
                                    value="{{ $config['companyPhone'] }}" required>
                            </div>
                        </div>
                        @if (auth()->user()->hasPermission('edit_company_info'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Company Details
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Banks --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-banks"{!! $activeTab !== 'banks' ? '' : '' !!}>
                    <p class="small mb-3 shf-text-gray">Banks available for quotation selection. Managed in <a
                            href="{{ route('loan-settings.index') }}#banks">Loan Settings → Banks</a>.</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($loanBanks as $bankName)
                            <span class="shf-badge shf-badge-blue"
                                style="font-size:0.8rem;padding:4px 12px;">{{ $bankName }}</span>
                        @endforeach
                        @if (empty($loanBanks))
                            <small class="text-muted">No banks configured. Add banks in Loan Settings.</small>
                        @endif
                    </div>
                </div>

                {{-- IOM Stamp Paper Charges --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-charges"{!! $activeTab !== 'charges' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.charges') }}">
                        @csrf
                        <p class="small mb-3 shf-text-gray">IOM Stamp Paper charge structure.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="shf-form-label d-block mb-1">Threshold Amount</label>
                                <input type="number" name="iomCharges[thresholdAmount]" class="shf-input"
                                    value="{{ $config['iomCharges']['thresholdAmount'] }}" required>
                                <p class="mt-1 shf-text-xs shf-text-gray-light">Loan amount up to which fixed charge applies
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="shf-form-label d-block mb-1">Fixed Charge (up to threshold)</label>
                                <input type="number" name="iomCharges[fixedCharge]" class="shf-input"
                                    value="{{ $config['iomCharges']['fixedCharge'] }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="shf-form-label d-block mb-1">Percentage (above threshold)</label>
                                <input type="number" name="iomCharges[percentageAbove]" step="0.01" class="shf-input"
                                    value="{{ $config['iomCharges']['percentageAbove'] }}" required>
                                <p class="mt-1 shf-text-xs shf-text-gray-light">e.g. 0.35 means 0.35% of loan amount</p>
                            </div>
                        </div>
                        @if (auth()->user()->hasPermission('edit_charges'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save IOM Stamp Paper Charges
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Bank Charges --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-bank-charges"{!! $activeTab !== 'bank-charges' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.bank-charges') }}" id="bankChargesForm">
                        @csrf
                        <p class="small mb-3 shf-text-gray">Default charges per bank (used when generating quotations).</p>

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

                        @if (auth()->user()->hasPermission('edit_charges'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Bank Charges
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- GST --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-gst"{!! $activeTab !== 'gst' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.gst') }}">
                        @csrf
                        <div class="shf-max-w-20">
                            <label class="shf-form-label d-block mb-1">GST Percentage (%)</label>
                            <input type="number" name="gstPercent" step="0.01" class="shf-input"
                                value="{{ $config['gstPercent'] }}" required>
                            <p class="mt-1 shf-text-xs shf-text-gray-light">Applied on PF & Admin charges. e.g. 18 for 18%
                            </p>
                        </div>
                        @if (auth()->user()->hasPermission('edit_gst'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save GST
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Services --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-services"{!! $activeTab !== 'services' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.services') }}">
                        @csrf
                        <div>
                            <label class="shf-form-label d-block mb-1">Our Services (shown in PDF footer)</label>
                            <textarea name="ourServices" rows="4" class="shf-input" style="resize: vertical; min-height: 80px;">{{ $config['ourServices'] }}</textarea>
                            <p class="mt-1 shf-text-xs shf-text-gray-light">Comma-separated list of services displayed in
                                every PDF footer.</p>
                        </div>
                        @if (auth()->user()->hasPermission('edit_services'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Services
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Tenures --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-tenures"{!! $activeTab !== 'tenures' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.tenures') }}" id="tenuresForm">
                        @csrf
                        <p class="small mb-3 shf-text-gray">EMI tenure options (in years) for quotation generation.</p>

                        <div class="d-flex flex-wrap gap-2 mb-3" id="tenureTagsContainer"></div>

                        <div class="d-flex align-items-center gap-2 shf-max-w-20">
                            <input type="number" id="newTenureInput" min="1" max="50"
                                placeholder="e.g. 25" class="shf-input flex-grow-1">
                            <button type="button" id="addTenureBtn" class="btn-accent-sm"><svg class="shf-icon-sm"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg> Add</button>
                        </div>

                        @if (auth()->user()->hasPermission('edit_tenures'))
                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Tenures
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Documents --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-documents"{!! $activeTab !== 'documents' ? '' : '' !!}>
                    <form method="POST" action="{{ route('settings.documents') }}" id="documentsForm">
                        @csrf
                        <p class="small mb-3 shf-text-gray">Required documents per business type (bilingual: English +
                            Gujarati).</p>

                        <!-- Sub-tabs for document types -->
                        <div class="d-flex flex-wrap gap-1 mb-3" style="border-bottom: 1px solid var(--border);">
                            @foreach (['proprietor' => 'Proprietor', 'partnership_llp' => 'Partnership / LLP', 'pvt_ltd' => 'PVT LTD', 'salaried' => 'Salaried'] as $docType => $docLabel)
                                <button type="button" class="doc-sub-tab small fw-semibold"
                                    data-doc-type="{{ $docType }}"
                                    style="padding:8px 12px;border:none;border-bottom:2px solid transparent;background:transparent;color:#6b7280;border-radius:4px 4px 0 0;cursor:pointer;">
                                    {{ $docLabel }}
                                </button>
                            @endforeach
                        </div>

                        @foreach (['proprietor', 'partnership_llp', 'pvt_ltd', 'salaried'] as $docType)
                            <div class="doc-type-pane shf-collapse-hidden" id="docPane-{{ $docType }}">
                                <div class="d-flex flex-column gap-2 mb-3" id="docList-{{ $docType }}"></div>

                                <div class="shf-reason-add-row row g-2 align-items-end">
                                    <div class="col-12 col-md-6 col-lg-5">
                                        <label class="shf-form-label d-block d-lg-none mb-1">English</label>
                                        <input type="text" class="shf-input w-100 newDocEn"
                                            placeholder="Document name (English)">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-5">
                                        <label class="shf-form-label d-block d-lg-none mb-1">Gujarati</label>
                                        <input type="text" class="shf-input w-100 newDocGu"
                                            placeholder="દસ્તાવેજ નામ (Gujarati)">
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <button type="button" class="btn-accent-sm addDocBtn w-100 justify-content-center"
                                            data-doc-type="{{ $docType }}"><svg class="shf-icon-sm" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg> Add</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if (auth()->user()->hasPermission('edit_documents'))
                            <div class="shf-form-actions d-flex justify-content-end gap-3 mt-4 mb-2">
                                <button type="submit" class="btn-accent">
                                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Documents
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- DVR Settings --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-dvr">
                    <p class="small mb-3 shf-text-gray">Configure contact types and visit purposes for Daily Visit Reports (DVR).</p>

                    {{-- Contact Types --}}
                    <div class="mb-4">
                        <h6 class="font-display fw-semibold mb-3">Contact Types / સંપર્ક પ્રકાર</h6>
                        <form method="POST" action="{{ route('settings.dvr-contact-types') }}" id="dvrContactTypesForm">
                            @csrf
                            <div class="d-flex flex-column gap-2 mb-3" id="dvrContactTypesList"></div>
                            <div class="shf-reason-add-row row g-2 align-items-end">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Key</label>
                                    <input type="text" id="newContactTypeKey" class="shf-input w-100" placeholder="key (e.g. architect)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="shf-form-label d-block d-lg-none mb-1">English</label>
                                    <input type="text" id="newContactTypeEn" class="shf-input w-100" placeholder="Label (English)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Gujarati</label>
                                    <input type="text" id="newContactTypeGu" class="shf-input w-100" placeholder="લેબલ (Gujarati)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <button type="button" id="addContactTypeBtn" class="btn-accent-sm w-100 justify-content-center">
                                        <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <hr class="my-4" style="border-color:var(--border);">

                    {{-- Purposes --}}
                    <div>
                        <h6 class="font-display fw-semibold mb-3">Visit Purposes / મુલાકાત હેતુ</h6>
                        <form method="POST" action="{{ route('settings.dvr-purposes') }}" id="dvrPurposesForm">
                            @csrf
                            <div class="d-flex flex-column gap-2 mb-3" id="dvrPurposesList"></div>
                            <div class="shf-reason-add-row row g-2 align-items-end">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Key</label>
                                    <input type="text" id="newPurposeKey" class="shf-input w-100" placeholder="key (e.g. site_visit)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="shf-form-label d-block d-lg-none mb-1">English</label>
                                    <input type="text" id="newPurposeEn" class="shf-input w-100" placeholder="Label (English)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Gujarati</label>
                                    <input type="text" id="newPurposeGu" class="shf-input w-100" placeholder="લેબલ (Gujarati)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <button type="button" id="addPurposeBtn" class="btn-accent-sm w-100 justify-content-center">
                                        <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if (auth()->user()->hasPermission('view_settings'))
                        <div class="shf-form-actions d-flex flex-wrap justify-content-end gap-3 mt-4 mb-2">
                            <button type="submit" form="dvrContactTypesForm" class="btn-accent">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Contact Types
                            </button>
                            <button type="submit" form="dvrPurposesForm" class="btn-accent">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Purposes
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Quotation Hold/Cancel Reasons --}}
                <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-quotation-reasons">
                    <p class="small mb-3 shf-text-gray">Configure the reasons users can choose when putting a quotation on hold or cancelling it.</p>

                    <p class="small mb-3 shf-text-gray-light">Each reason has an optional <strong>Group</strong>. Groups become <code>&lt;optgroup&gt;</code> headings in the Hold / Cancel modal dropdowns, so long lists stay scannable. Leave the group blank to land in "Other".</p>

                    {{-- Hold Reasons --}}
                    <div class="mb-4">
                        <h6 class="font-display fw-semibold mb-3">Hold Reasons / હોલ્ડ કારણો</h6>
                        <form method="POST" action="{{ route('settings.quotation-hold-reasons') }}" id="quotationHoldReasonsForm">
                            @csrf
                            <div class="d-flex flex-column gap-2 mb-3" id="quotationHoldReasonsList"></div>
                            <div class="shf-reason-add-row row g-2 align-items-end">
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Key</label>
                                    <input type="text" id="newHoldReasonKey" class="shf-input w-100" placeholder="key (e.g. rate_too_high)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">English</label>
                                    <input type="text" id="newHoldReasonEn" class="shf-input w-100" placeholder="Label (English)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Gujarati</label>
                                    <input type="text" id="newHoldReasonGu" class="shf-input w-100" placeholder="લેબલ (Gujarati)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Group</label>
                                    <input type="text" id="newHoldReasonGroup" class="shf-input w-100" placeholder="Group (e.g. Documents)" list="holdGroupOptions">
                                    <datalist id="holdGroupOptions"></datalist>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <button type="button" id="addHoldReasonBtn" class="btn-accent-sm w-100 justify-content-center">
                                        <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg> Add
                                    </button>
                                </div>
                            </div>
                            @if (auth()->user()->hasPermission('view_settings'))
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn-accent">
                                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Save Hold Reasons
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>

                    <hr class="my-4" style="border-color:var(--border);">

                    {{-- Cancel Reasons --}}
                    <div>
                        <h6 class="font-display fw-semibold mb-3">Cancel Reasons / રદ કારણો</h6>
                        <form method="POST" action="{{ route('settings.quotation-cancel-reasons') }}" id="quotationCancelReasonsForm">
                            @csrf
                            <div class="d-flex flex-column gap-2 mb-3" id="quotationCancelReasonsList"></div>
                            <div class="shf-reason-add-row row g-2 align-items-end">
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Key</label>
                                    <input type="text" id="newCancelReasonKey" class="shf-input w-100" placeholder="key (e.g. rate_better_elsewhere)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">English</label>
                                    <input type="text" id="newCancelReasonEn" class="shf-input w-100" placeholder="Label (English)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Gujarati</label>
                                    <input type="text" id="newCancelReasonGu" class="shf-input w-100" placeholder="લેબલ (Gujarati)">
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="shf-form-label d-block d-lg-none mb-1">Group</label>
                                    <input type="text" id="newCancelReasonGroup" class="shf-input w-100" placeholder="Group (e.g. Customer)" list="cancelGroupOptions">
                                    <datalist id="cancelGroupOptions"></datalist>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <button type="button" id="addCancelReasonBtn" class="btn-accent-sm w-100 justify-content-center">
                                        <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg> Add
                                    </button>
                                </div>
                            </div>
                            @if (auth()->user()->hasPermission('view_settings'))
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn-accent">
                                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Save Cancel Reasons
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Permissions Tab --}}
                @if (auth()->user()->hasPermission('manage_permissions'))
                    <div class="settings-tab-pane p-4 shf-collapse-hidden" id="tab-permissions"{!! $activeTab !== 'permissions' ? '' : '' !!}>
                        <p class="text-muted mb-3">Manage role-based permission assignments. <a
                                href="{{ route('permissions.index') }}" class="btn-accent-sm">
                                <svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Open Permission Manager
                            </a></p>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                // ============================================================
                //  TAB SWITCHING
                // ============================================================
                $('.shf-tabs').on('click', '.shf-tab', function() {
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
                } else {
                    // Show default active tab on page load
                    $('.shf-tab.active').first().trigger('click');
                }

                // ============================================================
                //  FORM VALIDATION
                // ============================================================
                // Company Details
                $('#tab-company form').on('submit', function(e) {
                    if (!SHF.validateForm($(this), {
                        companyName: { required: true, maxlength: 255, label: 'Company Name' },
                        companyEmail: { required: true, email: true, label: 'Email' },
                        companyAddress: { required: true, label: 'Address' },
                        companyPhone: { required: true, maxlength: 20, label: 'Phone' }
                    })) { e.preventDefault(); }
                });
                // IOM Stamp Paper Charges
                $('#tab-charges form').on('submit', function(e) {
                    if (!SHF.validateForm($(this), {
                        'iomCharges[thresholdAmount]': { required: true, numeric: true, min: 0, label: 'Threshold Amount' },
                        'iomCharges[fixedCharge]': { required: true, numeric: true, min: 0, label: 'Fixed Charge' },
                        'iomCharges[percentageAbove]': { required: true, numeric: true, min: 0, label: 'Percentage' }
                    })) { e.preventDefault(); }
                });
                // GST
                $('#tab-gst form').on('submit', function(e) {
                    if (!SHF.validateForm($(this), {
                        gstPercent: { required: true, numeric: true, min: 0, max: 100, label: 'GST Percentage' }
                    })) { e.preventDefault(); }
                });

                // ============================================================
                //  TENURES MANAGER
                // ============================================================
                var tenures = @json($config['tenures'] ?? []);

                function renderTenureTags() {
                    var html = '';
                    $.each(tenures, function(idx, t) {
                        html +=
                            '<span class="shf-tag" style="background:#f0fdf4;border-color:#86efac;color:#16a34a;">' +
                            '<span>' + t + ' Years</span>' +
                            '<input type="hidden" name="tenures[]" value="' + t + '">' +
                            '<button type="button" class="shf-tag-remove removeTenureBtn" data-idx="' + idx +
                            '" style="background:#16a34a;">&times;</button>' +
                            '</span>';
                    });
                    $('#tenureTagsContainer').html(html);
                }
                renderTenureTags();

                $('#addTenureBtn').on('click', function() {
                    var val = parseInt($('#newTenureInput').val());
                    if (val && val > 0 && $.inArray(val, tenures) === -1) {
                        tenures.push(val);
                        tenures.sort(function(a, b) {
                            return a - b;
                        });
                        renderTenureTags();
                        $('#newTenureInput').val('');
                    }
                });
                $('#newTenureInput').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $('#addTenureBtn').click();
                    }
                });
                $(document).on('click', '.removeTenureBtn', function() {
                    tenures.splice($(this).data('idx'), 1);
                    renderTenureTags();
                });
                // Auto-add pending tenure on form submit
                $('#tenuresForm').on('submit', function() {
                    var val = parseInt($('#newTenureInput').val());
                    if (val && val > 0 && $.inArray(val, tenures) === -1) {
                        tenures.push(val);
                        tenures.sort(function(a, b) {
                            return a - b;
                        });
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
                            '<td><strong class="shf-text-sm">' + bankName + '</strong>' +
                            '<input type="hidden" name="charges[' + idx + '][bank_name]" value="' + bankName +
                            '">' +
                            '</td>' +
                            '<td><input type="number" name="charges[' + idx + '][pf]" value="' + (c.pf || 0) +
                            '" step="0.01" class="shf-input small" style="width:6rem;"></td>' +
                            '<td><input type="number" name="charges[' + idx + '][admin]" value="' + (c.admin ||
                                0) + '" class="shf-input small" style="width:5rem;"></td>' +
                            '<td><input type="number" name="charges[' + idx + '][stamp_notary]" value="' + (c
                                .stamp_notary || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                            '<td><input type="number" name="charges[' + idx + '][registration_fee]" value="' + (
                                c.registration_fee || 0) +
                            '" class="shf-input small" style="width:5rem;"></td>' +
                            '<td><input type="number" name="charges[' + idx + '][advocate]" value="' + (c
                                .advocate || 0) + '" class="shf-input small" style="width:5rem;"></td>' +
                            '<td><input type="number" name="charges[' + idx + '][tc]" value="' + (c.tc || 0) +
                            '" class="shf-input small" style="width:5rem;"></td>' +
                            '<td>' +
                            '<input type="hidden" name="charges[' + idx + '][extra1_name]" value="' + (c
                                .extra1_name || '') + '">' +
                            '<input type="hidden" name="charges[' + idx + '][extra1_amt]" value="' + (c
                                .extra1_amt || 0) + '">' +
                            '<input type="hidden" name="charges[' + idx + '][extra2_name]" value="' + (c
                                .extra2_name || '') + '">' +
                            '<input type="hidden" name="charges[' + idx + '][extra2_amt]" value="' + (c
                                .extra2_amt || 0) + '">' +
                            '</td></tr>';
                    });
                    $('#bankChargesBody').html(html);
                }
                renderChargeRows();

                // ============================================================
                //  DOCUMENTS MANAGER
                // ============================================================
                var docs = {
                    proprietor: {
                        en: @json($config['documents_en']['proprietor'] ?? []),
                        gu: @json($config['documents_gu']['proprietor'] ?? [])
                    },
                    partnership_llp: {
                        en: @json($config['documents_en']['partnership_llp'] ?? []),
                        gu: @json($config['documents_gu']['partnership_llp'] ?? [])
                    },
                    pvt_ltd: {
                        en: @json($config['documents_en']['pvt_ltd'] ?? []),
                        gu: @json($config['documents_gu']['pvt_ltd'] ?? [])
                    },
                    salaried: {
                        en: @json($config['documents_en']['salaried'] ?? []),
                        gu: @json($config['documents_gu']['salaried'] ?? [])
                    }
                };
                var currentDocTab = 'proprietor';

                var sortableInstances = {};

                function renderDocList(type) {
                    var esc = function (s) { return $('<span>').text(s == null ? '' : s).html(); };
                    var html = '';
                    $.each(docs[type].en, function(idx, enVal) {
                        var guVal = docs[type].gu[idx] || '';
                        html +=
                            '<div class="doc-sortable-item shf-reason-row row g-2 align-items-center p-2 rounded" style="background:var(--bg);border:1px solid var(--border);margin:0 0 4px 0;">' +
                            '<div class="col-auto d-flex align-items-center gap-2" style="min-width:0;">' +
                                '<span class="doc-drag-handle" style="cursor:grab;color:#9ca3af;font-size:1rem;padding:0 2px;" title="Drag to reorder">⠿</span>' +
                                '<span class="fw-bold shf-text-xs shf-text-gray-light">' + (idx + 1) + '.</span>' +
                            '</div>' +
                            '<div class="col-12 col-md">' +
                                '<input type="text" name="documents_en[' + type + '][]" value="' + esc(enVal) + '" class="shf-input w-100 small" placeholder="English">' +
                            '</div>' +
                            '<div class="col-12 col-md">' +
                                '<input type="text" name="documents_gu[' + type + '][]" value="' + esc(guVal) + '" class="shf-input w-100 small" placeholder="Gujarati">' +
                            '</div>' +
                            '<div class="col-12 col-md-auto">' +
                                '<button type="button" class="btn-accent-sm removeDocBtn shf-btn-danger w-100 w-md-auto justify-content-center" data-type="' + type + '" data-idx="' + idx + '">' +
                                    '<svg class="shf-icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Remove' +
                                '</button>' +
                            '</div>' +
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
                        onEnd: function() {
                            // Read new order from DOM inputs
                            var newEn = [],
                                newGu = [];
                            $('#docList-' + type).find('.doc-sortable-item').each(function() {
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
                    $('.doc-sub-tab').css({
                        'border-bottom-color': 'transparent',
                        'color': '#6b7280',
                        'background': 'transparent'
                    });
                    $('.doc-sub-tab[data-doc-type="' + type + '"]').css({
                        'border-bottom-color': '#f15a29',
                        'color': '#f15a29',
                        'background': 'rgba(241,90,41,0.05)'
                    });
                    $('.doc-type-pane').hide();
                    $('#docPane-' + type).show();
                    renderDocList(type);
                }
                // Render ALL doc types on load so form always submits all types
                $.each(['proprietor', 'partnership_llp', 'pvt_ltd', 'salaried'], function(_, t) {
                    renderDocList(t);
                });
                switchDocTab('proprietor');

                $(document).on('click', '.doc-sub-tab', function() {
                    switchDocTab($(this).data('doc-type'));
                });

                $(document).on('click', '.addDocBtn', function() {
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

                $(document).on('click', '.removeDocBtn', function() {
                    var type = $(this).data('type');
                    var idx = $(this).data('idx');
                    docs[type].en.splice(idx, 1);
                    docs[type].gu.splice(idx, 1);
                    renderDocList(type);
                });

                // Auto-add any pending document input values on form submit
                $('#documentsForm').on('submit', function() {
                    $.each(['proprietor', 'partnership_llp', 'pvt_ltd', 'salaried'], function(_, type) {
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

                // ============================================================
                //  DVR CONTACT TYPES & PURPOSES MANAGER
                // ============================================================
                var dvrContactTypes = @json($config['dvrContactTypes'] ?? []);
                var dvrPurposes = @json($config['dvrPurposes'] ?? []);

                function renderDvrList(items, containerId, inputPrefix) {
                    var esc = function (s) { return $('<span>').text(s == null ? '' : s).html(); };
                    var html = '';
                    $.each(items, function(idx, item) {
                        html += '<div class="shf-reason-row row g-2 align-items-center p-2 rounded" style="background:var(--bg);border:1px solid var(--border);margin:0 0 4px 0;">'
                            + '<input type="hidden" name="' + inputPrefix + '[' + idx + '][key]" value="' + esc(item.key) + '">'
                            + '<div class="col-auto d-flex align-items-center gap-2" style="min-width:0;">'
                            +   '<span class="fw-bold shf-text-xs shf-text-gray-light">' + (idx + 1) + '.</span>'
                            +   '<span class="shf-badge shf-badge-gray shf-text-2xs text-truncate" style="max-width:10rem;" title="' + esc(item.key) + '">' + esc(item.key) + '</span>'
                            + '</div>'
                            + '<div class="col-12 col-md">'
                            +   '<input type="text" name="' + inputPrefix + '[' + idx + '][label_en]" value="' + esc(item.label_en) + '" class="shf-input w-100 small" placeholder="English">'
                            + '</div>'
                            + '<div class="col-12 col-md">'
                            +   '<input type="text" name="' + inputPrefix + '[' + idx + '][label_gu]" value="' + esc(item.label_gu) + '" class="shf-input w-100 small" placeholder="Gujarati">'
                            + '</div>'
                            + '<div class="col-12 col-md-auto">'
                            +   '<button type="button" class="btn-accent-sm shf-btn-danger dvr-remove-btn w-100 w-md-auto justify-content-center" data-list="' + containerId + '" data-idx="' + idx + '">'
                            +     '<svg class="shf-icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                            +   '</button>'
                            + '</div>'
                            + '</div>';
                    });
                    $('#' + containerId).html(html);
                }

                renderDvrList(dvrContactTypes, 'dvrContactTypesList', 'dvrContactTypes');
                renderDvrList(dvrPurposes, 'dvrPurposesList', 'dvrPurposes');

                $('#addContactTypeBtn').on('click', function() {
                    var key = $.trim($('#newContactTypeKey').val());
                    var en = $.trim($('#newContactTypeEn').val());
                    var gu = $.trim($('#newContactTypeGu').val());
                    if (key && en) {
                        key = key.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                        dvrContactTypes.push({ key: key, label_en: en, label_gu: gu || en });
                        renderDvrList(dvrContactTypes, 'dvrContactTypesList', 'dvrContactTypes');
                        $('#newContactTypeKey, #newContactTypeEn, #newContactTypeGu').val('');
                    }
                });

                $('#addPurposeBtn').on('click', function() {
                    var key = $.trim($('#newPurposeKey').val());
                    var en = $.trim($('#newPurposeEn').val());
                    var gu = $.trim($('#newPurposeGu').val());
                    if (key && en) {
                        key = key.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                        dvrPurposes.push({ key: key, label_en: en, label_gu: gu || en });
                        renderDvrList(dvrPurposes, 'dvrPurposesList', 'dvrPurposes');
                        $('#newPurposeKey, #newPurposeEn, #newPurposeGu').val('');
                    }
                });

                $(document).on('click', '.dvr-remove-btn', function() {
                    var listId = $(this).data('list');
                    var idx = $(this).data('idx');
                    if (listId === 'dvrContactTypesList') {
                        dvrContactTypes.splice(idx, 1);
                        renderDvrList(dvrContactTypes, 'dvrContactTypesList', 'dvrContactTypes');
                    } else if (listId === 'dvrPurposesList') {
                        dvrPurposes.splice(idx, 1);
                        renderDvrList(dvrPurposes, 'dvrPurposesList', 'dvrPurposes');
                    }
                });

                // ============================================================
                //  QUOTATION HOLD / CANCEL REASONS MANAGER (with `group`)
                // ============================================================
                var quotationHoldReasons = @json($config['quotationHoldReasons'] ?? []);
                var quotationCancelReasons = @json($config['quotationCancelReasons'] ?? []);

                function escapeHtml(s) { return $('<span>').text(s == null ? '' : s).html(); }

                function uniqueGroups(items) {
                    var seen = {}, out = [];
                    items.forEach(function (it) {
                        var g = (it.group || '').trim();
                        if (g && !seen[g]) { seen[g] = true; out.push(g); }
                    });
                    return out.sort();
                }

                function refreshGroupDatalist(datalistId, items) {
                    var html = '';
                    uniqueGroups(items).forEach(function (g) {
                        html += '<option value="' + escapeHtml(g) + '">';
                    });
                    $('#' + datalistId).html(html);
                }

                function renderReasonList(items, containerId, inputPrefix, datalistId) {
                    // Items keep their original index so form POST preserves order + allows targeted remove.
                    // We group by `group` just for visual rendering — server still stores the flat array order.
                    var groups = {}; // label -> [{idx,item}]
                    items.forEach(function (item, idx) {
                        var g = (item.group || 'Other').trim() || 'Other';
                        (groups[g] = groups[g] || []).push({ idx: idx, item: item });
                    });

                    var groupOrder = Object.keys(groups).sort(function (a, b) {
                        if (a === 'Other') return 1;
                        if (b === 'Other') return -1;
                        return a.localeCompare(b);
                    });

                    var html = '';
                    groupOrder.forEach(function (groupName) {
                        html += '<div class="shf-form-label mt-2 mb-1" style="color:var(--accent);">' + escapeHtml(groupName) + '</div>';
                        groups[groupName].forEach(function (entry) {
                            var item = entry.item, idx = entry.idx;
                            html += '<div class="shf-reason-row row g-2 align-items-center p-2 rounded" style="background:var(--bg);border:1px solid var(--border);margin:0 0 4px 0;">'
                                + '<input type="hidden" name="' + inputPrefix + '[' + idx + '][key]" value="' + escapeHtml(item.key) + '">'
                                + '<div class="col-auto d-flex align-items-center gap-2" style="min-width:0;">'
                                +   '<span class="fw-bold shf-text-xs shf-text-gray-light">' + (idx + 1) + '.</span>'
                                +   '<span class="shf-badge shf-badge-gray shf-text-2xs text-truncate" style="max-width:10rem;" title="' + escapeHtml(item.key) + '">' + escapeHtml(item.key) + '</span>'
                                + '</div>'
                                + '<div class="col-12 col-md-6 col-lg">'
                                +   '<input type="text" name="' + inputPrefix + '[' + idx + '][label_en]" value="' + escapeHtml(item.label_en) + '" class="shf-input w-100 small" placeholder="English">'
                                + '</div>'
                                + '<div class="col-12 col-md-6 col-lg">'
                                +   '<input type="text" name="' + inputPrefix + '[' + idx + '][label_gu]" value="' + escapeHtml(item.label_gu) + '" class="shf-input w-100 small" placeholder="Gujarati">'
                                + '</div>'
                                + '<div class="col-12 col-md-8 col-lg-3">'
                                +   '<input type="text" name="' + inputPrefix + '[' + idx + '][group]" value="' + escapeHtml(item.group || 'Other') + '" class="shf-input w-100 small" placeholder="Group" list="' + datalistId + '">'
                                + '</div>'
                                + '<div class="col-12 col-md-4 col-lg-auto text-md-end">'
                                +   '<button type="button" class="btn-accent-sm shf-btn-danger reason-remove-btn w-100 w-md-auto justify-content-center" data-list="' + containerId + '" data-idx="' + idx + '">'
                                +     '<svg class="shf-icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                +     ' <span class="d-lg-none">Remove</span>'
                                +   '</button>'
                                + '</div>'
                                + '</div>';
                        });
                    });

                    $('#' + containerId).html(html);
                    refreshGroupDatalist(datalistId, items);
                }

                renderReasonList(quotationHoldReasons, 'quotationHoldReasonsList', 'quotationHoldReasons', 'holdGroupOptions');
                renderReasonList(quotationCancelReasons, 'quotationCancelReasonsList', 'quotationCancelReasons', 'cancelGroupOptions');

                function pushReason(items, $keyIn, $enIn, $guIn, $groupIn) {
                    var key = $.trim($keyIn.val());
                    var en = $.trim($enIn.val());
                    if (!key || !en) return false;
                    var gu = $.trim($guIn.val());
                    var group = $.trim($groupIn.val()) || 'Other';
                    key = key.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                    items.push({ key: key, label_en: en, label_gu: gu || en, group: group });
                    $keyIn.val(''); $enIn.val(''); $guIn.val(''); $groupIn.val('');
                    return true;
                }

                $('#addHoldReasonBtn').on('click', function () {
                    if (pushReason(quotationHoldReasons, $('#newHoldReasonKey'), $('#newHoldReasonEn'), $('#newHoldReasonGu'), $('#newHoldReasonGroup'))) {
                        renderReasonList(quotationHoldReasons, 'quotationHoldReasonsList', 'quotationHoldReasons', 'holdGroupOptions');
                    }
                });

                $('#addCancelReasonBtn').on('click', function () {
                    if (pushReason(quotationCancelReasons, $('#newCancelReasonKey'), $('#newCancelReasonEn'), $('#newCancelReasonGu'), $('#newCancelReasonGroup'))) {
                        renderReasonList(quotationCancelReasons, 'quotationCancelReasonsList', 'quotationCancelReasons', 'cancelGroupOptions');
                    }
                });

                $(document).on('click', '.reason-remove-btn', function () {
                    var listId = $(this).data('list');
                    var idx = $(this).data('idx');
                    if (listId === 'quotationHoldReasonsList') {
                        quotationHoldReasons.splice(idx, 1);
                        renderReasonList(quotationHoldReasons, 'quotationHoldReasonsList', 'quotationHoldReasons', 'holdGroupOptions');
                    } else if (listId === 'quotationCancelReasonsList') {
                        quotationCancelReasons.splice(idx, 1);
                        renderReasonList(quotationCancelReasons, 'quotationCancelReasonsList', 'quotationCancelReasons', 'cancelGroupOptions');
                    }
                });

                // Auto-add pending items on form submit
                $('#quotationHoldReasonsForm').on('submit', function () {
                    if (pushReason(quotationHoldReasons, $('#newHoldReasonKey'), $('#newHoldReasonEn'), $('#newHoldReasonGu'), $('#newHoldReasonGroup'))) {
                        renderReasonList(quotationHoldReasons, 'quotationHoldReasonsList', 'quotationHoldReasons', 'holdGroupOptions');
                    }
                });
                $('#quotationCancelReasonsForm').on('submit', function () {
                    if (pushReason(quotationCancelReasons, $('#newCancelReasonKey'), $('#newCancelReasonEn'), $('#newCancelReasonGu'), $('#newCancelReasonGroup'))) {
                        renderReasonList(quotationCancelReasons, 'quotationCancelReasonsList', 'quotationCancelReasons', 'cancelGroupOptions');
                    }
                });

                // Auto-add pending DVR items on form submit
                $('#dvrContactTypesForm').on('submit', function() {
                    var key = $.trim($('#newContactTypeKey').val());
                    var en = $.trim($('#newContactTypeEn').val());
                    if (key && en) {
                        var gu = $.trim($('#newContactTypeGu').val());
                        key = key.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                        dvrContactTypes.push({ key: key, label_en: en, label_gu: gu || en });
                        renderDvrList(dvrContactTypes, 'dvrContactTypesList', 'dvrContactTypes');
                    }
                });
                $('#dvrPurposesForm').on('submit', function() {
                    var key = $.trim($('#newPurposeKey').val());
                    var en = $.trim($('#newPurposeEn').val());
                    if (key && en) {
                        var gu = $.trim($('#newPurposeGu').val());
                        key = key.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                        dvrPurposes.push({ key: key, label_en: en, label_gu: gu || en });
                        renderDvrList(dvrPurposes, 'dvrPurposesList', 'dvrPurposes');
                    }
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
