<?php
/**
 * UrbanDog Payments
 *
 * Handles manual payment logic and commissions.
 *
 * @package UrbanDog
 */

if (!defined('ABSPATH')) {
    exit;
}

class UD_Payments
{

    /**
     * Initialize payment-related hooks.
     */
    public static function init(): void
    {
        // Handled mostly via class-admin.php for now (MVP manual flow)
    }

    /**
     * Calculate payout and commission.
     * 
     * @param float $total_amount The total paid by the owner.
     * @return array [payout, commission]
     */
    public static function calculate_split(float $total_amount): array
    {
        $commission = round($total_amount * 0.25, 2);
        $payout = $total_amount - $commission;

        return [
            'commission' => $commission,
            'payout' => $payout,
        ];
    }
}
