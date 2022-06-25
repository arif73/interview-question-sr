<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{

    public function productVariantOne()
    {
      return $this->belongsTo(ProductVariant::class,'product_variant_one')->select(['id','variant']);
    }
    public function productVariantTwo()
    {
       return  $this->belongsTo(ProductVariant::class,'product_variant_two')->select(['id','variant']);
    }
    public function productVariantThree()
    {
       return  $this->belongsTo(ProductVariant::class,'product_variant_three')->select(['id','variant']);
    }
}
