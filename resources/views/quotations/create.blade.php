@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
            <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Quotation
        </h2>
        <a href="{{ route('dashboard') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Location Selector (always visible, mandatory) --}}
            <div class="shf-section mb-4">
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="shf-form-label">Location / સ્થાન <span class="text-danger">*</span></label>
                            <select id="quotationLocation" class="shf-input w-100" onchange="onLocationChange(); clearFieldError('quotationLocation')">
                                <option value="">-- Select Location / સ્થાન પસંદ કરો --</option>
                                @php
                                    $isAdminUser = in_array(auth()->user()->role, ['super_admin', 'admin']);
                                    $userLocIds = $isAdminUser ? [] : auth()->user()->locations->pluck('id')->toArray();
                                @endphp
                                @foreach($locStates as $locState)
                                    @php
                                        $stateCities = $locState->children->where('is_active', true);
                                        if (!$isAdminUser) {
                                            // Filter to only user's assigned cities (or cities under assigned states)
                                            $hasState = in_array($locState->id, $userLocIds);
                                            $stateCities = $hasState ? $stateCities : $stateCities->whereIn('id', $userLocIds);
                                        }
                                    @endphp
                                    @if($stateCities->isNotEmpty())
                                        <optgroup label="{{ $locState->name }}">
                                            @foreach($stateCities as $locCity)
                                                <option value="{{ $locCity->id }}" data-parent-id="{{ $locState->id }}"
                                                        {{ $defaultLocationId == $locCity->id ? 'selected' : '' }}>
                                                    {{ $locCity->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 1: Customer Info --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">1</div>
                    <span class="shf-section-title">Customer Information / <span style="font-weight:400;opacity:0.8;">ગ્રાહક માહિતી</span></span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="shf-form-label">Customer Name / ગ્રાહક નામ</label>
                            <input type="text" id="customerName" class="shf-input w-100" placeholder="Enter full name" oninput="clearFieldError('customerName')" />
                        </div>
                        <div class="col-md-6">
                            <label class="shf-form-label">Customer Type / ગ્રાહક પ્રકાર</label>
                            <select id="customerType" onchange="onCustomerTypeChange(); clearFieldError('customerType')" class="shf-input w-100">
                                <option value="">-- Select Type / પ્રકાર પસંદ કરો --</option>
                                <option value="proprietor">Proprietor / માલિકી</option>
                                <option value="partnership_llp">Partnership / LLP / ભાગીદારી / LLP</option>
                                <option value="pvt_ltd">Private Limited / પ્રાઇવેટ લિમિટેડ</option>
                                <option value="salaried">Salaried / પગારદાર</option>
                                <option value="all">All Types / બધા પ્રકાર</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Documents --}}
            <div class="shf-section mb-4" id="docSection" style="display:none;">
                <div class="shf-section-header">
                    <div class="shf-section-number">2</div>
                    <span class="shf-section-title">Required Documents / જરૂરી દસ્તાવેજો</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-2" id="docGrid"></div>
                </div>
            </div>

            {{-- Section 3: Loan Details --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">3</div>
                    <span class="shf-section-title">Loan Details / લોન વિગતો</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="shf-form-label">Loan Amount / લોન રકમ</label>
                            <input type="text" id="loanAmount" class="shf-input w-100" placeholder="e.g. 50,00,000" oninput="formatLoanAmount(this); updateAllBanks(); updateAllPfAmounts(); clearFieldError('loanAmount');" />
                        </div>
                        <div class="col-md-6">
                            <label class="shf-form-label">Amount in Words / રકમ શબ્દોમાં</label>
                            <input type="text" id="loanWords" class="shf-input w-100 small" style="background:#f9fafb;color:#6b7280;" readonly />
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="shf-form-label">Prepared By (Name) / તૈયાર કરનાર (નામ)</label>
                            <input type="text" id="preparedByName" class="shf-input w-100" value="{{ $user->name }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="shf-form-label">Prepared By (Mobile) / તૈયાર કરનાર (મોબાઇલ)</label>
                            <input type="text" id="preparedByMobile" class="shf-input w-100" value="{{ $user->phone ?? '' }}" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4: Bank Selection & EMI --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">4</div>
                    <span class="shf-section-title">Bank Selection & EMI / બેંક પસંદગી અને EMI</span>
                </div>
                <div class="shf-section-body">
                    <div class="d-flex flex-wrap gap-2 mb-4" id="bankChips"></div>
                    <div id="tenureSelection" style="display:none;" class="mb-4">
                        <div class="shf-form-label mb-2">Select Tenures for PDF / PDF માટે સમયગાળો પસંદ કરો</div>
                        <div id="tenureChips" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div id="bankCards" class="d-flex flex-column gap-3"></div>
                </div>
            </div>

            {{-- Section 5: Additional Notes --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">5</div>
                    <span class="shf-section-title">Additional Notes / વધારાની નોંધ</span>
                </div>
                <div class="shf-section-body">
                    <label class="shf-form-label">Notes (will appear highlighted in red on PDF)</label>
                    <textarea id="additionalNotes" rows="3" class="shf-input w-100" placeholder="Enter any additional notes for the proposal..."></textarea>
                </div>
            </div>

            {{-- Pending Offline Quotations Badge (clickable Sync Now) --}}
            <div id="pendingSyncBadge" style="display:none;" class="text-center mb-2">
                <button type="button" onclick="syncNow()" id="syncNowBtn" style="display:inline-flex;align-items:center;gap:6px;background:#f39c12;color:#fff;padding:6px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;border:none;cursor:pointer;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="pendingSyncCount">0</span> quotation(s) pending sync / ક્વોટેશન સિંક બાકી
                    <span id="syncNowLabel" style="margin-left:4px;font-size:0.75rem;opacity:0.9;">— Tap to sync / સિંક કરવા ટેપ કરો</span>
                </button>
            </div>

            {{-- Generate Button --}}
            <div class="d-flex justify-content-center gap-3 py-4">
                <a href="{{ route('dashboard') }}" class="btn-accent-outline" style="padding: 10px 28px; font-size: 0.95rem;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel
                </a>
                <button id="btnGenerate" onclick="handleGenerate()" class="btn-accent" style="padding: 10px 28px; font-size: 0.95rem;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    GENERATE PDF PROPOSAL
                </button>
            </div>

        </div>
    </div>

    {{-- Toast notifications --}}
    <div id="toastContainer" style="position:fixed;bottom:16px;right:16px;z-index:1050;display:flex;flex-direction:column;gap:8px;"></div>

    <style>
        .field-error {
            color: #c0392b;
            font-size: 0.75rem;
            margin-top: 4px;
            font-weight: 500;
        }
        .shf-input.input-error {
            border-color: #c0392b !important;
            box-shadow: 0 0 0 1px #c0392b;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    @push('scripts')
    <script>
        // ============================================================
        //  CONFIG — Loaded from server
        // ============================================================
        const CONFIG = @json($config);
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const BANK_LOCATION_MAP = @json($bankLocationMap);
        const ALL_BANK_NAMES = @json($config['banks']);

        // Filter banks by selected location
        function onLocationChange() {
            var locId = parseInt(document.getElementById('quotationLocation')?.value || 0);
            var parentId = 0;
            var sel = document.getElementById('quotationLocation');
            if (sel && sel.selectedOptions[0]) parentId = parseInt(sel.selectedOptions[0].dataset.parentId || 0);

            if (!locId) {
                CONFIG.banks = ALL_BANK_NAMES;
            } else {
                CONFIG.banks = ALL_BANK_NAMES.filter(function(bankName) {
                    var locs = BANK_LOCATION_MAP[bankName] || [];
                    if (locs.length === 0) return true; // no location restriction
                    if (locs.indexOf(locId) !== -1) return true;
                    if (parentId && locs.indexOf(parentId) !== -1) return true;
                    return false;
                });
            }
            // Reset bank selection and re-render chips
            selectedBanks = selectedBanks.filter(b => CONFIG.banks.indexOf(b) !== -1);
            renderBankChips();
            renderBankCards();
        }
        const LOGO_BASE64 = 'data:image/png;base64,{{ base64_encode(file_get_contents(public_path("images/logo3.png"))) }}';
        // const LOGO_BASE64 = 'data:image/png;base64,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';


        // ============================================================
        //  STATE
        // ============================================================
        let selectedBanks = [];
        let SAVED_CHARGES = {};
        let TEMP_BANK_DATA = {};

        // ============================================================
        //  TRANSLATIONS (bilingual labels)
        // ============================================================
        const T = {
            years: 'Years / વર્ષ',
            minRoi: 'Min ROI (%) / ન્યૂનતમ ROI',
            maxRoi: 'Max ROI (%) / મહત્તમ ROI',
            chargesAndFees: 'Charges & Fees / ચાર્જ અને ફી',
            pfCharge: 'PF Charge / PF ચાર્જ',
            adminCharges: 'Admin Charges / એડમિન ચાર્જ',
            stampDuty: 'Stamp Paper and Notary Charges / સ્ટેમ્પ પેપર અને નોટરી ચાર્જ',
            notaryCharges: 'Registration Fee / રજીસ્ટ્રેશન ફી',
            advocateFees: 'Advocate Fees / એડવોકેટ ફી',
            iomCharges: 'IOM Stamp Paper Charges / IOM સ્ટેમ્પ પેપર ચાર્જ',
            tcReportAmount: 'TC Report Charges / TC રિપોર્ટ ચાર્જ',
            additionalCharges: 'Additional Charges / વધારાના ચાર્જ',
            chargeName: 'Charge Name / ચાર્જ નામ',
            amount: 'Amount / રકમ',
            generatePdf: 'GENERATE PDF PROPOSAL',
        };

        // ============================================================
        //  NUMBER FORMATTING
        // ============================================================
        function formatIndianNumber(num) {
            if (isNaN(num)) return '';
            const str = num.toString();
            let result = '';
            let count = 0;
            for (let i = str.length - 1; i >= 0; i--) {
                if (count === 3 || (count > 3 && (count - 3) % 2 === 0)) result = ',' + result;
                result = str[i] + result;
                count++;
            }
            return result;
        }

        function formatCurrency(num) {
            if (isNaN(num) || num === 0) return '₹0';
            return '₹' + formatIndianNumber(Math.round(num));
        }

        // ============================================================
        //  NUMBER TO WORDS (bilingual)
        // ============================================================
        const EN_ONES = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
        const EN_TENS = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        const GU = ['','એક','બે','ત્રણ','ચાર','પાંચ','છ','સાત','આઠ','નવ','દસ','અગિયાર','બાર','તેર','ચૌદ','પંદર','સોળ','સત્તર','અઢાર','ઓગણીસ','વીસ','એકવીસ','બાવીસ','ત્રેવીસ','ચોવીસ','પચ્ચીસ','છવ્વીસ','સત્તાવીસ','અઠ્ઠાવીસ','ઓગણત્રીસ','ત્રીસ','એકત્રીસ','બત્રીસ','તેત્રીસ','ચોત્રીસ','પાંત્રીસ','છત્રીસ','સાડત્રીસ','આડત્રીસ','ઓગણચાલીસ','ચાલીસ','એકતાલીસ','બેતાલીસ','ત્રેતાલીસ','ચુંમાલીસ','પિસ્તાલીસ','છેતાલીસ','સુડતાલીસ','અડતાલીસ','ઓગણપચાસ','પચાસ','એકાવન','બાવન','ત્રેપન','ચોપન','પંચાવન','છપ્પન','સત્તાવન','અઠ્ઠાવન','ઓગણસાઈઐ','સાઈઐ','એકસઠ','બાસઠ','ત્રેસઠ','ચોસઠ','પાંસઠ','છાસઠ','સડસઠ','અડસઠ','ઓગણસિત્તેર','સિત્તેર','એકોતેર','બોતેર','તોતેર','ચુમોતેર','પંચોતેર','છોતેર','સિત્યોતેર','ઇઠ્યોતેર','ઓગણાએંસી','એંસી','એક્યાસી','બ્યાસી','ત્યાસી','ચોર્યાસી','પંચાસી','છ્યાસી','સત્યાસી','અઠ્ઠ્યાસી','નેવ્યાસી','નેવું','એકાણું','બાણું','ત્રાણું','ચોરાણું','પંચાણું','છન્નું','સત્તાણું','અઠ્ઠાણું','નવ્વાણું'];

        function twoEn(n) { return n<20?EN_ONES[n]:EN_TENS[Math.floor(n/10)]+(n%10?' '+EN_ONES[n%10]:''); }
        function threeEn(n) { return n>=100?EN_ONES[Math.floor(n/100)]+' Hundred'+(n%100?' '+twoEn(n%100):''):twoEn(n); }
        function innerEn(n) {
            let r = '';
            if (n >= 1e7) { r += innerEn(Math.floor(n / 1e7)) + ' Crore '; n %= 1e7; }
            if (n >= 1e5) { r += twoEn(Math.floor(n / 1e5)) + ' Lakh '; n %= 1e5; }
            if (n >= 1e3) { r += twoEn(Math.floor(n / 1e3)) + ' Thousand '; n %= 1e3; }
            if (n > 0) r += threeEn(n);
            return r.trim();
        }
        function toWordsEn(n) { if(n===0) return 'Zero'; let r=''; if(n>=1e7){r+=innerEn(Math.floor(n/1e7))+' Crore ';n%=1e7;} if(n>=1e5){r+=twoEn(Math.floor(n/1e5))+' Lakh ';n%=1e5;} if(n>=1e3){r+=twoEn(Math.floor(n/1e3))+' Thousand ';n%=1e3;} if(n>0)r+=threeEn(n); return r.trim()+' Rupees'; }
        function threeGu(n) { return n>=100?(GU[Math.floor(n/100)]||'')+' સો'+(n%100?' '+(GU[n%100]||''):''):(GU[n]||''); }
        function innerGu(n) {
            let r = '';
            if (n >= 1e7) { r += innerGu(Math.floor(n / 1e7)) + ' કરોડ '; n %= 1e7; }
            if (n >= 1e5) { r += (GU[Math.floor(n / 1e5)] || '') + ' લાખ '; n %= 1e5; }
            if (n >= 1e3) { r += (GU[Math.floor(n / 1e3)] || '') + ' હજાર '; n %= 1e3; }
            if (n > 0) r += threeGu(n);
            return r.trim();
        }
        function toWordsGu(n) { if(n===0) return 'શૂન્ય'; let r=''; if(n>=1e7){r+=innerGu(Math.floor(n/1e7))+' કરોડ ';n%=1e7;} if(n>=1e5){r+=(GU[Math.floor(n/1e5)]||'')+' લાખ ';n%=1e5;} if(n>=1e3){r+=(GU[Math.floor(n/1e3)]||'')+' હજાર ';n%=1e3;} if(n>0)r+=threeGu(n); return r.trim()+' રૂપિયા'; }
        function getBilingualAmountWords(n) { return toWordsEn(n) + ' / ' + toWordsGu(n); }

        // ============================================================
        //  DOCUMENT BILINGUAL LOOKUP
        // ============================================================
        function getBilingualDocName(enName, type, config) {
            const types = type === 'all' ? ['partnership_llp', 'pvt_ltd'] : [type];
            for (const t of types) {
                const enDocs = config.documents_en?.[t] || [];
                const guDocs = config.documents_gu?.[t] || [];
                const idx = enDocs.indexOf(enName);
                if (idx >= 0 && guDocs[idx]) return enName + ' / ' + guDocs[idx];
            }
            return enName;
        }

        // ============================================================
        //  CUSTOMER TYPE & DOCUMENTS
        // ============================================================
        function onCustomerTypeChange() {
            const type = document.getElementById('customerType').value;
            const docSection = document.getElementById('docSection');
            const docGrid = document.getElementById('docGrid');

            if (!type) { docSection.style.display = 'none'; return; }
            docSection.style.display = 'block';

            let enDocs = [];
            if (type === 'proprietor') enDocs = CONFIG.documents_en?.proprietor || [];
            else if (type === 'partnership_llp') enDocs = CONFIG.documents_en?.partnership_llp || [];
            else if (type === 'pvt_ltd') enDocs = CONFIG.documents_en?.pvt_ltd || [];
            else if (type === 'salaried') enDocs = CONFIG.documents_en?.salaried || [];
            else if (type === 'all') enDocs = [...new Set([...(CONFIG.documents_en?.partnership_llp || []), ...(CONFIG.documents_en?.pvt_ltd || [])])];

            docGrid.innerHTML = enDocs.map((doc, i) => {
                const biName = getBilingualDocName(doc, type, CONFIG);
                return `<div class="col-md-6"><label class="d-flex align-items-center gap-2 p-2 rounded" style="cursor:pointer;" id="docItem${i}">
                    <input type="checkbox" checked id="docCheck${i}" data-en="${doc.replace(/"/g, '&quot;')}" style="accent-color: #f15a29;">
                    <span class="small">${biName}</span>
                </label></div>`;
            }).join('');
        }

        function getSelectedDocuments() {
            const docs = [];
            document.querySelectorAll('#docGrid input[type="checkbox"]:checked').forEach(cb => {
                const enName = cb.dataset.en;
                const biText = cb.parentElement.querySelector('span').textContent;
                const slashIdx = biText.indexOf(' / ');
                docs.push({ en: enName, gu: slashIdx >= 0 ? biText.substring(slashIdx + 3) : '' });
            });
            return docs;
        }

        // ============================================================
        //  LOAN AMOUNT
        // ============================================================
        function formatLoanAmount(input) {
            let val = input.value.replace(/[^0-9]/g, '');
            if (val) {
                input.value = formatIndianNumber(parseInt(val));
                document.getElementById('loanWords').value = getBilingualAmountWords(parseInt(val));
            } else { input.value = ''; document.getElementById('loanWords').value = ''; }
        }
        function getLoanAmount() { const raw = document.getElementById('loanAmount').value.replace(/[^0-9]/g, ''); return raw ? parseInt(raw) : 0; }

        // ============================================================
        //  IOM & EMI CALCULATIONS
        // ============================================================
        function calculateIOM(amt) {
            if (amt <= 0) return 0;
            if (amt <= (CONFIG.iomCharges?.thresholdAmount || 0)) return CONFIG.iomCharges?.fixedCharge || 0;
            return Math.round(amt * (CONFIG.iomCharges?.percentageAbove || 0) / 100);
        }
        function getIOMNote(amt) {
            if (amt <= 0) return '';
            if (amt <= (CONFIG.iomCharges?.thresholdAmount || 0)) return 'Fixed: ' + formatCurrency(CONFIG.iomCharges?.fixedCharge || 0);
            return (CONFIG.iomCharges?.percentageAbove || 0) + '% of loan amount';
        }
        function calculateEMI(principal, rate, years) {
            if (!principal || !rate || !years) return 0;
            const r = rate / 12 / 100, n = years * 12;
            if (r === 0) return principal / n;
            return (principal * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
        }

        // ============================================================
        //  BANK CHIPS & CARDS
        // ============================================================
        function renderBankChips() {
            document.getElementById('bankChips').innerHTML = CONFIG.banks.map(bank =>
                `<button type="button" onclick="toggleBank('${bank.replace(/'/g, "\\'")}')" id="chip_${bank.replace(/\s+/g, '_')}"
                    class="shf-chip">${bank}</button>`
            ).join('');
        }

        function collectCurrentBankData() {
            selectedBanks.forEach((bank, idx) => {
                const p = 'bank_' + idx;
                const el = document.getElementById(p + '_roiMin');
                if (!el) return;
                TEMP_BANK_DATA[bank] = {
                    roiMin: el.value,
                    roiMax: document.getElementById(p + '_roiMax').value,
                    pfPercent: document.getElementById(p + '_pf').value,
                    admin: document.getElementById(p + '_admin').value,
                    stamp_notary: document.getElementById(p + '_stamp_notary').value,
                    registration_fee: document.getElementById(p + '_registration_fee').value,
                    advocate: document.getElementById(p + '_advocate').value,
                    iom: document.getElementById(p + '_iom').value,
                    tc: document.getElementById(p + '_tc').value,
                    extraName1: document.getElementById(p + '_extraName1').value,
                    extraAmt1: document.getElementById(p + '_extraAmt1').value,
                    extraName2: document.getElementById(p + '_extraName2').value,
                    extraAmt2: document.getElementById(p + '_extraAmt2').value,
                };
            });
        }

        function toggleBank(name) {
            collectCurrentBankData();
            const idx = selectedBanks.indexOf(name);
            if (idx > -1) selectedBanks.splice(idx, 1); else selectedBanks.push(name);
            CONFIG.banks.forEach(b => {
                const chip = document.getElementById('chip_' + b.replace(/\s+/g, '_'));
                if (chip) {
                    const isSelected = selectedBanks.includes(b);
                    chip.classList.toggle('active', isSelected);
                }
            });
            renderBankCards();
            renderTenureChips();
            if (selectedBanks.length > 0) clearFieldError('bankChips');
        }

        function renderTenureChips() {
            const container = document.getElementById('tenureChips');
            const selDiv = document.getElementById('tenureSelection');
            if (!selectedBanks.length) { selDiv.style.display = 'none'; return; }
            selDiv.style.display = '';
            if (!container.children.length) {
                container.innerHTML = CONFIG.tenures.map(t =>
                    `<label class="d-inline-flex align-items-center gap-1 small fw-medium" style="padding:4px 12px;border-radius:50px;border:2px solid #27ae60;background:rgba(39,174,96,0.08);cursor:pointer;color:#27ae60;">
                        <input type="checkbox" value="${t}" checked onchange="updateTenureVisuals()" style="accent-color: #27ae60;"> ${t} ${T.years}
                    </label>`
                ).join('');
            }
            updateTenureVisuals();
        }

        function getSelectedTenures() {
            const selected = [];
            document.querySelectorAll('#tenureChips input[type="checkbox"]').forEach(cb => { if (cb.checked) selected.push(parseInt(cb.value)); });
            return selected.length > 0 ? selected : CONFIG.tenures.slice();
        }

        function updateTenureVisuals() {
            const checked = getSelectedTenures();
            CONFIG.tenures.forEach(t => {
                for (let i = 0; i < selectedBanks.length; i++) {
                    const emiEl = document.getElementById('bank_' + i + '_emi_' + t);
                    if (emiEl) emiEl.closest('.emi-box').style.opacity = checked.includes(t) ? '1' : '0.35';
                }
            });
        }

        function renderBankCards() {
            const container = document.getElementById('bankCards');
            if (!selectedBanks.length) { container.innerHTML = ''; return; }
            const loanAmt = getLoanAmount(), iomAmt = calculateIOM(loanAmt), iomNote = getIOMNote(loanAmt);

            container.innerHTML = selectedBanks.map((bank, idx) => {
                const p = 'bank_' + idx;
                const initials = bank.split(' ').map(w => w[0]).join('').substring(0, 3).toUpperCase();
                return `<div class="shf-section">
                    <div style="background: linear-gradient(135deg, #3a3536 0%, #4a4546 100%); padding: 12px 16px; display: flex; align-items: center; gap: 10px; border-radius: 10px 10px 0 0;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #f15a29, #f47929); color: white; font-size: 0.6rem; font-weight: 700; font-family: 'Jost', sans-serif;">${initials}</span>
                        <span style="color: white; font-weight: 600; font-family: 'Jost', sans-serif; font-size: 0.95rem;">${bank}</span>
                    </div>
                    <div class="shf-section-body" style="padding: 16px;">
                        <div class="row g-3">
                            <div class="col-6 col-md-4"><label class="shf-form-label">${T.minRoi}</label>
                                <input type="number" step="0.01" id="${p}_roiMin" placeholder="e.g. 8.5" class="shf-input w-100 small" oninput="updateEMI(${idx}); validateRoi(${idx}); clearFieldError('${p}_roiMin')"></div>
                            <div class="col-6 col-md-4"><label class="shf-form-label">${T.maxRoi}</label>
                                <input type="number" step="0.01" id="${p}_roiMax" placeholder="e.g. 9.5" class="shf-input w-100 small" oninput="validateRoi(${idx}); clearFieldError('${p}_roiMax')"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-4" id="${p}_emiRow">
                            ${CONFIG.tenures.map(t => `<div class="emi-box text-center rounded px-3 py-2" style="background: rgba(241,90,41,0.08); border: 1px solid rgba(241,90,41,0.2); min-width: 80px;">
                                <div class="small fw-semibold" style="color: #f15a29; font-size: 0.75rem;">${t} ${T.years}</div>
                                <div class="small fw-bold font-display mt-1" style="color:#1f2937;" id="${p}_emi_${t}">--</div>
                            </div>`).join('')}
                        </div>
                        <div class="shf-form-label mt-4 pt-3" style="border-top: 1px solid #e6e7e8; font-weight: 700;">${T.chargesAndFees}</div>
                        <div class="row g-3 mt-2">
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.pfCharge} (%)</label>
                                <input type="number" step="0.01" min="0" id="${p}_pf" placeholder="e.g. 1.5" class="shf-input w-100 small" oninput="updatePfAmount(${idx})">
                                <div class="mt-1" style="font-size:0.75rem;color:#27ae60;" id="${p}_pfCalc"></div></div>
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.adminCharges}</label>
                                <input type="text" id="${p}_admin" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this); updateAdminGst(${idx})">
                                <div class="mt-1" style="font-size:0.75rem;color:#27ae60;" id="${p}_adminCalc"></div></div>
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.stampDuty}</label>
                                <input type="text" id="${p}_stamp_notary" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)"></div>
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.iomCharges}</label>
                                <input type="text" id="${p}_iom" value="${loanAmt > 0 ? formatIndianNumber(iomAmt) : ''}" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)">
                                <div class="mt-1" style="font-size:0.75rem;color:#9ca3af;" id="${p}_iomNote">${iomNote}</div></div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.notaryCharges}</label>
                                <input type="text" id="${p}_registration_fee" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)"></div>
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.advocateFees}</label>
                                <input type="text" id="${p}_advocate" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)"></div>
                            <div class="col-sm-6 col-md-3"><label class="shf-form-label">${T.tcReportAmount}</label>
                                <input type="text" id="${p}_tc" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)"></div>
                        </div>
                        <div class="shf-form-label mt-4 pt-3" style="border-top: 1px solid #e6e7e8; font-weight: 700;">${T.additionalCharges}</div>
                        <div class="row g-3 mt-2">
                            <div class="col-6"><label class="shf-form-label">${T.chargeName}</label>
                                <input type="text" id="${p}_extraName1" placeholder="e.g. Valuation Fee" class="shf-input w-100 small"></div>
                            <div class="col-6"><label class="shf-form-label">${T.amount}</label>
                                <input type="text" id="${p}_extraAmt1" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)"></div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-6"><label class="shf-form-label">${T.chargeName}</label>
                                <input type="text" id="${p}_extraName2" placeholder="e.g. Legal Fee" class="shf-input w-100 small"></div>
                            <div class="col-6"><label class="shf-form-label">${T.amount}</label>
                                <input type="text" id="${p}_extraAmt2" placeholder="0" class="shf-input w-100 small" oninput="formatChargeInput(this)"></div>
                        </div>
                    </div>
                </div>`;
            }).join('');

            // Restore data
            selectedBanks.forEach((bank, idx) => {
                const temp = TEMP_BANK_DATA[bank], saved = SAVED_CHARGES[bank], p = 'bank_' + idx;
                if (temp) {
                    if (temp.roiMin) document.getElementById(p + '_roiMin').value = temp.roiMin;
                    if (temp.roiMax) document.getElementById(p + '_roiMax').value = temp.roiMax;
                    if (temp.pfPercent) document.getElementById(p + '_pf').value = temp.pfPercent;
                    ['admin','stamp_notary','registration_fee','advocate','iom','tc'].forEach(f => { const el = document.getElementById(p+'_'+f); if(el && temp[f]) el.value = temp[f]; });
                    if (temp.extraName1) document.getElementById(p + '_extraName1').value = temp.extraName1;
                    if (temp.extraAmt1) document.getElementById(p + '_extraAmt1').value = temp.extraAmt1;
                    if (temp.extraName2) document.getElementById(p + '_extraName2').value = temp.extraName2;
                    if (temp.extraAmt2) document.getElementById(p + '_extraAmt2').value = temp.extraAmt2;
                } else if (saved) {
                    if (saved.pf) document.getElementById(p + '_pf').value = saved.pf;
                    ['admin','stamp_notary','registration_fee','advocate','tc'].forEach(f => { const el = document.getElementById(p+'_'+f); if(el && saved[f]) el.value = formatIndianNumber(saved[f]); });
                    if (saved.extra1_name) document.getElementById(p + '_extraName1').value = saved.extra1_name;
                    if (saved.extra1_amt) document.getElementById(p + '_extraAmt1').value = formatIndianNumber(saved.extra1_amt);
                    if (saved.extra2_name) document.getElementById(p + '_extraName2').value = saved.extra2_name;
                    if (saved.extra2_amt) document.getElementById(p + '_extraAmt2').value = formatIndianNumber(saved.extra2_amt);
                }
                updateEMI(idx); updatePfAmount(idx); updateAdminGst(idx);
            });
        }

        // ============================================================
        //  CHARGE HELPERS
        // ============================================================
        function formatChargeInput(input) { let v = input.value.replace(/[^0-9]/g, ''); input.value = v ? formatIndianNumber(parseInt(v)) : ''; }
        function getChargeValue(id) { const el = document.getElementById(id); if(!el) return 0; const r = el.value.replace(/[^0-9]/g, ''); return r ? parseInt(r) : 0; }

        function updatePfAmount(idx) {
            const p = 'bank_' + idx, pct = parseFloat(document.getElementById(p + '_pf').value) || 0;
            const pfBase = Math.round(getLoanAmount() * pct / 100), gstPct = CONFIG.gstPercent || 0, gstAmt = Math.round(pfBase * gstPct / 100);
            const el = document.getElementById(p + '_pfCalc');
            if(el) el.textContent = pfBase > 0 && gstPct > 0 ? `${formatCurrency(pfBase)} + ${formatCurrency(gstAmt)} (GST ${gstPct}%) = ${formatCurrency(pfBase + gstAmt)}` : pfBase > 0 ? `= ${formatCurrency(pfBase)}` : '';
        }
        function updateAllPfAmounts() { for(let i=0;i<selectedBanks.length;i++) updatePfAmount(i); }

        function updateAdminGst(idx) {
            const p = 'bank_' + idx, adminBase = getChargeValue(p + '_admin'), gstPct = CONFIG.gstPercent || 0, gstAmt = Math.round(adminBase * gstPct / 100);
            const el = document.getElementById(p + '_adminCalc');
            if(el) el.textContent = adminBase > 0 && gstPct > 0 ? `${formatCurrency(adminBase)} + ${formatCurrency(gstAmt)} (GST ${gstPct}%) = ${formatCurrency(adminBase + gstAmt)}` : '';
        }

        function updateEMI(idx) {
            const p = 'bank_' + idx, roi = parseFloat(document.getElementById(p + '_roiMin').value) || 0, amt = getLoanAmount();
            CONFIG.tenures.forEach(t => {
                const el = document.getElementById(p + '_emi_' + t);
                if(el) el.textContent = amt > 0 && roi > 0 ? formatCurrency(calculateEMI(amt, roi, t)) : '--';
            });
        }

        function validateRoi(idx) {
            const p = 'bank_' + idx, maxEl = document.getElementById(p + '_roiMax');
            const vMin = parseFloat(document.getElementById(p + '_roiMin').value) || 0, vMax = parseFloat(maxEl.value) || 0;
            maxEl.classList.toggle('input-error', vMax > 0 && vMax < vMin);
        }

        function updateAllBanks() {
            const loanAmt = getLoanAmount(), iomAmt = calculateIOM(loanAmt), iomNote = getIOMNote(loanAmt);
            selectedBanks.forEach((_, idx) => {
                const p = 'bank_' + idx;
                updateEMI(idx);
                const iomEl = document.getElementById(p + '_iom'), noteEl = document.getElementById(p + '_iomNote');
                if(iomEl) iomEl.value = loanAmt > 0 ? formatIndianNumber(iomAmt) : '';
                if(noteEl) noteEl.textContent = iomNote;
            });
        }

        // ============================================================
        //  TOAST
        // ============================================================
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const colors = type === 'success'
                ? 'border-left: 4px solid #27ae60;'
                : 'border-left: 4px solid #c0392b;';
            const icon = type === 'success'
                ? '<svg style="width:16px;height:16px;" fill="none" stroke="#27ae60" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : '<svg style="width:16px;height:16px;" fill="none" stroke="#c0392b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            toast.style.cssText = `background: #3a3536; color: white; padding: 12px 16px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 8px; ${colors}`;
            toast.innerHTML = icon + message;
            container.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3500);
        }

        // ============================================================
        //  INLINE FIELD VALIDATION
        // ============================================================
        function showFieldError(elementId, message) {
            const el = document.getElementById(elementId);
            if (!el) return;
            el.classList.add('input-error');
            let errorDiv = el.parentElement.querySelector('.field-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                el.insertAdjacentElement('afterend', errorDiv);
            }
            errorDiv.textContent = message;
        }

        function clearFieldError(elementId) {
            const el = document.getElementById(elementId);
            if (!el) return;
            el.classList.remove('input-error');
            const errorDiv = el.parentElement.querySelector('.field-error');
            if (errorDiv) errorDiv.remove();
        }

        function clearAllFieldErrors() {
            document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
            document.querySelectorAll('.field-error').forEach(el => el.remove());
        }

        function validateForm() {
            clearAllFieldErrors();
            let firstErrorEl = null;

            const customerName = document.getElementById('customerName').value.trim();
            const customerType = document.getElementById('customerType').value;
            const loanAmount = getLoanAmount();

            if (!document.getElementById('quotationLocation')?.value) {
                showFieldError('quotationLocation', 'Please select location / કૃપા કરી સ્થાન પસંદ કરો');
                if (!firstErrorEl) firstErrorEl = document.getElementById('quotationLocation');
            }
            if (!customerName) {
                showFieldError('customerName', 'Please enter customer name / કૃપા કરી ગ્રાહકનું નામ દાખલ કરો');
                if (!firstErrorEl) firstErrorEl = document.getElementById('customerName');
            }
            if (!customerType) {
                showFieldError('customerType', 'Please select customer type / કૃપા કરી ગ્રાહક પ્રકાર પસંદ કરો');
                if (!firstErrorEl) firstErrorEl = document.getElementById('customerType');
            }
            if (loanAmount <= 0) {
                showFieldError('loanAmount', 'Please enter loan amount / કૃપા કરી લોન રકમ દાખલ કરો');
                if (!firstErrorEl) firstErrorEl = document.getElementById('loanAmount');
            } else if (loanAmount > 1000000000000) {
                showFieldError('loanAmount', 'Loan amount cannot exceed 100 crore / લોન રકમ 100 કરોડથી વધુ ન હોઈ શકે');
                if (!firstErrorEl) firstErrorEl = document.getElementById('loanAmount');
            }
            if (!selectedBanks.length) {
                showFieldError('bankChips', 'Please select at least one bank / કૃપા કરી ઓછામાં ઓછી એક બેંક પસંદ કરો');
                if (!firstErrorEl) firstErrorEl = document.getElementById('bankChips');
            }

            for (let i = 0; i < selectedBanks.length; i++) {
                const p = 'bank_' + i;
                const roiMin = parseFloat(document.getElementById(p + '_roiMin').value) || 0;
                const roiMax = parseFloat(document.getElementById(p + '_roiMax').value) || 0;

                if (!(roiMin > 0)) {
                    showFieldError(p + '_roiMin', 'Enter Min ROI for ' + selectedBanks[i] + ' / ' + selectedBanks[i] + ' માટે ન્યૂનતમ ROI દાખલ કરો');
                    if (!firstErrorEl) firstErrorEl = document.getElementById(p + '_roiMin');
                } else if (roiMin > 30) {
                    showFieldError(p + '_roiMin', 'Min ROI cannot exceed 30% / ન્યૂનતમ ROI 30% થી વધુ ન હોઈ શકે');
                    if (!firstErrorEl) firstErrorEl = document.getElementById(p + '_roiMin');
                }

                if (!(roiMax > 0)) {
                    showFieldError(p + '_roiMax', 'Enter Max ROI for ' + selectedBanks[i] + ' / ' + selectedBanks[i] + ' માટે મહત્તમ ROI દાખલ કરો');
                    if (!firstErrorEl) firstErrorEl = document.getElementById(p + '_roiMax');
                } else if (roiMax > 30) {
                    showFieldError(p + '_roiMax', 'Max ROI cannot exceed 30% / મહત્તમ ROI 30% થી વધુ ન હોઈ શકે');
                    if (!firstErrorEl) firstErrorEl = document.getElementById(p + '_roiMax');
                }

                if (roiMin > 0 && roiMax > 0 && roiMin > roiMax) {
                    showFieldError(p + '_roiMin', 'Min ROI cannot be greater than Max ROI / ન્યૂનતમ ROI મહત્તમ ROI થી વધુ ન હોઈ શકે');
                    if (!firstErrorEl) firstErrorEl = document.getElementById(p + '_roiMin');
                }
            }

            if (firstErrorEl) {
                firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => firstErrorEl.focus(), 400);
                return false;
            }
            return true;
        }

        // ============================================================
        //  PDF GENERATION
        // ============================================================
        function handleGenerate() { generatePDF().catch(e => { console.error(e); showToast('Error generating PDF', 'error'); }); }

        async function generatePDF() {
            const customerName = document.getElementById('customerName').value.trim();
            const customerType = document.getElementById('customerType').value;
            const loanAmount = getLoanAmount();

            if (!validateForm()) return;

            const selectedTenures = getSelectedTenures();

            const btn = document.getElementById('btnGenerate');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<svg style="width:20px;height:20px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Generating PDF...';
            btn.disabled = true;
            btn.style.opacity = '0.7';

            const bankData = selectedBanks.map((bank, idx) => {
                const p = 'bank_' + idx;
                const roiMin = parseFloat(document.getElementById(p + '_roiMin').value) || 0;
                const roiMax = parseFloat(document.getElementById(p + '_roiMax').value) || 0;
                const emiByTenure = {};
                selectedTenures.forEach(t => {
                    const emi = calculateEMI(loanAmount, roiMin, t);
                    emiByTenure[t] = { emi: Math.round(emi), totalPayment: Math.round(emi * t * 12), totalInterest: Math.round(emi * t * 12 - loanAmount) };
                });
                const pfPct = parseFloat(document.getElementById(p + '_pf').value) || 0;
                const pfBase = Math.round(loanAmount * pfPct / 100), pfGst = Math.round(pfBase * (CONFIG.gstPercent || 0) / 100);
                const adminBase = getChargeValue(p + '_admin'), adminGst = Math.round(adminBase * (CONFIG.gstPercent || 0) / 100);
                const stamp_notary = getChargeValue(p + '_stamp_notary'), registration_fee = getChargeValue(p + '_registration_fee');
                const advocate = getChargeValue(p + '_advocate'), iom = getChargeValue(p + '_iom'), tc = getChargeValue(p + '_tc');
                const extra1Name = document.getElementById(p + '_extraName1')?.value?.trim() || '';
                const extra1Amt = getChargeValue(p + '_extraAmt1');
                const extra2Name = document.getElementById(p + '_extraName2')?.value?.trim() || '';
                const extra2Amt = getChargeValue(p + '_extraAmt2');
                const pf = pfBase + pfGst, admin = adminBase + adminGst;
                let total = pf + admin + stamp_notary + registration_fee + advocate + iom + tc + extra1Amt + extra2Amt;
                return { name: bank, roiMin, roiMax, emiByTenure, charges: { pf, pfPercent: pfPct, admin, adminBase, stamp_notary, registration_fee, advocate, iom, tc, extra1Name, extra1Amt, extra2Name, extra2Amt, total } };
            });

            const locationId = document.getElementById('quotationLocation')?.value || null;

            const payload = {
                customerName, customerType, loanAmount,
                location_id: locationId,
                preparedByName: document.getElementById('preparedByName').value.trim(),
                preparedByMobile: document.getElementById('preparedByMobile').value.trim(),
                banks: bankData, selectedTenures,
                documents: getSelectedDocuments(),
                additionalNotes: document.getElementById('additionalNotes').value.trim(),
                ourServices: CONFIG.ourServices || '',
            };

            try {
                const resp = await fetch('{{ route("quotations.generate") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await resp.json();
                if (result.success) {
                    window.open('{{ route("quotations.download-file") }}?file=' + encodeURIComponent(result.filename), '_blank');
                    showToast('PDF generated successfully!');

                    // Update saved charges for future auto-fill
                    bankData.forEach(function(b) {
                        var bIdx = selectedBanks.indexOf(b.name);
                        SAVED_CHARGES[b.name] = {
                            pf: parseFloat(document.getElementById('bank_' + bIdx + '_pf').value) || 0,
                            admin: getChargeValue('bank_' + bIdx + '_admin'),
                            stamp_notary: b.charges.stamp_notary, registration_fee: b.charges.registration_fee,
                            advocate: b.charges.advocate, tc: b.charges.tc,
                            extra1_name: b.charges.extra1Name, extra1_amt: b.charges.extra1Amt,
                            extra2_name: b.charges.extra2Name, extra2_amt: b.charges.extra2Amt
                        };
                    });
                    if (typeof OfflineManager !== 'undefined') {
                        OfflineManager.cacheCharges(SAVED_CHARGES).catch(function() {});
                    }

                    // Save additional notes
                    var notesText = document.getElementById('additionalNotes').value.trim();
                    if (notesText) {
                        fetch('{{ route("api.notes.save") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            body: JSON.stringify({ notes: notesText })
                        }).catch(function() {});
                        if (typeof OfflineManager !== 'undefined') {
                            OfflineManager.cacheNotes(notesText).catch(function() {});
                        }
                    }
                } else {
                    showToast(result.error || 'PDF generation failed', 'error');
                }
            } catch (e) {
                console.error(e);
                // Offline fallback: generate PDF client-side
                var pdfMethod = null;
                if (typeof PdfRenderer !== 'undefined') {
                    pdfMethod = PdfRenderer.generateOfflinePdf(payload, CONFIG, LOGO_BASE64);
                }
                // Queue to IndexedDB for server-quality PDF sync later
                if (typeof OfflineManager !== 'undefined') {
                    try {
                        await OfflineManager.queueQuotation(payload);
                        updatePendingBadge();
                    } catch (queueErr) {
                        console.error('Failed to queue offline:', queueErr);
                    }
                }
                if (pdfMethod === 'download') {
                    showToast('PDF downloaded — open the file and use Print > Save as PDF. Data will sync when online / PDF ડાઉનલોડ થયો — ફાઇલ ખોલો અને Print > Save as PDF વાપરો. ઑનલાઇન થતા સિંક થશે', 'success');
                } else if (pdfMethod === 'print') {
                    showToast('PDF opened for printing — high-quality version will sync when online / PDF પ્રિન્ટ માટે ખોલ્યો — ઑનલાઇન થતા ઉચ્ચ ગુણવત્તાનો PDF સિંક થશે', 'success');
                } else {
                    showToast('Saved offline — PDF will generate when back online / ઑફલાઇન સેવ થયું — ઑનલાઇન થતા PDF બનશે', 'success');
                }
            }

            btn.innerHTML = originalContent;
            btn.disabled = false;
            btn.style.opacity = '1';
        }

        // ============================================================
        //  PENDING BADGE HELPERS
        // ============================================================
        function updatePendingBadge() {
            if (typeof OfflineManager === 'undefined') return;
            OfflineManager.getPendingQuotations().then(function(items) {
                var badge = document.getElementById('pendingSyncBadge');
                var countEl = document.getElementById('pendingSyncCount');
                if (items.length > 0) {
                    countEl.textContent = items.length;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }).catch(function() {});
        }

        // Sync Now — manual trigger from pending badge
        function syncNow() {
            if (!navigator.onLine) {
                showToast('You are offline — connect to sync / તમે ઑફલાઇન છો — સિંક કરવા કનેક્ટ કરો', 'error');
                return;
            }
            showToast('Syncing... / સિંક થઈ રહ્યું છે...', 'success');
            OfflineManager.syncAll();
        }

        // Update sync button state based on connectivity
        function updateSyncBtnState() {
            var label = document.getElementById('syncNowLabel');
            var btn = document.getElementById('syncNowBtn');
            if (!label || !btn) return;
            if (navigator.onLine) {
                label.textContent = '— Tap to sync / સિંક કરવા ટેપ કરો';
                btn.style.cursor = 'pointer';
                btn.disabled = false;
            } else {
                label.textContent = '— Offline / ઑફલાઇન';
                btn.style.cursor = 'not-allowed';
                btn.disabled = true;
            }
        }
        window.addEventListener('online', updateSyncBtnState);
        window.addEventListener('offline', updateSyncBtnState);

        // Listen for sync completion — clear badge + notify (no auto-download)
        window.addEventListener('offlineSync', function(e) {
            updatePendingBadge();
            var detail = e.detail || {};
            var synced = detail.quotationsSynced || 0;
            var failed = detail.quotationsFailed || 0;

            if (synced > 0 && failed === 0) {
                showToast(synced + ' quotation(s) synced! Download from dashboard. / ' + synced + ' ક્વોટેશન સિંક થયા! ડેશબોર્ડ પરથી ડાઉનલોડ કરો.', 'success');
            } else if (synced > 0 && failed > 0) {
                showToast(synced + ' synced, ' + failed + ' failed — failed items will retry / ' + synced + ' સિંક થયા, ' + failed + ' નિષ્ફળ — ફરી પ્રયાસ થશે', 'error');
            } else if (failed > 0) {
                showToast('Sync failed for ' + failed + ' item(s) — will retry / ' + failed + ' આઇટમ સિંક નિષ્ફળ — ફરી પ્રયાસ થશે', 'error');
            }
        });

        // Listen for sync errors (CSRF expired, auth expired, etc.)
        window.addEventListener('offlineSyncError', function(e) {
            updatePendingBadge();
            var detail = e.detail || {};
            showToast(detail.message || 'Sync failed — your data is safe / સિંક નિષ્ફળ — ડેટા સુરક્ષિત છે', 'error');
        });

        // ============================================================
        //  INIT — Load saved charges from DB, then render UI
        // ============================================================
        (async function() {
            // Load saved bank charges for auto-fill (with offline fallback)
            try {
                const resp = await fetch('/api/config/public');
                if (resp.ok) {
                    const data = await resp.json();
                    if (data.bankCharges) {
                        data.bankCharges.forEach(c => { SAVED_CHARGES[c.bank_name] = c; });
                    }
                    // Cache to IndexedDB for offline use
                    if (typeof OfflineManager !== 'undefined') {
                        OfflineManager.cacheCharges(SAVED_CHARGES).catch(function() {});
                    }
                }
            } catch(e) {
                // Offline: load from IndexedDB cache
                if (typeof OfflineManager !== 'undefined') {
                    try {
                        const cached = await OfflineManager.getCachedCharges();
                        if (cached) SAVED_CHARGES = cached;
                    } catch(e2) {}
                }
            }

            // Cache CONFIG in IndexedDB for offline use
            if (typeof OfflineManager !== 'undefined') {
                OfflineManager.cacheConfig(CONFIG).catch(function() {});
            }

            // Load saved additional notes (with offline fallback)
            try {
                const notesResp = await fetch('/api/notes?t=' + Date.now());
                if (notesResp.ok) {
                    const notesData = await notesResp.json();
                    if (notesData.notes) {
                        document.getElementById('additionalNotes').value = notesData.notes;
                        if (typeof OfflineManager !== 'undefined') {
                            OfflineManager.cacheNotes(notesData.notes).catch(function() {});
                        }
                    }
                }
            } catch (e) {
                // Offline: try IndexedDB cache
                if (typeof OfflineManager !== 'undefined') {
                    try {
                        const cachedNotes = await OfflineManager.getCachedNotes();
                        if (cachedNotes) {
                            document.getElementById('additionalNotes').value = cachedNotes;
                        }
                    } catch(e2) {}
                }
            }

            renderBankChips();
            // Filter banks by default location on page load
            if (document.getElementById('quotationLocation')?.value) {
                onLocationChange();
            }

            // Show pending quotations count on load
            updatePendingBadge();
        })();
    </script>
    @endpush
@endsection
