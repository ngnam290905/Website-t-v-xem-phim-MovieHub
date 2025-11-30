@php
    $hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
@endphp

<div id="ai-chatbot" class="chatbot" data-endpoint="{{ url('/api/chat') }}">
  <button type="button" class="chatbot__toggle" aria-label="Mở trợ lý AI">
    <span class="chatbot__toggle-icon">💬</span>
    <span class="chatbot__toggle-label">Hỏi MovieBot</span>
  </button>

  <div class="chatbot__panel" aria-hidden="true">
    <header class="chatbot__header">
      <div>
        <p class="chatbot__title">MovieBot</p>
        <p class="chatbot__subtitle">Hỏi tôi về phim, suất chiếu, đặt vé</p>
      </div>
      <button type="button" class="chatbot__close" aria-label="Đóng">
        ×
      </button>
    </header>

    <div class="chatbot__body">
      <div class="chatbot__messages" data-element="messages">
        <div class="chatbot__message chatbot__message--assistant">
          <div class="chatbot__avatar">🤖</div>
          <div class="chatbot__bubble">
            <p>Chào bạn! Tôi là MovieBot. Hãy hỏi tôi bất cứ điều gì về phim, suất chiếu hoặc đặt vé nhé.</p>
          </div>
        </div>
      </div>
      <div class="chatbot__typing" data-element="typing" hidden>
        <span class="chatbot__dot"></span>
        <span class="chatbot__dot"></span>
        <span class="chatbot__dot"></span>
      </div>
    </div>

    <form class="chatbot__form" data-element="form">
      <input type="text" name="message" autocomplete="off" placeholder="Nhập câu hỏi của bạn..." class="chatbot__input">
      <button type="submit" class="chatbot__send" aria-label="Gửi">
        Gửi
      </button>
    </form>
  </div>
</div>

