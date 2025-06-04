<template>
  <div class="chat-container" v-if="activeChat">
    <div class="chat-header">
      <button class="back-btn" @click="closeChat">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <h4>{{ contactName }}</h4>
    </div>
    
    <div class="messages-list" ref="messagesContainer">
      <div v-for="msg in messages" :key="msg.id" 
           :class="['message-item', { 'sent': msg.sender_id === user.id }]">
        <div class="message-content">{{ msg.message }}</div>
        <span class="message-time">{{ formatTime(msg.created_at) }}</span>
      </div>
    </div>
    
    <div class="message-input-container">
      <input type="text" v-model="newMessage" @keyup.enter="sendMessage" 
             placeholder="Escribe un mensaje...">
      <button @click="sendMessage">Enviar</button>
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
      polling: null,
      contactName: ''
    }
  },
  watch: {
    activeChat(newChat) {
      if (newChat) {
        this.contactName = this.user.role === 'user' 
          ? newChat.trainer.name 
          : newChat.user.name;
        this.loadMessages();
        this.startPolling();
      }
    }
  },
  methods: {
    async loadMessages() {
      try {
        const response = await axios.get(`/api/chats/${this.activeChat.id}/messages`);
        this.messages = response.data;
        this.scrollToBottom();
      } catch (error) {
        console.error('Error loading messages', error);
      }
    },
    
    async sendMessage() {
      if (!this.newMessage.trim()) return;
      
      try {
        await axios.post(`/api/chats/${this.activeChat.id}/messages`, {
          message: this.newMessage
        });
        this.newMessage = '';
        this.loadMessages(); // Recargar mensajes después de enviar
      } catch (error) {
        console.error('Error sending message', error);
      }
    },
    
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        container.scrollTop = container.scrollHeight;
      });
    },
    
    formatTime(dateString) {
      const date = new Date(dateString);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    
    startPolling() {
      // Actualizar mensajes cada 5 segundos
      this.polling = setInterval(() => {
        this.loadMessages();
      }, 5000);
    },
    
    stopPolling() {
      if (this.polling) {
        clearInterval(this.polling);
        this.polling = null;
      }
    },
    
    closeChat() {
      this.stopPolling();
      this.$emit('close-chat');
    }
  },
  beforeUnmount() {
    this.stopPolling();
  }
}
</script>

<style scoped>
/* Estilos del chat (similares a los anteriores) */
.chat-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: white;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #eee;
  background: #f9f9f9;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.back-btn {
  background: none;
  border: none;
  cursor: pointer;
  margin-right: 10px;
}

.messages-list {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
}

.message-item {
  margin-bottom: 15px;
  max-width: 80%;
}

.message-item.sent {
  margin-left: auto;
  text-align: right;
}

.message-content {
  display: inline-block;
  padding: 10px 15px;
  border-radius: 18px;
  background: #f1f0f0;
}

.message-item.sent .message-content {
  background: #3498db;
  color: white;
}

.message-time {
  display: block;
  font-size: 0.7rem;
  color: #999;
  margin-top: 5px;
}

.message-input-container {
  display: flex;
  padding: 15px;
  border-top: 1px solid #eee;
}

.message-input-container input {
  flex: 1;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 24px;
  margin-right: 10px;
  font-size: 1rem;
}

.message-input-container button {
  background: #3498db;
  color: white;
  border: none;
  border-radius: 24px;
  padding: 0 20px;
  cursor: pointer;
  font-weight: 500;
}
</style>