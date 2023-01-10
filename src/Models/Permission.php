<?php

namespace KiyoraDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class Permission
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
class Permission extends Model implements Auditable
{
  use \OwenIt\Auditing\Auditable;
  static $rules = [
    'name' => 'required',
    'guard_name' => 'required',
    'permission_group_id' => 'required'
  ];

  protected $perPage = 20;

  /**
   * Attributes that should be mass-assignable.
   *
   * @var array
   */
  protected $fillable = ['name', 'guard_name', 'permission_group_id'];

  protected $table = 'permissions';

  public function Group()
  {
    return $this->hasOne(\KiyoraDashboard\Models\PermissionGroup::class, 'id', 'permission_group_id');
  }
}
