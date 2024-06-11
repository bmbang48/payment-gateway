<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $guarder = [];

    protected $fillable = [
        'donor_name',
        'donor_email',
        'donation_type',
        'amount',
        'note',
        'status',
        'snap_token',
        'created_at',
        'updated_at'
    ];

    public function setStatusPending()
    {
        $this->attributes['status'] = 'pending';
        self::save();
    }

    public function setStatusSuccess()
    {
        $this->attributes['status'] = 'success';
        self::save();
    }
    public function setStatusFailed()
    {
        $this->attributes['status'] = 'failed';
        self::save();
    }
    public function setStatusExpired()
    {
        $this->attributes['status'] = 'expired';
        self::save();
    }
}
