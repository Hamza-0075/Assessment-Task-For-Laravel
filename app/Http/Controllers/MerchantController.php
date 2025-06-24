<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $from = Carbon::parse($request->input('from'))->startOfDay();
        $to = Carbon::parse($request->input('to'))->endOfDay();

        /** @var Merchant $merchant */
        $merchant = $request->user()->merchant;

        $orders = $merchant->orders()->whereBetween('created_at', [$from, $to])->get();

        $count = $orders->count();
        $revenue = $orders->sum('subtotal');

        $commissionOwed = $orders
        ->whereNotNull('affiliate_id')
        ->where('payout_status', Order::STATUS_UNPAID)
        ->sum('commission_owed');

        return response()->json([
            'count' => $count,
            'commissions_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }
}
