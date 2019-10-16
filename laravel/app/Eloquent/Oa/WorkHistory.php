<?php
/**
 * Created by PhpStorm.
 * @author wenwenbin
 * Date: 2017/12/22
 * Time: 10:32
 */
namespace App\Eloquent\Oa;

use Framework\BaseClass\Eloquent\Model;

class WorkHistory extends Model
{
    protected $table = 'oa_work_history';

    public function contactsInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Contacts', 'oa_contacts_id', 'id');
    }

    public function departmentInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Department', 'oa_department_id', 'id');
    }

    public function talentPoolInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\TalentPool', 'oa_work_history_id', 'id');
    }
}
