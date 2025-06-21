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
      otherUser: '',
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

      if (this.user.user_type === 'user') {
        return {
          id: this.activeChat.trainer.id,
          name: this.activeChat.trainer.user.name,
          image: this.activeChat.trainer.user.image
            ? `/storage/users/${this.activeChat.trainer.user.id}/${this.activeChat.trainer.user.image}`
            : 'public/storage/users/Perfil-Icon.png'
        };
      } else {
        return {
          id: this.activeChat.user.id,
          name: this.activeChat.user.name,
          image: this.activeChat.user.image
            ? `/storage/users/${this.activeChat.user.id}/${this.activeChat.user.image}`
            : 'public/storage/users/Perfil-Icon.png'
        };
      }
    },

    otherUserAvatar() {
      if (!this.otherUser?.image) {
        return '/storage/users/Perfil-Icon.png';
      }

      // Verificar si ya tiene la ruta completa
      if (this.otherUser.image.startsWith('http') || this.otherUser.image.startsWith('/')) {
        return this.otherUser.image;
      }

      return `/storage/users/${this.otherUser.id}/${this.otherUser.image}`;
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

    isMessageFromMe(message) {
      if (this.user.user_type === 'user') {
        return message.sender_type === 'user';
      } else if (this.user.user_type === 'entrenador') {
        return message.sender_type === 'trainer';
      }
      return false;
    },

    async fetchMessages() {
      this.loadingMessages = true;
      try {
        const response = await axios.get(`/chats/${this.chatId}`);
        // Acceder a response.data.messages en lugar de response.data
        this.messages = response.data.messages;

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

        const senderType = this.user.user_type === 'user' ? 'user' : 'trainer';

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
        this.$nextTick(this.scrollToBottom);

        const response = await axios.post(`/chats/${this.chatId}/messages`, {
          message: messageContent,
        });

        const index = this.messages.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
          this.messages.splice(index, 1, response.data);
        } else {
          this.messages.push(response.data);
        }
      } catch (error) {
        console.error('Error sending message:', error);
        this.$toast.error('Error al enviar mensaje');
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
      if (!time) return '';

      const dateObj = time instanceof Date ? time : new Date(time);

      return dateObj.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    identifyOtherUser() {
      if (!this.activeChat) return;

      if (this.user.id === this.activeChat.user_id) {
        this.otherUser = {
          id: this.activeChat.trainer.id,
          name: this.activeChat.trainer.user.name,
          image: this.activeChat.trainer.user.image
            ? `/storage/users/${this.activeChat.trainer.user.id}/${this.activeChat.trainer.user.image}`
            : '/storage/users/Perfil-Icon.png'
        };
      } else if (this.user.id === this.activeChat.trainer_id) {
        this.otherUser = {
          id: this.activeChat.user.id,
          name: this.activeChat.user.name,
          image: this.activeChat.user.image
            ? `/storage/users/${this.activeChat.user.id}/${this.activeChat.user.image}`
            : '/storage/users/Perfil-Icon.png'
        };
      } else {
        this.otherUser = {
          name: 'Usuario desconocido',
          image: '/storage/users/Perfil-Icon.png'
        };
      }
    },


    setupChannels() {
      // Verificar que Echo esté disponible
      if (!window.Echo) {
        console.error("Echo no está disponible. ¿Están configuradas las variables de Pusher?");

        // Intenta inicializar con valores por defecto
        window.Echo = new Echo({
          broadcaster: "pusher",
          key: '337abba0601b16bbbce2',
          cluster: 'mt1',
          forceTLS: true,
          encrypted: true
        });
      }

      // Canal para mensajes
    this.echoChannel = window.Echo.channel(`chat.${this.chatId}`)
        .listen('NewMessage', (data) => {
            this.handleNewMessage(data);
        });

      console.log(`Suscrito al canal chat.${this.chatId}`);

      // Presence Channel para estado en línea
      // this.presenceChannel = window.Echo.join(`presence-chat.${this.chatId}`)
      //   .here((users) => {
      //     this.updateOnlineStatus(users);
      //   })
      //   .joining((user) => {
      //     this.userJoined(user);
      //   })
      //   .leaving((user) => {
      //     this.userLeft(user);
      //   });

      // console.log(`Suscrito al canal presence-chat.${this.chatId}`);
    },



    async markMessagesAsRead() {
      try {
        await axios.post(`/chats/${this.chatId}/read`);
        this.$emit('messages-read');
      } catch (error) {
        console.error('Error marcando mensajes como leídos', error);
      }
    },

    handleNewMessage(newMessage) {
      console.log('Nuevo mensaje recibido via Pusher:', newMessage);

      const exists = this.messages.some(m => m.id === newMessage.id);
      if (!exists) {
        console.log('Agregando nuevo mensaje a la lista');
        this.messages.push(newMessage);

        this.$nextTick(() => {
          this.scrollToBottom();

          if (!this.isMessageFromMe(newMessage)) {
            this.markMessagesAsRead();
          }
        });
      } else {
        console.log('El mensaje ya existe en la lista');
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

    updateReadStatus(data) {
      this.messages.forEach(msg => {
        if (this.isMessageFromMe(msg) && !msg.read) {
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