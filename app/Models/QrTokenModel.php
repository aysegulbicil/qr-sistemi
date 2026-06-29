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

    /** Invalidates all still-unused tokens for a location (artik issue() tarafindan cagrilmiyor). */
    public function invalidateForLocation(int $locationId): void
    {
        $this->builder()
            ->where('location_id', $locationId)
            ->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s')]);
    }

    /** Tek-kullanim: yalnizca hala kullanilmamissa tuketir. true: bu cagri tuketti. */
    public function markUsedById(int $id): bool
    {
        $this->builder()
            ->where('id', $id)
            ->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows() > 0;
    }

    /** Teshis amacli: dogrulama hatasinin nedeni (missing|expired|used|ok). */
    public function classifyFailure(string $token, int $locationId): string
    {
        $row = $this->where('token', $token)->where('location_id', $locationId)->first();
        if ($row === null) {
            return 'missing';
        }
        if (! empty($row['used_at'])) {
            return 'used';
        }
        if ($row['expires_at'] < date('Y-m-d H:i:s')) {
            return 'expired';
        }

        return 'ok';
    }
}
