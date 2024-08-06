<?php

namespace App\Livewire;

use App\Services\ProductBoxOptimizerService;
use App\Services\ProductOptimizerService;
use App\Services\VolumetricSummationServices;
use Livewire\Component;

class ShippingBoxOptimizer extends Component
{
    public $items = [];
    public $length, $width, $height, $weight, $quantity;
    public $boxAssignments = [];
    public $unfitProducts = [];
    public $predefinedBoxes = [
        ["name" => "BOXA", "length" => 20, "width" => 15, "height" => 10, "weight_limit" => 5],
        ["name" => "BOXB", "length" => 30, "width" => 25, "height" => 20, "weight_limit" => 10],
        ["name" => "BOXC", "length" => 60, "width" => 55, "height" => 50, "weight_limit" => 50],
        ["name" => "BOXD", "length" => 50, "width" => 45, "height" => 40, "weight_limit" => 30],
        ["name" => "BOXE", "length" => 40, "width" => 35, "height" => 30, "weight_limit" => 20],
    ];

    protected $rules = [
        'length' => 'required|numeric|min:1',
        'width' => 'required|numeric|min:1',
        'height' => 'required|numeric|min:1',
        'weight' => 'required|numeric|min:1',
        'quantity' => 'required|numeric|min:1',
    ];

    public function addItem()
    {
        $this->validate();

        $totalQuantity = array_reduce($this->items, function ($carry, $item) {
            return $carry + $item['quantity'];
        }, 0);

        if ($totalQuantity + $this->quantity > 10) {
            $this->dispatch('alert', ['message' => 'You can only add up to 10 items.']);
            return;
        }

        $this->items[] = [
            'length' => (float) $this->length,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'weight' => (float) $this->weight,
            'quantity' => (int) $this->quantity,
        ];

        $this->reset(['length', 'width', 'height', 'weight', 'quantity']);
        $this->dispatch('add-item');
        $this->process();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->process();
    }

    public function process()
    {
        $products = [];
        $totalWeight = 0;

        foreach ($this->items as $item) {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $products[] = [
                    'length' => $item['length'],
                    'width' => $item['width'],
                    'height' => $item['height'],
                    'weight' => $item['weight'],
                    'quantity' => 1,
                ];
                $totalWeight += $item['weight'];
            }
        }
        $productOptimzer = new ProductOptimizerService;
        $res = $productOptimzer->calculateOptimalBoxes($products);
        $this->boxAssignments = $res['boxAssignments'];
        $this->unfitProducts = $res['unfitProducts'];
    }

    public function render()
    {
        return view('livewire.shipping-box-optimizer')->layout('layouts.app');
    }

    public function exampleGroup($option){
        $this->reset();
        if($option == 1){
            $this->items = [
                ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 1, 'quantity' => 1]
            ];
        }
        if($option == 2){
            $this->items = [
                ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 1, 'quantity' => 1],
                ['length' => 15, 'width' => 20, 'height' => 30, 'weight' => 0.5, 'quantity' => 1]
            ];
        }
        if($option == 3){
            for ($i = 0; $i < 10; $i++) {
                $this->items[] = ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 3, 'quantity' => 1];
            }
        }
        if($option == 4){
            for ($i = 0; $i < 10; $i++) {
                $this->items[] = ['length' => 30, 'width' => 20, 'height' => 25, 'weight' => 1, 'quantity' => 1];
            }
            $this->items[] = ['length' => 100, 'width' => 100, 'height' => 100, 'weight' => 1, 'quantity' => 1];
            $this->items[] = ['length' => 15, 'width' => 10, 'height' => 5, 'weight' => 70, 'quantity' => 1];
        }
        if($option == 5){
            for ($i = 0; $i < 5; $i++) {
                $this->items[] = ['length' => 30, 'width' => 25, 'height' => 25, 'weight' => 5, 'quantity' => 1];
            }
        }
        if($option == 6){
            for ($i = 0; $i < 5; $i++) {
                $this->items[] = ['length' => 2, 'width' => 2, 'height' => 2, 'weight' => 10, 'quantity' => 1];
            }
        }
        if($option == 7){
            $this->items = [
                //Total is 4; Weight is 4 kgs
                ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 1, 'quantity' => 1],
                ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 1, 'quantity' => 1],
                ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 1, 'quantity' => 1],
                ['length' => 5, 'width' => 5, 'height' => 5, 'weight' => 1, 'quantity' => 1],
                //Total is 5; Weight is 12 kgs
                ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 2, 'quantity' => 1],
                ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 2, 'quantity' => 2],
                ['length' => 10, 'width' => 10, 'height' => 10, 'weight' => 2, 'quantity' => 2],
                //Total is 5; Weight is 5 kgs
                ['length' => 20, 'width' => 15, 'height' => 10, 'weight' => 1, 'quantity' => 2],
                ['length' => 20, 'width' => 15, 'height' => 10, 'weight' => 1, 'quantity' => 1],
                ['length' => 20, 'width' => 15, 'height' => 10, 'weight' => 1, 'quantity' => 1],
                ['length' => 20, 'width' => 15, 'height' => 10, 'weight' => 1, 'quantity' => 1],
                //Total is 4; Weight is 48 kgs
                ['length' => 30, 'width' => 25, 'height' => 20, 'weight' => 8, 'quantity' => 1],
                ['length' => 30, 'width' => 25, 'height' => 20, 'weight' => 8, 'quantity' => 1],
                ['length' => 30, 'width' => 25, 'height' => 20, 'weight' => 8, 'quantity' => 2],
                ['length' => 30, 'width' => 25, 'height' => 20, 'weight' => 8, 'quantity' => 2],
                //Total is 2; Weight is 120 kgs
                ['length' => 70, 'width' => 60, 'height' => 50, 'weight' => 60, 'quantity' => 1],
                ['length' => 70, 'width' => 60, 'height' => 50, 'weight' => 60, 'quantity' => 1],
            ];
        }
        $this->process();
    }
}
