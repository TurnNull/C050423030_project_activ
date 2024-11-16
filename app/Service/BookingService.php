<?php 

namespace App\Service;

use App\Repositories\Contracts\BookingRepositoryInterface;
use WorkshopRepositoryInterface;
use App\Models\BookingTransaction;
use App\Models\WorkshopParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService {
    protected $bookingRepository;
    protected $workshopRepository;

    public function __construct(WorkshopRepositoryInterface $workshopRepository, BookingRepositoryInterface $bookingRepository) {
        $this->bookingRepository = $bookingRepository;
        $this->workshopRepository = $workshopRepository;
    }

    public function storeBooking(array $validationData) {
        $existingData = $this->bookingRepository->getOrderDataFromSession();
        $updateData = array_merge($existingData, $validationData);
        $this->bookingRepository->saveToSession($updateData);
        return $updateData;
    } 

    public function isBookingSessionAvailable() {
        return $this->bookingRepository->getOrderDataFromSession() !== null;
    }

    public function getBookingDetails() {
        $orderData = $this->bookingRepository->getOrderDataFromSession();

        if(empty($orderData)) {
            return null;
        }

        $workshop = $this->workshopRepository->find($orderData['workshop_id']);

        $quantity = isset($orderData['quantity']) ? $orderData['quantity'] : 1;
        $subTotalAmount = $workshop->price * $quantity;

        $taxRate = 0.11;
        $totalTax = $subTotalAmount * $taxRate;

        $TotalAmount = $subTotalAmount * $taxRate;

        $orderData['sub_total_amount'] = $subTotalAmount;
        $orderData['total_tax'] = $totalTax;
        $orderData['total_amount'] = $TotalAmount;
        
        $this->bookingRepository->saveToSession($orderData);

        return compact('orderData', 'workshop');
    }

    public function finalizeBookingAndPayment(array $paymentData) {
        $orderData = $this->bookingRepository->getOrderDataFromSession();

        if (!$orderData) {
            throw new \Exception('Booking data is missing from session.');
        }
            Log::info('Order Data', $orderData);
            
        if (!isset($paymentData['total_amount'])) {
            throw new \Exception('Total amount is missing from the order data.');
        }
        if (!isset($paymentData['proof'])) {
            $proofPath = $paymentData['proof']->store('proofs'. 'public');
        }

        DB::beginTransantion();
        try {
            $bookingTransaction = BookingTransaction::create([
                'name' => $orderData['name'],
                'email' => $orderData['email'],
                'phone' => $orderData['phone'],
                'customer_bank_name' => $paymentData['customer_bank_name'],
                'customer_bank_number' => $paymentData['customer_bank_number'],
                'customer_bank_account' => $paymentData['customer_bank_account'],
                'proof' => $proofPath,
                'quantity' => $orderData['quantity'],
                'total_amount' => $orderData['total_amount'],
                'is_paid' => false,
                'workshop_id' => $orderData['workshop_id'],
                'booking_trx_id' => BookingTransaction::generateUniqueTrxId(),
            ]);

            foreach ($orderData['participants'] as $participant) {
                WorkshopParticipant::create([
                    'name' => $participant['name'],
                    'occupation' => $participant['occupation'],
                    'email' => $participant['email'],
                    'workshop_id' => $bookingTransaction->workshop->id,
                    'booking_transaction_id' => $bookingTransaction->id
                ]);
            }
            DB::commit();
            $this->bookingRepository->clearSession();

            return $bookingTransaction->id;
        } catch (\Exception $e) {
            Log::error('Payment processing failed '. $e->getMessage());

            DB::rollBack();

            throw $e;
        }
    }

    public function getMyBookingDetails(array $validated) {
        return $this->bookingRepository->findByTrxIdAndPhoneNumber($validated['booking_trx_id'], $validated['phone']);
    }
}