<?php

namespace App\Validation;

/**
 * Shared validation rules for loan create/update flows.
 * Controllers still call $request->validate() inline — they just pull rules
 * from here instead of repeating the same 12-field ruleset per action.
 */
class LoanValidationRules
{
    /**
     * Rules for creating or updating a loan (identical across both paths).
     */
    public static function upsert(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_type' => 'required|in:proprietor,partnership_llp,pvt_ltd,salaried',
            'loan_amount' => 'required|numeric|min:1|max:1000000000000',
            'bank_id' => 'required|exists:banks,id',
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'pan_number' => 'required|string|max:10',
            'assigned_advisor' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Rules for status change (active/on_hold/cancelled) with required reason
     * on non-active transitions.
     */
    public static function statusChange(): array
    {
        return [
            'status' => 'required|in:active,on_hold,cancelled',
            'reason' => 'required_unless:status,active|nullable|string|max:1000',
        ];
    }
}
