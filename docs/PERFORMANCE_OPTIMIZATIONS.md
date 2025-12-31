# Performance Optimizations Summary

## Overview
Optimized HBL List, Loaded Shipment List, and All Shipments List pages to fix slow initial data fetching issues.

## Issues Identified

### Common Issues Across All Pages

**Frontend Issues:**
- Unused parameters in watchers causing unnecessary overhead
- Inconsistent pagination behavior

**Backend Issues:**
- **CRITICAL**: Loading ALL records on initial page load via `getLoadedContainers()`
- **N+1 Query Problem**: Multiple queries per record for relationships
- No eager loading in dataset methods
- Ambiguous column names when using polymorphic relationships with `latestOfMany()`

## Optimizations Applied

### Frontend Optimizations (All Pages)

1. **Removed unused watcher parameters**
   ```javascript
   // Before
   watch(() => filters.value.warehouse.value, (newValue) => {
   
   // After
   watch(() => filters.value.warehouse.value, () => {
   ```

2. **Fixed pagination consistency**
   ```javascript
   // Before
   fetchData(currentPage.value);
   
   // After
   fetchData(currentPage.value, filters.value.global.value);
   ```

3. **Reset to page 1 on filter clear**
   ```javascript
   // Before
   fetchData(currentPage.value);
   
   // After
   fetchData(1);
   ```

4. **Removed unnecessary props**
   ```javascript
   // Removed containers prop that was loading all data
   // Now uses paginated data from API
   ```

### Backend Optimizations

#### 1. HBL List (app/Repositories/HBLRepository.php)

**Added Eager Loading:**
```php
$query->with([
    'mhbl:id,hbl_number,reference',
    'user:id,name',
    'packages:id,hbl_id,is_loaded',
    'tokens' => function ($query) {
        $query->select('id', 'hbl_id', 'token', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->with(['customerQueue:id,token_id,type']);
    },
    'latestDetainRecord' => function ($query) {
        // Use table prefix to avoid ambiguous column names with latestOfMany()
        $query->select('detain_records.id', 'detain_records.rtfable_id', 'detain_records.rtfable_type', 'detain_records.is_rtf', 'detain_records.detain_type');
    },
])
->withCount([
    'packages as total_packages_count',
    'packages as loaded_packages_count' => function ($query) {
        $query->where('is_loaded', 1);
    }
])
->with(['hblPayment' => function ($query) {
    $query->select('id', 'hbl_id', 'status')
        ->latest()
        ->limit(1);
}]);
```

**Optimized HBLResource (app/Http/Resources/HBLResource.php):**
- Use eager-loaded counts instead of querying: `$this->total_packages_count`
- Use eager-loaded relationships with `whenLoaded()`
- Eliminated multiple queries per record

#### 2. Loaded Shipment List

**Removed unnecessary data loading (app/Http/Controllers/LoadedContainerController.php):**
```php
// REMOVED this line that was loading ALL containers
'containers' => $this->containerRepository->getLoadedContainers(),
```

**Added Eager Loading (app/Repositories/LoadedContainerRepository.php):**
```php
$query->with([
    'branch:id,name',
    'latestDetainRecord' => function ($query) {
        // Use table prefix to avoid ambiguous column names with latestOfMany()
        $query->select('detain_records.id', 'detain_records.rtfable_id', 'detain_records.rtfable_type', 'detain_records.is_rtf', 'detain_records.detain_type');
    },
]);
```

#### 3. All Shipments List

**Removed unnecessary data loading (app/Http/Controllers/ContainerController.php):**
```php
// REMOVED this line that was loading ALL containers
'containers' => $this->containerRepository->getLoadedContainers(),
```

**Added Eager Loading (app/Repositories/ContainerRepositories.php):**
```php
$query->with([
    'branch:id,name',
    'latestDetainRecord' => function ($query) {
        $query->select('detain_records.id', 'detain_records.rtfable_id', 'detain_records.rtfable_type', 'detain_records.is_rtf', 'detain_records.detain_type');
    },
]);
```

**Optimized ContainerResource (app/Http/Resources/ContainerResource.php):**
- Use `whenLoaded()` for relationships
- Prevent N+1 queries

## Key Technical Solutions

### Polymorphic Relationship Optimization

The `latestDetainRecord` uses a polymorphic relationship (`morphOne`) with `latestOfMany()`, which creates a join. This caused two issues:

