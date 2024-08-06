<?php

namespace App\Services;

class ProductOptimizerService
{
    private int $defaultBoxWidth;
    private int $defaultBoxHeight;
    private int $defaultBoxLength;
    private array $boxes;

    public function __construct(int $defaultBoxWidth = 60, int $defaultBoxHeight = 55, int $defaultBoxLength = 50)
    {
        // Initialize default box dimensions
        $this->defaultBoxWidth = $defaultBoxWidth;
        $this->defaultBoxHeight = $defaultBoxHeight;
        $this->defaultBoxLength = $defaultBoxLength;

        // Initialize and sort boxes by volume
        $this->boxes = $this->initializeBoxes();
        $this->sortBoxesByVolume();
    }

    public function calculateOptimalBoxes(array $products): array
    {
        $boxAssignments = [];
        $unfitProducts = [];

        // Separate products into individual units and sort them
        $products = $this->separateProducts($products);
        $products = $this->prepareAndSortProducts($products);

        // Identify products that cannot fit into any box
        foreach ($products as $key => $product) {
            if (!$this->canFitInAnyBox($product)) {
                $unfitProducts[] = $product;
                unset($products[$key]);
            }
        }

        $unpackedProducts = [];
        // Assign boxes to products
        $this->assignBoxes($products, $boxAssignments, $unpackedProducts);

        // Handle remaining unpacked products
        while (!empty($unpackedProducts)) {
            $this->assignBoxes($unpackedProducts, $boxAssignments, $unpackedProducts);
        }

        return ['boxAssignments' => $boxAssignments, 'unfitProducts' => $unfitProducts];
    }

    private function initializeBoxes(): array
    {
        return json_decode('[
            {"name": "BOXA", "length": 20, "width": 15, "height": 10, "weight_limit": 5},
            {"name": "BOXB", "length": 30, "width": 25, "height": 20, "weight_limit": 10},
            {"name": "BOXC", "length": 60, "width": 55, "height": 50, "weight_limit": 50},
            {"name": "BOXD", "length": 50, "width": 45, "height": 40, "weight_limit": 30},
            {"name": "BOXE", "length": 40, "width": 35, "height": 30, "weight_limit": 20}
        ]', true);
    }

    private function sortBoxesByVolume(): void
    {
        usort($this->boxes, fn($a, $b) => ($a['length'] * $a['width'] * $a['height']) - ($b['length'] * $b['width'] * $b['height']));
    }

    private function prepareAndSortProducts(array $products): array
    {
        // Add volume to each product
        foreach ($products as &$product) {
            $product['volume'] = $product['length'] * $product['width'] * $product['height'];
        }
        unset($product);

        // Sort products by volume in descending order
        usort($products, fn($a, $b) => $b['volume'] - $a['volume']);

        return $products;
    }

    private function assignBoxes(array &$products, array &$boxAssignments, array &$unpackedProducts): void
    {
        while (!empty($products)) {
            $bestFit = $this->findBestFit($products);

            if ($bestFit) {
                $boxAssignments[] = $bestFit;
                // Remove packed products from the list
                foreach ($bestFit['products'] as $packedProduct) {
                    $index = array_search($packedProduct, $products);
                    unset($products[$index]);
                }
                $products = array_values($products);
            } else {
                // If no fit is found, move the product to unpacked products
                $unpackedProducts[] = array_shift($products);
            }
        }
    }

    private function findBestFit(array $products): ?array
    {
        foreach ($this->boxes as $box) {
            $totalWeight = array_sum(array_column($products, 'weight'));
            $totalVolume = array_sum(array_column($products, 'volume'));
            $totalBoxVolume = ($box['length'] * $box['width'] * $box['height']);

            // Check if the products can fit into the current box
            if ($totalVolume <= $totalBoxVolume && $totalWeight <= $box['weight_limit']) {
                $resultStack = $this->calculateOptimalDimension($products, $box);
                if ($resultStack) {
                    return [
                        'box' => $box,
                        'products' => $products,
                        'products_details' => [
                            'total_weight' => $totalWeight,
                            'total_volume' => $totalVolume,
                        ],
                        'optimal_stack' => $resultStack
                    ];
                }
            }
        }

        return null;
    }

