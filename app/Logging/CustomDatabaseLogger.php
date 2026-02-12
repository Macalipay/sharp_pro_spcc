<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\DB;

class CustomDatabaseLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('database');

        $logger->pushHandler(new class extends AbstractProcessingHandler {
            protected function write(array $record): void
            {
                DB::table('app_logs')->insert([
                    'level' => $record['level_name'],
                    'message' => $record['message'],
                    'context' => json_encode($record['context']),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'log_date' => now(),
                ]);
            }
        });

        return $logger;
    }
}
