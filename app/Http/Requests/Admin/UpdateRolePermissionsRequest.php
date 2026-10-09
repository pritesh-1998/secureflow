<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');
        $roleModel = $role instanceof Role ? $role : Role::find($role);

        if ($roleModel && $roleModel->name === 'admin') {
            $permissions = $this->input('permissions', []);
            if (! is_array($permissions) || ! in_array('manage users', $permissions) || ! in_array('manage roles', $permissions)) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }
}
