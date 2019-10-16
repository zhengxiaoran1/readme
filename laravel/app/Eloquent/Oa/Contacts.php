<?php
/**
 * Created by PhpStorm.
 * @author wenwenbin
 * Date: 2017/12/22
 * Time: 10:32
 */
namespace App\Eloquent\Oa;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacts extends Model
{
    use SoftDeletes;
    protected $table = 'oa_contacts';
    protected $dates = ['deleted_at'];

    public function departmentInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Department', 'oa_department_id', 'id');
    }

    public function companyInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Company', 'oa_company_id', 'id');
    }

    public function employeeNumberInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\EmployeeNumber', 'oa_contacts_id', 'id');
    }

    public function workHistoryList()
    {
        return $this->hasMany('App\Eloquent\Oa\WorkHistory', 'oa_contacts_id', 'id');
    }

    public function userInfo()
    {
        return $this->belongsTo('App\Eloquent\App\User', 'user_id', 'customer_id');
    }
}
