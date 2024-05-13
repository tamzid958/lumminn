<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function success($invoice_id){
        return view("order-success");
    }
    
    public function failOrCancel($invoice_id){
        return view("order-fail-or-cancel");
    }

    public function create(Request $request) {

    }
}
