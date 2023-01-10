<?php

namespace KiyoraDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class PermissionGroup
 *
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $title
 * @property $description
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class PermissionGroup extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    static $rules = [
        'title' => 'required',
        'description' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'description'];

    protected $table = 'permission_group';

    public static function selectField()
    {
        $data = [];
        $list = PermissionGroup::all();
        foreach ($list as $key => $value) {
            $data[$value->id] = $value->title;
        }
        return $data;
    }

    public function permissionList()
    {
        return $this->hasMany(\KiyoraDashboard\Models\Permission::class, 'permission_group_id', 'id');
    }
}
