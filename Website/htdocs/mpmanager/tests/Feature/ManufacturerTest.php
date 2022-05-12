<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ManufacturerTest extends TestCase
{
    use CreateEnvironments;

    public function test_user_gets_manufacturer_list()
    {

        $this->createTestData();
        $manufacturerCount = 4;
        $this->createMeterManufacturer($manufacturerCount);
        $response = $this->actingAs($this->user)->get('/api/manufacturers');
        $response->assertStatus(200);
        $this->assertEquals(count($response['data']), count($this->manufacturers));
    }

    public function test_user_gets_manufacturer_by_id()
    {
        $this->createTestData();
        $this->createMeterManufacturer();
        $response = $this->actingAs($this->user)->get(sprintf('/api/manufacturers/%s', $this->manufacturers[0]->id));
        $response->assertStatus(200);
        $this->assertEquals($response['data']['id'],$this->manufacturers[0]->id);
    }

    public function test_user_creates_new_manufacturer()
    {
        $this->createTestData();
        $manufacturerData = [
            'name' => 'test meters company',
            'website' => $this->faker->url,
            'api_name' => $this->faker->name,
            'email' => 'test@test.com',
            'city_id' => $this->cities[0]->id,
            'phone' => $this->faker->phoneNumber,
        ];
        $response = $this->actingAs($this->user)->post('/api/manufacturers',$manufacturerData);
        $response->assertStatus(201);
        $this->assertEquals($response['data']['name'],$manufacturerData['name']);
    }

    public function actingAs($user, $driver = null)
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', "Bearer {$token}");
        parent::actingAs($user);

        return $this;
    }
}
