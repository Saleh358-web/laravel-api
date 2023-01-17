<?php

namespace App\Permissions;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissionsTrait 
{
    public function givePermissionsTo(... $permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }
    
    public function withdrawPermissionsTo( ... $permissions )
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }
  
    public function refreshPermissions( ... $permissions )
    {
        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }

    public function hasPermissionTo($permission)
    {
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }
  
    public function hasPermissionThroughRole($permission)
    {
        
        foreach ($permission->roles as $role) {
            if($this->roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function allowedTo(string $permissionsString)
    {
        /* Super admins are allowed all permissions and full access */
        if ($this->roles->contains('slug', 'super-admin')) {
            return true;
        }

        $permissions = explode('/', $permissionsString);
        foreach ($permissions as $permission) {
            if ($this->permissions->contains('slug', $permission)) {
                return true;
            }
        }
        return false;
    }
  
    public function hasRole(string $rolesString)
    {
        $roles = explode('/', $rolesString);
        foreach ($roles as $role) {
            if ($this->roles->contains('slug', $role)) {
                return true;
            }
        }
        return false;
    }
  
    public function roles()
    {
        return $this->belongsToMany(Role::class,'users_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'users_permissions');
    }
  
    protected function hasPermission($permission)
    {
        return (bool) $this->permissions->where('slug', $permission->slug)->count();
    }
  
    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('slug',$permissions)->get();
    }

}