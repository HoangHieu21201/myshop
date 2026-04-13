<template>
  <div class="admin-chat-page">

    <!-- Header -->
    <div class="chat-page-header">
      <div class="d-flex align-items-center gap-3">
        <div class="header-icon">
          <i class="bi bi-chat-dots-fill"></i>
        </div>
        <div>
          <h4 class="mb-0 fw-bold">Hỗ Trợ Khách Hàng</h4>
          <p class="mb-0 text-muted small">
            <span v-if="isSocketActive" class="online-dot me-1"></span>
            <span v-else class="offline-dot me-1"></span>
            {{ isSocketActive ? 'Đang kết nối real-time' : 'Mất kết nối' }}
            &bull; {{ contacts.length }} cuộc hội thoại
          </p>
        </div>
      </div>
    </div>

    <!-- Main Chat Container -->
    <div class="chat-container">

      <!-- LEFT SIDEBAR: Danh sách khách -->
      <div class="contacts-sidebar">

        <div class="contacts-search">
          <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Tìm khách hàng..."
              class="search-input"
            >
          </div>
        </div>

        <div class="contacts-list">
          <div v-if="isLoadingContacts" class="loading-state">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span>Đang tải...</span>
          </div>

          <div v-else-if="filteredContacts.length === 0" class="empty-state">
            <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
            <span class="text-muted small">Chưa có tin nhắn nào</span>
          </div>

          <div
            v-for="user in filteredContacts"
            :key="user.id"
            class="contact-item"
            :class="{ 'contact-active': activeUserId === user.id }"
            @click="selectUser(user)"
          >
            <div class="contact-avatar">
              {{ (user.fullName || user.email || 'U').charAt(0).toUpperCase() }}
            </div>
            <div class="contact-info">
              <div class="contact-name">{{ user.fullName || 'Khách hàng' }}</div>
              <div class="contact-email">{{ user.email }}</div>
            </div>
            <div v-if="unreadMap[user.id]" class="unread-badge">
              {{ unreadMap[user.id] }}
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Khung chat chi tiết -->
      <div class="chat-main">

        <!-- Màn hình chờ chọn user -->
        <div v-if="!activeUserId" class="chat-empty">
          <div class="chat-empty-content">
            <div class="chat-empty-icon">
              <i class="bi bi-chat-heart"></i>
            </div>
            <h5 class="mt-3 fw-bold text-dark">Chọn một cuộc hội thoại</h5>
            <p class="text-muted small">Nhấn vào khách hàng bên trái để bắt đầu trả lời</p>
          </div>
        </div>

        <template v-else>
          <!-- Chat Header -->
          <div class="chat-header">
            <div class="d-flex align-items-center gap-3">
              <div class="chat-avatar-lg">
                {{ (activeUser.fullName || activeUser.email || 'U').charAt(0).toUpperCase() }}
              </div>
              <div>
                <h6 class="mb-0 fw-bold">{{ activeUser.fullName || 'Khách hàng' }}</h6>
                <small class="text-muted">{{ activeUser.email }}</small>
              </div>
            </div>
            <div class="chat-header-actions">
              <span class="online-badge">
                <span class="online-dot me-1"></span>
                Đang hoạt động
              </span>
            </div>
          </div>

          <!-- Messages -->
          <div class="messages-area" ref="chatBodyRef">
            <div v-if="isLoadingMessages" class="loading-state">
              <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>

            <template v-else>
              <!-- Date separator -->
              <div class="date-separator">
                <span>Hôm nay</span>
              </div>

              <div
                v-for="(msg, index) in messages"
                :key="index"
                class="message-row"
                :class="msg.sender_id === 1 ? 'message-sent' : 'message-received'"
              >
                <!-- Avatar (chỉ show khi không phải admin) -->
                <div v-if="msg.sender_id !== 1" class="msg-avatar">
                  {{ (activeUser.fullName || 'K').charAt(0).toUpperCase() }}
                </div>

                <div class="message-group">
                  <div class="message-bubble" :class="msg.sender_id === 1 ? 'bubble-sent' : 'bubble-received'">
                    {{ msg.content }}
                  </div>
                  <div class="message-time">
                    <span v-if="msg.created_at">
                      {{ formatTime(msg.created_at) }}
                    </span>
                    <span v-if="msg.sender_id === 1" class="ms-1">
                      <i class="bi bi-check2-all text-primary" style="font-size: 0.7rem;"></i>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Typing indicator -->
              <div v-if="isSending" class="message-row message-sent">
                <div class="message-group">
                  <div class="bubble-sent message-bubble typing-indicator">
                    <span></span><span></span><span></span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Input Area -->
          <div class="chat-input-area">
            <div class="input-toolbar">
              <button class="toolbar-btn" title="Đính kèm file">
                <i class="bi bi-paperclip"></i>
              </button>
              <button class="toolbar-btn" title="Emoji">
                <i class="bi bi-emoji-smile"></i>
              </button>
            </div>
            <form @submit.prevent="sendMessage" class="input-form">
              <input
                type="text"
                v-model="newMessage"
                class="message-input"
                placeholder="Nhập tin nhắn trả lời khách hàng..."
                :disabled="isSending"
                @keydown.enter.prevent="sendMessage"
              >
              <button
                type="submit"
                class="send-btn"
                :disabled="!newMessage.trim() || isSending"
              >
                <i v-if="!isSending" class="bi bi-send-fill"></i>
                <div v-else class="spinner-border spinner-border-sm" role="status"></div>
              </button>
            </form>
          </div>
        </template>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import axios from 'axios';

