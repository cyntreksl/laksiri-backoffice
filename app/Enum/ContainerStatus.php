<?php

namespace App\Enum;

enum ContainerStatus: string
{
    case REQUESTED = 'REQUESTED';
    case DRAFT = 'DRAFT';
    case LOADING = 'LOADING';
    case UNLOADED = 'UNLOADED';
    case RETURNED = 'RETURNED';
    case LOADED = 'LOADED';
}
