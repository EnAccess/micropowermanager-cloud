<?php


namespace Inensus\KelinMeter\Console\Commands;


use App\Console\Commands\AbstractSharedCommand;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Inensus\KelinMeter\Services\KelinCredentialService;
use Inensus\KelinMeter\Services\MinutelyConsumptionService;


class ReadMinutelyMeterConsumptions extends AbstractSharedCommand
{
    protected $signature = 'kelin-meter:read-minutely-consumptions';
    protected $description = 'Reads daily meter consumptions.';

    public function __construct(
        private  MinutelyConsumptionService $minutelyConsumptionService,
        private  KelinCredentialService $credentialService
    ) {
        parent::__construct();
    }

  public  function runInCompanyScope(): void
    {
        $credentials = $this->credentialService->getCredentials();
        $timeStart = microtime(true);
        $this->info('#############################');
        $this->info('# Kelin Meter Package #');
        $startedAt = Carbon::now()->toIso8601ZuluString();
        $this->info('read-minutely-consumptions command started at ' . $startedAt);
        if ($credentials->is_authenticated==1){
            $this->minutelyConsumptionService->getMinutelyDataFromAPI();
        }else{
            $this->info('# Kelin credentials not authenticated #');
        }
        $timeEnd = microtime(true);
        $totalTime = $timeEnd - $timeStart;
        $this->info("Took " . $totalTime . " seconds.");
        $this->info('#############################');
    }
}