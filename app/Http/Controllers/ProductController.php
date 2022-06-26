<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $variants=Variant::with('variantOptions')->get();
        $products=Product::latest()->with('productVariantPrices.productVariantOne','productVariantPrices.productVariantTwo')
        ->where(function($query) use ($request){
            if($request->title){
                $query->where('title', 'like', '%'.$request->title.'%')->get();
            }
            if($request->variant){
                $query->whereHas('productVariant',function($q) use ($request){
                    $q->where('product_variants.variant',$request->variant);
                })->get();
            }
            if($request->price_from && $request->price_to){
                $query->whereHas('productVariantPrices', function($q) use ($request){
                    $q->whereBetween('price',[$request->price_from,$request->price_to]);
                })->get();
            }
            if($request->date){
                $query->whereDate('created_at',$request->date)->get();
            }
        })
        ->paginate(2);
        return view('products.index',compact('products','variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {

        $data['title'] =$request->title;
        $data['sku']   =$this->createSku($request->sku);
        $data['description']=$request->description;
        if($request->hasFile('product_image')){
            $images = $request->file('product_image');
            foreach($images as $image){
                $path = $image->store('uploads');
                ProductImage::create([
                    'product_id' => 1,
                    'file_path' => '/storage/'.$path
                  ]);
            }
        }
        $product=Product::create($data);
        foreach($request->product_variant as $variants){
            foreach($variants['tags'] as $key => $tag){
                $product->productVariant()->attach([$variants['option']=>['variant' => $tag]]);
            }
        }
       $v_array=[];
       foreach($request->product_variant_prices as $pvp){
            $explode= explode('/',$pvp['title']);
            $pv_one=ProductVariant::where(['product_id' =>  $product->id, 'variant' => $explode[0]])->first();
            if($pv_one){
                $product_variant_one=$pv_one->id;
            }
            if(isset($explode[1])){
                $pv_two=ProductVariant::where(['product_id' =>  $product->id, 'variant' => $explode[1]])->first();
                if($pv_two){
                    $product_variant_two=$pv_two->id;
                }
            }
            if(isset($explode[2])){
                $pv_three=ProductVariant::where(['product_id' =>  $product->id, 'variant' => $explode[1]])->first();
                if($pv_two){
                    $product_variant_three=$pv_three->id;
                }
            }

            $v_array[]=[
                'product_id' => $product->id,
                'product_variant_one' => $product_variant_one?? null,
                'product_variant_two' => $product_variant_two?? null,
                'product_variant_three' => $product_variant_three?? null,
                'price' => $pvp['price'],
                'stock' => $pvp['stock'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
       }

        ProductVariantPrice::insert($v_array);
        return response()->json(['success' => true],200);

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function createSku($sku)
    {
        $check_exists=Product::where('sku', $sku)->first();
        if($check_exists){
            $sku=$sku.rand(10,100);
            return $this->createSku($sku);
        }
        return $sku;
    }
}
