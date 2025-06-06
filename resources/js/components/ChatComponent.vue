<template>
  <div class="chat-container" v-if="activeChat">
    <div class="chat-header">
      <button class="back-btn" @click="closeChat">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </button>
      <h4>{{ contactName }}</h4>
    </div>

    <div class="messages-list" ref="messagesContainer">
      <div v-for="msg in messages" :key="msg.id" :class="['message-item', { 'sent': msg.sender_id === user.id }]">
        <div class="message-content">{{ msg.message }}</div>
        <span class="message-time">{{ formatTime(msg.created_at) }}</span>
      </div>
    </div>

    <div class="message-input-container">
      <input type="text" v-model="newMessage" @keyup.enter="sendMessage" placeholder="Escribe un mensaje..." />
      <button @click="sendMessage">Enviar</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    activeChat: Object,
    user: Object
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      contactName: '',
      pollingInterval: null,
    };
  },
  watch: {
    activeChat: {
      immediate: true,
      handler(newChat) {
        if (this.pollingInterval) {
          clearInterval(this.pollingInterval);
          this.pollingInterval = null;
        }

        if (newChat) {
          this.contactName = this.user.role === 'user'
            ? newChat.trainer?.name || 'Entrenador'
            : newChat.user?.name || 'Usuario';

          this.loadMessages();

          // Iniciar polling para refrescar mensajes cada 5 segundos
          this.pollingInterval = setInterval(() => {
            this.loadMessages();
          }, 5000);
        } else {
          this.messages = [];
        }
      }
    }
  },
  methods: {
    async loadMessages() {
      try {
        const response = await axios.get(`/chats/${this.activeChat.id}/messages`);
        this.messages = response.data;
        this.scrollToBottom();
      } catch (error) {
        console.error('Error loading messages:', error);
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim()) return;

      try {
        await axios.post(`/chats/${this.activeChat.id}/messages`, {
          message: this.newMessage
        });
        this.newMessage = '';
        // Recarga los mensajes para mostrar el nuevo
        this.loadMessages();
      } catch (error) {
        console.error('Error sending message:', error);
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },

    formatTime(dateString) {
      const date = new Date(dateString);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    closeChat() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }
      this.$emit('close-chat');
    }
  },
  beforeUnmount() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
      this.pollingInterval = null;
    }
  }
};
</script>

<style scoped>
.chat-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  background: #f0f0f0;
  border-bottom: 1px solid #ddd;
}

.back-btn {
  background: none;
  border: none;
  cursor: pointer;
  margin-right: 1rem;
}

.messages-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #fff;
}

.message-item {
  margin-bottom: 1rem;
  max-width: 70%;
  word-wrap: break-word;
}

.message-item.sent {
  align-self: flex-end;
  text-align: right;
}

.message-content {
  background: #e0e0e0;
  padding: 0.5rem 1rem;
  border-radius: 1rem;
}

.message-item.sent .message-content {
  background: #d1f0d1;
}

.message-time {
  font-size: 0.75rem;
  color: #666;
  margin-top: 0.25rem;
  display: block;
}

.message-input-container {
  display: flex;
  padding: 0.5rem;
  border-top: 1px solid #ddd;
}

.message-input-container input {
  flex: 1;
  padding: 0.5rem;
}

.message-input-container button {
  margin-left: 0.5rem;
}
</style>