    private function calculateOptimalDimension(array $selectedCart, array $boxDimensions = null): ?array
    {
        // Use provided box dimensions or defaults
        $boxWidth = $boxDimensions['width'] ?? $this->defaultBoxWidth;
        $boxHeight = $boxDimensions['height'] ?? $this->defaultBoxHeight;
        $boxLength = $boxDimensions['length'] ?? $this->defaultBoxLength;

        // Calculate total volume and generate dimension combinations
        $totalVolume = $this->calculateTotalVolume($selectedCart);
        $widthCombinations = $this->generateCombinations(array_column($selectedCart, 'width'));
        $heightCombinations = $this->generateCombinations(array_column($selectedCart, 'height'));
        $lengthCombinations = $this->generateCombinations(array_column($selectedCart, 'length'));

        // Find the optimal stack for the products in the box
        return $this->findOptimalStack($totalVolume, $widthCombinations, $heightCombinations, $lengthCombinations, $boxWidth, $boxHeight, $boxLength);
    }

    private function calculateTotalVolume(array $products): int
    {
        $totalVolume = 0;

        // Sum up the volume of all products
        foreach ($products as $product) {
            $totalVolume += $product['width'] * $product['height'] * $product['length'];
        }

        return $totalVolume;
    }

    private function generateCombinations(array $dimensions): array
    {
        $combinations = [];
        $n = count($dimensions);

        // Generate all possible combinations of dimensions
        for ($i = 1; $i < (1 << $n); $i++) {
            $sum = 0;
            for ($j = 0; $j < $n; $j++) {
                if ($i & (1 << $j)) {
                    $sum += $dimensions[$j];
                }
            }
            $combinations[$sum] = true;
        }

        return array_keys($combinations);
    }

    private function findOptimalStack(int $totalVolume, array $widthCombinations, array $heightCombinations, array $lengthCombinations, int $boxWidth, int $boxHeight, int $boxLength): ?array
    {
        $minVolume = PHP_INT_MAX;
        $minDimensionSum = PHP_INT_MAX;
        $optimalStack = null;

        // Find the best combination that fits the box and meets the volume requirement
        foreach ($widthCombinations as $width) {
            foreach ($heightCombinations as $height) {
                foreach ($lengthCombinations as $length) {
                    $volume = $width * $height * $length;
                    $dimensionSum = $width + $height + $length;

                    if ($this->fitsInBox($width, $height, $length, $boxWidth, $boxHeight, $boxLength) && $volume >= $totalVolume && ($volume < $minVolume || ($volume == $minVolume && $dimensionSum < $minDimensionSum))) {
                        $minVolume = $volume;
                        $minDimensionSum = $dimensionSum;
                        $optimalStack = ['dimensions' => [$width, $height, $length], 'volume' => $volume];
                    }
                }
            }
        }

        if ($optimalStack) {
            rsort($optimalStack['dimensions']);
        }

        return $optimalStack;
    }

    private function fitsInBox(int $width, int $height, int $length, int $boxWidth, int $boxHeight, int $boxLength): bool
    {
        // Check all possible orientations of fitting the dimensions into the box
        return ($width <= $boxWidth && $height <= $boxHeight && $length <= $boxLength) ||
               ($width <= $boxWidth && $height <= $boxLength && $length <= $boxHeight) ||
               ($width <= $boxHeight && $height <= $boxWidth && $length <= $boxLength) ||
               ($width <= $boxHeight && $height <= $boxLength && $length <= $boxWidth) ||
               ($width <= $boxLength && $height <= $boxWidth && $length <= $boxHeight) ||
               ($width <= $boxLength && $height <= $boxHeight && $length <= $boxWidth);
    }

    private function canFitInAnyBox(array $product): bool
    {
        foreach ($this->boxes as $box) {
            if ($this->canFitProductInBox($product, $box)) {
                return true;
            }
        }
        return false;
    }

    private function canFitProductInBox(array $product, array $box): bool
    {
        // Check if a single product can fit into a box
        return $product['length'] <= $box['length'] &&
               $product['width'] <= $box['width'] &&
               $product['height'] <= $box['height'] &&
               $product['weight'] <= $box['weight_limit'];
    }

    private function separateProducts(array $products): array
    {
        $separatedProducts = [];

        // Separate products based on their quantity
        foreach ($products as $product) {
            $quantity = $product['quantity'];
            unset($product['quantity']);
            for ($i = 0; $i < $quantity; $i++) {
                $separatedProducts[] = $product;
            }
        }

        return $separatedProducts;
    }
}
