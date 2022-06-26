<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];


    public function productVariantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

    public function productVariant()
    {
        return $this->belongsToMany(Variant::class,'product_variants','product_id','variant_id')->withPivot('variant')->withTimestamps();
    }
}
