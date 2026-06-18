<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Order;

class LoaderController extends Controller
{
    public function activeOrders(): JsonResponse
    {
        $orders = Order::where('loader_id', auth()->id())
            ->whereIn('status', ['active', 'searching'])
            ->get();
        
        return response()->json($orders);
    }
    
    public function historyOrders(): JsonResponse
    {
        $orders = Order::where('loader_id', auth()->id())
            ->whereIn('status', ['completed', 'cancelled'])
            ->get();
        
        return response()->json($orders);
    }
    
    public function stats(): JsonResponse
    {
        $orders = Order::where('loader_id', auth()->id())
            ->where('status', 'completed')
            ->get();
        
        return response()->json([
            'totalOrders' => $orders->count(),
            'totalEarnings' => $orders->sum('price')
        ]);
    }
}