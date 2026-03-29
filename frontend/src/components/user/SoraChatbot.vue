<template>
  <div class="sora-chatbot-wrapper z-index-max font-luxury">
    
    <button 
      class="chatbot-fab btn rounded-circle shadow-lg d-flex align-items-center justify-content-center position-fixed transition-transform"
      :class="isOpen ? 'scale-0' : 'scale-100'"
      @click="toggleChat"
    >
      <i class="bi bi-chat-right-quote-fill text-gold fs-3"></i>
      <span class="position-absolute top-0 start-100 translate-middle p-2 bg-accent-red border border-light rounded-circle animate-pulse">
        <span class="visually-hidden">New alerts</span>
      </span>
    </button>

    <div 
      class="chatbot-window position-fixed bg-white shadow-lg overflow-hidden d-flex flex-column transition-all"
      :class="isOpen ? 'chatbot-open' : 'chatbot-closed'"
    >
      
      <div class="chatbot-header bg-primary-luxury p-3 position-relative d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar-wrapper position-relative">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=150&auto=format&fit=crop" alt="SORA Assistant" class="rounded-circle object-fit-cover border border-gold" style="width: 45px; height: 45px;">
            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="margin-bottom: 2px; margin-right: 2px;"></span>
          </div>
          <div>
            <h6 class="font-serif fw-bold text-gold mb-0 fs-5 lh-1">SORA Assistant</h6>
            <small class="text-white opacity-75 tracking-widest text-uppercase" style="font-size: 0.65rem;">Chuyên viên tư vấn</small>
          </div>
        </div>
        <button @click="toggleChat" class="btn btn-link text-white opacity-75 hover-opacity-100 p-0 border-0 shadow-none">
          <i class="bi bi-x-lg fs-5"></i>
        </button>
      </div>

      <div class="bg-light text-center py-2 border-bottom border-secondary border-opacity-10">
        <small class="text-muted font-serif fst-italic">Chúng tôi thường phản hồi ngay lập tức</small>
      </div>

      <div class="chatbot-body flex-grow-1 p-3 overflow-y-auto" ref="chatBody" style="background-color: #fcfaf8;">
        
        <div v-for="msg in messages" :key="msg.id" class="message-wrapper mb-3 d-flex" :class="msg.type === 'user' ? 'justify-content-end' : 'justify-content-start'">
          
          <div v-if="msg.type === 'bot'" class="flex-shrink-0 me-2 mt-auto">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=150&auto=format&fit=crop" class="rounded-circle object-fit-cover" style="width: 28px; height: 28px;">
          </div>

          <div 
            class="message-bubble p-3 shadow-sm"
            :class="msg.type === 'user' ? 'bg-primary-luxury text-white rounded-user' : 'bg-white text-dark border border-secondary border-opacity-10 rounded-bot'"
            style="max-width: 85%;"
          >
            <p class="mb-0" :class="msg.type === 'bot' ? 'font-serif' : 'font-luxury'" style="font-size: 0.95rem; line-height: 1.5;" v-html="msg.text"></p>
            <div class="text-end mt-1">
              <small :class="msg.type === 'user' ? 'text-white opacity-75' : 'text-muted'" style="font-size: 0.65rem;">{{ msg.time }}</small>
            </div>
          </div>
        </div>

        <div v-if="isTyping" class="message-wrapper mb-3 d-flex justify-content-start">
          <div class="flex-shrink-0 me-2 mt-auto">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=150&auto=format&fit=crop" class="rounded-circle object-fit-cover" style="width: 28px; height: 28px;">
          </div>
          <div class="message-bubble bg-white p-3 shadow-sm rounded-bot border border-secondary border-opacity-10 d-flex align-items-center gap-1">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
          </div>
        </div>

        <div v-if="showQuickReplies" class="quick-replies d-flex flex-wrap gap-2 mt-4 fade-in-up">
          <button 
            v-for="(reply, index) in quickReplies" 
            :key="index"
            @click="sendQuickReply(reply)"
            class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1 font-luxury"
            style="font-size: 0.8rem;"
          >
            {{ reply }}
          </button>
        </div>

      </div>

      <div class="chatbot-footer bg-white p-3 border-top border-secondary border-opacity-10">
        <form @submit.prevent="sendMessage" class="d-flex align-items-center gap-2 bg-light rounded-pill px-3 py-1 border border-secondary border-opacity-25 focus-ring-gold">
          <input 
            type="text" 
            v-model="inputText" 
            class="form-control border-0 bg-transparent shadow-none font-luxury" 
            placeholder="Nhập yêu cầu của quý khách..."
            style="font-size: 0.95rem;"
          >
          <button type="submit" class="btn btn-link text-primary-luxury p-1 text-decoration-none shadow-none" :disabled="!inputText.trim()">
            <i class="bi bi-send-fill fs-5" :class="inputText.trim() ? 'text-primary-luxury' : 'text-muted opacity-50'"></i>
          </button>
        </form>
        <div class="text-center mt-2">
          <small class="text-muted tracking-widest text-uppercase" style="font-size: 0.6rem;">Được bảo mật bởi SORA</small>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const isOpen = ref(false);
const inputText = ref('');
const isTyping = ref(false);
const chatBody = ref(null);
const showQuickReplies = ref(true);

// Mock data: Tin nhắn ban đầu
const messages = ref([
  { 
    id: 1, 
    type: 'bot', 
    text: 'Kính chào Quý khách! SORA rất hân hạnh được đón tiếp. <br>Tôi là trợ lý kim hoàn cá nhân của Quý khách. Tôi có thể hỗ trợ gì cho Quý khách hôm nay?', 
    time: 'Vừa xong' 
  }
]);

