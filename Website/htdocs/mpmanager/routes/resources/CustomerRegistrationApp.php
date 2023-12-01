<?php

use App\Http\Requests\AndroidAppRequest;
use App\Http\Resources\ApiResource;
use App\Services\AddressesService;
use App\Services\AddressGeographicalInformationService;
use App\Services\GeographicalInformationService;
use App\Services\MeterService;
use App\Services\PersonService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use MPM\Device\DeviceAddressService;
use MPM\Device\DeviceService;
use MPM\Meter\MeterDeviceService;


Route::group(['prefix' => 'customer-registration-app'], static function () {
    Route::get('/people', 'PersonController@index');
    Route::get('/manufacturers', 'ManufacturerController@index');
    Route::get('/meter-types', 'MeterTypeController@index');
    Route::get('/tariffs', 'MeterTariffController@index');
    Route::get('/cities', 'CityController@index');
    Route::get('/connection-groups', 'ConnectionGroupController@index');
    Route::get('/connection-types', 'ConnectionTypeController@index');
    Route::get('/sub-connection-types', 'SubConnectionTypeController@index');
    Route::post('/', static function (AndroidAppRequest $r) {
        try {
            DB::connection('shard')->beginTransaction();
            $personService = App::make(PersonService::class);
            $meterService = App::make(MeterService::class);
            $deviceService = App::make(DeviceService::class);
            $meterDeviceService = App::make(MeterDeviceService::class);
            $addressService = App::make(AddressesService::class);
            $deviceAddressService = App::make(DeviceAddressService::class);
            $geographicalInformationService = App::make(GeographicalInformationService::class);
            $addressGeographicalInformationService = App::make(AddressGeographicalInformationService::class);
            $serialNumber = $r->input('serial_number');
            $meter = $meterService->getBySerialNumber($serialNumber);
            $phone = $r->input('phone');

            if ($meter) {
                throw new \Exception('Meter already exists');
            }

            $person = $personService->getByPhoneNumber($phone);
            $manufacturerId = $r->input('manufacturer');
            $meterTypeId = $r->input('meter_type');
            $connectionTypeId = $r->input('connection_type_id');
            $connectionGroupId = $r->input('connection_group_id');
            $tariffId = $r->input('tariff_id');
            $cityId = $r->input('city_id');
            $geoPoints = $r->input('geo_points');
            if ($person === null) {
                $r->attributes->add(['is_customer' => 1]);
                $personService = App::make(PersonService::class);
                $person = $personService->createFromRequest($r);
            }
            $meterData = [
                'serial_number' => $serialNumber,
                'connection_group_id' => $connectionGroupId,
                'manufacturer_id' => $manufacturerId,
                'meter_type_id' => $meterTypeId,
                'connection_type_id' => $connectionTypeId,
                'tariff_id' => $tariffId,
                'in_use' => 1,
            ];
            $meter = $meterService->create($meterData);
            $device = $deviceService->make([
                'person_id' => $person->id,
                'device_serial' => $meter->serial_number,
            ]);
            $meterDeviceService->setAssigned($device);
            $meterDeviceService->setAssignee($meter);
            $meterDeviceService->assign();
            $deviceService->save($device);
            $addressData = [
                'city_id' => $cityId ?? 1,
            ];
            $address = $addressService->make($addressData);
            $deviceAddressService->setAssigned($address);
            $deviceAddressService->setAssignee($device);
            $deviceAddressService->assign();
            $addressService->save($address);
            $geographicalInformation = $geographicalInformationService->make([
                'points' => $geoPoints,
            ]);
            $addressGeographicalInformationService->setAssigned($geographicalInformation);
            $addressGeographicalInformationService->setAssignee($address);
            $addressGeographicalInformationService->assign();
            $geographicalInformationService->save($geographicalInformation);
            //initializes a new Access Rate Payment for the next Period
            event('accessRatePayment.initialize', $meter);
            DB::connection('shard')->commit();

            return ApiResource::make($person)->response()->setStatusCode(201);

        } catch (\Exception $e) {
            DB::connection('shard')->rollBack();
            Log::critical('Error while adding new Customer', ['message' => $e->getMessage()]);
            throw new \Exception($e->getMessage());
        }
    });
});