<?php 

namespace App\Repositories;

use App\Models\BookingTransaction;
use Illuminate\Support\Facades\Session;
use App\Repositories\Contracts\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface {
    public function createBooking(array $data) {
        return BookingTransaction::create($data);
    }

    public function findByTrxIdAndPhoneNumber($bookingTrxId, $phoneNumber)
    {
        return BookingTransaction::where('booking_trx_id', $bookingTrxId)->where('phone', $phoneNumber)->first();
    }

    public function saveToSession(array $data)
    {
        Session::put('orderData', $data);
    }

    public function getOrderDataFromSession()
    {
        return Session::get('orderData', []);
    }

    public function updateSessionData(array $data)
    {
        $orderData = session('orderData', []);
        $orderData = array_merge($orderData, $data);
        session(['orderData' => $orderData]);
    }

    public function clearSession()
    {
        Session::forget('orderData');
    }
}