// Danh sách câu hỏi gợi ý
const quickReplies = ref([
  'Tư vấn quà tặng', 
  'Hướng dẫn đo size nhẫn', 
  'Chính sách thu đổi', 
  'Đang có bộ sưu tập nào mới?'
]);

const toggleChat = () => {
  isOpen.value = !isOpen.value;
};

// Hàm cuộn xuống tin nhắn mới nhất
const scrollToBottom = async () => {
  await nextTick();
  if (chatBody.value) {
    chatBody.value.scrollTop = chatBody.value.scrollHeight;
  }
};

// Khách ấn nút Gợi ý
const sendQuickReply = (text) => {
  inputText.value = text;
  showQuickReplies.value = false; // Ẩn nút gợi ý sau khi bấm
  sendMessage();
};

// Hàm gửi tin nhắn
const sendMessage = () => {
  if (!inputText.value.trim()) return;

  const userMessage = inputText.value;
  
  // 1. Thêm tin nhắn của User vào mảng
  messages.value.push({
    id: Date.now(),
    type: 'user',
    text: userMessage,
    time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
  });

  inputText.value = '';
  scrollToBottom();

  // 2. Hiển thị trạng thái Bot đang gõ chữ
  isTyping.value = true;
  scrollToBottom();

  // 3. GIẢ LẬP GỌI API BACKEND LẤY CÂU TRẢ LỜI (Sau này bạn ghép Laravel/SQL vào đây)
  setTimeout(() => {
    isTyping.value = false;
    
    // Đây là chỗ bạn sẽ nhận Response từ Backend. Tạm thời mình hardcode theo từ khóa
    let botReply = 'Cảm ơn Quý khách. Chuyên viên của chúng tôi sẽ tiếp nhận yêu cầu và tư vấn chi tiết ngay lập tức.';
    
    const lowerMsg = userMessage.toLowerCase();
    if (lowerMsg.includes('quà')) {
      botReply = 'SORA có các tuyệt tác trang sức rất tuyệt vời để làm quà tặng. Quý khách muốn tìm quà cho phu nhân, người yêu hay người thân ạ?';
    } else if (lowerMsg.includes('size') || lowerMsg.includes('cỡ')) {
      botReply = 'Để đo size chuẩn nhất, Quý khách vui lòng dùng một sợi chỉ quấn quanh ngón tay, sau đó đo chiều dài đoạn chỉ (mm) và báo lại cho tôi nhé.';
    }

    messages.value.push({
      id: Date.now() + 1,
      type: 'bot',
      text: botReply,
      time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
    });

    scrollToBottom();
    
    // Hiện lại nút gợi ý sau 3s (Tùy chọn)
    setTimeout(() => {
       showQuickReplies.value = true;
       scrollToBottom();
    }, 3000);

  }, 1500); // Giả lập Bot "suy nghĩ" 1.5 giây
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap');

.font-luxury { font-family: 'Montserrat', sans-serif; }
.font-serif { font-family: 'Playfair Display', serif; }
.tracking-widest { letter-spacing: 0.1em; }

.bg-primary-luxury { background-color: #9f273b !important; }
.text-primary-luxury { color: #9f273b !important; }
.bg-accent-red { background-color: #cc1e2e !important; }
.text-gold { color: #e7ce7d !important; }
.border-gold { border-color: #e7ce7d !important; }

/* Buttons */
.btn-outline-gold { background-color: transparent; color: #9f273b; border: 1px solid #e7ce7d; transition: all 0.3s; }
.btn-outline-gold:hover { background-color: #e7ce7d; color: #111; }

.z-index-max { z-index: 9999; }
.transition-all { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
.transition-transform { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

/* Nút FAB */
.chatbot-fab {
  bottom: 30px;
  right: 30px;
  width: 65px;
  height: 65px;
  background-color: #111;
  border: 2px solid #e7ce7d;
  cursor: pointer;
}
.chatbot-fab:hover { transform: scale(1.08); background-color: #9f273b; }
.scale-0 { transform: scale(0); pointer-events: none; opacity: 0; }
.scale-100 { transform: scale(1); opacity: 1; }

/* Khung cửa sổ Chat */
.chatbot-window {
  bottom: 30px;
  right: 30px;
  width: 360px;
  height: 600px;
  max-height: 85vh;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.1);
  transform-origin: bottom right;
}
.chatbot-open { transform: scale(1); opacity: 1; pointer-events: auto; }
.chatbot-closed { transform: scale(0.5); opacity: 0; pointer-events: none; }

/* Bong bóng Chat */
.rounded-bot { border-radius: 4px 16px 16px 16px; }
.rounded-user { border-radius: 16px 4px 16px 16px; }

/* Scrollbar Custom mỏng nhẹ */
.overflow-y-auto::-webkit-scrollbar { width: 5px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background-color: #ddd; border-radius: 10px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background-color: #bbb; }

/* Input Focus Ring */
.focus-ring-gold:focus-within { border-color: #e7ce7d !important; box-shadow: 0 0 0 0.2rem rgba(231, 206, 125, 0.25); }

/* Hiệu ứng gõ chữ (Typing Dots) */
.typing-dot {
  width: 6px;
  height: 6px;
  background-color: #999;
  border-radius: 50%;
  animation: typing 1.4s infinite ease-in-out both;
}
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
@keyframes typing {
  0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
  40% { transform: scale(1); opacity: 1; }
}

/* Chấm đỏ nhấp nháy cho Nút FAB */
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: .5; }
}

/* Animations */
.fade-in-up { animation: fadeInUp 0.5s ease; }
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Mobile Responsive */
@media (max-width: 576px) {
  .chatbot-window {
    width: 100%;
    height: 100%;
    bottom: 0;
    right: 0;
    max-height: 100vh;
    border-radius: 0;
  }
  .chatbot-fab { bottom: 20px; right: 20px; }
}
</style>