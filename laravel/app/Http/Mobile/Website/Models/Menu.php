<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2016/6/25
 * Time: 20:07
 */
namespace App\Http\Mobile\Website\Models;

use Framework\BaseClass\Http\Mobile\Model;
use App\Eloquent\Admin\Menu as EloquentMenu;
use App\Eloquent\Admin\RoleMenu;

class Menu extends Model
{
    public function getMenuList()
    {
        return EloquentMenu::all();
    }

    public function getMenuListByPermissionAssignment($roleId)
    {
        $roleMenuIds = RoleMenu::where('admin_role_id', $roleId)->get()->pluck('admin_menu_id')->toArray();

        $menuList = EloquentMenu::all();

        $topMenuIds = [];
        foreach ($menuList as $menu) {
            if ($menu->pid == 0) $topMenuIds[] = $menu->id;
        }

        $leftMenuIds = [];
        foreach ($menuList as $menu) {
            if (in_array($menu->pid, $topMenuIds)) $leftMenuIds[] = $menu->id;
        }

        $filterMenuIds = array_merge($topMenuIds, $leftMenuIds);
        $returnData = [];
        foreach ($menuList as $key => $menu) {
            if (!in_array($menu->id, $filterMenuIds)) {
                $menu->permission = (in_array($menu->id, $roleMenuIds)) ? 1 : 0;
                $menu->role_id = $roleId;
                $returnData[] = $menu->toArray();
            }
        }

        return $returnData;
    }
}