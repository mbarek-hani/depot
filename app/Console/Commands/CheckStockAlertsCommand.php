<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckStockAlertsCommand extends Command
{
    protected $signature = 'stock:check-alerts';

    protected $description = 'فحص المخزون وإرسال تنبيهات الكميات المنخفضة وقرب انتهاء الصلاحية';

    public function handle(AlertService $alerts): int
    {
        $sent = $alerts->checkAll();

        $this->info("تم فحص المخزون وإنشاء {$sent} تنبيهًا جديدًا.");

        return self::SUCCESS;
    }
}
