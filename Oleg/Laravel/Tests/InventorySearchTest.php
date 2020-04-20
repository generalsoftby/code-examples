<?php

namespace Tests\Feature\Inventory;

use Carbon\Carbon;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class InventorySearchTest extends TestCase
{
    use RefreshDatabase;

    private const TOTAL = 42;

    protected function setUp(): void
    {
        parent::setUp();

        factory(User::class)->create([
            'id' => 1,
            'api_token' => 'THE_MOST_SECURE_TOKEN_EVER',
        ]);

        factory(Inventory::class)->createMany([
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Cup',
                'model' => 'Glass cup 15',
                'serial_number' => 'CUP150618',
                'amount' => 60,
                'is_active' => true,
                'manufacturer' => 'DGW',
                'created_at' => new Carbon('15.06.2018'),
                'updated_at' => new Carbon('19.03.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Dish',
                'model' => 'China dish 350',
                'serial_number' => 'DISH230819',
                'amount' => 60,
                'is_active' => true,
                'manufacturer' => 'CDF',
                'created_at' => new Carbon('23.08.2019'),
                'updated_at' => new Carbon('23.08.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Electric cooker',
                'model' => 'Electrolux EC350D',
                'serial_number' => 'EC350D18',
                'amount' => 5,
                'is_active' => true,
                'manufacturer' => 'Electrolux',
                'created_at' => new Carbon('03.09.2019'),
                'updated_at' => new Carbon('03.09.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Blender',
                'model' => 'Braun BBS1515',
                'serial_number' => 'B6548643445',
                'amount' => 10,
                'is_active' => true,
                'manufacturer' => 'Braun',
                'created_at' => new Carbon('03.09.2019'),
                'updated_at' => new Carbon('03.09.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Knife',
                'model' => 'Sharp knife 175',
                'serial_number' => 'SK175168435',
                'amount' => 20,
                'is_active' => false,
                'manufacturer' => 'DMW',
                'created_at' => new Carbon('22.10.2019'),
                'updated_at' => new Carbon('22.10.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Fork',
                'model' => 'Sharp fork 10',
                'serial_number' => 'SF10168435',
                'amount' => 30,
                'is_active' => false,
                'manufacturer' => 'DMW',
                'created_at' => new Carbon('22.10.2019'),
                'updated_at' => new Carbon('22.10.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Fork',
                'model' => 'Sharp fork 5',
                'serial_number' => 'SF5168435',
                'amount' => 60,
                'is_active' => true,
                'manufacturer' => 'DMW',
                'created_at' => new Carbon('22.10.2019'),
                'updated_at' => new Carbon('22.10.2019'),
            ],
            [
                'user_id' => 1,
                'title' => 'Crepe pan',
                'model' => 'Crepe pan 25',
                'serial_number' => 'CP25-68435',
                'amount' => 5,
                'is_active' => true,
                'manufacturer' => 'DMW',
                'created_at' => new Carbon('30.10.2019'),
                'updated_at' => new Carbon('30.10.2019'),
            ]
        ]);

        // Another user
        factory(Inventory::class)->create([
            'user_id' => 2,
            'title' => 'Plate',
        ]);
    }

    /**
     * @param array $params
     * @param int $expected
     *
     * @dataProvider paramsProvider
     */
    public function testSearch(array $params, int $expected): void
    {
        $response = $this->getJson(route('inventories.list', $params), [
            'Authorization' => 'Bearer THE_MOST_SECURE_TOKEN_EVER',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($expected, $response->json('total'));
    }

    public function paramsProvider(): array
    {
        return [
            'forks' => [
                'params' => [
                    'title' => 'fork',
                ],
                'expected' => 2,
            ],
            'one_knife' => [
                'params' => [
                    'title' => 'knife',
                ],
                'expected' => 1,
            ],
            'gas_cooker' => [
                'params' => [
                    'title' => 'gas cooker'
                ],
                'expected' => 0,
            ],
            'undefined_field' => [
                'params' => [
                    'location' => 'Hamburg',
                ],
                'expected' => 8,
            ],
            'use_id' => [
                'params' => [
                    'id' => 1,
                ],
                'expected' => 1,
            ],
        ];
    }
}
