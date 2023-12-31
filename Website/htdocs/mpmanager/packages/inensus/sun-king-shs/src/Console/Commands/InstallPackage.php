<?php
namespace Inensus\SunKingSHS\Console\Commands;

use App\Models\Meter\MeterTariff;
use Illuminate\Console\Command;
use Inensus\SunKingSHS\Services\SunKingCredentialService;
use Inensus\SunKingSHS\Services\ManufacturerService;
use Inensus\SunKingSHS\Services\MenuItemService;

class InstallPackage extends Command
{
    protected $signature = 'sun-king-shs:install';
    protected $description = 'Install SunKingSHS Package';

    public function __construct(
        private MenuItemService $menuItemService,
        private ManufacturerService $manufacturerService,
        private SunKingCredentialService $credentialService,
        private MeterTariff $meterTariff,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Installing SunKingSHS Integration Package\n');

        $this->manufacturerService->register();
        $this->credentialService->createCredentials();

        $meterTariff = $this->meterTariff->newQuery()->where('name', 'SunKing SHS Initial Tariff')->first();
        if (!$meterTariff) {
            $meterTariff = $this->meterTariff->newQuery()->create([
                'name' => 'SunKing SHS Initial Tariff',
                'price' => 0,
                'total_price' => 0,
                'currency' => 'MZN',
                'factor' => MeterTariff::SHS_FACTOR,
            ]);
        }
        $this->info('Package installed successfully..');
    }

    private function publishMigrations()
    {
        $this->info('Copying migrations\n');
        $this->call('vendor:publish', [
            '--provider' => "Inensus\SunKingSHS\Providers\SunKingSHSServiceProvider",
            '--tag' => "migrations"
        ]);
    }

    private function createDatabaseTables()
    {
        $this->info('Creating database tables\n');
        $this->call('migrate');
    }

    private function publishVueFiles()
    {
        $this->info('Copying vue files\n');
        $this->call('vendor:publish', [
            '--provider' => "Inensus\SunKingSHS\Providers\SunKingSHSServiceProvider",
            '--tag' => "vue-components"
        ]);
    }

    private function createPluginRecord()
    {
        $this->call('plugin:add', [
            'name' => "SunKingSHS",
            'composer_name' => "inensus/sun-king-shs",
            'description' => "SunKingSHS integration package for MicroPowerManager",
        ]);
    }

    private function createMenuItems()
    {
        $menuItems = $this->menuItemService->createMenuItems();
        $this->call('menu-items:generate', [
            'menuItem' => $menuItems['menuItem'],
            'subMenuItems' => $menuItems['subMenuItems'],
        ]);
    }
}