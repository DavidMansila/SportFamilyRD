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
            <span class="status-text">{{ isOnline ? 'En línea' : 'Desconectado' }}</span>
          </div>
        </div>
      </div>
    </div>


    <div class="messages" ref="messagesContainer" v-if="!loadingMessages">
      <div v-for="(message, index) in messages" :key="message.id"
        :class="['message', isMessageFromMe(message) ? 'sent' : 'received']">
        <p>{{ message.message }}</p>
        <span class="time">{{ formatTime(message.created_at) }}</span>

        <!-- Indicador de estado de lectura -->
        <div v-if="isMessageFromMe(message)" class="message-status">
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
      if (!this.activeChat) return null;

      try {
        const isUser = this.user.user_type === 'user';
        const target = isUser ?
          (this.activeChat.trainer || this.activeChat.trainer_id) :
          (this.activeChat.user || this.activeChat.user_id);

        if (!target) return null;

        return {
          id: target.id || target,
          name: target.user?.name || target.name || (isUser ? 'Entrenador' : 'Usuario'),
          trainer_id: target.trainer_id,
          image: this.getUserImage(target)
        };
      } catch (error) {
        console.error('Error al obtener otherUser:', error);
        return null;
      }
    },

    otherUserAvatar() {
      return this.otherUser?.image || '/storage/users/Perfil-Icon.png';
    },

    otherUserName() {
      return this.otherUser?.name || (this.user.id === this.activeChat?.user_id ? 'Entrenador' : 'Usuario');
    }
  },
  watch: {
    chatId: {
      immediate: true,
      async handler(newVal) {
        if (newVal) {
          await this.initializeChat();
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
    getUserImage(user) {
      if (!user) return '/storage/users/Perfil-Icon.png';

      // Si es un objeto completo
      if (user.user?.image) {
        return `/storage/users/${user.user.id}/${user.user.image}`;
      }
      if (user.image) {
        return `/storage/users/${user.id}/${user.image}`;
      }

      // Si es solo un ID (caso de respaldo)
      return '/storage/users/Perfil-Icon.png';
    },

    async initializeChat() {
      await this.fetchMessages();
      await this.loadEchoLibrary();
      this.setupChannels();
    },

    async loadEchoLibrary() {
      if (window.Echo || this.echoInitialized) return;

      try {
        const token = sessionStorage.getItem('token');
        if (!token) {
          throw new Error('Token no encontrado en sessionStorage');
        }

        // Verificar token
        const userResponse = await axios.get('/api/user', {
          headers: { Authorization: `Bearer ${token}` }
        });
        console.log('Usuario autenticado:', userResponse.data);

        const [EchoModule, PusherModule] = await Promise.all([
          import('laravel-echo'),
          import('pusher-js')
        ]);

        window.Pusher = PusherModule.default;
        window.Echo = new EchoModule.default({
          broadcaster: 'pusher',
          key: import.meta.env.VITE_PUSHER_APP_KEY,
          cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
          forceTLS: true,
          encrypted: true,
          disableStats: true,
          authEndpoint: '/api/broadcasting/auth',
          auth: {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          }
        });

        this.echoInitialized = true;
      } catch (error) {
        console.error('Error inicializando Echo:', error);

        // Intento de refrescar el token
        try {
          const refreshResponse = await axios.post('/api/auth/refresh');
          sessionStorage.setItem('token', refreshResponse.data.token);
          this.loadEchoLibrary(); // Reintentar
        } catch (refreshError) {
          console.error('Error refrescando token:', refreshError);
          this.$emit('auth-error');
        }
      }
    },

    isMessageFromMe(message) {
      if (!message || !this.user) return false;

      if (this.user.user_type === 'user') {
        return message.sender_type === 'user';
      } else if (this.user.user_type === 'entrenador') {
        return message.sender_type === 'trainer';
      }
      return false;
    },

    async fetchMessages() {
      if (!this.chatId) return;

      this.loadingMessages = true;
      try {
        const response = await axios.get(`/chats/${this.chatId}`);
        this.messages = response.data.messages || [];

        this.$nextTick(() => {
          this.scrollToBottom(true);
          this.markMessagesAsRead();
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

        const senderType = this.user.user_type === 'user' ? 'user' : 'trainer';

        // Crear mensaje temporal
        const tempMessage = {
          id: Date.now(),
          chat_id: this.chatId,
          sender_id: this.user.id,
          sender_type: senderType,
          message: messageContent,
          created_at: new Date().toISOString(),
          read: false
        };

        this.messages.push(tempMessage);
        this.scrollToBottom(true);

        // Enviar mensaje real al servidor
        const response = await axios.post(`/chats/${this.chatId}/messages`, {
          message: messageContent,
          user_id: this.user.id,
        });

        // Reemplazar mensaje temporal con el real
        const index = this.messages.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
          this.messages.splice(index, 1, response.data);
        }
      } catch (error) {
        console.error('Error sending message:', error);

        // Eliminar mensaje temporal en caso de error
        const index = this.messages.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
          this.messages.splice(index, 1);
        }
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
      if (!this.chatId || !window.Echo) {
        console.warn('No se pueden configurar canales sin chatId o Echo');
        return;
      }

      this.leaveChannels();

      // Canal para mensajes
      this.echoChannel = window.Echo.private(`chat.${this.chatId}`)
        .listen('.message-sent', (data) => {
          this.handleIncomingMessage(data);
        })
        .error((error) => {
          console.error('Error en canal de mensajes:', error);
        });

      // Canal de presencia
      this.presenceChannel = window.Echo.join(`online.${this.chatId}`)
        .here((users) => {
          this.updateOnlineStatus(users);
        })
        .joining((user) => {
          this.userJoined(user);
        })
        .leaving((user) => {
          this.userLeft(user);
        })
        .error((error) => {
          console.error('Error en canal de presencia:', error);
        });
    },

    handleIncomingMessage(data) {
      if (!data || !data.id) return;

      // Evitar duplicados
      const messageExists = this.messages.some(msg => msg.id === data.id);
      if (!messageExists) {
        this.messages.push(data);
        this.scrollToBottom();

        if (!this.isMessageFromMe(data)) {
          this.markMessagesAsRead();
        }
      }
    },

    leaveChannels() {
      if (this.echoChannel) {
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
        this.$emit('messages-read');
      } catch (error) {
        console.error('Error marcando mensajes como leídos', error);
      }
    },

    updateOnlineStatus(users) {
      if (!this.otherUser || !users) {
        this.isOnline = false;
        return;
      }

      this.isOnline = users.some(user =>
        user.id === this.otherUser.id ||
        user.trainer_id === this.otherUser.id
      );
    },

    userJoined(user) {
      if (!this.otherUser || !user) return;

      if ((user.id && user.id === this.otherUser.id) ||
        (user.trainer_id && user.trainer_id === this.otherUser.id)) {
        this.isOnline = true;
      }
    },

    userLeft(user) {
      if (!this.otherUser || !user) return;

      if ((user.id && user.id === this.otherUser.id) ||
        (user.trainer_id && user.trainer_id === this.otherUser.id)) {
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
    this.leaveChannels();
  },
  mounted() {
    if (this.chatId) {
      this.initializeChat();
    }
  }
}
</script>


<style scoped>
@import '../../scss/Entrenadores/chatcomponent.scss';
</style>