1. **Column not found**: Initially tried to use `container_id` or `hbl_id`, but the table uses `rtfable_id` and `rtfable_type`
2. **Ambiguous column names**: When using `latestOfMany()` with a join, column names become ambiguous

**Solution**: Use a closure with fully qualified column names:
```php
'latestDetainRecord' => function ($query) {
    $query->select('detain_records.id', 'detain_records.rtfable_id', 'detain_records.rtfable_type', 'detain_records.is_rtf', 'detain_records.detain_type');
}
```

## Performance Impact

### Before Optimization
- **HBL List**: N+1 queries for every HBL record (10 records = 50+ queries)
- **Loaded Shipment List**: Loading ALL containers on page load (could be thousands of records)
- **All Shipments List**: Loading ALL containers on page load + N+1 queries

### After Optimization
- **HBL List**: 1 main query + ~7 eager load queries (total ~8 queries regardless of record count)
- **Loaded Shipment List**: 1 main query + 2 eager load queries (total ~3 queries)
- **All Shipments List**: 1 main query + 2 eager load queries (total ~3 queries)

### Expected Results
- **Initial page load**: 70-90% faster
- **Database queries**: Reduced from 50+ to ~3-8 queries per page
- **Memory usage**: Significantly reduced (no longer loading all records)
- **Pagination**: Smooth and consistent

## Recommended Next Steps

1. **Add Database Indexes** (for further optimization):
   ```sql
   -- HBL table
   CREATE INDEX idx_hbl_status ON hbl(status);
   CREATE INDEX idx_hbl_warehouse ON hbl(warehouse);
   CREATE INDEX idx_hbl_cargo_type ON hbl(cargo_type);
   CREATE INDEX idx_hbl_hbl_type ON hbl(hbl_type);
   CREATE INDEX idx_hbl_created_at ON hbl(created_at);
   CREATE INDEX idx_hbl_created_by ON hbl(created_by);
   CREATE INDEX idx_hbl_search ON hbl(hbl_number, reference, hbl_name, contact_number);
   
   -- Container table
   CREATE INDEX idx_container_status ON containers(status);
   CREATE INDEX idx_container_cargo_type ON containers(cargo_type);
   CREATE INDEX idx_container_created_at ON containers(created_at);
   CREATE INDEX idx_container_search ON containers(reference, container_number, bl_number, awb_number);
   
   -- Detain Records (polymorphic)
   CREATE INDEX idx_detain_records_rtfable ON detain_records(rtfable_type, rtfable_id);
   CREATE INDEX idx_detain_records_branch ON detain_records(branch_id);
   ```

2. **Consider Query Result Caching** for frequently accessed filter combinations

3. **Monitor Query Performance** using Laravel Debugbar or Telescope

## Files Modified

### Frontend
- `resources/js/Pages/HBL/HBLList.vue`
- `resources/js/Pages/Loading/LoadedShipmentList.vue`
- `resources/js/Pages/Container/AllShipmentList.vue`
- `resources/js/Pages/Arrival/ShipmentsArrivalsList.vue`

### Backend
- `app/Repositories/HBLRepository.php`
- `app/Http/Resources/HBLResource.php`
- `app/Http/Controllers/LoadedContainerController.php`
- `app/Repositories/LoadedContainerRepository.php`
- `app/Http/Resources/ContainerResource.php`
- `app/Http/Controllers/ContainerController.php`
- `app/Repositories/ContainerRepositories.php`

## Additional Fix: Modal Data Loading

### Issue
After removing the `containers` prop, clicking "Show" in the context menu caused an error:
```
Cannot read properties of undefined (reading 'name')
```

### Solution
Instead of finding the container in the paginated list (which may not have all nested relationships), fetch the full container details from the API when opening the modal:

```javascript
// Before
const confirmViewLoadedShipment = (id) => {
    loadedShipment.value = containers.value.find(
        (container) => container.id === id
    );
    showConfirmLoadedShipmentModal.value = true;
};

// After
const confirmViewLoadedShipment = async (id) => {
    try {
        const response = await axios.get(`/loading/loaded-containers/get-container/${id}`);
        loadedShipment.value = response.data;
        showConfirmLoadedShipmentModal.value = true;
    } catch (error) {
        console.error("Error fetching container details:", error);
    }
};
```

This ensures the modal always has complete container data with all relationships loaded.
