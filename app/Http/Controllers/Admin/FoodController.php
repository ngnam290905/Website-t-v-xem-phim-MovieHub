<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $foods = Food::orderByDesc('id')->paginate(12);

        // Quick stats
        $totalFoods = (int) Food::count();
        $activeFoods = (int) Food::where('is_active', true)->count();
        $inactiveFoods = (int) Food::where('is_active', false)->count();
        $soldToday = (int) DB::table('chi_tiet_dat_ve_food as f')
            ->join('dat_ve as d', 'd.id', '=', 'f.id_dat_ve')
            ->whereDate('d.created_at', now()->toDateString())
            ->where('d.trang_thai', 1)
            ->sum('f.quantity');

        return view('admin.foods.index', compact('foods', 'totalFoods', 'activeFoods', 'inactiveFoods', 'soldToday'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.foods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('foods', 'public');
            $data['image'] = $imagePath;
        }

        Food::create($data);
        return redirect()->route('admin.foods.index')->with('success', 'Tạo đồ ăn thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Food $food)
    {
        // Get statistics
        $totalSold = (int) DB::table('chi_tiet_dat_ve_food')
            ->where('food_id', $food->id)
            ->join('dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve_food.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->sum('chi_tiet_dat_ve_food.quantity');

        $totalRevenue = (float) DB::table('chi_tiet_dat_ve_food')
            ->where('food_id', $food->id)
            ->join('dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve_food.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->sum(DB::raw('chi_tiet_dat_ve_food.price * chi_tiet_dat_ve_food.quantity'));

        $recentOrders = DB::table('chi_tiet_dat_ve_food')
            ->where('food_id', $food->id)
            ->join('dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve_food.id_dat_ve')
            ->join('nguoi_dung', 'nguoi_dung.id', '=', 'dat_ve.id_nguoi_dung')
            ->select('chi_tiet_dat_ve_food.*', 'dat_ve.created_at as order_date', 'nguoi_dung.ho_ten')
            ->orderByDesc('dat_ve.created_at')
            ->limit(10)
            ->get();

        return view('admin.foods.show', compact('food', 'totalSold', 'totalRevenue', 'recentOrders'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Food $food)
    {
        return view('admin.foods.edit', compact('food'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Food $food)
    {
        $data = $this->validatedData($request);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $imagePath = $request->file('image')->store('foods', 'public');
            $data['image'] = $imagePath;
        } else {
            // Keep existing image if not uploading new one
            unset($data['image']);
        }

        $food->update($data);
        return redirect()->route('admin.foods.index')->with('success', 'Cập nhật đồ ăn thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Food $food)
    {
        // Prevent deleting foods that have been used in bookings
        $hasRelatedBookings = DB::table('chi_tiet_dat_ve_food')
            ->where('food_id', $food->id)
            ->exists();

        if ($hasRelatedBookings) {
            return redirect()->route('admin.foods.index')
                ->with('error', 'Không thể xóa đồ ăn vì đã có giao dịch liên quan.');
        }

        // Delete image if exists
        if ($food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();
        return redirect()->route('admin.foods.index')->with('success', 'Đã xóa đồ ăn.');
    }

    /**
     * Toggle food active status
     */
    public function toggleStatus(Food $food)
    {
        $food->is_active = !$food->is_active;
        $food->save();

        return redirect()->route('admin.foods.index')
            ->with('success', $food->is_active ? 'Đã kích hoạt đồ ăn.' : 'Đã tắt đồ ăn.');
    }

    /**
     * Statistics page for best-selling foods
     */
    public function statistics()
    {
        $topFoods = DB::table('chi_tiet_dat_ve_food as f')
            ->join('foods', 'foods.id', '=', 'f.food_id')
            ->join('dat_ve as d', 'd.id', '=', 'f.id_dat_ve')
            ->where('d.trang_thai', 1)
            ->select(
                'foods.id',
                'foods.name',
                'foods.image',
                DB::raw('SUM(f.quantity) as total_sold'),
                DB::raw('SUM(f.price * f.quantity) as total_revenue')
            )
            ->groupBy('foods.id', 'foods.name', 'foods.image')
            ->orderByDesc('total_sold')
            ->limit(20)
            ->get();

        // Overall statistics
        $totalFoodsSold = (int) DB::table('chi_tiet_dat_ve_food')
            ->join('dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve_food.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->sum('chi_tiet_dat_ve_food.quantity');

        $totalFoodRevenue = (float) DB::table('chi_tiet_dat_ve_food')
            ->join('dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve_food.id_dat_ve')
            ->where('dat_ve.trang_thai', 1)
            ->sum(DB::raw('chi_tiet_dat_ve_food.price * chi_tiet_dat_ve_food.quantity'));

        return view('admin.foods.statistics', compact('topFoods', 'totalFoodsSold', 'totalFoodRevenue'));
    }

    /**
     * Validate request data
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock' => 'required|integer|min:0',
            'is_active' => 'required|in:0,1',
        ]);

        // Convert checkbox to boolean
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return $validated;
    }
}
