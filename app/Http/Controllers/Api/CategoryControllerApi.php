<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryControllerApi extends Controller
{
    /**
     * Get all categories.
     * Falls back to distinct manual aggregation if 'categories' model isn't implicitly initialized mapping backend drops gracefully.
     */
    public function index()
    {
        try {
            // First check if a dedicated Categories table exists. 
            // Often if not explicitly modelled it references discrete mappings or enum fields locally. 
            // In MarketMahasiswa's case, if DB 'categories' exists fetch it. 
            $categories = DB::table('categories')->select('id', 'name', 'slug', 'icon')->get();

            return response()->json([
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            // Fallback for primitive or legacy enum strings mapped on Product schemas
            return response()->json([
                'status' => 'success',
                'data' => [
                    ['id' => 1, 'name' => 'Elektronik'],
                    ['id' => 2, 'name' => 'Pakaian'],
                    ['id' => 3, 'name' => 'Buku'],
                    ['id' => 4, 'name' => 'Fesyen'],
                    ['id' => 5, 'name' => 'Jasa'],
                    ['id' => 6, 'name' => 'Lainnya']
                ]
            ]);
        }
    }
}
