<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait RequestHelperTrait
{
    // Paginate
    private function paginate($query, $page, $quantity){
      $query->offset($page * ($quantity - 1));
      $query->limit($quantity);
      return $query;
    }
}