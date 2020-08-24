<?php

namespace Tritility\MatthewCSVTest;

use Illuminate\Console\Command;

class GenerateCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GenerateCSVSignature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a CSV file based an an xls file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Generating CSV file....');
        $csv = new \Tritility\MatthewCSVTest\MatthewCSVTestController();
        $result = $csv->convertSpreadSheetContent();
        $this->info('Operation complete.');
    }
}