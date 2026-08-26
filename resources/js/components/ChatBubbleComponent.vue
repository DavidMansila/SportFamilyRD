<template>
  <div v-if="user && chats.length > 0" class="message-bubble" :class="{ 'expanded': showMessages }">
    <div class="message-icon-container" @click="toggleMessages">
      <svg class="message-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span class="notification-badge" v-if="unreadMessages > 0">{{ unreadMessages }}</span>
    </div>

    <div v-if="showMessages" class="messages-container">
      <div class="messages-header" v-if="!activeChat">
        <h3>Chats</h3>
        <button class="close-btn" @click="toggleMessages">×</button>
      </div>

      <div v-if="!activeChat" class="contact-list">
        <div v-for="chat in approvedChats" :key="chat.id" class="contact-item" @click="openChat(chat)">
          <img :src="chat.other_participant.image" class="message-avatar" />
          <div class="message-content">
            <div class="message-header">
              <span class="sender-name">{{ chat.other_participant.name }}</span>
              <span v-if="chat.unread" class="unread-badge">{{ chat.unread }}</span>
            </div>
            <p v-if="chat.last_message" class="message-preview">
              <span v-if="chat.last_message.sender_id === user.id">Tú: </span>
              {{ truncateText(chat.last_message.message, 30) }}
            </p>
            <p v-else class="no-messages">No hay mensajes aún</p>
          </div>
        </div>

        <div v-if="approvedChats.length === 0" class="empty-chats">
          <p>Aun no tienes Chats</p>
        </div>
      </div>

      <div v-else class="active-chat-container">
        <ChatComponent ref="chatComponent" :active-chat="activeChat" :user="user" @close-chat="closeChat"
          @messages-read="loadChats" />
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import ChatComponent from './ChatComponent.vue';
import { getEcho } from '../echo';

export default {
  name: 'ChatBubble',
  components: {
    ChatComponent
  },
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      chats: [],
      showMessages: false,
      unreadMessages: 0,
      activeChat: null,
      personalChannelName: null
    };
  },
  computed: {
    approvedChats() {
      return this.chats.filter(chat => chat.status === 'accepted');
    }
  },
  methods: {

    toggleMessages() {
      this.showMessages = !this.showMessages;
      if (this.showMessages) {
        this.loadChats();
        document.body.classList.add('chat-open');
      } else {
        document.body.classList.remove('chat-open');
      }
    },

    truncateText(text, maxLength) {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    },

    async openChat(chat) {
      this.activeChat = chat;
      await this.$nextTick();

      if (this.$refs.chatComponent?.initializeChat) {
        await this.$refs.chatComponent.initializeChat();
      }
    },

    closeChat() {
      if (this.$refs.chatComponent && this.$refs.chatComponent.leaveChannels) {
        this.$refs.chatComponent.leaveChannels();
      }

      this.activeChat = null;
      this.loadChats();
      document.body.classList.remove('chat-open');
    },

    async loadChats() {
      if (!this.user) return;
      try {
        const response = await axios.get('/chats', { params: { user_id: this.user.id } });

        this.chats = response.data.map(chat => {
          const isUser = this.user.user_type === 'user';

          if (isUser && chat.trainer && chat.trainer.user) {
            return {
              id: chat.id,
              user_id: chat.user_id,
              trainer_id: chat.trainer_id,
              status: chat.status,
              unread: chat.unread_count || 0,
              last_message: chat.last_message,
              other_participant: {
                id: chat.trainer.user.id,
                name: chat.trainer.user.name,
                // El backend (ChatController) ya manda una URL completa de
                // Supabase Storage en chat.trainer.user.image, no un nombre
                // de archivo suelto: usarla tal cual, sin reconstruirla.
                image: chat.trainer.user.image || '/imagenes/Perfil-Icon.png',
                type: 'trainer'
              }
            };
          } else if (!isUser && chat.user) {
            return {
              id: chat.id,
              user_id: chat.user_id,
              trainer_id: chat.trainer_id,
              status: chat.status,
              unread: chat.unread_count || 0,
              last_message: chat.last_message,
              other_participant: {
                id: chat.user.id,
                name: chat.user.name,
                image: chat.user.image || '/imagenes/Perfil-Icon.png',
                type: 'user'
              }
            };
          }

          return {
            id: chat.id,
            user_id: chat.user_id,
            trainer_id: chat.trainer_id,
            status: chat.status,
            unread: chat.unread_count || 0,
            last_message: chat.last_message,
            other_participant: {
              name: 'Usuario desconocido',
              image: '/imagenes/Perfil-Icon.png'
            }
          };
        });

        this.unreadMessages = this.chats.reduce((total, chat) => total + (chat.unread || 0), 0);
      } catch (error) {
        console.error('Error cargando chats', error.response ? error.response.data : error);
      }
    },

    // Escucha el canal personal del usuario para enterarse de mensajes nuevos
    // en CUALQUIER chat, este abierto o no. Antes la lista solo se refrescaba al
    // montar el componente o al abrir/cerrar una conversacion, asi que un
    // mensaje entrante no se veia hasta recargar la pagina.
    async subscribeToPersonalChannel() {
      if (!this.user?.id) return;

      const echo = await getEcho();
      if (!echo) return;

      this.personalChannelName = `user.${this.user.id}`;
      echo.private(this.personalChannelName)
        .listen('.chat.updated', () => {
          this.loadChats();
        });
    },

    leavePersonalChannel() {
      if (this.personalChannelName && window.Echo) {
        window.Echo.leave(this.personalChannelName);
        this.personalChannelName = null;
      }
    },
  },
  mounted() {
    this.loadChats();
    this.subscribeToPersonalChannel();
  },
  beforeUnmount() {
    if (this.$refs.chatComponent && this.$refs.chatComponent.leaveChannels) {
      this.$refs.chatComponent.leaveChannels();
    }
    this.leavePersonalChannel();
    document.body.classList.remove('chat-open');
  }
};
</script>

<style scoped>
@import '../../scss/Entrenadores/entrenadores_mensajes.scss';
</style>