<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Cart;
use Illuminate\Support\Facades\DB;


class cartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getcart(Request $request)
    {
        if($request['status'] == true){
            $user = JWTAuth::parseToken()->toUser();
            $user_id = $user['id'];
            $cart = DB::table('products')
                ->leftJoin('carts', 'products.id', '=', 'carts.prod_id')
                ->where('carts.user', '=', $user_id)
                ->select('products.name','products.category','products.price', 'products.discount','products.image','products.description','carts.*')
                ->get();
            $count = count($cart);
            return response()->json([
                'count' => $count,
                'cart' => $cart
            ], 200);
        }else{
            $id = $request['device'];
            $cart = DB::table('products')
                ->leftJoin('carts', 'products.id', '=', 'carts.prod_id')
                ->where('carts.token', '=', $id)
                ->select('products.name','products.category','products.price', 'products.discount','products.image','products.description','carts.*')
                ->get();
            $count = count($cart);
            return response()->json([
                'count' => $count,
                'cart' => $cart
            ], 200);
        }
    }
    public function getcartcount(Request $request)
    {
        if($request['status'] == true){
            $user = JWTAuth::parseToken()->toUser();
            $user_id = $user['id'];
            $count = DB::table('carts')->where('user', $user_id)->count();

            return response()->json([
                'count' => $count
            ], 200);
        }else{
            $id = $request['device'];
            $count = DB::table('carts')->where('token', $id)->count();
            return response()->json([
                'count' => $count
            ], 200);
        }
    }


    public function store(Request $request)
    {
        $prod_id = $request['id'];
        $token = $request['device'];

        if($request['status'] == true){
            $user = JWTAuth::parseToken()->toUser();
            $user_id = $user['id'];
            $check = DB::table('carts')->where([
                ['user', '=', $user_id],
                ['prod_id', '=', $prod_id]
            ])->first();
            if($check){
                $checkID = $check->id;
                $newQty = $check->quantity + 1;
                $newEntry = Cart::find($checkID);
                $newEntry->quantity = $newQty;
                $newEntry->update();

                return response()->json([
                    'status' => 2,
                    'msg' => 'successful'
                ], 200);
            }else{
                $item = new Cart();
                $item->prod_id = $prod_id;
                $item->user = $user_id;
                $item->quantity = 1;
                $item->save();

                return response()->json([
                    'status' => 1,
                    'msg' => 'successful'
                ], 200);
            }
        }else{
            $check = DB::table('carts')->where([
                ['token', '=', $token],
                ['prod_id', '=', $prod_id]
            ])->first();

            if($check){
                $checkID = $check->id;
                $newQty = $check->quantity + 1;

                $newEntry = Cart::find($checkID);
                $newEntry->quantity = $newQty;
                $newEntry->update();

                return response()->json([
                    'status' => 2,
                    'msg' => 'successful'
                ], 200);

            }else{
                $item = new Cart();
                $item->token = $token;
                $item->prod_id = $prod_id;
                $item->quantity = 1;
                $item->save();

                return response()->json([
                    'status' => 1,
                    'msg' => 'successful'
                ], 200);

            }

        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = Cart::findOrFail($id);
            $product->delete();

        } catch (\Throwable $th) {
            return response()->json(['status' => 'An error has occured!'], 500);
        }
        return response()->json(['status' => 'Item deleted'], 200);
    }
}
