<?php

namespace TomatoPHP\FilamentMediaManager\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

/**
 * Single place that decides who may see a folder and whether a folder password is correct.
 * The panel and the API both rely on these rules.
 */
class FolderAccess
{
    /**
     * Folders the given user may see: unowned folders, folders owned by the user,
     * public folders, and folders explicitly shared with the user.
     * Without a user nothing is visible.
     */
    public static function visibleTo(?Authenticatable $user, ?Builder $query = null): Builder
    {
        $query ??= config('filament-media-manager.model.folder')::query();
        $query->withoutGlobalScope('user');

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $type = get_class($user);
        $id = $user->getAuthIdentifier();

        return $query->where(function (Builder $query) use ($type, $id) {
            $query
                ->whereNull('user_id')
                ->orWhere(function (Builder $query) use ($type, $id) {
                    $query->where('user_id', $id)->where('user_type', $type);
                })
                ->orWhere('is_public', true)
                ->orWhere(function (Builder $query) use ($type, $id) {
                    $query->where('has_user_access', true)
                        ->whereHas('users', function ($query) use ($type, $id) {
                            $query->where('model_id', $id)->where('model_type', $type);
                        });
                });
        });
    }

    public static function canView(?Authenticatable $user, $folder): bool
    {
        return $folder && self::visibleTo($user)->whereKey($folder->getKey())->exists();
    }

    /**
     * Compare a supplied password with the folder password the same way the panel does.
     * A protected folder without a stored password can never be opened.
     */
    public static function passwordMatches($folder, mixed $given): bool
    {
        if (! $folder->is_protected) {
            return true;
        }

        $stored = (string) $folder->password;
        $given = is_scalar($given) ? (string) $given : '';

        if ($stored === '' || $given === '') {
            return false;
        }

        if (Hash::info($stored)['algoName'] !== 'unknown') {
            return Hash::check($given, $stored);
        }

        return hash_equals($stored, $given);
    }
}
