@extends('admin.layout')

@section('title', 'Tạo suất chiếu tự động')
@section('page-title', 'Tạo suất chiếu tự động')
@section('page-description', 'Sinh lịch chiếu hàng loạt, tránh trùng phòng, trong giờ hoạt động')

@section('content')
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Tạo suất chiếu tự động</h1>
        <p class="text-[#a6a6b0]">Chọn phim, phòng và khoảng thời gian để sinh lịch hàng loạt</p>
      </div>
      <a href="{{ route('admin.suat-chieu.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
        <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
      </a>
    </div>

    <!-- Config Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 space-y-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Phim</label>
          <select id="autoMovie" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
            <option value="">Chọn phim</option>
            @foreach($phim as $movie)
              <option value="{{ $movie->id }}" data-duration="{{ (int)($movie->do_dai ?? $movie->thoi_luong ?? 120) }}">{{ $movie->ten_phim }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Phòng chiếu</label>
          <select id="autoRoom" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
            <option value="">Chọn phòng</option>
            @foreach($phongChieu as $phong)
              <option value="{{ $phong->id }}">{{ $phong->ten_phong }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Từ ngày</label>
          <input type="date" id="fromDate" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
        </div>
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Đến ngày</label>
          <input type="date" id="toDate" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
        </div>
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Bắt đầu (HH:MM)</label>
          <input type="time" id="startAt" value="09:00" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
        </div>
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Khoảng cách giữa suất (phút)</label>
          <input type="number" id="interval" min="0" step="5" value="0" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="Ví dụ 180">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Thời lượng phim (phút)</label>
          <input type="number" id="duration" min="60" step="5" placeholder="Lấy theo phim hoặc nhập" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
        </div>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2 text-sm text-[#a6a6b0]">
            <input type="checkbox" id="allDays" class="rounded"> Tạo cho tất cả ngày trong khoảng (bao gồm cuối tuần)
          </label>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="text-xs text-[#a6a6b0]">Giờ hoạt động: 08:00–24:00. Tránh trùng phòng và tự-chồng.</div>
        <div class="flex items-center gap-2">
          <button id="btnGenerate" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-cog mr-2"></i>Tạo đề xuất</button>
          <button id="btnSave" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold" disabled><i class="fas fa-save mr-2"></i>Lưu tất cả</button>
        </div>
      </div>
      <div id="warn" class="text-yellow-400 text-sm hidden"></div>
      <div id="error" class="text-red-400 text-sm hidden"></div>
    </div>

    <!-- Preview -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-[#1a1d24]">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-[#a6a6b0] uppercase">Phim</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-[#a6a6b0] uppercase">Phòng</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-[#a6a6b0] uppercase">Ngày</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-[#a6a6b0] uppercase">Giờ</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-[#a6a6b0] uppercase">Trạng thái</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-[#a6a6b0] uppercase">Thao tác</th>
            </tr>
          </thead>
          <tbody id="tbody" class="divide-y divide-[#262833]"></tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function toMin(hm){ if(!hm) return NaN; const [h,m]=hm.split(':').map(Number); return h*60+m; }
    function addMin(d, m){ const x=new Date(d.getTime()); x.setMinutes(x.getMinutes()+m); return x; }
    function fmtDateISO(d){ const y=d.getFullYear(), mo=String(d.getMonth()+1).padStart(2,'0'), da=String(d.getDate()).padStart(2,'0'); return `${y}-${mo}-${da}`; }
    function fmtHM(d){ return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`; }

    document.addEventListener('DOMContentLoaded', function(){
      const movieSel=document.getElementById('autoMovie');
      const roomSel=document.getElementById('autoRoom');
      const from=document.getElementById('fromDate');
      const to=document.getElementById('toDate');
      const startAt=document.getElementById('startAt');
      const intervalEl=document.getElementById('interval');
      const durEl=document.getElementById('duration');
      const allDays=document.getElementById('allDays');
      const btnGen=document.getElementById('btnGenerate');
      const btnSave=document.getElementById('btnSave');
      const tbody=document.getElementById('tbody');
      const warn=document.getElementById('warn');
      const error=document.getElementById('error');

      // Autofill duration on movie change
      movieSel.addEventListener('change', ()=>{ 
        const opt=movieSel.selectedOptions[0]; 
        if(opt){ 
          const d=parseInt(opt.dataset.duration||''); 
          if(d) {
            durEl.value=d;
            // Đảm bảo thời lượng không nhỏ hơn thời lượng phim
            durEl.min = d;
          }
        }
      });
      
      // Validate duration khi người dùng nhập
      durEl.addEventListener('change', function() {
        const opt = movieSel.selectedOptions[0];
        if (opt && this.value) {
          const movieDuration = parseInt(opt.dataset.duration || '');
          const inputDuration = parseInt(this.value || '0');
          if (movieDuration && inputDuration < movieDuration) {
            error.textContent = `Thời lượng suất chiếu (${inputDuration} phút) không thể nhỏ hơn thời lượng phim (${movieDuration} phút).`;
            error.classList.remove('hidden');
            this.value = movieDuration;
          } else {
            error.classList.add('hidden');
          }
        }
      });

      function withinBusiness(s,e){ 
        const open=8*60; // 8:00
        const close=24*60; // 24:00 (00:00 ngày hôm sau)
        // Cho phép suất chiếu kết thúc đúng lúc 24:00 hoặc trước đó
        return s>=open && s<close && e>s && e<=close; 
      }
      function selfOverlap(list, s, e){ return list.some(x => !(e<=x.sMin || s>=x.eMin)); }

      let rows=[];
      function render(){
        tbody.innerHTML='';
        rows.forEach((r,i)=>{
          const tr=document.createElement('tr');
          tr.className='hover:bg-[#1a1d24]';
          let badge = '';
          if(r.conflict) {
            const reason = r.conflictReason || 'Không hợp lệ';
            badge = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-300" title="${reason}">Không hợp lệ</span>`;
          } else {
            badge = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">Hợp lệ</span>';
          }
          tr.innerHTML=`<td class="px-3 py-2 text-white">${r.movieName}</td>
                        <td class="px-3 py-2 text-white">${r.roomName}</td>
                        <td class="px-3 py-2 text-white">${r.dateVN}</td>
                        <td class="px-3 py-2 text-white">${r.startHM}–${r.endHM}</td>
                        <td class="px-3 py-2">${badge}</td>
                        <td class="px-3 py-2"><button data-i="${i}" class="del bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">Xóa</button></td>`;
          tbody.appendChild(tr);
        });
        tbody.querySelectorAll('.del').forEach(b=>b.addEventListener('click',e=>{ 
          const i=parseInt(e.currentTarget.dataset.i); 
          rows.splice(i,1); 
          render(); 
          // Kiểm tra lại conflict sau khi xóa
          if(rows.length > 0) {
            checkDatabaseConflicts();
          } else {
            btnSave.disabled = true;
          }
        }));
      }

      // Hàm kiểm tra conflict với database
      async function checkDatabaseConflicts() {
        if(rows.length === 0) {
          btnSave.disabled = true;
          return;
        }
        
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let conflictCount = 0;
        
        // Kiểm tra từng suất chiếu với database
        for(let i = 0; i < rows.length; i++) {
          const r = rows[i];
          
          // Bỏ qua nếu đã có conflict (tự-chồng hoặc quá khứ)
          if(r.conflict) {
            conflictCount++;
            continue;
          }
          
          try {
            const res = await fetch('{{ route('admin.suat-chieu.check-conflict') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                room_id: r.roomId,
                start_time: r.startISO,
                end_time: r.endISO
              })
            });
            
            if(res.ok) {
              const data = await res.json();
              if(data.has_conflict) {
                r.conflict = true;
                r.conflictReason = data.message || 'Trùng với suất chiếu trong database';
                conflictCount++;
              }
            }
          } catch(e) {
            console.error('Error checking conflict:', e);
          }
        }
        
        // Render lại để hiển thị trạng thái conflict
        render();
        
        // Cập nhật thông báo và disable nút lưu nếu có conflict
        if(conflictCount > 0) {
          warn.textContent = `Có ${conflictCount} suất chiếu không hợp lệ (trùng với suất chiếu đã có hoặc tự-chồng). Vui lòng xóa các suất chiếu không hợp lệ trước khi lưu.`;
          warn.classList.remove('hidden');
          btnSave.disabled = true;
        } else {
          warn.classList.add('hidden');
          btnSave.disabled = false;
        }
      }

      btnGen.addEventListener('click', function(e){
        e.preventDefault(); warn.classList.add('hidden'); warn.textContent=''; rows=[]; btnSave.disabled=true;
        const movieId=movieSel.value, roomId=roomSel.value, fd=from.value, td=to.value, sHM=startAt.value;
        const dur=parseInt(durEl.value||'0'), interval=parseInt(intervalEl.value||'0');
        const errs=[];
        if(!movieId) errs.push('Chọn phim.');
        if(!roomId) errs.push('Chọn phòng.');
        if(!fd||!td) errs.push('Chọn khoảng ngày.');
        const sMin=toMin(sHM); if(isNaN(sMin)) errs.push('Giờ bắt đầu không hợp lệ.');
        if(!(dur>=60)) errs.push('Thời lượng tối thiểu 60 phút.');
        
        // Kiểm tra thời lượng không nhỏ hơn thời lượng phim
        const opt = movieSel.selectedOptions[0];
        if(opt) {
          const movieDuration = parseInt(opt.dataset.duration || '');
          if(movieDuration && dur < movieDuration) {
            errs.push(`Thời lượng suất chiếu (${dur} phút) không thể nhỏ hơn thời lượng phim (${movieDuration} phút).`);
          }
        }
        
        // Kiểm tra giờ hoạt động: 8:00 - 24:00
        const open=8*60, close=24*60;
        if(sMin < open || sMin >= close) {
          errs.push('Rạp đang đóng cửa. Giờ hoạt động: 08:00–24:00.');
        }
        if(errs.length){ 
          error.textContent = errs.join('\n');
          error.classList.remove('hidden');
          alert(errs.join('\n'));
          return; 
        }
        error.classList.add('hidden');

        const movieName=movieSel.selectedOptions[0].textContent.trim();
        const roomName=roomSel.selectedOptions[0].textContent.trim();
        for(let d=new Date(fd+'T00:00:00'); d<=new Date(td+'T23:59:59'); d=addMin(new Date(fmtDateISO(d)+'T00:00:00'), 24*60)){
          const dow=d.getDay(); if(!allDays.checked && (dow===0||dow===6)) continue;
          let cur=sMin; const day=[];
          while(true){
            const end=cur+dur; 
            // Kiểm tra xem suất chiếu có trong giờ hoạt động không
            if(!withinBusiness(cur,end)) break;
            // Kiểm tra overlap: suất mới phải cách suất trước ít nhất interval phút
            // Kiểm tra tất cả các trường hợp trùng:
            // 1. Suất mới nằm hoàn toàn trong suất cũ: x.sMin <= cur && x.eMin >= end
            // 2. Suất mới bao trùm suất cũ: cur <= x.sMin && end >= x.eMin
            // 3. Suất mới bắt đầu khi suất cũ chưa kết thúc: cur < x.eMin && cur >= x.sMin
            // 4. Suất mới kết thúc khi suất cũ đã bắt đầu: end > x.sMin && end <= x.eMin
            // 5. Hai suất chiếu chạm nhau: x.eMin == cur || end == x.sMin
            // Logic tổng quát: Overlap nếu: x.sMin <= end && x.eMin >= cur
            // Nếu có interval, cần kiểm tra khoảng cách >= interval
            const overlap = day.some(x => {
              // Kiểm tra overlap trực tiếp (bao gồm chạm nhau)
              if(x.sMin <= end && x.eMin >= cur) return true;
              // Nếu có interval, kiểm tra khoảng cách
              if(interval > 0) {
                // Nếu suất mới bắt đầu sau suất cũ, khoảng cách phải >= interval
                if(cur > x.eMin && cur - x.eMin < interval) return true;
                // Nếu suất mới bắt đầu trước suất cũ, khoảng cách phải >= interval
                if(cur < x.sMin && x.sMin - end < interval) return true;
              }
              return false;
            });
            day.push({sMin:cur, eMin:end, conflict:overlap});
            // Suất tiếp theo bắt đầu sau khi suất hiện tại kết thúc + interval
            cur = end + interval;
            // Kiểm tra xem suất tiếp theo có thể bắt đầu trong giờ hoạt động không
            // (phải bắt đầu trước 24:00 và có thể kết thúc trước hoặc đúng 24:00)
            if(cur >= 24*60 || cur + dur > 24*60) break;
          }
          const base=new Date(fmtDateISO(d)+'T00:00:00');
          const now = new Date();
          day.forEach(item=>{
            const s=addMin(base,item.sMin); const e=addMin(base,item.eMin);
            // Kiểm tra thời gian không được trong quá khứ
            if(s <= now || e <= now) {
              item.conflict = true; // Đánh dấu conflict nếu trong quá khứ
            }
            rows.push({ movieId, movieName, roomId, roomName,
              dateISO: fmtDateISO(d), dateVN: fmtDateISO(d).split('-').reverse().join('/'),
              startISO: s.toISOString(), endISO: e.toISOString(), startHM: fmtHM(s), endHM: fmtHM(e), conflict: item.conflict });
          });
        }
        if(rows.length===0){ alert('Không tạo được suất nào.'); return; }
        const now = new Date();
        const pastRows = rows.filter(r => {
          const start = new Date(r.startISO);
          const end = new Date(r.endISO);
          return start <= now || end <= now;
        });
        if(pastRows.length > 0) {
          warn.textContent=`Có ${pastRows.length} suất chiếu trong quá khứ đã được đánh dấu trùng. Chỉ có thể tạo suất chiếu trong tương lai.`;
          warn.classList.remove('hidden');
        } else if(rows.some(r=>r.conflict)){ 
          warn.textContent='Có suất tự-chồng trong ngày. Điều chỉnh tham số để phù hợp.'; 
          warn.classList.remove('hidden'); 
        }
        render();
        
        // Kiểm tra conflict với database cho tất cả các suất chiếu
        checkDatabaseConflicts();
      });

      btnSave.addEventListener('click', async function(e){
        e.preventDefault();
        const token=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const valids=rows.filter(r=>!r.conflict); if(!valids.length){ alert('Không có suất hợp lệ.'); return; }
        btnSave.disabled=true; let ok=0, fail=0, conflicts=0;

        const concurrency = 8; // số request chạy song song tối đa
        let index = 0;
        async function worker(){
          while(index < valids.length){
            const i = index++;
            const r = valids[i];
            try{
              const res = await fetch('{{ route('admin.suat-chieu.store') }}', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
                body: JSON.stringify({ movie_id:r.movieId, room_id:r.roomId, start_time:r.startISO, end_time:r.endISO })
              });
              if(res.status===302 || res.ok){ ok++; }
              else {
                const text = await res.text();
                if(text.includes('trùng') || text.includes('Thời gian này đã bị trùng')) {
                  conflicts++;
                } else {
                  fail++;
                }
              }
            }catch{ fail++; }
          }
        }

        const workers = Array.from({length: Math.min(concurrency, valids.length)}, () => worker());
        await Promise.all(workers);

        if(conflicts > 0) {
          alert(`Đã tạo thành công ${ok} suất.\n${conflicts} suất bị trùng lịch với suất chiếu đã có trong cùng phòng và không được lưu.\nLỗi khác: ${fail}.`);
        } else {
          alert(`Đã tạo thành công ${ok} suất. Lỗi khác: ${fail}.`);
        }
        if(ok > 0 || conflicts === 0) {
          window.location.href='{{ route('admin.suat-chieu.index') }}';
        }
      });
    });
  </script>
@endsection
