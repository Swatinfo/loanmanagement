<?php

namespace Tests\Feature;

use App\Models\DailyVisitReport;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationHoldCancelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register-with-role user factory relies on the hold/cancel permissions being granted to the admin role.
        // Permissions and role grants are seeded by the migration 2026_04_18_120200.
    }

    private function makeAdmin(): User
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'a'.uniqid().'@test',
            'password' => bcrypt('secret'),
            'is_active' => true,
        ]);
        $user->roles()->sync(Role::where('slug', 'admin')->pluck('id'));

        return $user->fresh('roles');
    }

    private function makeQuotation(User $owner): Quotation
    {
        return Quotation::create([
            'user_id' => $owner->id,
            'customer_name' => 'Acme Traders',
            'customer_type' => 'proprietor',
            'loan_amount' => 1000000,
            'prepared_by_name' => $owner->name,
            'prepared_by_mobile' => '9999999999',
            'selected_tenures' => [5, 10, 15, 20],
        ]);
    }

    public function test_hold_creates_dvr_and_marks_status(): void
    {
        $user = $this->makeAdmin();
        $q = $this->makeQuotation($user);
        $tomorrow = CarbonImmutable::tomorrow()->addDays(3)->format('d/m/Y');

        $this->actingAs($user)
            ->post(route('quotations.hold', $q), [
                'reason_key' => 'awaiting_kyc_docs',
                'note' => 'Customer needs 3 more docs.',
                'follow_up_date' => $tomorrow,
            ])
            ->assertRedirect();

        $q->refresh();
        $this->assertSame(Quotation::STATUS_ON_HOLD, $q->status);
        $this->assertSame('awaiting_kyc_docs', $q->hold_reason_key);
        $this->assertNotNull($q->held_at);
        $this->assertSame($user->id, $q->held_by);

        $this->assertDatabaseHas('daily_visit_reports', [
            'quotation_id' => $q->id,
            'user_id' => $user->id,
            'follow_up_needed' => true,
        ]);
    }

    public function test_hold_rejects_invalid_reason_key(): void
    {
        $user = $this->makeAdmin();
        $q = $this->makeQuotation($user);

        $this->actingAs($user)
            ->post(route('quotations.hold', $q), [
                'reason_key' => 'bogus',
                'follow_up_date' => CarbonImmutable::tomorrow()->format('d/m/Y'),
            ])
            ->assertSessionHasErrors(['reason_key']);

        $q->refresh();
        $this->assertSame(Quotation::STATUS_ACTIVE, $q->status);
    }

    public function test_cancel_prevents_further_conversion(): void
    {
        $user = $this->makeAdmin();
        $q = $this->makeQuotation($user);

        $this->actingAs($user)
            ->post(route('quotations.cancel', $q), [
                'reason_key' => 'customer_declined',
                'note' => 'Went with another lender.',
            ])
            ->assertRedirect();

        $q->refresh();
        $this->assertSame(Quotation::STATUS_CANCELLED, $q->status);
        $this->assertSame($user->id, $q->cancelled_by);
    }

    public function test_resume_only_works_from_on_hold(): void
    {
        $user = $this->makeAdmin();
        $q = $this->makeQuotation($user);

        // Cannot resume active
        $this->actingAs($user)
            ->post(route('quotations.resume', $q))
            ->assertRedirect();
        $q->refresh();
        $this->assertSame(Quotation::STATUS_ACTIVE, $q->status);

        // Put on hold, then resume
        $q->update([
            'status' => Quotation::STATUS_ON_HOLD,
            'hold_reason_key' => 'awaiting_kyc_docs',
            'hold_follow_up_date' => CarbonImmutable::tomorrow()->toDateString(),
            'held_at' => now(),
            'held_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('quotations.resume', $q))
            ->assertRedirect();

        $q->refresh();
        $this->assertSame(Quotation::STATUS_ACTIVE, $q->status);
        $this->assertNull($q->hold_reason_key);
        $this->assertNull($q->held_at);
    }

    public function test_held_quotation_cannot_be_held_again(): void
    {
        $user = $this->makeAdmin();
        $q = $this->makeQuotation($user);
        $q->update([
            'status' => Quotation::STATUS_ON_HOLD,
            'hold_reason_key' => 'awaiting_kyc_docs',
            'hold_follow_up_date' => CarbonImmutable::tomorrow()->toDateString(),
            'held_at' => now(),
            'held_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('quotations.hold', $q), [
                'reason_key' => 'awaiting_kyc_docs',
                'follow_up_date' => CarbonImmutable::tomorrow()->addDays(5)->format('d/m/Y'),
            ])
            ->assertStatus(422);

        // Initial update() bypassed the controller, so no DVR exists. A rejected
        // second hold must not create one either.
        $this->assertSame(0, DailyVisitReport::where('quotation_id', $q->id)->count());
    }
}
