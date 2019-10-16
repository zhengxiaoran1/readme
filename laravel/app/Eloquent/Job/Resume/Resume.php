<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 17:15
 */

namespace App\Eloquent\Job\Resume;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;

    protected $table = 'job_resume';
    protected $dates = ['deleted_at'];

    public function jobPreferencesInfo()
    {
        return $this->hasOne('App\Eloquent\Job\Resume\JobPreferences', 'job_resume_id');
    }

    public function userInfo()
    {
        return $this->belongsTo('App\Eloquent\App\User', 'user_id', 'customer_id');
    }
}