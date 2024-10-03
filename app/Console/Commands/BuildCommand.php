<?php

namespace App\Console\Commands;

use App\Services\BuildService;
use Illuminate\Console\Command;

class BuildCommand extends Command
{
    // Устанавливаем имя команды для Artisan
    protected $signature = 'build';

    // Описание команды
    protected $description = 'Donor-recipient data transfer and technical tag cleaning';

    // Логика выполнения команды
    public function handle()
    {
        (new \App\Services\BuildService)->copyW3xConfig();
    }
}
