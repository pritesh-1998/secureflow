<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdatePermissionMatrixRequest extends FormRequest
{
    public function authorize(): bool
    {
        $matrix = $this->input('matrix', []);
        if (is_array($matrix) && isset($matrix['admin'])) {
            $adminPerms = is_array($matrix['admin']) ? $matrix['admin'] : [];
            if (! in_array('manage users', $adminPerms, true) || ! in_array('manage roles', $adminPerms, true)) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'matrix' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $matrix = $this->input('matrix', []);
            if (! is_array($matrix)) {
                $validator->errors()->add('matrix', 'Matrix data must be an array.');

                return;
            }

            $validRoles = Role::pluck('name')->all();
            $validPermissions = Permission::pluck('name')->all();

            foreach ($matrix as $roleName => $permissions) {
                if (! in_array($roleName, $validRoles, true)) {
                    $validator->errors()->add("matrix.{$roleName}", "Role '{$roleName}' does not exist.");

                    continue;
                }

                if (! is_array($permissions)) {
                    $validator->errors()->add("matrix.{$roleName}", "Permissions for '{$roleName}' must be an array.");

                    continue;
                }

                foreach ($permissions as $permissionName) {
                    if (! in_array($permissionName, $validPermissions, true)) {
                        $validator->errors()->add("matrix.{$roleName}", "Permission '{$permissionName}' does not exist.");
                    }
                }
            }
        });
    }
}
