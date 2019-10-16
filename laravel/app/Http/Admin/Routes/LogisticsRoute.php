<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class LogisticsRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Logistics\Controllers', 'prefix' => 'logistics'], function ($router) {
            //物流
            $router->any('lists', 'IndexController@lists');
            $router->any('edit', 'IndexController@edit');
            $router->any('line-list', 'LineController@lists');
            $router->any('line-edit', 'LineController@edit');
            $router->any('notes-list', 'NotesController@lists');
            $router->any('notes-edit', 'NotesController@edit');
            $router->any('notes-delete', 'NotesController@del');
            $router->any('bill-list', 'BillController@lists');
            $router->any('bill-edit', 'BillController@edit');
            $router->any('bill-delete', 'BillController@del');
            $router->any('invoice-list', 'InvoiceController@lists');
            $router->any('invoice-edit', 'InvoiceController@edit');
            $router->any('invoice-delete', 'InvoiceController@del');
            $router->any('delete', 'IndexController@delete');

        });
    }
}