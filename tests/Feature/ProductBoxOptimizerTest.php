<?php

namespace Tests\Feature;

use App\Services\ProductBoxOptimizerService;
use App\Services\ProductOptimizerService;
use Exception;
use Tests\TestCase;

class ProductBoxOptimizerTest extends TestCase
{
    public function test_single_item_single_box()
    {
        $service = new ProductOptimizerService();
        $products = [
            ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 1, 'quantity' => 1]
        ];
        $result = $service->calculateOptimalBoxes($products);

        $this->assertCount(1, $result['boxAssignments']);
        $this->assertEmpty($result['unfitProducts']);
    }

    public function test_multiple_items_that_will_fit_in_any_box()
    {
        $service = new ProductOptimizerService();
        $products = [
            ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 1, 'quantity' => 1],
            ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 0.5, 'quantity' => 1]
        ];
        $result = $service->calculateOptimalBoxes($products);

        $this->assertCount(1, $result['boxAssignments']);
        $this->assertEmpty($result['unfitProducts']);
    }

    public function test_multiple_items_split_into_two_boxes_due_to_weight_constraint()
    {
        $service = new ProductOptimizerService();
        $products = [];
        for ($i = 0; $i < 10; $i++) {
            $products[] = ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 10, 'quantity' => 1];
        }
        $result = $service->calculateOptimalBoxes($products);

        $this->assertCount(2, $result['boxAssignments']);
        $this->assertEmpty($result['unfitProducts']);
    }

    public function test_items_split_into_multiple_boxes_due_to_dimension_constraint_and_has_two_unfit()
    {
        $service = new ProductOptimizerService();
        $products = [];
        for ($i = 0; $i < 10; $i++) {
            $products[] = ['length' => 30, 'width' => 20, 'height' => 25, 'weight' => 1, 'quantity' => 1];
        }
        $products[] = ['length' => 100, 'width' => 100, 'height' => 100, 'weight' => 1, 'quantity' => 1];
        $products[] = ['length' => 15, 'width' => 10, 'height' => 5, 'weight' => 70, 'quantity' => 1];

        $result = $service->calculateOptimalBoxes($products);
        $this->assertCount(2, $result['boxAssignments']);
        $this->assertCount(2, $result['unfitProducts']);
    }

    public function test_items_fitted_to_largest_box_due_to_dimension_constraint()
    {
        $service = new ProductOptimizerService();
        $products = [];
        for ($i = 0; $i < 5; $i++) {
            $products[] = ['length' => 30, 'width' => 25, 'height' => 25, 'weight' => 5, 'quantity' => 1];
        }
        $result = $service->calculateOptimalBoxes($products);
        $this->assertEquals('BOXC', $result['boxAssignments'][0]['box']['name']);
        $this->assertCount(1, $result['boxAssignments']);
        $this->assertEmpty($result['unfitProducts']);
    }

    public function test_five_items_small_dimension_but_heavy_weight()
    {
        $service = new ProductOptimizerService();
        $products = [];
        for ($i = 0; $i < 5; $i++) {
            $products[] = ['length' => 2, 'width' => 2, 'height' => 2, 'weight' => 10, 'quantity' => 1];
        }
        $result = $service->calculateOptimalBoxes($products);

        $this->assertCount(1, $result['boxAssignments']);
        $this->assertEquals('BOXC', $result['boxAssignments'][0]['box']['name']);
        $this->assertEmpty($result['unfitProducts']);
    }
}
