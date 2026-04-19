<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\QueryResponse;
use App\Models\StageAssignment;
use App\Models\StageQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class StageQueryService
{
    public function raiseQuery(StageAssignment $assignment, string $queryText, int $userId): StageQuery
    {
        $query = StageQuery::create([
            'stage_assignment_id' => $assignment->id,
            'loan_id' => $assignment->loan_id,
            'stage_key' => $assignment->stage_key,
            'query_text' => trim($queryText),
            'raised_by' => $userId,
            'status' => 'pending',
        ]);

        // Auto-assign the query to the current stage owner (the person assigned to this stage)
        // The query is directed at whoever currently holds the stage assignment
        if ($assignment->assigned_to && $assignment->assigned_to !== $userId) {
            // Query goes to the stage assignee — no model field for this, handled via stage assignment
        }

        ActivityLog::log('raise_query', $query, [
            'loan_number' => $assignment->loan->loan_number,
            'stage_key' => $assignment->stage_key,
            'preview' => Str::limit($queryText, 100),
        ]);

        // Notify the stage assignee
        if ($assignment->assigned_to) {

            $message = "A query was raised for Loan of ({$assignment->loan->customer_name}) #{$assignment->loan->loan_number} on ".$assignment->stage_key.': '.Str::limit($queryText, 80);

            app(NotificationService::class)->notify(
                $assignment->assigned_to,
                'Query Raised',
                $message,
                'warning',
                $assignment->loan_id,
                $assignment->stage_key,
            );
        }

        return $query;
    }

    public function respondToQuery(StageQuery $query, string $responseText, int $userId): QueryResponse
    {
        $response = QueryResponse::create([
            'stage_query_id' => $query->id,
            'response_text' => trim($responseText),
            'responded_by' => $userId,
        ]);

        $query->update(['status' => 'responded']);

        // Notify the user who raised the query that a response was given
        if ($query->raised_by !== $userId) {
            $message = 'Your query on '.$query->stage_key." for Loan of ({$query->loan->customer_name}) #{$query->loan->loan_number} has been responded to.";

            app(NotificationService::class)->notify(
                $query->raised_by,
                'Query Responded',
                $message,
                'info',
                $query->loan_id,
                $query->stage_key,
            );
        }

        return $response;
    }

    public function resolveQuery(StageQuery $query, int $userId): StageQuery
    {
        $query->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);

        return $query->fresh();
    }

    public function getQueriesForStage(StageAssignment $assignment): Collection
    {
        return $assignment->queries()
            ->with(['raisedByUser', 'responses.respondedByUser'])
            ->latest()
            ->get();
    }
}
