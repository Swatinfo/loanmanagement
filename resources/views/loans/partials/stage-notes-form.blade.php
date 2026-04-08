{{-- Generic stage notes form partial. Included in stage cards. --}}
{{-- Expects: $assignment (StageAssignment), $loan (LoanDetail), $fields (array of field definitions) --}}
{{-- Optional: $disabled (bool) — renders inputs as disabled/readonly --}}

@php
    $notesData = $assignment->getNotesData();
    $isDisabled = $disabled ?? false;
    // Helper to get field value: saved data → field default → empty
    $fieldValue = function($field) use ($notesData) {
        return $notesData[$field['name']] ?? ($field['default'] ?? '');
    };
@endphp

<div class="mt-2 border-top pt-2">
    <form class="shf-stage-notes-form" data-notes-url="{{ route('loans.stages.notes', [$loan, $assignment->stage_key]) }}">
        <div class="row g-2">
            @foreach($fields as $field)
                <div class="col-sm-{{ $field['col'] ?? 6 }}">
                    <label class="form-label small">{{ $field['label'] }}@if(($field['required'] ?? false) && !$isDisabled) <span class="text-danger">*</span>@endif</label>
                    @if(($field['type'] ?? 'text') === 'textarea')
                        <textarea name="{{ $field['name'] }}" class="shf-input shf-input-sm" rows="2" placeholder="{{ $field['placeholder'] ?? '' }}" {{ $isDisabled ? 'disabled' : '' }}>{{ $fieldValue($field) }}</textarea>
                    @elseif(($field['type'] ?? 'text') === 'select')
                        <select name="{{ $field['name'] }}" class="shf-input shf-input-sm" {{ $isDisabled ? 'disabled' : '' }}>
                            @foreach($field['options'] as $val => $label)
                                <option value="{{ $val }}" {{ $fieldValue($field) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @elseif(($field['type'] ?? 'text') === 'number')
                        <input type="number" name="{{ $field['name'] }}" class="shf-input shf-input-sm" value="{{ $fieldValue($field) }}"
                               step="{{ $field['step'] ?? '1' }}" min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}" placeholder="{{ $field['placeholder'] ?? '' }}" {{ $isDisabled ? 'disabled' : '' }} {{ !empty($field['readonly']) ? 'readonly' : '' }}>
                    @elseif(($field['type'] ?? 'text') === 'currency')
                        <div class="input-group input-group-sm flex-nowrap">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="{{ $field['name'] }}" class="form-control" value="{{ $fieldValue($field) }}"
                                   step="0.01" min="0" placeholder="{{ $field['placeholder'] ?? '' }}" {{ $isDisabled ? 'disabled' : '' }} {{ !empty($field['readonly']) ? 'readonly' : '' }}>
                        </div>
                    @elseif(($field['type'] ?? 'text') === 'date')
                        <input type="text" name="{{ $field['name'] }}" class="shf-input shf-input-sm {{ empty($field['readonly']) ? 'shf-datepicker' : '' }}"
                               value="{{ $fieldValue($field) }}" placeholder="dd/mm/yyyy" autocomplete="off" {{ $isDisabled ? 'disabled' : '' }} {{ !empty($field['readonly']) ? 'readonly style=background:#f8f9fa;' : '' }}>
                    @else
                        <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" class="shf-input shf-input-sm"
                               value="{{ $fieldValue($field) }}" placeholder="{{ $field['placeholder'] ?? '' }}" {{ $isDisabled ? 'disabled' : '' }}>
                    @endif
                    @if(!empty($field['hint']))
                        <small class="text-info d-block" style="font-size:0.7rem;">Original: {{ $field['hint'] }}</small>
                    @endif
                </div>
            @endforeach
            @if(!$isDisabled)
                <div class="col-12">
                    <button type="submit" class="btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Details</button>
                </div>
            @endif
        </div>
    </form>
</div>