@if (! $hasViteAssets)
  <style>
    .chatbot{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;align-items:flex-end;gap:12px;z-index:60;font-family:"Inter",sans-serif}
    .chatbot__toggle{display:flex;align-items:center;gap:8px;padding:12px 16px;border-radius:999px;background:linear-gradient(135deg,#F53003,#ff7849);color:#fff;font-weight:600;border:none;cursor:pointer;box-shadow:0 12px 24px rgba(245,48,3,.28);transition:transform .2s ease,box-shadow .2s ease}
    .chatbot__toggle:hover{transform:translateY(-2px);box-shadow:0 18px 36px rgba(245,48,3,.32)}
    .chatbot__toggle-icon{font-size:18px}
    .chatbot__panel{display:none;flex-direction:column;width:min(360px,calc(100vw - 32px));max-height:520px;background:#16161d;border-radius:20px;border:1px solid rgba(255,255,255,.08);box-shadow:0 24px 48px rgba(0,0,0,.45);overflow:hidden}
    .chatbot--open .chatbot__panel{display:flex}
    .chatbot__header{display:flex;justify-content:space-between;align-items:flex-start;padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.08);background:rgba(15,15,18,.95)}
    .chatbot__title{margin:0;font-weight:700;font-size:16px}
    .chatbot__subtitle{margin:2px 0 0;color:rgba(255,255,255,.6);font-size:12px}
    .chatbot__close{background:transparent;border:none;color:rgba(255,255,255,.7);font-size:20px;cursor:pointer}
    .chatbot__body{display:flex;flex-direction:column;gap:12px;padding:16px 20px;overflow:hidden}
    .chatbot__messages{flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:12px;padding-right:6px;scrollbar-width:thin}
    .chatbot__messages::-webkit-scrollbar{width:6px}
    .chatbot__messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:999px}
    .chatbot__message{display:flex;gap:12px;align-items:flex-start}
    .chatbot__message--user{flex-direction:row-reverse}
    .chatbot__avatar{width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;font-size:16px}
    .chatbot__bubble{max-width:240px;padding:12px 14px;border-radius:16px;background:rgba(255,255,255,.08);font-size:13px;line-height:1.5;color:rgba(255,255,255,.92)}
    .chatbot__message--user .chatbot__bubble{background:linear-gradient(135deg,#F53003,#ff7849);color:#fff}
    .chatbot__bubble ul{margin:8px 0 0;padding-left:18px}
    .chatbot__link{color:#ff9470}
    .chatbot__typing{display:flex;gap:6px;padding:0 4px}
    .chatbot__dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.22);animation:chatbotDot 1.2s infinite}
    .chatbot__dot:nth-child(2){animation-delay:.15s}
    .chatbot__dot:nth-child(3){animation-delay:.3s}
    @keyframes chatbotDot{0%,80%,100%{opacity:.3;transform:translateY(0)}40%{opacity:1;transform:translateY(-4px)}}
    .chatbot__form{display:flex;gap:8px;padding:16px 20px 20px;border-top:1px solid rgba(255,255,255,.06);background:rgba(12,12,18,.95)}
    .chatbot__input{flex:1;border:none;border-radius:999px;padding:10px 16px;background:rgba(255,255,255,.08);color:#fff}
    .chatbot__input::placeholder{color:rgba(255,255,255,.5)}
    .chatbot__send{padding:10px 18px;border-radius:999px;border:none;font-weight:600;cursor:pointer;color:#0f0f12;background:#fff;transition:background .2s ease,color .2s ease}
    .chatbot__send:hover{background:#ff7849;color:#fff}
    @media (max-width:640px){.chatbot{right:16px;bottom:16px}.chatbot__toggle{padding:10px 14px}.chatbot__panel{width:calc(100vw - 32px);bottom:0;right:0}}
  </style>
  <script>
    (function(){
      var root=document.getElementById('ai-chatbot');
      if(!root){return;}
      var panel=root.querySelector('.chatbot__panel');
      var toggle=root.querySelector('.chatbot__toggle');
      var close=root.querySelector('.chatbot__close');
      var form=root.querySelector('[data-element="form"]');
      var input=root.querySelector('.chatbot__input');
      var messages=root.querySelector('[data-element="messages"]');
      var typing=root.querySelector('[data-element="typing"]');
      var endpoint=root.dataset.endpoint;
      var csrf=document.querySelector('meta[name="csrf-token"]');

      function scrollToBottom(){
        messages.scrollTop=messages.scrollHeight;
      }

      function appendMessage(role,html){
        var wrap=document.createElement('div');
        wrap.className=role==='user'?'chatbot__message chatbot__message--user':'chatbot__message chatbot__message--assistant';

        var avatar=document.createElement('div');
        avatar.className='chatbot__avatar';
        avatar.textContent=role==='user'?'🙋':'🤖';

        var bubble=document.createElement('div');
        bubble.className='chatbot__bubble';
        bubble.innerHTML=html;

        wrap.appendChild(avatar);
        wrap.appendChild(bubble);
        messages.appendChild(wrap);
        scrollToBottom();
      }

      function renderReply(data){
        if(data.type==='movie_search' && Array.isArray(data.movies) && data.movies.length){
          var items=data.movies.map(function(movie){
            var bits=[movie.ten_phim,movie.the_loai,movie.quoc_gia].filter(Boolean).join(' • ');
            return '<li>'+bits+'</li>';
          }).join('');
          return '<p>Mình tìm được những phim sau:</p><ul>'+items+'</ul>';
        }
        if(data.type==='showtime_search' && Array.isArray(data.showtimes) && data.showtimes.length){
          var slots=data.showtimes.map(function(show){
            var name=show && show.phim && show.phim.ten_phim?show.phim.ten_phim+' — ':'';
            return '<li>'+name+(show.thoi_gian_bat_dau || '')+'</li>';
          }).join('');
          return '<p>Các suất chiếu phù hợp:</p><ul>'+slots+'</ul>';
        }
        if(data.type==='booking_link' && data.link){
          return '<p>Bạn có thể đặt vé tại đây: <a class="chatbot__link" href="'+data.link+'">'+data.link+'</a></p>';
        }
        if(data.type==='training' && data.response){
          return '<p>'+data.response+'</p>';
        }
        if(data.response){
          return '<p>'+data.response+'</p>';
        }
        return '<p>Xin lỗi, hiện mình chưa tìm được thông tin phù hợp.</p>';
      }

      function setTyping(show){
        typing.hidden=!show;
      }

      function openPanel(){
        root.classList.add('chatbot--open');
        panel.setAttribute('aria-hidden','false');
        if(input){input.focus({preventScroll:true});}
      }

      function closePanel(){
        root.classList.remove('chatbot--open');
        panel.setAttribute('aria-hidden','true');
      }

      toggle.addEventListener('click',function(){
        if(root.classList.contains('chatbot--open')){closePanel();return;}
        openPanel();
      });
      close.addEventListener('click',closePanel);

      form.addEventListener('submit',function(event){
        event.preventDefault();
        var text=input.value.trim();
        if(!text){return;}

        appendMessage('user','<p>'+text+'</p>');
        input.value='';
        setTyping(true);

        fetch(endpoint,{
          method:'POST',
          headers:{
            'Content-Type':'application/json',
            'Accept':'application/json',
            'X-Requested-With':'XMLHttpRequest',
            'X-CSRF-TOKEN':csrf?csrf.content:''
          },
          body:JSON.stringify({message:text})
        }).then(function(res){
          if(!res.ok){throw new Error('Network error');}
          return res.json();
        }).then(function(json){
          appendMessage('assistant',renderReply(json));
        }).catch(function(err){
          console.error('Chatbot error:',err);
          appendMessage('assistant','<p>Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau.</p>');
        }).finally(function(){
          setTyping(false);
        });
      });
    })();
  </script>
@endif
