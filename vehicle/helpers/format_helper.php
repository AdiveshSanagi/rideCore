<?php
/**
 * Helper functions for formatting data
 */

/**
 * Format currency in Indian Rupees
 * 
 * @param float $amount The amount to format
 * @param int $decimals Number of decimal places
 * @return string Formatted amount with ₹ symbol
 */
function formatIndianRupee($amount, $decimals = 2) {
    return '₹' . number_format($amount, $decimals);
}
?>