const adminId = ref(1); // Admin luôn là ID 1
const contacts = ref([]);
const activeUserId = ref(null);
const activeUser = ref(null);
const messages = ref([]);
const newMessage = ref('');
const searchQuery = ref('');
const isSocketActive = ref(false);
const isLoadingContacts = ref(false);
const isLoadingMessages = ref(false);
const isSending = ref(false);
const chatBodyRef = ref(null);
const unreadMap = ref({});
// Track message IDs đã hiển thị để tránh duplicate
const renderedIds = ref(new Set());

const API_URL = 'http://localhost:8000/api/admin';
const getToken = () => localStorage.getItem('admin_token') || localStorage.getItem('auth_token');
const axiosConfig = () => ({
  headers: { Authorization: `Bearer ${getToken()}`, Accept: 'application/json' }
});

const filteredContacts = computed(() => {
  if (!searchQuery.value.trim()) return contacts.value;
  const q = searchQuery.value.toLowerCase();
  return contacts.value.filter(u =>
    (u.fullName || '').toLowerCase().includes(q) ||
    (u.email || '').toLowerCase().includes(q)
  );
});

const formatTime = (timestamp) => {
  if (!timestamp) return '';
  return new Date(timestamp).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatBodyRef.value) {
    chatBodyRef.value.scrollTop = chatBodyRef.value.scrollHeight;
  }
};

const fetchContacts = async () => {
  isLoadingContacts.value = true;
  try {
    const res = await axios.get(`${API_URL}/messages/conversations`, axiosConfig());
    if (res.data.status) {
      contacts.value = res.data.data;
    }
  } catch (err) {
    console.error('Lỗi lấy danh sách:', err);
  } finally {
    isLoadingContacts.value = false;
  }
};

