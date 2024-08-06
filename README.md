# Shipping Box Optimizer

This package helps in optimizing the distribution of products across multiple boxes based on their dimensions and weight.

## Installation

Follow these steps to install and set up the app:

1. Clone the repository:
    ```bash
    git clone https://github.com/ArcazSuarez/shipping-box-optimizer
    ```

2. Navigate to the project directory:
    ```bash
    cd shipping-box-optimizer
    ```

3. Install the composer dependencies:
    ```bash
    composer install
    ```

4. Install the npm dependencies:
    ```bash
    npm install
    ```

5. Compile the assets:
    ```bash
    npm run dev
    ```

## Usage

To use the package, you can input the products in the following format:

### Example Input #1

```json
[
    {"length": 5, "width": 5, "height": 5, "weight": 1, "quantity": 1},
    {"length": 5, "width": 5, "height": 5, "weight": 1, "quantity": 1},
    {"length": 5, "width": 5, "height": 5, "weight": 1, "quantity": 1},
    {"length": 5, "width": 5, "height": 5, "weight": 1, "quantity": 1},
    {"length": 10, "width": 10, "height": 10, "weight": 2, "quantity": 1},
    {"length": 10, "width": 10, "height": 10, "weight": 2, "quantity": 2},
    {"length": 10, "width": 10, "height": 10, "weight": 2, "quantity": 2},
    {"length": 20, "width": 15, "height": 10, "weight": 1, "quantity": 2},
    {"length": 20, "width": 15, "height": 10, "weight": 1, "quantity": 1},
    {"length": 20, "width": 15, "height": 10, "weight": 1, "quantity": 1},
    {"length": 20, "width": 15, "height": 10, "weight": 1, "quantity": 1},
    {"length": 30, "width": 25, "height": 20, "weight": 8, "quantity": 1},
    {"length": 30, "width": 25, "height": 20, "weight": 8, "quantity": 1},
    {"length": 30, "width": 25, "height": 20, "weight": 8, "quantity": 2},
    {"length": 30, "width": 25, "height": 20, "weight": 8, "quantity": 2},
    {"length": 70, "width": 60, "height": 50, "weight": 60, "quantity": 1},
    {"length": 70, "width": 60, "height": 50, "weight": 60, "quantity": 1}
]
```

### Example Output #1

##### BOXC
Dimensions
60x55x50
+ Product 1: 30x25x20, 8kg
+ Product 2: 30x25x20, 8kg
+ Product 3: 30x25x20, 8kg
+ Product 4: 20x15x10, 1kg
+ Product 5: 20x15x10, 1kg
+ Product 6: 20x15x10, 1kg
+ Product 7: 20x15x10, 1kg
+ Product 8: 20x15x10, 1kg
+ Product 9: 10x10x10, 2kg
+ Product 10: 10x10x10, 2kg
+ Product 11: 10x10x10, 2kg
+ Product 12: 10x10x10, 2kg
+ Product 13: 10x10x10, 2kg
+ Product 14: 5x5x5, 1kg
+ Product 15: 5x5x5, 1kg
+ Product 16: 5x5x5, 1kg
+ Product 17: 5x5x5, 1kg

##### BOXD
Dimensions
50x45x40
+ Product 1: 30x25x20, 8kg
+ Product 2: 30x25x20, 8kg
+ Product 3: 30x25x20, 8kg

##### Unfit
+ Product 1: 70x60x50, 60kg
+ Product 2: 70x60x50, 60kg
