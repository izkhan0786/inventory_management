<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;

class DataExchangeService
{
    /**
     * Export all products to a CSV file.
     */
    public function exportProducts()
    {
        $variants = ProductVariant::with('product')->get();
        $filename = 'exports/inventory_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['ID', 'Product Name', 'SKU', 'Cost Price', 'Selling Price', 'Current Stock', 'Barcode']);

        foreach ($variants as $variant) {
            fputcsv($handle, [
                $variant->id,
                $variant->product->name . ' (' . $variant->name . ')',
                $variant->sku,
                $variant->cost_price,
                $variant->selling_price,
                $variant->stock_qty,
                $variant->barcode
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('public')->put($filename, $content);

        return Storage::disk('public')->url($filename);
    }

    /**
     * Import products from CSV.
     */
    public function importProducts($filePath)
    {
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle); // Skip header

        $count = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Simple import logic - usually needs more validation
            ProductVariant::updateOrCreate(
                ['sku' => $data[2]],
                [
                    'cost_price' => $data[3],
                    'selling_price' => $data[4],
                    'stock_qty' => $data[5],
                    'barcode' => $data[6]
                ]
            );
            $count++;
        }
        fclose($handle);

        return $count;
    }
}