const selectUser = async (user) => {
  activeUserId.value = user.id;
  activeUser.value = user;
  messages.value = [];
  renderedIds.value = new Set();
  // Xóa unread badge khi mở chat
  if (unreadMap.value[user.id]) {
    delete unreadMap.value[user.id];
  }
  isLoadingMessages.value = true;
  try {
    const res = await axios.get(`${API_URL}/messages?partner_id=${user.id}`, axiosConfig());
    if (res.data.status) {
      messages.value = res.data.data;
      // Đánh dấu tất cả đã render
      messages.value.forEach(m => renderedIds.value.add(m.id));
      scrollToBottom();
    }
  } catch (err) {
    console.error('Lỗi lấy tin nhắn:', err);
  } finally {
    isLoadingMessages.value = false;
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return;
  const text = newMessage.value.trim();
  newMessage.value = '';
  isSending.value = true;

  // Optimistic update - thêm tin nhắn tạm thời với ID tạm thời
  const tempId = 'temp_' + Date.now();
  const optimisticMsg = {
    id: tempId,
    sender_id: 1,
    receiver_id: activeUserId.value,
    content: text,
    created_at: new Date().toISOString()
  };
  messages.value.push(optimisticMsg);
  renderedIds.value.add(tempId);
  scrollToBottom();

  try {
    const res = await axios.post(`${API_URL}/messages`, {
      receiver_id: activeUserId.value,
      content: text
    }, axiosConfig());

    // Thay thế optimistic message bằng message thật từ server
    if (res.data.status) {
      const realMsg = res.data.data;
      
      // Nếu tin nhắn này đã được thêm bởi WebSocket trước đó
      if (renderedIds.value.has(realMsg.id)) {
        messages.value = messages.value.filter(m => m.id !== tempId);
        renderedIds.value.delete(tempId);
      } else {
        const idx = messages.value.findIndex(m => m.id === tempId);
        if (idx !== -1) {
          renderedIds.value.delete(tempId);
          messages.value[idx] = realMsg;
          renderedIds.value.add(realMsg.id);
        }
      }
    }
  } catch (err) {
    console.error('Lỗi gửi tin', err);
    // Xóa tin nhắn lỗi
    messages.value = messages.value.filter(m => m.id !== tempId);
    renderedIds.value.delete(tempId);
  } finally {
    isSending.value = false;
  }
};

onMounted(() => {
  fetchContacts();

  if (window.Echo) {
    isSocketActive.value = true;

    // Admin lắng nghe kênh riêng của mình (chat.1)
    window.Echo.private(`chat.1`)
      .listen('.MessageSent', (e) => {
        const msg = e.message;

        // Tránh duplicate
        if (renderedIds.value.has(msg.id)) return;

        // Nếu đang xem conversation của user này
        if (activeUserId.value && (msg.sender_id === activeUserId.value || msg.receiver_id === activeUserId.value)) {
          // Nếu là admin gửi (từ tab khác chẳng hạn), kiểm tra xem có tin tạm tương ứng không
          if (msg.sender_id === 1) {
            const tempIdx = messages.value.findIndex(m => String(m.id).startsWith('temp_') && m.content === msg.content);
            if (tempIdx !== -1) {
              renderedIds.value.delete(messages.value[tempIdx].id);
              messages.value[tempIdx] = msg;
              renderedIds.value.add(msg.id);
              return;
            }
          }

          renderedIds.value.add(msg.id);
          messages.value.push(msg);
          scrollToBottom();
        } else if (msg.sender_id !== 1) {
          // Tin từ user khác → Tăng unread badge
          unreadMap.value[msg.sender_id] = (unreadMap.value[msg.sender_id] || 0) + 1;
          // Cập nhật danh sách contacts nếu user mới
          if (!contacts.value.find(c => c.id === msg.sender_id)) {
            fetchContacts();
          }
        }
      });
  }
});

onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leaveChannel(`chat.1`);
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* { font-family: 'Inter', sans-serif; }

.admin-chat-page {
  min-height: 100vh;
  background: var(--bs-body-bg, #f0f2f5);
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ===== PAGE HEADER ===== */
.chat-page-header {
  background: linear-gradient(135deg, #1e3a5f 0%, #2d6a4f 100%);
  border-radius: 16px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: white;
  box-shadow: 0 4px 20px rgba(30, 58, 95, 0.3);
}

.header-icon {
  width: 48px;
  height: 48px;
  background: rgba(255,255,255,0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.chat-page-header h4 { color: white; }
.chat-page-header .text-muted { color: rgba(255,255,255,0.7) !important; }

/* ===== MAIN CONTAINER ===== */
.chat-container {
  display: flex;
  background: var(--bs-body-bg, white);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  height: calc(100vh - 160px);
  min-height: 500px;
}

/* ===== CONTACTS SIDEBAR ===== */
.contacts-sidebar {
  width: 300px;
  flex-shrink: 0;
  border-right: 1px solid var(--bs-border-color, #f0f0f0);
  display: flex;
  flex-direction: column;
  background: var(--bs-tertiary-bg, #fafbfc);
}

.contacts-search {
  padding: 16px;
  border-bottom: 1px solid var(--bs-border-color, #f0f0f0);
}

.search-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.85rem;
}

.search-input {
  width: 100%;
  border: 1px solid var(--bs-border-color, #e5e7eb);
  border-radius: 10px;
  padding: 9px 12px 9px 34px;
  font-size: 0.85rem;
  background: var(--bs-body-bg, white);
  color: var(--bs-body-color, #1f2937);
  transition: all 0.2s;
  outline: none;
}

.search-input:focus {
  border-color: #1e3a5f;
  box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
}

.contacts-list {
  flex: 1;
  overflow-y: auto;
}

.contacts-list::-webkit-scrollbar { width: 4px; }
.contacts-list::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 32px;
  color: #6b7280;
  font-size: 0.85rem;
}

.empty-state {
  text-align: center;
  padding: 48px 16px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  cursor: pointer;
  transition: all 0.15s;
  border-bottom: 1px solid var(--bs-border-color, #f5f5f5);
  position: relative;
}

.contact-item:hover { background: var(--bs-secondary-bg, #f3f4f6); }

.contact-active {
  background: var(--bs-primary-bg-subtle, #e8f0fe) !important;
  border-left: 3px solid var(--bs-primary, #1e3a5f);
}

.contact-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1e3a5f, #2d6a4f);
  color: white;
  font-weight: 700;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.contact-active .contact-avatar {
  background: linear-gradient(135deg, #1e3a5f, #1a56db);
}

.contact-info { flex: 1; min-width: 0; }

.contact-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--bs-body-color, #111827);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contact-active .contact-name { color: var(--bs-primary, #1e3a5f); }

.contact-email {
  font-size: 0.75rem;
  color: #9ca3af;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unread-badge {
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* ===== MAIN CHAT AREA ===== */
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-empty-content { text-align: center; }

.chat-empty-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #1e3a5f15, #2d6a4f15);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: var(--bs-primary, #1e3a5f);
  margin: 0 auto;
}

/* ===== CHAT HEADER ===== */
.chat-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--bs-border-color, #f0f0f0);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--bs-body-bg, white);
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.chat-avatar-lg {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1e3a5f, #1a56db);
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.online-badge {
  background: #dcfce7;
  color: #166534;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

/* ===== MESSAGES ===== */
.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: var(--bs-tertiary-bg, #f8f9fa);
}

.messages-area::-webkit-scrollbar { width: 5px; }
.messages-area::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

.date-separator {
  text-align: center;
  margin: 8px 0 16px;
}

.date-separator span {
  background: var(--bs-secondary-bg, #e5e7eb);
  color: var(--bs-secondary-color, #6b7280);
  font-size: 0.72rem;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 500;
}

.message-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  margin-bottom: 2px;
}

.message-sent {
  flex-direction: row-reverse;
}

.msg-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6b7280, #374151);
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.message-group {
  display: flex;
  flex-direction: column;
  max-width: 65%;
}

.message-sent .message-group { align-items: flex-end; }
.message-received .message-group { align-items: flex-start; }

.message-bubble {
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 0.9rem;
  line-height: 1.5;
  word-break: break-word;
}

.bubble-sent {
  background: linear-gradient(135deg, #1e3a5f, #1a56db);
  color: white;
  border-bottom-right-radius: 4px;
}

.bubble-received {
  background: var(--bs-body-bg, white);
  color: var(--bs-body-color, #1f2937);
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  border: 1px solid var(--bs-border-color, #f0f0f0);
}

.message-time {
  font-size: 0.68rem;
  color: #9ca3af;
  margin-top: 3px;
  padding: 0 4px;
  display: flex;
  align-items: center;
}

/* Typing indicator */
.typing-indicator {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 12px 16px;
}

.typing-indicator span {
  width: 7px;
  height: 7px;
  background: rgba(255,255,255,0.7);
  border-radius: 50%;
  animation: typing-bounce 1.4s infinite ease-in-out both;
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing-bounce {
  0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}

/* ===== INPUT AREA ===== */
.chat-input-area {
  border-top: 1px solid var(--bs-border-color, #f0f0f0);
  background: var(--bs-body-bg, white);
  padding: 12px 16px;
}

.input-toolbar {
  display: flex;
  gap: 4px;
  margin-bottom: 8px;
}

.toolbar-btn {
  background: none;
  border: none;
  color: #6b7280;
  padding: 6px 8px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.1rem;
  transition: all 0.15s;
}

.toolbar-btn:hover {
  background: var(--bs-secondary-bg, #f3f4f6);
  color: var(--bs-primary, #1e3a5f);
}

.input-form {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bs-tertiary-bg, #f8f9fa);
  border: 1.5px solid var(--bs-border-color, #e5e7eb);
  border-radius: 14px;
  padding: 6px 6px 6px 16px;
  transition: all 0.2s;
}

.input-form:focus-within {
  border-color: var(--bs-primary, #1e3a5f);
  box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
  background: var(--bs-body-bg, white);
}

.message-input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.9rem;
  color: var(--bs-body-color, #1f2937);
  padding: 6px 0;
}

.message-input::placeholder { color: #9ca3af; }

.send-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #1e3a5f, #1a56db);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(30,58,95,0.4);
}

.send-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ===== ONLINE / OFFLINE DOTS ===== */
.online-dot, .offline-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.online-dot { background: #22c55e; }
.offline-dot { background: #ef4444; }
</style>