<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;

class AuthGates
{
    public function handle($request, Closure $next)
    {
        $user = \Auth::user();

        if (!app()->runningInConsole() && $user) {
            // $roles = Role::with('permissions')->get();

            // foreach ($roles as $role) {
            //     foreach ($role->permissions as $permissions) {
            //         $permissionsArray[$permissions->title][] = $role->id;
            //     }
            // }

            // foreach ($permissionsArray as $title => $roles) {
            //     Gate::define($title, function (User $user) use ($roles) {
            //         return count(array_intersect($user->roles->pluck('id')->toArray(), $roles)) > 0;
            //     });
            // }
        }

        return $next($request);
    }
}
