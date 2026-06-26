<?php

namespace App\Models;

use CodeIgniter\Model;

class QrTokenModel extends Model
{
    protected $table         = 'qr_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $updatedField  = ''; // table only has created_at
    protected $allowedFields = [
        'location_id', 'token', 'issued_at', 'expires_at', 'used_at',
    ];

    /** Finds an unused, unexpired token for a location. */
    public function findValid(string $token, int $locationId): ?array
    {
        return $this->where('token', $token)
            ->where('location_id', $locationId)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->where('used_at', null)
            ->first();
    }
}
