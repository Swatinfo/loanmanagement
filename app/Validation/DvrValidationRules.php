<?php

namespace App\Validation;

/**
 * Shared validation rules for daily visit report create/update flows.
 */
class DvrValidationRules
{
    /**
     * Core fields common to create and update.
     */
    private static function core(): array
    {
        return [
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_type' => 'required|string|max:50',
            'purpose' => 'required|string|max:50',
            'visit_date' => 'required|date_format:d/m/Y',
            'notes' => 'nullable|string|max:5000',
            'outcome' => 'nullable|string|max:5000',
            'follow_up_needed' => 'nullable|boolean',
            'follow_up_date' => 'nullable|required_if:follow_up_needed,1|date_format:d/m/Y|after:today',
            'follow_up_notes' => 'nullable|string|max:5000',
            'quotation_id' => 'nullable|exists:quotations,id',
            'loan_id' => 'nullable|exists:loan_details,id',
        ];
    }

    /**
     * Create allows linking to a parent visit (follow-up chain).
     */
    public static function create(): array
    {
        return self::core() + [
            'parent_visit_id' => 'nullable|exists:daily_visit_reports,id',
        ];
    }

    /**
     * Update cannot change the parent visit.
     */
    public static function update(): array
    {
        return self::core();
    }
}
