<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');
        $permissionModel = $permission instanceof Permission ? $permission : Permission::find($permission);

        if ($permissionModel && in_array($permissionModel->name, ['manage users', 'manage roles'], true)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');
        $permissionId = $permission instanceof Permission ? $permission->id : $permission;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permissionId)],
        ];
    }
}
