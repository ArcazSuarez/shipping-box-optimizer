<?php

namespace App\Services;

use Exception;

class VolumetricSummationServices
{
    private $boxes;

    public function __construct()
    {
        $this->boxes = json_decode('[
            {"name": "BOXA", "length": 20, "width": 15, "height": 10, "weight_limit": 5},
            {"name": "BOXB", "length": 30, "width": 25, "height": 20, "weight_limit": 10},
            {"name": "BOXC", "length": 60, "width": 55, "height": 50, "weight_limit": 50},
            {"name": "BOXD", "length": 50, "width": 45, "height": 40, "weight_limit": 30},
            {"name": "BOXE", "length": 40, "width": 35, "height": 30, "weight_limit": 20}
        ]', true);

        // Sort boxes by volume (smallest first)
        usort($this->boxes, function ($a, $b) {
            return ($a['length'] * $a['width'] * $a['height']) - ($b['length'] * $b['width'] * $b['height']);
        });
    }

    public function calculateOptimalBoxes($products)
    {
        $boxAssignments = [];
        $unfitProducts = [];

        $products = $this->separateProducts($products);
        // Add volume to each product
        foreach ($products as &$product) {
            $product['volume'] = $product['length'] * $product['width'] * $product['height'];
        }
        unset($product);

        // Sort products by volume (largest first)
        usort($products, function ($a, $b) {
            return $b['volume'] - $a['volume'];
        });

        // Remove products that cannot fit into any box
        foreach ($products as $key => $product) {
            if (!$this->canFitInAnyBox($product)) {
                $unfitProducts[] = $product;
                unset($products[$key]);
            }
        }

        $unpackedProducts = [];

        while (!empty($products)) {
            $bestFit = $this->findBestFit($products);

            if ($bestFit) {
                $boxAssignments[] = $bestFit;
                foreach ($bestFit['products'] as $packedProduct) {
                    $index = array_search($packedProduct, $products);
                    unset($products[$index]);
                }
                $products = array_values($products);
            } else {
                $unpackedProducts[] = array_shift($products);
            }
        }

        while (!empty($unpackedProducts)) {
            $products = $unpackedProducts;
            $unpackedProducts = [];

            while (!empty($products)) {
                $bestFit = $this->findBestFit($products);
                if ($bestFit) {
                    $boxAssignments[] = $bestFit;
                    foreach ($bestFit['products'] as $packedProduct) {
                        $index = array_search($packedProduct, $products);
                        unset($products[$index]);
                    }
                    $products = array_values($products);
                } else {
                    // If no fit found, handle the largest product separately
                    $unpackedProducts[] = array_shift($products);
                }
            }
        }
        return ['boxAssignments' => $boxAssignments, 'unfitProducts' => $unfitProducts];
    }

    private function findBestFit($products)
    {
        foreach ($this->boxes as $box) {

            // $totalWeight = array_reduce($products, function ($carry, $product) {
            //     return $carry + $product['weight'];
            // }, 0);
            $totalWeight = array_sum(array_column($products, 'weight'));
            // $totalVolume = array_reduce($products, function ($carry, $item) {
            //     return $carry + $item['volume'];
            // }, 0);
            $totalVolume = array_sum(array_column($products, 'volume'));

            $totalBoxVolume = ($box['length'] * $box['width'] * $box['height']);


            if ($totalVolume <= $totalBoxVolume && $totalWeight <= $box['weight_limit']) {
                if ($this->canFitAllProductsInBox($products, $box)) {
                    return [
                        'box' => $box,
                        'products' => $products,
                        'products_details' => [
                            'total_weight' => $totalWeight,
                            'total_volume' => $totalVolume,
                        ],
                        'optimal_stack' => [
                            'length' => $box['length'],
                            'width' => $box['width'],
                            'height' => $box['height']
                        ]
                    ];
                }
            }
        }

        return null;
    }

    private function canFitAllProductsInBox($products, $box)
    {
        foreach ($products as $product) {
            if (
                $product['length'] > $box['length'] ||
                $product['width'] > $box['width'] ||
                $product['height'] > $box['height']
            ) {
                return false;
            }
        }

        return true;
    }

    private function canFitProductInBox($product, $box)
    {
        return $product['length'] <= $box['length'] &&
            $product['width'] <= $box['width'] &&
            $product['height'] <= $box['height'] &&
            $product['weight'] <= $box['weight_limit'];
    }

    private function canFitInAnyBox($product)
    {
        foreach ($this->boxes as $box) {
            if ($this->canFitProductInBox($product, $box)) {
                return true;
            }
        }
        return false;
    }


    private function separateProducts($products)
    {
        $separatedProducts = [];

        foreach ($products as $product) {
            $quantity = $product['quantity'];
            unset($product['quantity']);
            for ($i = 0; $i < $quantity; $i++) {
                $separatedProducts[] = $product;
            }
        }

        // dd($separatedProducts);
        return $separatedProducts;
    }
}
