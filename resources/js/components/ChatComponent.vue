  <template>
    <div class="chat-container">
      <div class="chat-header">
        <button class="back-btn" @click="closeChat">
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
              <span class="status-text">
                {{ isOnline ? 'En línea' : 'Desconectado' }}
              </span>
            </div>
          </div>
        </div>
      </div>


      <div class="messages" ref="messagesContainer" v-if="!loadingMessages">
        <div v-for="(message, index) in messages" :key="message.id"
          :class="['message', isMessageFromMe(message) ? 'sent' : 'received']">
          <p>{{ message.message }}</p>
          <div class="message-footer">
            <span class="time">{{ formatTime(message.created_at) }}</span>
            <div v-if="isMessageFromMe(message)" class="message-status">
              <span v-if="message.read" class="read-indicator">✓✓</span>
              <span v-else class="unread-indicator">✓</span>
            </div>
          </div>
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
            <path d="M22 2L11 13M22 2L15 22L11 13M11 13L2 9" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <div v-else class="sending-spinner"></div>
        </button>
      </div>
    </div>
  </template>

<script>
import axios from 'axios';

export default {
  props: ['activeChat', 'user'],
  data() {
    return {
      messages: [],
      newMessage: '',
      isOnline: false,
      loadingMessages: true,
      sendingMessage: false,
      echoChannel: null,
      presenceChannel: null,
      echoInitialized: false
    }
  },
  computed: {
    chatId() {
      return this.activeChat?.id;
    },

    otherUser() {
      return this.activeChat?.other_participant || null;
    },
    otherUserAvatar() {
      return this.otherUser?.image || '/storage/users/Perfil-Icon.png';
    },
    otherUserName() {
      return this.otherUser?.name || 'Usuario';
    }
  },
  watch: {
    chatId: {
      immediate: true,
      async handler(newVal) {
        if (newVal) {
          await this.initializeChat();
        } else {
          this.leaveChannels();
        }
      }
    }
  },

  methods: {

    getUserImage(user) {
      if (!user) return '/storage/users/Perfil-Icon.png';

      if (user.user?.image) {
        return `/storage/users/${user.user.id}/${user.user.image}`;
      }
      if (user.image) {
        return `/storage/users/${user.id}/${user.image}`;
      }

      return '/storage/users/Perfil-Icon.png';
    },

    async initializeChat() {
      await this.fetchMessages();
      await this.loadEchoLibrary();
      this.setupChannels();
    },

    async loadEchoLibrary() {
      if (window.Echo) return;

      try {
        const token = sessionStorage.getItem('token');
        if (!token) throw new Error('Token not found');

        // Importación dinámica
        const { default: Echo } = await import('laravel-echo');
        const { default: Pusher } = await import('pusher-js');

        window.Pusher = Pusher;
        window.Echo = new Echo({
          broadcaster: 'pusher',
          key: import.meta.env.VITE_PUSHER_APP_KEY,
          cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
          forceTLS: true,
          authEndpoint: '/api/broadcasting/auth',
          auth: {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          }
        });

      } catch (error) {
        console.error('Error inicializando Echo:', error);
      }
    },


    isMessageFromMe(message) {
      if (!message || !this.user) return false;

      const myType = this.user.user_type === 'entrenador' ? 'trainer' : 'user';
      return message.sender_id === this.user.id && message.sender_type === myType;
    },

    async fetchMessages() {
      if (!this.chatId) return;

      this.loadingMessages = true;
      try {
        const response = await axios.get(`/chats/${this.chatId}`);
        this.messages = response.data.messages || [];

        this.$nextTick(() => {
          this.scrollToBottom(true);
        });
      } catch (error) {
        console.error('Error fetching messages:', error);
      } finally {
        this.loadingMessages = false;
      }
    },


    scrollToBottom(force = false) {
      const container = this.$refs.messagesContainer;
      if (!container) return;

      this.$nextTick(() => {
        if (force || container.scrollTop + container.clientHeight >= container.scrollHeight - 100) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },


    async sendMessage() {
      if (!this.newMessage.trim() || this.sendingMessage) return;

      this.sendingMessage = true;
      try {
        const messageContent = this.newMessage.trim();
        this.newMessage = '';

        // Enviar directamente al servidor
        const response = await axios.post(`/chats/${this.chatId}/messages`, {
          message: messageContent,
          user_id: this.user.id,
        });

        // Añadir el mensaje a la lista cuando se recibe la respuesta
        this.messages.push(response.data);
        this.scrollToBottom(true);
      } catch (error) {
        console.error('Error sending message:', error);
      } finally {
        this.sendingMessage = false;
      }
    },


    formatTime(time) {
      if (!time) return '';
      try {
        const dateObj = new Date(time);
        return dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      } catch {
        return '';
      }
    },



    setupChannels() {
      if (!this.chatId || !window.Echo) return;
      this.leaveChannels();

      // 1) Mensajes
      this.echoChannel = window.Echo.private(`chat.${this.chatId}`)
        .listen('.message-sent', this.handleIncomingMessage);

      // 2) Presencia + read receipts
      this.presenceChannel = window.Echo.join(`online.${this.chatId}`)
        .here(users => {
          // Actualiza estado online
          this.updateOnlineStatus(users);

          this.markMessagesAsRead();
        })
        .joining(user => { this.userJoined(user); })
        .leaving(user => { this.userLeft(user); })
        .listen('.message.read', () => {
          this.messages = this.messages.map(msg => ({
            ...msg,
            read: this.isMessageFromMe(msg) ? true : msg.read
          }));
        })
        .error(err => console.error('Error en presencia:', err));
    },


    handleIncomingMessage(data) {
      if (data.sender_id === this.user.id) {
        return;
      }

      const exists = this.messages.some(m => m.id === data.id);
      if (!exists) {
        this.messages.push(data);
        this.scrollToBottom();
        this.markMessagesAsRead();
      }
    },


    leaveChannels() {
      if (this.echoChannel) {
        this.echoChannel.stopListening('.message-sent');
        window.Echo.leave(`chat.${this.chatId}`);
        this.echoChannel = null;
      }
      if (this.presenceChannel) {
        window.Echo.leave(`online.${this.chatId}`);
        this.presenceChannel = null;
      }
    },


    async markMessagesAsRead() {
      if (!this.chatId) return;

      try {
        await axios.post(`/chats/${this.chatId}/read`);

        this.messages.forEach(msg => {
          if (!this.isMessageFromMe(msg)) {
            msg.read = true;
          }
        });

        this.$emit('messages.read');
      } catch (error) {
        console.error('Error marking as read:', error);
      }
    },


    updateOnlineStatus(users) {
      this.isOnline = users.some(u => u.id === this.otherUser.id);
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
    },


    leaveChatChannel() {
      this.leaveChannels();
    },

    closeChat() {
      this.leaveChannels();
      this.$emit('close-chat');
    }

  },
  beforeUnmount() {
    console.log('Component unmounting - leaving channels');
    this.leaveChannels();
  },
  mounted() {
    if (this.chatId) {
      console.log('Initializing chat for ID:', this.chatId);
      this.initializeChat();
    }
  }
}
</script>


<style scoped>
@import '../../scss/Entrenadores/chatcomponent.scss';

/* Estilos mejorados para el pie de mensaje */
.message .message-footer {
  display: flex;
  align-items: center;
  margin-top: 4px;
}

.message.sent .message-footer {
  justify-content: flex-end;
}

.message.received .message-footer {
  justify-content: flex-start;
}

.message-footer {
  display: flex;
  justify-content: flex-end; /* Empuja todo a la derecha */
  align-items: center;
  gap: 4px; /* Espacio entre hora y ticks */
  margin-top: 4px;
}

.message-footer .time {
  font-size: 0.7rem;
  color: #aaa;
  white-space: nowrap;
}

.message-footer .message-status {
  display: flex;
  align-items: center;
  position: static; /* Elimina absolute para que flex lo maneje bien */
}



/* Estilos específicos para alinear tiempo y estado */
.time {
  font-size: 11px;
  color: rgba(0, 0, 0, 0.5);
  /* display: inline-block; */
  vertical-align: middle;
}

.message-status {
  display: inline-flex;
  align-items: center;
  margin-left: 6px;
  vertical-align: middle;
  font-size: 12px;
  line-height: 1;
}

.read-indicator {
  color: #4fc3f7;
  font-weight: bold;
  letter-spacing: -1px;
}

.unread-indicator {
  color: #b0bec5;
  font-weight: bold;
}

.time,
.message-status {
  top: 35px;
  left: 73px;
}
</style>