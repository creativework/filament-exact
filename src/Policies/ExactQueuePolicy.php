<?php

namespace CreativeWork\FilamentExact\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class ExactQueuePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        // If no permission system, allow everything
        if (! method_exists($user, 'can')) {
            return true;
        }

        return $user->can('view_any_exact_queue');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, $exactQueue = null): bool
    {
        // If no permission system, allow everything
        if (! method_exists($user, 'can')) {
            return true;
        }

        return $user->can('view_exact_queue');
    }

    /**
     * Determine whether the user can authorize ExactOnline.
     */
    public function authorize($user): bool
    {
        // If no permission system, allow everything
        if (! method_exists($user, 'can')) {
            return true;
        }

        return $user->can('authorize_exact_queue');
    }

    /**
     * Determine whether the user can duplicate queue items.
     */
    public function duplicate($user, $exactQueue = null): bool
    {
        // If no permission system, allow everything
        if (! method_exists($user, 'can')) {
            return true;
        }

        return $user->can('duplicate_exact_queue');
    }

    /**
     * Determine whether the user can cancel queue items.
     */
    public function cancel($user, $exactQueue = null): bool
    {
        // If no permission system, allow everything
        if (! method_exists($user, 'can')) {
            return true;
        }

        return $user->can('cancel_exact_queue');
    }

    /**
     * Determine whether the user can prioritize queue items.
     */
    public function prioritize($user, $exactQueue = null): bool
    {
        // If no permission system, allow everything
        if (! method_exists($user, 'can')) {
            return true;
        }

        return $user->can('prioritize_exact_queue');
    }
}
