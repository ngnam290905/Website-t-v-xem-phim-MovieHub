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

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Thời lượng phim (phút)</label>
          <input type="number" id="duration" min="60" step="5" placeholder="Lấy theo phim hoặc nhập" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
        </div>
        <div>
          <label class="block text-xs font-medium text-[#a6a6b0] mb-1">Dọn dẹp (phút)</label>
          <input type="number" id="cleanup" min="0" step="5" value="30" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
        </div>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2 text-sm text-[#a6a6b0]">
            <input type="checkbox" id="allDays" class="rounded"> Tạo cho tất cả ngày trong khoảng (bao gồm cuối tuần)
          </label>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="text-xs text-[#a6a6b0]">Giờ hoạt động: 08:00–23:00. Tránh trùng phòng và tự-chồng.</div>
        <div class="flex items-center gap-2">
          <button id="btnGenerate" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-cog mr-2"></i>Tạo đề xuất</button>
          <button id="btnSave" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold" disabled><i class="fas fa-save mr-2"></i>Lưu tất cả</button>
        </div>
      </div>
      <div id="warn" class="text-yellow-400 text-sm hidden"></div>
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
      const cleanupEl=document.getElementById('cleanup');
      const durEl=document.getElementById('duration');
      const allDays=document.getElementById('allDays');
      const btnGen=document.getElementById('btnGenerate');
      const btnSave=document.getElementById('btnSave');
      const tbody=document.getElementById('tbody');
      const warn=document.getElementById('warn');

      // Autofill duration on movie change
      movieSel.addEventListener('change', ()=>{ const opt=movieSel.selectedOptions[0]; if(opt){ const d=parseInt(opt.dataset.duration||''); if(d) durEl.value=d; }});

      function withinBusiness(s,e){ const open=8*60, close=23*60; return s>=open && e<=close; }
      function selfOverlap(list, s, e){ return list.some(x => !(e<=x.sMin || s>=x.eMin)); }

      let rows=[];
      function render(){
        tbody.innerHTML='';
        rows.forEach((r,i)=>{
          const tr=document.createElement('tr');
          tr.className='hover:bg-[#1a1d24]';
          const badge=r.conflict?'<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-300">Trùng</span>':'<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">Hợp lệ</span>';
          tr.innerHTML=`<td class="px-3 py-2 text-white">${r.movieName}</td>
                        <td class="px-3 py-2 text-white">${r.roomName}</td>
                        <td class="px-3 py-2 text-white">${r.dateVN}</td>
                        <td class="px-3 py-2 text-white">${r.startHM}–${r.endHM}</td>
                        <td class="px-3 py-2">${badge}</td>
                        <td class="px-3 py-2"><button data-i="${i}" class="del bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">Xóa</button></td>`;
          tbody.appendChild(tr);
        });
        tbody.querySelectorAll('.del').forEach(b=>b.addEventListener('click',e=>{ const i=parseInt(e.currentTarget.dataset.i); rows.splice(i,1); render(); btnSave.disabled = rows.length===0 || rows.some(r=>r.conflict);}));
      }

      btnGen.addEventListener('click', function(e){
        e.preventDefault(); warn.classList.add('hidden'); warn.textContent=''; rows=[]; btnSave.disabled=true;
        const movieId=movieSel.value, roomId=roomSel.value, fd=from.value, td=to.value, sHM=startAt.value;
        const dur=parseInt(durEl.value||'0'), interval=parseInt(intervalEl.value||'0'), cleanup=parseInt(cleanupEl.value||'0');
        const errs=[];
        if(!movieId) errs.push('Chọn phim.');
        if(!roomId) errs.push('Chọn phòng.');
        if(!fd||!td) errs.push('Chọn khoảng ngày.');
        const sMin=toMin(sHM); if(isNaN(sMin)) errs.push('Giờ bắt đầu không hợp lệ.');
        if(!(dur>=60)) errs.push('Thời lượng tối thiểu 60 phút.');
        if(errs.length){ alert(errs.join('\n')); return; }

        const movieName=movieSel.selectedOptions[0].textContent.trim();
        const roomName=roomSel.selectedOptions[0].textContent.trim();
        for(let d=new Date(fd+'T00:00:00'); d<=new Date(td+'T23:59:59'); d=addMin(new Date(fmtDateISO(d)+'T00:00:00'), 24*60)){
          const dow=d.getDay(); if(!allDays.checked && (dow===0||dow===6)) continue;
          let cur=sMin; const day=[];
          while(true){
            const end=cur+dur; const endWith= end + cleanup; if(!withinBusiness(cur,endWith)) break;
            const overlap = selfOverlap(day, cur, end);
            day.push({sMin:cur, eMin:end, conflict:overlap});
            cur = endWith + interval;
          }
          const base=new Date(fmtDateISO(d)+'T00:00:00');
          day.forEach(item=>{
            const s=addMin(base,item.sMin); const e=addMin(base,item.eMin);
            rows.push({ movieId, movieName, roomId, roomName,
              dateISO: fmtDateISO(d), dateVN: fmtDateISO(d).split('-').reverse().join('/'),
              startISO: s.toISOString(), endISO: e.toISOString(), startHM: fmtHM(s), endHM: fmtHM(e), conflict: item.conflict });
          });
        }
        if(rows.length===0){ alert('Không tạo được suất nào.'); return; }
        if(rows.some(r=>r.conflict)){ warn.textContent='Có suất tự-chồng trong ngày. Điều chỉnh tham số để phù hợp.'; warn.classList.remove('hidden'); }
        render();
        btnSave.disabled = rows.length===0 || rows.some(r=>r.conflict);
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
                if(text.includes('trùng')) conflicts++; else fail++;
              }
            }catch{ fail++; }
          }
        }

        const workers = Array.from({length: Math.min(concurrency, valids.length)}, () => worker());
        await Promise.all(workers);

        alert(`Đã tạo thành công ${ok} suất. Trùng lịch: ${conflicts}. Lỗi khác: ${fail}.`);
        window.location.href='{{ route('admin.suat-chieu.index') }}';
      });
    });
  </script>
@endsection
