<?php

namespace KiyoraDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class Role
 *
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $name
 * @property $guard_name
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Role extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    static $rules = [
        'name' => 'required',
        'guard_name' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'guard_name'];

    protected $table = 'roles';

    public function users()
    {
        return $this->belongsToMany('KiyoraDashboard\Models\User', 'role_user', 'role_id', 'user_id');
    }


    public static function selectField()
    {
        $data = [];
        foreach (Role::all() as $key => $value) {
            $data[$value->name] = Str::title($value->name);
        }
        return $data;
    }

    public function permissions()
    {
        return $this->belongsToMany('KiyoraDashboard\Models\Permission', 'role_has_permissions', 'role_id', 'permission_id');
    }

    public function permissionId()
    {
        $data = [];
        foreach ($this->permissions as $key => $value) {
            $data[] = $value->name;
        }
        return $data;
    }
}
