<?php

namespace App\Policies;

use App\Models\ProcurementItem;
use App\Models\ProcurementRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcurementPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability)
    {
        if ($user->role === 'super_admin') {
            if ($ability === 'cancel') {
                return null;
            }

            return true;
        }
    }

    /**
     * Determine if the user can view the procurement request.
     */
    public function view(User $user, ProcurementRequest $procurement)
    {
        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

        // Holding roles can view all
        if (in_array($user->role, $holdingRoles)) {
            return true;
        }

        // Purchasing in holding company can view all
        if ($user->role === 'purchasing' && $user->company && $user->company->is_holding) {
            return true;
        }

        // Manager can view requests from any company as long as they are the unit approver
        if ($user->role === 'manager') {
            $unit = $procurement->unit;

            return $unit && $unit->approval_by === $user->id;
        }

        // Non-holding users must be in the same company
        if ($procurement->company_id !== $user->company_id) {
            return false;
        }

        // Unit role can only see their own unit's data
        if ($user->role === 'unit' && $procurement->unit_id !== $user->unit_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can update the procurement request.
     */
    public function update(User $user, ProcurementRequest $procurement)
    {
        // Only owner can edit, and only if status is submitted
        return $procurement->user_id === $user->id && $procurement->status === 'submitted';
    }

    /**
     * Determine if the user can approve the procurement request.
     */
    public function approve(User $user, ProcurementRequest $procurement)
    {
        // Manager can approve requests from any company as long as they are the approver
        if ($user->role === 'manager') {
            $unit = $procurement->unit;
            if (! $unit || $unit->approval_by !== $user->id) {
                return false;
            }
        }

        // Check if user's role can approve the current status
        $nextStatus = ProcurementRequest::getNextStatus($procurement->status, $user->role, $procurement->request_type, $procurement->total_amount);

        return $nextStatus !== null;
    }

    /**
     * Determine if the user can reject the procurement request.
     */
    public function reject(User $user, ProcurementRequest $procurement)
    {
        // Scope Check: User must be able to view the request first (handles company/unit logic)
        if (! $this->view($user, $procurement)) {
            return false;
        }

        // For now, any authenticated user with appropriate role can reject
        // You may want to add more specific logic here
        $approverRoles = [
            'manager',
            'budgeting',
            'director_company',
            'finance_manager_holding',
            'finance_director_holding',
            'general_director_holding',
        ];

        return in_array($user->role, $approverRoles);
    }

    /**
     * Determine if the user can reject a specific item.
     */
    public function rejectItem(User $user, ProcurementItem $item)
    {
        $procurement = $item->procurementRequest;

        // Only manager can reject items
        if ($user->role !== 'manager') {
            return false;
        }

        // Items can only be rejected in submitted status
        if ($procurement->status !== 'submitted') {
            return false;
        }

        // Manager must be the approver of the unit
        $unit = $procurement->unit;
        if (! $unit || $unit->approval_by !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can cancel rejection of a specific item.
     */
    public function cancelRejectItem(User $user, ProcurementItem $item)
    {
        // Same authorization as rejectItem
        return $this->rejectItem($user, $item);
    }

    /**
     * Determine if the user can toggle item check status.
     */
    public function toggleItemCheck(User $user, ProcurementItem $item)
    {
        $procurement = $item->procurementRequest;

        // Only purchasing team can check items
        if ($user->role !== 'purchasing') {
            return false;
        }

        // Only items in purchasing phase can be checked
        if ($procurement->status !== 'processing') {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can delete a document.
     */
    public function deleteDocument(User $user, ProcurementRequest $procurement)
    {
        // Only owner can delete documents if status is submitted
        return $procurement->user_id === $user->id && $procurement->status === 'submitted';
    }

    /**
     * Determine if the user can cancel the procurement request.
     */
    public function cancel(User $user, ProcurementRequest $procurement)
    {
        // Only owner can cancel, and only if status is submitted or draft
        return $procurement->user_id === $user->id && in_array($procurement->status, ['draft', 'submitted']);
    }
}
