<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        //check duplication of oder
        if (Order::where('external_order_id', $data['order_id'])->exists()) {
            return;
        }

        //find merchant
        $merchant = Merchant::where('domain', $data['merchant_domain'])->firstOrFail();

        //find affiliate by discount code
        $affiliate = Affiliate::where('discount_code', $data['discount_code'])
            ->where('merchant_id', $merchant->id)
            ->first();

        if (!$affiliate) {
            // return null;
            return;
        }

        $existingUser = User::where('email', $data['customer_email'])->first();
        if (!$existingUser) {
            if ($merchant->turn_customers_into_affiliates) {
                $this->affiliateService->register(
                    $merchant,
                    $data['customer_email'],
                    $data['customer_name'],
                    $merchant->default_commission_rate
                );
            }
        }

        Order::create([
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
            'discount_code' => $data['discount_code'],
            'external_order_id' => $data['order_id'],
            'payout_status' => Order::STATUS_UNPAID,
        ]);
    }
}
