<?php

namespace App\Enum;

enum ContainerStatus: string
{
    case REQUESTED = 'CONTAINER ORDERED'; // Container ordered
    case DRAFT = 'DRAFT'; // Draft stage
    case LOADING = 'LOADING'; // Loading at origin
    case LOADED = 'LOADED'; // Shipment loaded at origin warehouse
    case IN_TRANSIT = 'IN TRANSIT'; // Currently in transit
    case REACHED_DESTINATION = 'REACHED DESTINATION'; // Arrived at destination
    case ARRIVED_PRIMARY_WAREHOUSE = 'ARRIVED PRIMARY WAREHOUSE'; // Arrived at destination warehouse
    case UNLOADED = 'UNLOADED'; // Shipment unloaded at bonded warehouse
    case DEPARTED_PRIMARY_WAREHOUSE = 'DEPARTED PRIMARY WAREHOUSE'; // Left destination warehouse
    case RETURNED = 'RETURNED';  // Returned shipment
}
