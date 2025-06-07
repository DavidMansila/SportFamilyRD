<template>
  <div class="chat-container">
    <div class="chat-header">
      <button @click="closeChat" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </button>
      <div class="user-info">
        <img :src="otherUser.foto" :alt="otherUser.name" class="avatar">
        <div>
          <h4>{{ otherUser.name }}</h4>
          <div class="status-indicator">
            <span :class="['status-dot', isOnline ? 'online' : 'offline']"></span>
            <span class="status-text">{{ isOnline ? 'En línea' : 'Desconectado' }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="messages" ref="messagesContainer" v-if="!loadingMessages">
      <div v-for="(message, index) in messages" :key="message.id"
        :class="['message', message.sender_id === user.id ? 'sent' : 'received']">
        <p>{{ message.message }}</p>
        <span class="time">{{ formatTime(message.created_at) }}</span>

        <!-- Indicador de estado de lectura -->
        <div v-if="message.sender_id === user.id" class="message-status">
          <span v-if="message.read" class="read-indicator">✓✓</span>
          <span v-else class="unread-indicator">✓</span>
        </div>
      </div>

      <!-- Indicador de mensajes nuevos -->
      <div v-if="newMessagesIndicator" class="new-messages-indicator">
        Nuevos mensajes
      </div>
    </div>

    <div v-else class="loading-messages">
      <div class="spinner"></div>
      <p>Cargando mensajes...</p>
    </div>

    <div class="message-input">
      <input v-model="newMessage" @keyup.enter="sendMessage" :disabled="sendingMessage"
        placeholder="Escribe un mensaje...">
      <button @click="sendMessage" :disabled="!newMessage.trim() || sendingMessage">
        <svg v-if="!sendingMessage" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M22 2L11 13M22 2L15 22L11 13M11 13L2 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
        <div v-else class="sending-spinner"></div>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    activeChat: Object,
    user: Object
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      otherUser: null,
      isOnline: false,
      loadingMessages: true,
      sendingMessage: false,
      echoChannel: null,
      presenceChannel: null,
      newMessagesIndicator: false,
      unreadMessages: 0
    }
  },
  computed: {
    chatId() {
      return this.activeChat?.id;
    },
    recipientId() {
      return this.user.id === this.activeChat.user_id
        ? this.activeChat.trainer_id
        : this.activeChat.user_id;
    }
  },
  watch: {
    chatId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.identifyOtherUser();
          this.fetchMessages();
          this.setupChannels();
        }
      }
    }
  },
  methods: {
    async fetchMessages() {
      this.loadingMessages = true;
      try {
        const response = await axios.get(`/chats/${this.chatId}`);
        this.messages = response.data.messages;
        this.$nextTick(() => {
          this.scrollToBottom();
          this.markMessagesAsRead();
        });
      } catch (error) {
        console.error('Error fetching messages:', error);
        this.$toast.error('Error al cargar mensajes');
      } finally {
        this.loadingMessages = false;
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || this.sendingMessage) return;

      this.sendingMessage = true;
      try {
        const messageContent = this.newMessage.trim();
        this.newMessage = '';

        // Optimistic UI: Mostrar el mensaje inmediatamente
        const tempMessage = {
          id: Date.now(), // ID temporal
          chat_id: this.chatId,
          sender_id: this.user.id,
          message: messageContent,
          created_at: new Date().toISOString(),
          read: false
        };

        this.messages.push(tempMessage);
        this.$nextTick(this.scrollToBottom);

        // Enviar al servidor
        const response = await axios.post(`/chats/${this.chatId}/messages`, {
          message: messageContent
        });

        // Reemplazar mensaje temporal con el real del servidor
        const index = this.messages.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
          this.messages.splice(index, 1, response.data);
        } else {
          this.messages.push(response.data);
        }
      } catch (error) {
        console.error('Error sending message:', error);
        this.$toast.error('Error al enviar mensaje');
        // Revertir mensaje si falla
        const index = this.messages.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
          this.messages.splice(index, 1);
        }
      } finally {
        this.sendingMessage = false;
      }
    },

    scrollToBottom() {
      const container = this.$refs.messagesContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },

    formatTime(time) {
      return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    identifyOtherUser() {
      this.otherUser = this.user.id === this.activeChat.user_id
        ? this.activeChat.trainer
        : this.activeChat.user;
    },

    setupChannels() {
      // Limpiar canales anteriores
      if (this.echoChannel) {
        window.Echo.leave(`chat.${this.chatId}`);
      }
      if (this.presenceChannel) {
        window.Echo.leave(`presence-chat.${this.chatId}`);
      }

      // Canal para mensajes
      this.echoChannel = window.Echo.private(`chat.${this.chatId}`)
        .listen('NewMessage', (data) => {
          const newMessage = data.message;

          // Solo agregar si no es nuestro propio mensaje (ya está en optimistic UI)
          if (newMessage.sender_id !== this.user.id) {
            this.messages.push(newMessage);
            this.$nextTick(this.scrollToBottom);
            this.markMessagesAsRead();
          }
        })
        .listen('MessageRead', (data) => {
          // Actualizar estado de mensajes leídos
          this.messages.forEach(msg => {
            if (msg.sender_id === this.user.id && !msg.read) {
              msg.read = true;
            }
          });
        });

      // Presence Channel para estado en línea
      this.presenceChannel = window.Echo.join(`presence-chat.${this.chatId}`)
        .here((users) => {
          this.isOnline = users.some(user => user.id === this.otherUser.id);
        })
        .joining((user) => {
          if (user.id === this.otherUser.id) {
            this.isOnline = true;
          }
        })
        .leaving((user) => {
          if (user.id === this.otherUser.id) {
            this.isOnline = false;
          }
        });
    },

    markMessagesAsRead() {
      const unreadMessages = this.messages.filter(
        msg => msg.sender_id !== this.user.id && !msg.read
      );

      if (unreadMessages.length > 0) {
        // Enviar evento al servidor para marcar como leídos
        axios.post(`/chats/${this.chatId}/read`)
          .then(() => {
            // Actualizar estado local
            unreadMessages.forEach(msg => msg.read = true);
          })
          .catch(error => {
            console.error('Error marking messages as read:', error);
          });
      }
    },

    closeChat() {
      // Limpiar canales antes de cerrar
      if (this.echoChannel) {
        window.Echo.leave(`chat.${this.chatId}`);
      }
      if (this.presenceChannel) {
        window.Echo.leave(`presence-chat.${this.chatId}`);
      }

      this.$emit('close-chat');
    },

    handleNewMessage(data) {
      const newMessage = data.message;
      if (newMessage.sender_id !== this.user.id) {
        this.messages.push(newMessage);
        this.$nextTick(this.scrollToBottom);
      }
    },

    updateReadStatus(data) {
      this.messages.forEach(msg => {
        if (msg.sender_id === this.user.id && !msg.read) {
          msg.read = true;
        }
      });
    },

    updateOnlineStatus(users) {
      this.isOnline = users.some(user => user.id === this.otherUser.id);
    },

    userJoined(user) {
      if (user.id === this.otherUser.id) {
        this.isOnline = true;
      }
    },

    userLeft(user) {
      if (user.id === this.otherUser.id) {
        this.isOnline = false;
      }
    }


  },



  beforeUnmount() {
    if (this.echoChannel) {
      window.Echo.leave(`chat.${this.chatId}`);
    }
    if (this.presenceChannel) {
      window.Echo.leave(`presence-chat.${this.chatId}`);
    }
  }
}
</script>

