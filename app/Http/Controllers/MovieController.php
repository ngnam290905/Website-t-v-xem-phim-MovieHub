<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    /**
     * Display a listing of movies
     * Admin: Can see all movies with CRUD actions
     * Staff: Can only view movies (read-only)
     */
    // ...existing code...

    /**
     * Show the form for creating a new movie
     * Only Admin can access
     */
    public function create()
    {
        return view('admin.movies.create');
    }

    /**
     * Store a newly created movie
     * Only Admin can access
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_phim' => 'required|string|max:255',
            'ten_goc' => 'nullable|string|max:255',
            'do_dai' => 'required|integer|min:1|max:600',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mo_ta' => 'required|string|min:10|max:2000',
            'dao_dien' => 'required|string|max:100',
            'dien_vien' => 'required|string|max:500',
            'the_loai' => 'nullable|string|max:255',
            'quoc_gia' => 'nullable|string|max:100',
            'ngon_ngu' => 'nullable|string|max:100',
            'do_tuoi' => 'nullable|string|max:10',
            'ngay_khoi_chieu' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_khoi_chieu',
            'trailer' => 'nullable|url|max:500',
            'trang_thai' => 'required|in:sap_chieu,dang_chieu,ngung_chieu',
        ], [
            'ten_phim.required' => 'Tên phim không được để trống.',
            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự.',
            'do_dai.required' => 'Độ dài phim không được để trống.',
            'do_dai.min' => 'Độ dài phim phải lớn hơn 0 phút.',
            'do_dai.max' => 'Độ dài phim không được vượt quá 600 phút.',
            'poster.image' => 'File poster phải là hình ảnh.',
            'poster.mimes' => 'Poster phải có định dạng: jpeg, png, jpg, gif, webp.',
            'poster.max' => 'Kích thước poster không được vượt quá 5MB.',
            'mo_ta.required' => 'Mô tả không được để trống.',
            'mo_ta.min' => 'Mô tả phải có ít nhất 10 ký tự.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'dao_dien.required' => 'Tên đạo diễn không được để trống.',
            'dao_dien.max' => 'Tên đạo diễn không được vượt quá 100 ký tự.',
            'dien_vien.required' => 'Danh sách diễn viên không được để trống.',
            'dien_vien.max' => 'Danh sách diễn viên không được vượt quá 500 ký tự.',
            'trailer.url' => 'Link trailer phải là URL hợp lệ.',
            'trailer.max' => 'Link trailer không được vượt quá 500 ký tự.',
            'trang_thai.required' => 'Trạng thái phim không được để trống.',
            'trang_thai.in' => 'Trạng thái phim không hợp lệ.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày khởi chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin nhập vào.');
        }

        try {
            $data = $request->all();
            
            // Handle poster upload
            if ($request->hasFile('poster')) {
                $file = $request->file('poster');
                $filename = time() . '_' . $file->getClientOriginalName();
                $posterPath = $file->storeAs('posters', $filename, 'public');
                $data['poster'] = $posterPath;
            }

            // Set default values
            $data['diem_danh_gia'] = 0;
            $data['so_luot_danh_gia'] = 0;

            $movie = Phim::create($data);

            return redirect()->route('admin.movies.index')
                ->with('success', 'Thêm phim "' . $movie->ten_phim . '" thành công!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi thêm phim. Vui lòng thử lại.');
        }
    }

    /**
     * Display the specified movie
     * Admin and Staff can access
     */
    public function show(Phim $movie)
    {
        $movie->load(['suatChieu.phongChieu']);
        return view('admin.movies.show', compact('movie'));
    }

    /**
     * Show the form for editing the specified movie
     * Only Admin can access
     */
    public function edit(Phim $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    /**
     * Update the specified movie
     * Only Admin can access
     */
    public function update(Request $request, Phim $movie)
    {
        $validator = Validator::make($request->all(), [
            'ten_phim' => 'required|string|max:255',
            'ten_goc' => 'nullable|string|max:255',
            'do_dai' => 'required|integer|min:1|max:600',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mo_ta' => 'required|string|min:10|max:2000',
            'dao_dien' => 'required|string|max:100',
            'dien_vien' => 'required|string|max:500',
            'the_loai' => 'nullable|string|max:255',
            'quoc_gia' => 'nullable|string|max:100',
            'ngon_ngu' => 'nullable|string|max:100',
            'do_tuoi' => 'nullable|string|max:10',
            'ngay_khoi_chieu' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_khoi_chieu',
            'trailer' => 'nullable|url|max:500',
            'trang_thai' => 'required|in:sap_chieu,dang_chieu,ngung_chieu',
        ], [
            'ten_phim.required' => 'Tên phim không được để trống.',
            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự.',
            'do_dai.required' => 'Độ dài phim không được để trống.',
            'do_dai.min' => 'Độ dài phim phải lớn hơn 0 phút.',
            'do_dai.max' => 'Độ dài phim không được vượt quá 600 phút.',
            'poster.image' => 'File poster phải là hình ảnh.',
            'poster.mimes' => 'Poster phải có định dạng: jpeg, png, jpg, gif, webp.',
            'poster.max' => 'Kích thước poster không được vượt quá 5MB.',
            'mo_ta.required' => 'Mô tả không được để trống.',
            'mo_ta.min' => 'Mô tả phải có ít nhất 10 ký tự.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'dao_dien.required' => 'Tên đạo diễn không được để trống.',
            'dao_dien.max' => 'Tên đạo diễn không được vượt quá 100 ký tự.',
            'dien_vien.required' => 'Danh sách diễn viên không được để trống.',
            'dien_vien.max' => 'Danh sách diễn viên không được vượt quá 500 ký tự.',
            'trailer.url' => 'Link trailer phải là URL hợp lệ.',
            'trailer.max' => 'Link trailer không được vượt quá 500 ký tự.',
            'trang_thai.required' => 'Trạng thái phim không được để trống.',
            'trang_thai.in' => 'Trạng thái phim không hợp lệ.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày khởi chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Handle poster upload
        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
                Storage::disk('public')->delete($movie->poster);
            }
            
            $file = $request->file('poster');
            $filename = time() . '_' . $file->getClientOriginalName();
            $posterPath = $file->storeAs('posters', $filename, 'public');
            $data['poster'] = $posterPath;
        }

        $movie->update($data);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Cập nhật phim thành công!');
    }

    /**
     * Remove the specified movie
     * Only Admin can access
     */
    public function destroy(Phim $movie)
    {
        // Delete poster file if exists
        if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
            Storage::disk('public')->delete($movie->poster);
        }

        $movie->delete();

        return redirect()->route('admin.movies.index')
            ->with('success', 'Xóa phim thành công!');
    }

    /**
     * Toggle movie status
     * Only Admin can access
     */
    public function toggleStatus(Phim $movie)
    {
        $statusMap = [
            'sap_chieu' => 'dang_chieu',
            'dang_chieu' => 'ngung_chieu',
            'ngung_chieu' => 'sap_chieu'
        ];
        
        $newStatus = $statusMap[$movie->trang_thai] ?? 'sap_chieu';
        $movie->update(['trang_thai' => $newStatus]);
        
        $statusText = [
    'sap_chieu' => 'sắp chiếu',
    'dang_chieu' => 'đang chiếu',
    'ngung_chieu' => 'ngừng chiếu'
];

$displayText = $statusText[$newStatus] ?? 'không xác định';

return redirect()->back()
    ->with('success', "Đã cập nhật trạng thái phim thành '{$displayText}'!");
    }
}
