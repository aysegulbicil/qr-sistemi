<?php

namespace App\Services;

/**
 * Shared employee logic (avoids duplication between create/update).
 */
class EmployeeService
{
    /** Builds the users payload from the submitted form (HR + login fields). */
    public function payloadFromRequest($request, bool $isEdit): array
    {
        $status = $request->getPost('employment_status');
        $sType  = $request->getPost('salary_type');

        $data = [
            'full_name'         => trim((string) $request->getPost('full_name')),
            'employee_code'     => $request->getPost('employee_code') ?: null,
            'national_id'       => $request->getPost('national_id') ?: null,
            'birth_date'        => $request->getPost('birth_date') ?: null,
            'phone'             => $request->getPost('phone') ?: null,
            'contact_email'     => $request->getPost('contact_email') ?: null,
            'email'             => $request->getPost('contact_email') ?: null,
            'address'           => $request->getPost('address') ?: null,
            'department_id'     => $request->getPost('department_id') ?: null,
            'position_id'       => $request->getPost('position_id') ?: null,
            'shift_id'          => $request->getPost('shift_id') ?: null,
            'employment_status' => in_array($status, ['active', 'passive', 'terminated'], true) ? $status : 'active',
            'hire_date'         => $request->getPost('hire_date') ?: null,
            'salary_type'       => in_array($sType, ['monthly', 'daily', 'hourly'], true) ? $sType : 'monthly',
            'salary_amount'     => $request->getPost('salary_amount') !== '' ? (float) $request->getPost('salary_amount') : 0,
            'iban'              => $request->getPost('iban') ?: null,
            'username'          => trim((string) $request->getPost('username')),
            'role'              => $request->getPost('role') === 'admin' ? 'admin' : 'employee',
            'is_active'         => $request->getPost('is_active') ? 1 : 0,
        ];

        $password = (string) $request->getPost('password');
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $data;
    }
}