<style scoped>
/* Estilos mejorados para el chat */
.chat-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #eee;
  background: #f8f9fa;
  position: relative;
  z-index: 10;
}

.back-btn {
  background: none;
  border: none;
  margin-right: 15px;
  cursor: pointer;
  padding: 5px;
  border-radius: 50%;
  transition: background 0.2s;
}

.back-btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.back-btn svg {
  width: 20px;
  height: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  flex: 1;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
  object-fit: cover;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-dot.online {
  background-color: #4ade80;
}

.status-dot.offline {
  background-color: #9ca3af;
}

.status-text {
  font-size: 0.8rem;
  color: #666;
}

.messages {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background: #f5f5f5;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message {
  max-width: 75%;
  padding: 10px 15px;
  border-radius: 18px;
  position: relative;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.sent {
  background: #007bff;
  color: white;
  margin-left: auto;
  border-bottom-right-radius: 4px;
}

.received {
  background: #e9ecef;
  margin-right: auto;
  border-bottom-left-radius: 4px;
}

.time {
  display: block;
  font-size: 0.7rem;
  opacity: 0.7;
  margin-top: 5px;
}

.sent .time {
  color: rgba(255, 255, 255, 0.8);
}

.message-status {
  position: absolute;
  bottom: 4px;
  right: 10px;
  font-size: 0.7rem;
}

.read-indicator {
  color: #4ade80;
}

.unread-indicator {
  color: rgba(255, 255, 255, 0.6);
}

.new-messages-indicator {
  background: #007bff;
  color: white;
  padding: 5px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
  margin: 10px auto;
  width: fit-content;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    opacity: 0.7;
  }

  50% {
    opacity: 1;
  }

  100% {
    opacity: 0.7;
  }
}

.loading-messages {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 15px;
  background: #f5f5f5;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(0, 123, 255, 0.2);
  border-radius: 50%;
  border-top-color: #007bff;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.message-input {
  display: flex;
  padding: 10px;
  border-top: 1px solid #eee;
  background: #fff;
}

.message-input input {
  flex: 1;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 24px;
  margin-right: 10px;
  outline: none;
  transition: border-color 0.2s;
  font-size: 0.9rem;
}

.message-input input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
}

.message-input button {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #007bff;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.message-input button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.message-input button:not(:disabled):hover {
  background: #0069d9;
}

.message-input button svg {
  width: 18px;
  height: 18px;
}

.sending-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}
</style>