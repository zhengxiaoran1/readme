<?php

namespace App\Engine;

use App\Eloquent\Ygt\Dictionary as DictionaryModel;


class Dictionary
{
    public static function getUnitList(){
        return DictionaryModel::where('type','unit')->select('id','value')->get();
    }

}