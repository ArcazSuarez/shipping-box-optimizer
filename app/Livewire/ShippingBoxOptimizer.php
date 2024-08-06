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
}
