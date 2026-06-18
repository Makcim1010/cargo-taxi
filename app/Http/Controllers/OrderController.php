<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    // Заказчик создаёт заказ
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'from_address' => 'required|string',
            'to_address' => 'nullable|string',
            'vehicle_type' => 'required|string',
            'order_type' => 'required|in:point_to_point,one_point',
            'desired_time' => 'nullable|string',
            'comment' => 'nullable|string',
            'need_loaders' => 'boolean',
            'work_volume' => 'nullable|string',
            'price' => 'required|numeric|min:1'
        ]);

        $order = Order::create([
            'customer_id' => $user->id,
            'from_address' => $validated['from_address'],
            'to_address' => $validated['to_address'] ?? null,
            'vehicle_type' => $validated['vehicle_type'],
            'order_type' => $validated['order_type'],
            'desired_time' => $validated['desired_time'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'need_loaders' => $validated['need_loaders'] ?? false,
            'work_volume' => $validated['work_volume'] ?? null,
            'price' => $validated['price'] ?? 500,
            'driver_price' => 500,
            'loader_price' => $validated['need_loaders'] ? 100 : 0,
            'status' => 'searching'
        ]);

        return response()->json($order, 201);
    }

    // Заказы для водителя (только searching)
    public function availableForDriver(): JsonResponse
    {
        $orders = Order::where('status', 'searching')->get();
        return response()->json($orders);
    }

    // Заказы для грузчика (только waiting_for_loader)
    public function availableForLoader(): JsonResponse
    {
        $orders = Order::where('status', 'waiting_for_loader')
            ->where('need_loaders', true)
            ->get();
        return response()->json($orders);
    }

    // Водитель принимает заказ
    public function acceptByDriver(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $withLoader = $request->input('with_loader', false);

        if ($order->status !== 'searching') {
            return response()->json(['message' => 'Заказ уже занят'], 400);
        }

        $order->driver_id = auth()->id();

        if ($withLoader && $order->need_loaders) {
            $order->status = 'waiting_for_loader';
        } else {
            $order->status = 'active';
        }

        $order->save();

        return response()->json($order);
    }

    // Грузчик принимает заказ
    public function acceptByLoader($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'waiting_for_loader') {
            return response()->json(['message' => 'Заказ не ожидает грузчика'], 400);
        }

        if (!$order->need_loaders) {
            return response()->json(['message' => 'Для этого заказа не требуются грузчики'], 400);
        }

        $order->loader_id = auth()->id();
        $order->status = 'active';
        $order->save();

        return response()->json($order);
    }

    // Активные заказы для водителя
    public function driverActive(): JsonResponse
    {
        $orders = Order::where('driver_id', auth()->id())
            ->whereIn('status', ['active', 'waiting_for_loader', 'completed_by_driver'])
            ->get();

        return response()->json($orders);
    }

    // История заказов для водителя
    public function driverHistory(): JsonResponse
    {
        $orders = Order::where('driver_id', auth()->id())
            ->whereIn('status', ['confirmed_by_customer', 'cancelled'])
            ->get();

        return response()->json($orders);
    }

    // Активные заказы для грузчика
    public function loaderActive(): JsonResponse
    {
        $orders = Order::where('loader_id', auth()->id())
            ->whereIn('status', ['active', 'completed_by_loader'])
            ->get();

        return response()->json($orders);
    }

    // История заказов для грузчика
    public function loaderHistory(): JsonResponse
    {
        $orders = Order::where('loader_id', auth()->id())
            ->whereIn('status', ['confirmed_by_customer', 'cancelled'])
            ->get();

        return response()->json($orders);
    }

    // Активные заказы для заказчика
    public function customerActive(): JsonResponse
    {
        $orders = Order::where('customer_id', auth()->id())
            ->whereIn('status', ['searching', 'waiting_for_loader', 'active', 'completed_by_driver', 'completed_by_loader'])
            ->get();

        return response()->json($orders);
    }

    // История заказов для заказчика
    public function customerHistory(): JsonResponse
    {
        $orders = Order::where('customer_id', auth()->id())
            ->whereIn('status', ['confirmed_by_customer', 'cancelled'])
            ->get();

        return response()->json($orders);
    }

    // Водитель завершает свою часть
    public function completeByDriver($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->driver_id !== auth()->id()) {
            return response()->json(['message' => 'Вы не назначены на этот заказ'], 403);
        }

        if (!in_array($order->status, ['active', 'waiting_for_loader'])) {
            return response()->json(['message' => 'Заказ не в активном статусе'], 400);
        }

        // Если есть грузчик — ждём его завершения
        if ($order->need_loaders && $order->loader_id) {
            $order->status = 'completed_by_driver';
        } else {
            // Если грузчик не нужен — сразу завершён
            $order->status = 'completed_by_driver';
        }

        $order->save();

        return response()->json($order);
    }

    // Грузчик завершает свою часть
    public function completeByLoader($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->loader_id !== auth()->id()) {
            return response()->json(['message' => 'Вы не назначены на этот заказ'], 403);
        }

        if ($order->status !== 'active') {
            return response()->json(['message' => 'Заказ не в активном статусе'], 400);
        }

        if (!$order->need_loaders) {
            return response()->json(['message' => 'Для этого заказа не требуются грузчики'], 400);
        }

        $order->status = 'completed_by_loader';
        $order->save();

        return response()->json($order);
    }

    // Заказчик подтверждает завершение
    public function confirmByCustomer($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->customer_id !== auth()->id()) {
            return response()->json(['message' => 'Вы не заказчик'], 403);
        }

        // Проверяем, что оба исполнителя завершили (если нужны грузчики)
        if ($order->need_loaders) {
            if ($order->status !== 'completed_by_driver' && $order->status !== 'completed_by_loader') {
                return response()->json(['message' => 'Ожидайте завершения всех исполнителей'], 400);
            }
        } else {
            if ($order->status !== 'completed_by_driver') {
                return response()->json(['message' => 'Ожидайте завершения водителя'], 400);
            }
        }

        // Перевод денег
        $customer = $order->customer;
        $driver = $order->driver;
        $loader = $order->loader;

        if ($customer->balance < $order->price) {
            return response()->json(['message' => 'Недостаточно средств'], 400);
        }

        $customer->balance -= $order->price;
        $customer->save();

        $driver->balance = ($driver->balance ?? 0) + $order->driver_price;
        $driver->save();

        if ($loader && $order->need_loaders) {
            $loader->balance = ($loader->balance ?? 0) + $order->loader_price;
            $loader->save();
        }

        $order->status = 'confirmed_by_customer';
        $order->save();

        return response()->json($order);
    }
}