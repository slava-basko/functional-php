<?php

namespace Tests\Functional;

use Basko\Functional as f;

class ExampleTest extends BaseTest
{
    public static function products()
    {
        return [
            [
                'description' => 't-shirt',
                'qty' => 2,
                'value' => 20,
            ],
            [
                'description' => 'jeans',
                'qty' => 1,
                'value' => 30,
            ],
            [
                'description' => 'boots',
                'qty' => 1,
                'value' => 40,
            ],
        ];
    }

    public function test_products()
    {
        $imperativeTotalQty = 0;
        foreach (static::products() as $product) {
            $imperativeTotalQty += $product['qty'];
        }

        $imperativeAmount = 0;
        foreach (static::products() as $product) {
            $imperativeAmount += $product['qty'] * $product['value'];
        }

        $totalQty = f\compose(f\sum, f\pluck('qty'));
        $pipedTotalQty = f\pipe(f\pluck('qty'), f\sum);
        $amount = f\compose(f\sum, f\map(f\compose(f\product, f\props(['value', 'qty']))));

        $this->assertEquals(4, $totalQty(static::products()));
        $this->assertEquals(4, $pipedTotalQty(static::products()));
        $this->assertEquals($imperativeTotalQty, $totalQty(static::products()));
        $this->assertEquals(110, $amount(static::products()));
        $this->assertEquals($imperativeAmount, $amount(static::products()));
    }

    public function test_filter()
    {
        $valueGreaterThen35 = f\compose(f\gt(35), f\prop('value'));

        $this->assertEquals([
            2 => [
                'description' => 'boots',
                'qty' => 1,
                'value' => 40,
            ],
        ], array_filter(static::products(), $valueGreaterThen35));
    }

    public function test_get_query_param()
    {
        $getParams = f\if_else('is_string', f\identity, 'http_build_query');
        $this->assertEquals('a=1&b=2', $getParams(f\prop('params', [
            'params' => [
                'a' => 1,
                'b' => 2,
            ],
        ])));
        $this->assertEquals('a=1&b=2', $getParams(f\prop('params', [
            'params' => 'a=1&b=2',
        ])));
    }

    public function test_json_encode_if_not_string()
    {
        // $response = !is_string($data['response']) ? json_encode($data['response']) : $data['response'];
        $prepareResponseToSave = f\if_else(f\not('is_string'), 'json_encode', f\identity);
        $this->assertEquals('OK', $prepareResponseToSave(f\prop('response', [
            'response' => 'OK',
        ])));
        $this->assertEquals('{"a":1,"b":2}', $prepareResponseToSave(f\prop('response', [
            'response' => [
                'a' => 1,
                'b' => 2,
            ],
        ])));
    }

    public function test_repeat_either()
    {
        $obj = [
            'shipper_country' => 'NL',
            'consignee_country' => '',
            'pickup_hub_id' => 5,
        ];
        $oldObj = [
            'shipper_country' => 'NL',
            'consignee_country' => 'US',
            'pickup_hub_id' => 5,
        ];
        $getProp = f\either(f\partial_r(f\prop, $obj), f\partial_r(f\prop, $oldObj));

        $this->assertEquals('NL', $getProp('shipper_country'));
        $this->assertEquals('US', $getProp('consignee_country'));
    }

    public function test_upper_specific_fields()
    {
        $obj = [
            'shipper_country' => 'nl',
            'consignee_country' => 'ca',
            'name' => 'John',
        ];

        $m_obj = array_merge($obj, f\map(f\ary('strtoupper', 1), f\select_keys(['shipper_country', 'consignee_country'], $obj)));
        $this->assertEquals('NL', f\prop('shipper_country', $m_obj));
        $this->assertEquals('CA', f\prop('consignee_country', $m_obj));
        $this->assertEquals('John', f\prop('name', $m_obj));

        $toUpperSomeFields = f\converge(
            'array_merge',
            [
                f\always($obj),
                f\pipe(f\select_keys(['shipper_country', 'consignee_country']), f\map(f\ary('strtoupper', 1))),
            ]
        );
        $m2_obj = $toUpperSomeFields($obj);
        $this->assertEquals('NL', f\prop('shipper_country', $m2_obj));
        $this->assertEquals('CA', f\prop('consignee_country', $m2_obj));
        $this->assertEquals('John', f\prop('name', $m2_obj));

        $m3_obj = f\map_keys('strtoupper', ['shipper_country', 'consignee_country'], $obj);
        $this->assertEquals('NL', f\prop('shipper_country', $m3_obj));
        $this->assertEquals('CA', f\prop('consignee_country', $m3_obj));
        $this->assertEquals('John', f\prop('name', $m3_obj));
    }

