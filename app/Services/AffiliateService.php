<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        // Check if the email is already used by a merchant

        $existingMerchantUser = User::where('email', $email)->where('type', User::TYPE_MERCHANT)->first();
        if ($existingMerchantUser) {
            throw new AffiliateCreateException("Email already in use by a merchant.");
        }

        // Check if the email is already used by an affiliate
        $existingAffiliateUser = User::where('email', $email)->where('type', User::TYPE_AFFILIATE)->first();
        if ($existingAffiliateUser) {
            throw new AffiliateCreateException("Email already in use by another affiliate.");
        }

        $discount = $this->apiService->createDiscountCode($merchant);
        $discountCode = $discount['code'];

        //Create a new user for the affiliate
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE,
            'password' => bcrypt(Str::random(16)),
        ]);

        //Create the affiliate record
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode,
        ]);

        Mail::to($user->email)->send(new AffiliateCreated($affiliate));

        return $affiliate;

    }
}
