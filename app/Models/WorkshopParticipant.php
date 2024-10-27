<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkshopParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'workshop_participants';
    protected $guarded = ['id'];

    public function workshop() {
        return $this->belongsTo(Workshop::class, 'workshop_id', 'id');
    }

    public function bookingTransaction() {
        return $this->belongsTo(BookingTransaction::class, 'booking_transaction_id', 'id');
    }
}
