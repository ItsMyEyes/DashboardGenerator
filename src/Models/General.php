<?php

namespace KiyoraDashboard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class General extends Model
{
    use HasFactory;

    public static function IsActiveRoute($route)
    {
        return request()->is($route) ? ' active' : '';
    }
}
