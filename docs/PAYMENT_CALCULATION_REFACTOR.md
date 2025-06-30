# Payment Calculation System - SOLID Refactor

## Overview

The `CalculatePayment` action has been refactored to follow SOLID principles, improving maintainability, testability, and extensibility.

## Architecture

### SOLID Principles Applied

#### 1. Single Responsibility Principle (SRP)
Each class has a single, well-defined responsibility:

- **`PaymentCalculationRequest`**: Data transfer object for request parameters
- **`PaymentCalculationResult`**: Data transfer object for calculation results
- **`PackagePaymentCalculator`**: Handles package-based payment calculations
- **`StandardPaymentCalculator`**: Handles standard payment calculations
- **`FreightChargeCalculator`**: Handles freight charge calculations
- **`PriceRuleProcessor`**: Handles price rule processing and sorting

#### 2. Open/Closed Principle (OCP)
The system is open for extension but closed for modification:

- New payment calculation strategies can be added by implementing `PaymentCalculatorInterface`
- Existing calculators can be extended without modifying their core logic

#### 3. Liskov Substitution Principle (LSP)
All calculators implement the same interface and can be used interchangeably:

```php
interface PaymentCalculatorInterface
{
    public function calculate(PaymentCalculationRequest $request): array;
}
```

#### 4. Interface Segregation Principle (ISP)
Interfaces are focused and specific to their use cases:

- `PaymentCalculatorInterface` is minimal and focused
- Each calculator only depends on what it needs

#### 5. Dependency Inversion Principle (DIP)
High-level modules depend on abstractions, not concretions:

- `CalculatePayment` depends on `PaymentCalculatorInterface`
- Dependencies are injected through constructor injection
- Services are registered in the DI container

## Class Structure

```
app/
├── Actions/HBL/
│   └── CalculatePayment.php                    # Main action class
├── Services/PaymentCalculation/
│   ├── PaymentCalculationRequest.php          # Request DTO
│   ├── PaymentCalculationResult.php           # Result DTO
│   ├── PaymentCalculatorInterface.php         # Calculator interface
│   ├── PackagePaymentCalculator.php           # Package-based calculator
│   ├── StandardPaymentCalculator.php          # Standard calculator
│   ├── FreightChargeCalculator.php            # Freight charge calculator
│   └── PriceRuleProcessor.php                 # Price rule processor
└── Providers/
    └── PaymentCalculationServiceProvider.php  # Service registration
```

## Usage

### Basic Usage

```php
use App\Actions\HBL\CalculatePayment;

// Using dependency injection
$result = CalculatePayment::run(
    cargo_type: 'Sea Cargo',
    hbl_type: 'Import',
    grand_total_volume: 10.5,
    grand_total_weight: 5.2,
    package_list_length: 2,
    destination_branch: 1,
    is_active_package: true,
    package_list: [['package_rule' => 1, 'quantity' => 2]]
);
```

### Manual Instantiation

```php
$calculator = CalculatePayment::make();
$result = $calculator->handle($params);
```

## Benefits

### 1. Improved Testability
- Each class can be unit tested in isolation
- Dependencies can be easily mocked
- Clear separation of concerns makes testing straightforward

### 2. Better Maintainability
- Changes to one calculation type don't affect others
- Clear, focused classes are easier to understand and modify
- Reduced cognitive load when working on specific features

### 3. Enhanced Extensibility
- New payment calculation strategies can be added easily
- Existing functionality can be extended without breaking changes
- Plugin-like architecture for different calculation methods

### 4. Dependency Management
- Proper dependency injection through Laravel's container
- Services are registered and managed centrally
- Easy to swap implementations for testing or different environments

## Testing

The refactored system includes comprehensive unit tests:

```bash
php artisan test tests/Unit/PaymentCalculationTest.php
```

Tests cover:
- Request/Response DTOs
- Calculator logic
- Error handling
- Data formatting

## Migration Guide

### From Old Implementation

The public API remains the same, so existing code continues to work:

```php
// Old way (still works)
$result = CalculatePayment::run($params);

// New way (recommended)
$calculator = CalculatePayment::make();
$result = $calculator->handle($params);
```

### Adding New Calculators

To add a new payment calculation strategy:

1. Implement `PaymentCalculatorInterface`
2. Register in `PaymentCalculationServiceProvider`
3. Update the main action to use the new calculator

```php
class CustomPaymentCalculator implements PaymentCalculatorInterface
{
    public function calculate(PaymentCalculationRequest $request): array
    {
        // Custom calculation logic
        return $result;
    }
}
```

## Configuration

Services are automatically registered via `PaymentCalculationServiceProvider`. To customize:

1. Modify the service provider
2. Update dependency bindings
3. Configure calculator selection logic

## Performance Considerations

- Services are registered as singletons for optimal performance
- Calculations are performed in memory with minimal database queries
- Result formatting is handled efficiently through DTOs

## Future Enhancements

Potential improvements:
- Caching layer for price rules
- Async calculation for large datasets
- Validation pipeline for request data
- Event system for calculation hooks
- Metrics and monitoring integration 