    public function test_fetch_uniq_names()
    {
        $response = [
            'items' => [
                [
                    'id' => 1,
                    'type' => 'train',
                    'users' => [
                        ['id' => 1, 'name' => 'Jimmy Page'],
                        ['id' => 5, 'name' => 'Roy Harper'],
                    ],
                ],
                [
                    'id' => 421,
                    'type' => 'hotel',
                    'users' => [
                        ['id' => 1, 'name' => 'Jimmy Page'],
                        ['id' => 2, 'name' => 'Robert Plant'],
                    ],
                ],
                [
                    'id' => 876,
                    'type' => 'flight',
                    'users' => [
                        ['id' => 3, 'name' => 'John Paul Jones'],
                        ['id' => 4, 'name' => 'John Bonham'],
                    ],
                ],
            ],
        ];

        $getAllUsersNames = f\pipe(
            f\prop('items'),
            f\flat_map(f\prop('users')),
            f\map(f\prop('name')),
            f\uniq
        );

        $this->assertEquals(
            [
                'Jimmy Page',
                'Roy Harper',
                'Robert Plant',
                'John Paul Jones',
                'John Bonham',
            ],
            $getAllUsersNames($response)
        );
    }

    public function test_create_map_from_collection()
    {
        $countries = [
            [
                'name' => 'Netherlands',
                'alpha2' => 'NL',
                'alpha3' => 'NLD',
                'numeric' => '528',
                'currency' => [
                    'EUR',
                ]
            ],
            [
                'name' => 'Ukraine',
                'alpha2' => 'UA',
                'alpha3' => 'UKR',
                'numeric' => '804',
                'currency' => [
                    'UAH',
                ],
            ],
        ];

        $f = f\converge('array_combine', [
            f\pluck('alpha2'),
            f\pluck('name'),
        ]);
        $this->assertEquals([
            'NL' => 'Netherlands',
            'UA' => 'Ukraine',
        ], $f($countries));

        $this->assertEquals([
            'NL' => 'Netherlands',
            'UA' => 'Ukraine',
        ], $f(new \ArrayIterator($countries)));


        $f2 = f\combine('alpha2');
        $f2 = $f2('name');

        $this->assertEquals([
            'NL' => 'Netherlands',
            'UA' => 'Ukraine',
        ], $f2($countries));

        $this->assertEquals([
            'NL' => 'Netherlands',
            'UA' => 'Ukraine',
        ], $f2(new \ArrayIterator($countries)));
    }

    public function test_user_process()
    {
        $user = [
            'first_name' => ' Slava',
            'last_name' => 'Basko  ',
            'role' => 'developer',
            'location' => [
                'country' => 'ua',
                'city' => 'odessa',
                'zip' => '',
            ],
            'contacts' => [
                'some@exemple.com',
                'some2@exemple.com',
                'some@exemple.com',
            ]
        ];

        $normalizeFunction = f\pipe(
            f\map_keys('trim', ['first_name', 'last_name']),
            f\map_keys('ucfirst', ['role']),
            f\assoc('location', f\pipe(
                f\prop('location'),
                f\reject(f\not('strlen')),
                f\map_keys('strtoupper', ['country']),
                f\map_keys('ucfirst', ['city'])
            )),
            f\assoc('contacts', f\pipe(
                f\prop('contacts'),
                f\uniq
            ))
        );

        $this->assertEquals(
            [
                'first_name' => 'Slava',
                'last_name' => 'Basko',
                'role' => 'Developer',
                'location' => [
                    'country' => 'UA',
                    'city' => 'Odessa',
                ],
                'contacts' => [
                    'some@exemple.com',
                    'some2@exemple.com',
                ]
            ],
            $normalizeFunction($user)
        );

        $this->assertEquals(
            (object)[
                'first_name' => 'Slava',
                'last_name' => 'Basko',
                'role' => 'Developer',
                'location' => [
                    'country' => 'UA',
                    'city' => 'Odessa',
                ],
                'contacts' => [
                    'some@exemple.com',
                    'some2@exemple.com',
                ]
            ],
            $normalizeFunction((object)$user)
        );
    }
}
