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

class FlowInterview extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_interview';
    protected $dates = ['deleted_at'];

    public function resumeInfo()
    {
        return $this->belongsTo('App\Eloquent\Job\Resume\Resume', 'job_resume_id', 'id');
    }

    public function recruitmentInfo()
    {
        return $this->belongsTo('App\Eloquent\Job\Recruitment', 'job_recruitment_id', 'id');
    }
}
