<template>
  <div class="live-chat-wrapper z-index-max font-luxury">
    <!-- Nút Mở Live Chat, đặt lệch bên trái nút AI bot) -->
    <button 
      class="live-chat-fab btn rounded-circle shadow-lg d-flex align-items-center justify-content-center position-fixed transition-transform bg-primary"
      :class="isOpen ? 'scale-0' : 'scale-100'"
      @click="toggleChat"
      title="Chat trực tiếp với nhân viên"
    >
      <i class="bi bi-headset text-white fs-4"></i>
      <span class="position-absolute top-0 start-0 translate-middle p-2 bg-danger border border-light rounded-circle">
        <span class="visually-hidden">New messages</span>
      </span>
    </button>

    <!-- Cửa sổ Chat -->
    <div 
      class="live-chat-window position-fixed bg-white shadow-lg overflow-hidden d-flex flex-column transition-all"
      :class="isOpen ? 'chatbot-open' : 'chatbot-closed'"
    >
      <!-- Header -->
      <div class="bg-primary p-3 position-relative d-flex align-items-center justify-content-between text-white">
        <div class="d-flex align-items-center gap-2">
          <div class="position-relative">
            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-headset fs-5"></i>
            </div>
            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="margin-bottom: 2px; margin-right: 2px;"></span>
          </div>
          <div>
            <h6 class="fw-bold mb-0 lh-1">CSKH Trực Tuyến</h6>
            <small class="text-white-50" style="font-size: 0.7rem;">Nhân viên sẽ trả lời ngay</small>
          </div>
        </div>
        <button @click="toggleChat" class="btn btn-link text-white opacity-75 p-0 border-0 shadow-none">
          <i class="bi bi-x-lg fs-5"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="flex-grow-1 p-3 overflow-y-auto" ref="chatBodyRef" style="background-color: #f8f9fa;">
        <div v-for="msg in messages" :key="msg.id" class="d-flex mb-3" :class="msg.type === 'user' ? 'justify-content-end' : 'justify-content-start'">
          <div v-if="msg.type === 'admin'" class="me-2 mt-auto">
            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.7rem;">NV</div>
          </div>
          <div 
            class="p-2 shadow-sm rounded"
            :class="msg.type === 'user' ? 'bg-primary text-white text-end rounded-user' : 'bg-white border text-start rounded-bot'"
            style="max-width: 80%;"
          >
            <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4;" v-text="msg.text"></p>
            <small :class="msg.type === 'user' ? 'text-white-50' : 'text-muted'" style="font-size: 0.65rem;">{{ msg.time }}</small>
          </div>
        </div>
        
        <div v-if="isTyping" class="mb-3 d-flex justify-content-start">
          <div class="me-2 mt-auto">
             <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.7rem;">NV</div>
          </div>
          <div class="bg-white p-2 shadow-sm rounded-bot border d-flex align-items-center gap-1">
             <span class="spinner-grow spinner-grow-sm text-secondary" style="width: 0.5rem; height: 0.5rem;"></span>
             <span class="spinner-grow spinner-grow-sm text-secondary" style="width: 0.5rem; height: 0.5rem;"></span>
             <span class="spinner-grow spinner-grow-sm text-secondary" style="width: 0.5rem; height: 0.5rem;"></span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-white p-2 border-top">
        <form @submit.prevent="sendMessage" class="d-flex align-items-center gap-2">
          <input 
            type="text" 
            v-model="inputText" 
            class="form-control rounded-pill bg-light border-0 px-3 py-2" 
            placeholder="Nhập tin nhắn..."
            :disabled="!isLoggedIn"
          >
          <button type="submit" class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" :disabled="!inputText.trim() || !isLoggedIn">
            <i class="bi bi-send-fill"></i>
          </button>
        </form>
        <div v-if="!isLoggedIn" class="text-center mt-2 text-danger" style="font-size: 0.75rem;">
          Vui lòng đăng nhập để gửi tin nhắn cho CSKH!
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const inputText = ref('');
const isTyping = ref(false);
const chatBodyRef = ref(null);
const messages = ref([]);
const isSocketActive = ref(false);
const userId = ref(null);
const isLoggedIn = ref(false);

const API_URL = 'http://localhost:8000/api/client';
const getToken = () => localStorage.getItem('auth_token') || localStorage.getItem('token');
const axiosConfig = () => ({
  headers: { Authorization: `Bearer ${getToken()}`, Accept: 'application/json' }
});

