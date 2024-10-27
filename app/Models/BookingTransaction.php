<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_transactions';
    protected $guarded = ['id'];

    public static function generateUniqueTrxId() {
        $prefix = 'AKT';
        do {
            $randomString = $prefix . mt_rand(1000, 9999);
        } while (self::where('booking_trx_id', $randomString)->exists());

        return $randomString;
    }
    
    public function participants() {
        return $this->hasMany(WorkshopParticipant::class);
    }

    public function workshop() {
        return $this->belongsTo(Workshop::class, 'workshop_id', 'id');
    }
}
