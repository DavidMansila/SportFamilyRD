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
        console.log('Fetching messages for chat ID:', this.chatId);

        const response = await axios.get(`/chats/${this.chatId}`, {
          // params: {
          //   user_id: this.user.id,
          // }
        });
        console.log('Messages response:', response.data);

        this.messages = response.data.messages;

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

      if (force || container.scrollTop + container.clientHeight >= container.scrollHeight - 100) {
        this.$nextTick(() => {
          container.scrollTop = container.scrollHeight;
        });
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
          user_id: this.user.id,
        });

        const index = this.messages.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
          this.messages.splice(index, 1, response.data);
        } else {
          this.messages.push(response.data);
        }

      } catch (error) {
        console.error('Error sending message:', error);
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

      const dateObj = typeof time === 'string' || typeof time === 'number'
        ? new Date(time)
        : time;

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
      if (!this.chatId || !window.Echo) return;

      this.leaveChannels();

      // Canal privado para mensajes
      this.echoChannel = window.Echo.private(`private-chat.${this.chatId}`)
        .listen('.message.sent', this.handleIncomingMessage);

      // Canal de presencia para estado
      this.presenceChannel = window.Echo.join(`presence-chat.${this.chatId}`)
        .here(this.updateOnlineStatus)
        .joining(this.userJoined)
        .leaving(this.userLeft);
    },


    handleIncomingMessage(data) {
      if (this.messages.some(msg => msg.id === data.id)) return;

      this.messages.push(data);
      this.scrollToBottom();

      if (!this.isMessageFromMe(data)) {
        this.markMessagesAsRead();
      }
    },


    leaveChannels() {
      if (this.echoChannel) {
        window.Echo.leave(`chat.${this.chatId}`);
        this.echoChannel = null;
      }
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
      // Evitar duplicados
      const messageExists = this.messages.some(msg => msg.id === newMessage.id);

      if (!messageExists) {
        this.messages.push(newMessage);
        this.$nextTick(this.scrollToBottom);

        // Marcar como leído si es mensaje entrante
        if (!this.isMessageFromMe(newMessage)) {
          this.markMessagesAsRead();
        }
      }
    },

    closeChat() {
      if (this.echoChannel) {
        window.Echo.leave(`chat.${this.chatId}`);
      }
      if (this.presenceChannel) {
        window.Echo.leave(`presence-chat.${this.chatId}`);
      }

      this.$emit('close-chat');
    },

    updateReadStatus() {
      this.messages.forEach(msg => {
        if (this.isMessageFromMe(msg)) {
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
  mounted() {
    this.setupChannels();
  },

  beforeUnmount() {
    this.leaveChannels();
  }
}
</script>

<style scoped>
@import '../../scss/Entrenadores/chatcomponent.scss';
</style>