const checkAuth = () => {
    try {
        const userData = JSON.parse(localStorage.getItem('userData'));
        if (userData && userData.id) {
            userId.value = userData.id;
            isLoggedIn.value = true;
            return true;
        }
        // Fallback for older auth
        const authData = JSON.parse(localStorage.getItem('auth') || '{}');
        if (authData && authData.user) {
            userId.value = authData.user.id;
            isLoggedIn.value = true;
            return true;
        }
    } catch(e) {}
    isLoggedIn.value = false;
    return false;
};

const fetchHistory = async () => {
    if (!checkAuth()) {
        messages.value = [{ id: 'sys1', type: 'admin', text: 'Kính chào quý khách! Vui lòng Đăng Nhập ở góc trên cùng bên phải để chuyên viên có thể hỗ trợ trực tiếp.', time: 'Ngay bây giờ' }];
        return;
    }
    
    try {
        const res = await axios.get(`${API_URL}/messages`, axiosConfig());
        if (res.data.status) {
            messages.value = res.data.data.map(m => ({
                id: m.id,
                type: m.sender_id === userId.value ? 'user' : 'admin',
                text: m.content,
                time: new Date(m.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
            }));

            if (messages.value.length === 0) {
                 messages.value.push({ 
                    id: 'sys2', 
                    type: 'admin', 
                    text: 'Chào bạn! Đây là cổng chat hỗ trợ trực tiếp từ nhân viên thật. Mình có thể giúp gì cho bạn?', 
                    time: 'Vừa xong' 
                });
            }
            scrollToBottom();
        }
    } catch(err) {
        console.error(err);
    }
};

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if(isOpen.value && messages.value.length === 0) {
      fetchHistory();
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatBodyRef.value) {
    chatBodyRef.value.scrollTop = chatBodyRef.value.scrollHeight;
  }
};

const sendMessage = async () => {
  if (!inputText.value.trim() || !isLoggedIn.value) return;

  const userMessage = inputText.value;
  const tempMsg = {
    id: Date.now(),
    type: 'user',
    text: userMessage,
    time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
  };
  messages.value.push(tempMsg);
  inputText.value = '';
  scrollToBottom();

  isTyping.value = true;
  scrollToBottom();

  try {
      await axios.post(`${API_URL}/messages`, {
          receiver_id: 1, 
          content: userMessage
      }, axiosConfig());
  } catch(err) {
      console.error(err);
  } finally {
      isTyping.value = false;
  }
};

const renderedIds = new Set();

onMounted(() => {
    if (checkAuth()) {
        fetchHistory();

        if (window.Echo) {
            isSocketActive.value = true;
            window.Echo.private(`chat.${userId.value}`)
                .listen('.MessageSent', (e) => {
                    const msg = e.message;
                    // Tránh duplicate do broadcast trên 2 kênh
                    if (renderedIds.has(msg.id)) return;
                    renderedIds.add(msg.id);
                    // Chỉ hiển thị tin từ Admin (sender_id = 1)
                    if (msg.sender_id === 1) {
                        messages.value.push({
                            id: msg.id,
                            type: 'admin',
                            text: msg.content,
                            time: new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
                        });
                        scrollToBottom();
                        if(!isOpen.value) toggleChat();
                    }
                });
        }
    }
});

onUnmounted(() => {
    if (window.Echo && userId.value) {
        window.Echo.leaveChannel(`chat.${userId.value}`);
    }
});
</script>

<style scoped>
.z-index-max { z-index: 100000; }
.transition-all { transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); }
.transition-transform { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

.live-chat-fab {
  bottom: 30px;
  right: 110px; /* Cách mép để không đụng vào SORA Chatbot AI (nằm ở right: 30px) */
  width: 60px;
  height: 60px;
  z-index: 100001;
}

.scale-0 { transform: scale(0); opacity: 0; pointer-events: none; }
.scale-100 { transform: scale(1); opacity: 1; pointer-events: auto; }

.live-chat-window {
  bottom: 30px;
  right: 110px;
  width: 340px;
  height: 520px;
  max-height: 85vh;
  border-radius: 14px;
  transform-origin: bottom right;
  z-index: 100001;
}

.chatbot-open { transform: scale(1); opacity: 1; pointer-events: auto; }
.chatbot-closed { transform: scale(0.5); opacity: 0; pointer-events: none; }

.rounded-bot { border-radius: 4px 16px 16px 16px; }
.rounded-user { border-radius: 16px 4px 16px 16px; }

.overflow-y-auto::-webkit-scrollbar { width: 5px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 10px; }
</style>