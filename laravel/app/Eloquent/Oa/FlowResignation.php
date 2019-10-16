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

class FlowResignation extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_resignation';
    protected $dates = ['deleted_at'];

    public function companyInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Company', 'oa_company_id', 'id');
    }

    public function contactsInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\Contacts', 'oa_contacts_id', 'id');
    }
}
