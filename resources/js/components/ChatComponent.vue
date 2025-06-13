<template>
  <div class="chat-container">
    <div class="chat-header">
      <button class="back-btn" @click="$emit('close-chat')">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </button>
      <div class="user-info">
        <img :src="otherUserAvatar" class="avatar">
        <div class="user-details">
          <h4>{{ otherUserName }}</h4>
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
  props: ['activeChat', 'user'],
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
    },
    otherUser() {
      if (!this.activeChat) return null;

      // Determinar quién es el otro usuario en el chat
      if (this.user.id === this.activeChat.user_id) {
        return this.activeChat.trainer;
      } else {
        return this.activeChat.user;
      }
    },
    otherUserAvatar() {
      return this.otherUser?.image || '/storage/users/Perfil-Icon.png';
    },
    otherUserName() {
      return this.otherUser?.name || (this.user.id === this.activeChat.user_id ? 'Entrenador' : 'Usuario');
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

    },
    messages: {
      deep: true,
      handler() {
        this.$nextTick(this.scrollToBottom);
      }
    }
  },
  methods: {
    async fetchMessages() {
      this.loadingMessages = true;
      try {
        const response = await axios.get(`/chats/${this.chatId}`);
        this.messages = response.data.messages;

        // Identificar el otro usuario
        this.identifyOtherUser();

        this.$nextTick(() => {
          setTimeout(() => {
            this.scrollToBottom(true);
          }, 100);
          this.markMessagesAsRead();
        });
      } catch (error) {
        console.error('Error fetching messages:', error);
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

    scrollToBottom(instant = false) {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (container) {
          container.scrollTo({
            top: container.scrollHeight,
            behavior: instant ? 'auto' : 'smooth'
          });
        }
      });
    },

    formatTime(time) {
      return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    identifyOtherUser() {
      if (!this.activeChat) return;

      this.otherUser = this.user.id === this.activeChat.user_id
        ? this.activeChat.trainer
        : this.activeChat.user;
    },

    setupChannels() {

      if (process.env.NODE_ENV !== 'production') {
        console.log("Broadcasting deshabilitado en desarrollo");
        return;
      }

      // Canal para mensajes
      this.echoChannel = window.Echo.private(`chat.${this.chatId}`)
        .listen('NewMessage', (data) => {
          this.handleNewMessage(data.message);
        })
        .listen('MessageRead', (data) => {
          this.updateReadStatus(data);
        });

      // Presence Channel para estado en línea
      this.presenceChannel = window.Echo.join(`presence-chat.${this.chatId}`)
        .here((users) => {
          this.updateOnlineStatus(users);
        })
        .joining((user) => {
          this.userJoined(user);
        })
        .leaving((user) => {
          this.userLeft(user);
        });
    },

    async markMessagesAsRead() {
      try {
        await axios.post(`/chats/${this.chatId}/read`);
        this.$emit('messages-read');
      } catch (error) {
        console.error('Error marcando mensajes como leídos', error);
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

    handleNewMessage(newMessage) {
      // Solo agregar si no existe
      const exists = this.messages.some(m => m.id === newMessage.id);
      if (!exists) {
        this.messages.push(newMessage);
        this.$nextTick(() => {
          this.scrollToBottom();
          if (newMessage.sender_id !== this.user.id) {
            this.markMessagesAsRead();
          }
        });
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
@import '../../scss/Entrenadores/chatcomponent.scss';
</style>