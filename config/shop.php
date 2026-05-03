<?php

return [
    'tax_rate' => 0.05,
    'return_window_days' => 10,
    'low_stock_threshold' => (int) env('SHOP_LOW_STOCK_THRESHOLD', 5),
    'shipping_zones' => [
        'metro' => ['label' => 'Metro delivery', 'fee' => 7.99, 'eta_days' => 2],
        'regional' => ['label' => 'Regional delivery', 'fee' => 14.99, 'eta_days' => 4],
        'pickup' => ['label' => 'Store pickup', 'fee' => 0.00, 'eta_days' => 1],
    ],
];
