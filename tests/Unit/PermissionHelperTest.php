<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\PermissionHelper;

class PermissionHelperTest extends TestCase
{
    public function testParseNodesReturnsEmptyArrayForInvalidInput()
    {
        $this->assertEquals([], PermissionHelper::parseNodes(null));
    }

    public function testParseNodes()
    {
        $data = [
            'category1' => true, // normal selected category node, should be kept
            'category1_node_1' => true, // child node with selected category, should be kept
            'category1_node_2' => false, // child node set to false, should be removed
            'category2' => false, // category node set to false, should be removed
            'category2_node_2' => true, // child node with category node set to false, should be removed
            'category3_node_1' => true, // child node with non-existing category node, should be removed
            'category4' => true // category with no child nodes, should be removed
        ];

        $expected = [
            'category1',
            'category1_node_1'
        ];

        $this->assertEquals($expected, PermissionHelper::parseNodes($data));
    }
}
