<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $file = 'products.json';

    public function index()
    {
        return view('products.index');
    }

    public function getProducts()
    {
        $products = $this->readData();

        // Sort by latest (newest first)
        usort($products, function ($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
        ]);

        $products = $this->readData();

        $newProduct = [
            'name' => $request->name,
            'quantity' => (int)$request->quantity,
            'price' => (float)$request->price,
            'datetime' => now()->toDateTimeString(),
        ];

        $products[] = $newProduct;

        Storage::put($this->file, json_encode($products, JSON_PRETTY_PRINT));

        return response()->json([
            'message' => 'Product added successfully'
        ]);
    }


    public function update(Request $request, $index)
    {
    $request->validate([
        'name' => 'required|string',
        'quantity' => 'required|numeric|min:1',
        'price' => 'required|numeric|min:1',
    ]);

    $products = $this->readData();

    if (!isset($products[$index])) {
        return response()->json(['error' => 'Not found'], 404);
    }

    $products[$index] = [
        'name' => $request->name,
        'quantity' => (int)$request->quantity,
        'price' => (float)$request->price,
        'datetime' => now()->toDateTimeString(),
    ];

    Storage::put($this->file, json_encode($products, JSON_PRETTY_PRINT));

    return response()->json(['message' => 'Updated']);
    }

    public function delete($index)
    {
    $products = $this->readData();

    if (!isset($products[$index])) {
        return response()->json(['error' => 'Not found'], 404);
    }

    array_splice($products, $index, 1);

    Storage::put($this->file, json_encode($products, JSON_PRETTY_PRINT));

    return response()->json(['message' => 'Deleted']);
    }


    private function readData()
    {
        if (!Storage::exists($this->file)) {
            Storage::put($this->file, json_encode([]));
        }

        $data = Storage::get($this->file);

        return json_decode($data, true) ?? [];
    }
}