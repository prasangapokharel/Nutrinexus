<?php

$deliveryfee = 150;
$coupon = 50; // Assuming a fixed coupon discount for demonstration

$finaldeliveryfee = $deliveryfee - $coupon;

if ($deliveryfee > 0) {
    echo "Delivery fee is applicable: $finaldeliveryfee";
} else {
    echo "Delivery fee is not applicable";
}