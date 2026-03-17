<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\CheckExpiringStockJob;
use App\Jobs\CheckLowStockJob;

// Schedule the Low Stock checker to run daily at midnight
Schedule::job(new CheckLowStockJob)->daily();

// Schedule the Expiring Stock checker to run weekly on Sunday at 2 AM
Schedule::job(new CheckExpiringStockJob)->weeklyOn(0, '02:00');
