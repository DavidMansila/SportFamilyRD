<template>

  <div class="settings-view">



    <!-- Navbar -->
    <Navbar />


    <div class="settings-container">
      <h1 class="settings-title">Configuración de Cuenta</h1>

      <div class="settings-tabs">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="{ 'active': activeTab === tab.id }">
          {{ tab.label }}
        </button>
      </div>

      <div class="settings-content">
        <!-- Pestaña de Configuración de Cuenta -->
        <div v-if="activeTab === 'account'" class="tab-content">

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

              <span>Notificaciones por Telefono</span>
              <label class="switch">
                <input type="checkbox" v-model="notifications.push">
                <span class="slider"></span>
              </label>
            </div>

          </div>

        </div>



        <div v-if="activeTab === 'privacy'" class="tab-content" role="tabpanel" aria-labelledby="privacy-tab">
          <h2 class="privacy-heading">Configuración de Privacidad</h2>

          <section class="privacy-section">
            <h3 class="section-title">Preferencias de visibilidad</h3>
            <div class="privacy-options">
              <div class="privacy-item" v-for="option in privacyOptions" :key="option.id">
                <div class="option-label">
                  <span>{{ option.label }}</span>
                  <span class="option-description" v-if="option.description">{{ option.description }}</span>
                </div>
                <label class="switch">
                  <input type="checkbox" v-model="privacy[option.model]"
                    :aria-label="`${option.label} - actualmente ${privacy[option.model] ? 'activado' : 'desactivado'}`">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </section>

          <section class="data-section" aria-labelledby="data-heading">
            <h3 id="data-heading" class="section-title">Gestión de datos</h3>

            <div class="data-option danger-zone">
              <h4>Eliminar cuenta</h4>
              <p>Esta acción no se puede deshacer. Todos tus datos serán eliminados permanentemente.</p>
              <button class="btn btn-danger" @click="confirmDeletion" aria-describedby="delete-warning">
                Eliminar Cuenta
              </button>
              <p id="delete-warning" class="warning-text">
                <i class="icon-warning"></i> Advertencia: Esta acción eliminará todos tus datos de forma permanente.
              </p>
            </div>

          </section>

          <!-- Modal de confirmación para eliminación -->
          <div v-if="showDeleteModal" class="modal-overlay">
            <div class="modal-content" role="dialog" aria-labelledby="modal-title" aria-modal="true">
              <h3 id="modal-title">Confirmar eliminación</h3>
              <p>¿Estás seguro de que quieres eliminar tu cuenta permanentemente?</p>
              <div class="modal-actions">
                <button class="btn btn-secondary" @click="showDeleteModal = false">Cancelar</button>
                <button class="btn btn-danger" @click="deleteAccount">Confirmar</button>
              </div>
            </div>
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
import Navbar from '../navbarComponent.vue';

export default {
  name: 'Ajustes',
  components: {
    Navbar
  },
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
      currentAction: null,


      privacyOptions: [
        {
          id: 'public-profile',
          label: 'Perfil Público',
          description: 'Hace que tu perfil sea visible para todos los usuarios',
          model: 'publicProfile'
        },
        {
          id: 'show-stats',
          label: 'Mostrar Estadísticas',
          description: 'Comparte tus estadísticas de actividad públicamente',
          model: 'showStats'
        },
        {
          id: 'allow-messages',
          label: 'Permitir Mensajes',
          description: 'Permite que otros usuarios te envíen mensajes directos',
          model: 'allowMessages'
        }
      ],

      dataFormat: 'json',
      showDeleteModal: false

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
    },


    confirmDeletion() {
      this.showDeleteModal = true;
    },
    deleteAccount() {
      // Lógica para eliminar cuenta
      this.showDeleteModal = false;
    },
  },
  mounted() {
    document.title = 'Ajustes';
  }
}
</script>




<style scoped>
@import '/resources/scss/Ajustes/ajustes.scss';

@import '/resources/scss/Ajustes/ajustes_navbar.scss';

@import '/resources/scss/Ajustes/ajustes_forom.scss';

@import '/resources/scss/Ajustes/ajustes_modal.scss';

@import '/resources/scss/Ajustes/ajustes_privacidad.scss';

@import '/resources/scss/Ajustes/ajustes_responsive.scss';
</style>