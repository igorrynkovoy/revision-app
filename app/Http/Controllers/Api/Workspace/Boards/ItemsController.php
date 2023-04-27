<?php

namespace App\Http\Controllers\Api\Workspace\Boards;

use App\Http\Controllers\Controller;
use App\Models\Workspace;

class ItemsController extends Controller
{
    public function getItems(Workspace $workspace, Workspace\Board\Board $board)
    {
        $data = [
            'board_id' => $board->id,
            'nodes' => [
                [
                    'id' => 17,
                    'type' => 'Address',
                    'labels' => [
                        'Address'
                    ],
                    'properties' => [
                        'address' => 'ltc1qsek999jt4au7ewjlq88pc0vr4jkgs25t3ragn3',
                        'transactions' => 2,
                        'unique_addresses' => 4,
                        'input_addresses' => 1,
                        'output_addresses' => 3
                    ]
                ],
                [
                    'id' => 18,
                    'type' => 'Address',
                    'labels' => [
                        'Address'
                    ],
                    'properties' => [
                        'address' => 'ltc1q9a5a8f8wv8quavdl8aufmzu2kudtfxa8yg40sr',
                        'transactions' => mt_rand(100, 9000),
                        'unique_addresses' => mt_rand(2, 300),
                        'input_addresses' => mt_rand(2, 300),
                        'output_addresses' => mt_rand(2, 300)
                    ]
                ],
                [
                    'id' => 19,
                    'type' => 'Address',
                    'labels' => [
                        'Address'
                    ],
                    'properties' => [
                        'address' => 'ltc1qs5fjzw6958a7jurvud9razqp3ynm7at3p8chcz',
                        'transactions' => mt_rand(100, 9000),
                        'unique_addresses' => mt_rand(2, 300),
                        'input_addresses' => mt_rand(2, 300),
                        'output_addresses' => mt_rand(2, 300)
                    ]
                ],
                [
                    'id' => 16,
                    'type' => 'Transaction',
                    'labels' => [
                        'Transaction'
                    ],
                    'properties' => [
                        'hash' => '2f3a50b2bf0b5d6ae4cd619a0b3fef2d7cd38891300bfaeb4148a6e903f50732',
                        'block_number' => 2391597,
                        'inputs' => 1,
                        'outputs' => 2
                    ]
                ],
            ],
            'edges' => [
                [
                    'id' => 14,
                    'source' => 17,
                    'target' => 16,
                    'direction' => "target",
                    'type' => 'AS_INPUT',
                    'properties' => [
                        "value" => "650442655"
                    ]
                ],
                [
                    'id' => 16,
                    'source' => 16,
                    'target' => 19,
                    'direction' => "target",
                    'type' => 'AS_OUTPUT',
                    'properties' => [
                        "value" => "1838638"
                    ]
                ],
                [
                    'id' => 15,
                    'source' => 16,
                    'target' => 18,
                    'direction' => "target",
                    'type' => 'AS_OUTPUT',
                    'properties' => [
                        "value" => "648603875"
                    ]
                ],
            ]
        ];

        return response()->json(['data' => $data]);
    }
}
