<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/3
 * Time: 19:28
 */
namespace App\Api\OA\Attendance\Controllers;

use App\Api\OA\Attendance\Models\Attendance;
use Framework\BaseClass\Api\Controller;

class AttendanceController extends Controller
{
    public function test()
    {
        exit;
//        $token = app('token')->checkToken();
//        return $token;
    }

    public function checkInStatus()
    {
        $token = app('token')->checkToken();
        if ($token['oa_contacts_id'] === 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $contactsId = $token['oa_contacts_id'];
        $params = $this->getRequestParameters(['lat', 'lng']);
        $manager = new Attendance();
        $data = $manager->checkInStatus($contactsId, $params['lat'], $params['lng']);
        return $data;
    }

    public function checkIn()
    {
        $token = app('token')->checkToken();
        if ($token['oa_contacts_id'] === 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $contactsId = $token['oa_contacts_id'];
        $params = $this->getRequestParameters(['lat', 'lng']);
        $manager = new Attendance();
        $data = $manager->checkIn($contactsId, $params['lat'], $params['lng'], '');
        return $data;
    }
}