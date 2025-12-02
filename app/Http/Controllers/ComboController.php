<?php

namespace App\Http\Controllers;

use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComboController extends Controller
{
    public function index()
    {
        $combos = Combo::orderByDesc('id')->paginate(12);

        // Quick stats
        $totalCombos = (int) Combo::count();
        $activeCombos = (int) Combo::where('trang_thai', 1)->count();
        $pausedCombos = (int) Combo::where('trang_thai', 0)->count();
        $soldToday = (int) DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
            ->whereDate('d.created_at', now()->toDateString())
            ->where('d.trang_thai', 1)
            ->sum(DB::raw('COALESCE(c.so_luong,1)'));

        return view('admin.combos.index', compact('combos', 'totalCombos', 'activeCombos', 'pausedCombos', 'soldToday'));
    }

    public function create()
    {
        return view('admin.combos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['created_by'] = Auth::id();
        Combo::create($data);
        return redirect()->route('admin.combos.index')->with('success', 'Tạo combo thành công.');
    }

    public function show(Combo $combo)
    {
        return view('admin.combos.show', compact('combo'));
    }

    public function edit(Combo $combo)
    {
        return view('admin.combos.edit', compact('combo'));
    }

    public function update(Request $request, Combo $combo)
    {
        $data = $this->validatedData($request);
        $data['updated_by'] = Auth::id();
        $combo->update($data);
        return redirect()->route('admin.combos.index')->with('success', 'Cập nhật combo thành công.');
    }

    public function destroy(Combo $combo)
    {
        // Prevent deleting combos that have been used in bookings
        $hasRelatedBookings = $combo->bookingCombos()->exists()
            || DB::table('chi_tiet_dat_ve_combo')->where('id_combo', $combo->id)->exists();

        if ($hasRelatedBookings) {
            return redirect()->route('admin.combos.index')
                ->with('error', 'Không thể xóa combo vì đã có giao dịch liên quan.');
        }

        $combo->delete();
        return redirect()->route('admin.combos.index')->with('success', 'Đã xóa combo.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'ten' => 'required|string|max:100',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'gia_goc' => 'nullable|numeric|min:0',
            'anh' => 'nullable|string|max:500',
            'combo_noi_bat' => 'nullable|boolean',
            'so_luong_toi_da' => 'nullable|integer|min:1',
            'yeu_cau_it_nhat_ve' => 'nullable|integer|min:1',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'trang_thai' => 'required|in:0,1',
        ]);

        // Chuẩn hóa boolean checkbox
        $validated['combo_noi_bat'] = (bool) ($validated['combo_noi_bat'] ?? false);
        // Datetime-local -> datetime format (optional, leave to DB if null)
        foreach (['ngay_bat_dau', 'ngay_ket_thuc'] as $f) {
            if (!empty($validated[$f])) {
                $validated[$f] = (string) \Carbon\Carbon::parse($validated[$f]);
            }
        }

        return $validated;
    }
}
