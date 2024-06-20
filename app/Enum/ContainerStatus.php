<?php

namespace App\Enum;

enum ContainerStatus: string
{
    case REQUESTED = 'REQUESTED';
    case DRAFT = 'DRAFT';
    case LOADING = 'LOADING';
    case UNLOADED = 'UNLOADED'; // shipment unloaded at bonded warehouse
    case RETURNED = 'RETURNED';
    case LOADED = 'LOADED'; // shipment loaded at origin warehouse
    case IN_TRANSIT = 'IN TRANSIT'; // in transit
    case REACHED_DESTINATION = 'REACHED DESTINATION'; // shipment reached destination airport
}
