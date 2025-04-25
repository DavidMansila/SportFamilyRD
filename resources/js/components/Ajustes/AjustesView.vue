<template>
  
  <div class="settings-view">


    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-container">
          <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>
      </div>
        <div class="nav-links">
            <a href="/Noticias" class="nav-link">Noticias</a>
            <a href="/Calendario" class="nav-link">Calendario</a>
             <a href="/Tienda" class="nav-link">Tienda</a>
             <a href="/Entrenadores" class="nav-link">Entrenadores</a>
             <a href="/Foro" class="nav-link">Foro</a>
        </div>

        <div class="Imagenes">

            <a class="Carrito">
                <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
            </a>

            <a href= "/Ajustes" class="Ajustes">
                <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
            </a>

            <a href= "/Perfil" class="Perfil">
                <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
            </a>

            <a :href=" login ? '/Login' : '/Logout' " class="Logout">
                <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
            </a>

        </div>
      </nav>

    <div class="settings-container">
      <h1 class="settings-title">Configuración de Cuenta</h1>
      
      <div class="settings-tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.id" 
          @click="activeTab = tab.id"
          :class="{ 'active': activeTab === tab.id }"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="settings-content">
        <!-- Pestaña de Configuración de Cuenta -->
        <div v-if="activeTab === 'account'" class="tab-content">
          <div class="form-section">
            <h3>Información Personal</h3>
            <div class="form-group">
              <label>Nombre</label>
              <input type="text" v-model="user.name" placeholder="Tu nombre completo">
            </div>
            <div class="form-group">
              <label>Correo Electrónico</label>
              <input type="email" v-model="user.email" placeholder="tu@email.com">
            </div>
            <div class="form-group">
              <label>Teléfono</label>
              <input type="tel" v-model="user.phone" placeholder="+1 234 567 890">
            </div>
          </div>

          <div class="form-section">
            <h3>Seguridad</h3>
            <div class="form-group">
              <label>Contraseña Actual</label>
              <input type="password" v-model="security.currentPassword" placeholder="••••••••">
            </div>
            <div class="form-group">
              <label>Nueva Contraseña</label>
              <input type="password" v-model="security.newPassword" placeholder="••••••••">
            </div>
            <div class="form-group">
              <label>Confirmar Contraseña</label>
              <input type="password" v-model="security.confirmPassword" placeholder="••••••••">
            </div>
          </div>

          <button class="save-btn" @click="saveSettings">Guardar Cambios</button>
        </div>



        <!-- Pestaña de Notificaciones -->
        <div v-if="activeTab === 'notifications'" class="tab-content">
          <h3>Preferencias de Notificación</h3>
          <div class="toggle-group">
            <div class="toggle-item">
              <span>Notificaciones por Email</span>
              <label class="switch">
                <input type="checkbox" v-model="notifications.email">
                <span class="slider"></span>
              </label>
            </div>
            <div class="toggle-item">
              <span>Notificaciones Push</span>
              <label class="switch">
                <input type="checkbox" v-model="notifications.push">
                <span class="slider"></span>
              </label>
            </div>
            <div class="toggle-item">
              <span>Recordatorios de Entrenamiento</span>
              <label class="switch">
                <input type="checkbox" v-model="notifications.reminders">
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div class="notification-frequency">
            <h4>Frecuencia de Notificaciones</h4>
            <select v-model="notifications.frequency">
              <option value="instant">Inmediatas</option>
              <option value="daily">Resumen Diario</option>
              <option value="weekly">Resumen Semanal</option>
            </select>
          </div>
        </div>



        <!-- Pestaña de Privacidad -->
        <div v-if="activeTab === 'privacy'" class="tab-content">
          <h3>Configuración de Privacidad</h3>
          <div class="privacy-options">
            <div class="privacy-item">
              <span>Perfil Público</span>
              <label class="switch">
                <input type="checkbox" v-model="privacy.publicProfile">
                <span class="slider"></span>
              </label>
            </div>
            <div class="privacy-item">
              <span>Mostrar Estadísticas</span>
              <label class="switch">
                <input type="checkbox" v-model="privacy.showStats">
                <span class="slider"></span>
              </label>
            </div>
            <div class="privacy-item">
              <span>Permitir Mensajes</span>
              <label class="switch">
                <input type="checkbox" v-model="privacy.allowMessages">
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div class="data-section">
            <h4>Descargar Mis Datos</h4>
            <p>Solicita un archivo con toda la información que tenemos sobre ti.</p>
            <button class="data-btn" @click="requestData">Solicitar Datos</button>
          </div>

          <div class="delete-section">
            <h4>Eliminar Cuenta</h4>
            <p>Esta acción no se puede deshacer. Todos tus datos serán eliminados permanentemente.</p>
            <button class="delete-btn" @click="confirmDeletion">Eliminar Cuenta</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Modal de Confirmación -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ modalTitle }}</h3>
        <p>{{ modalMessage }}</p>
        <div class="modal-actions">
          <button @click="showModal = false">Cancelar</button>
          <button @click="confirmAction" class="confirm">Confirmar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SettingsView',
  data() {
    return {
      activeTab: 'account',
      tabs: [
        { id: 'account', label: 'Cuenta' },
        { id: 'notifications', label: 'Notificaciones' },
        { id: 'privacy', label: 'Privacidad' }
      ],
      user: {
        name: '',
        email: '',
        phone: '',
        avatar: ''
      },
      security: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      },
      notifications: {
        email: true,
        push: true,
        reminders: true,
        frequency: 'instant'
      },
      privacy: {
        publicProfile: true,
        showStats: true,
        allowMessages: true
      },
      showModal: false,
      modalTitle: '',
      modalMessage: '',
      currentAction: null
    }
  },
  methods: {
    saveSettings() {
      // Lógica para guardar ajustes
      console.log('Configuración guardada:', {
        user: this.user,
        security: this.security,
        notifications: this.notifications,
        privacy: this.privacy
      });
      this.showToast('Tus cambios se han guardado correctamente');
    },
    requestData() {
      this.modalTitle = 'Solicitar Mis Datos';
      this.modalMessage = '¿Quieres solicitar un archivo con todos tus datos? Esto puede tomar hasta 48 horas.';
      this.currentAction = 'requestData';
      this.showModal = true;
    },
    confirmDeletion() {
      this.modalTitle = 'Eliminar Cuenta';
      this.modalMessage = '¿Estás seguro de que quieres eliminar tu cuenta permanentemente? Esta acción no se puede deshacer.';
      this.currentAction = 'deleteAccount';
      this.showModal = true;
    },
    confirmAction() {
      if (this.currentAction === 'deleteAccount') {
        console.log('Cuenta eliminada');
        this.showToast('Tu cuenta ha sido eliminada');
        // Redirigir al inicio
      } else if (this.currentAction === 'requestData') {
        console.log('Datos solicitados');
        this.showToast('Hemos recibido tu solicitud de datos');
      }
      this.showModal = false;
    },
    showToast(message) {
      // Implementar lógica de toast/notificación
      alert(message); // Temporal
    }
  }
}
</script>




<style scoped lang="scss">
@forward '/resources/scss/Ajustes/ajustes' as ajs-*;
</style>