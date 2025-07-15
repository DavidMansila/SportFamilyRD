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
              <div class="password-input">
                <input :type="showCurrentPassword ? 'text' : 'password'" v-model="security.currentPassword"
                  placeholder="••••••••">
                <span class="toggle-password" @click="showCurrentPassword = !showCurrentPassword">
                  <i :class="showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </span>
              </div>
            </div>
            <div class="form-group">
              <label>Nueva Contraseña</label>
              <div class="password-input">
                <input :type="showNewPassword ? 'text' : 'password'" v-model="security.newPassword"
                  placeholder="••••••••">
                <span class="toggle-password" @click="showNewPassword = !showNewPassword">
                  <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </span>
              </div>
            </div>
            <div class="form-group">
              <label>Confirmar Contraseña</label>
              <div class="password-input">
                <input :type="showConfirmPassword ? 'text' : 'password'" v-model="security.confirmPassword"
                  placeholder="••••••••">
                <span class="toggle-password" @click="showConfirmPassword = !showConfirmPassword">
                  <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </span>
              </div>
            </div>
          </div>

          <button class="save-btn" @click="changePassword">Guardar Cambios</button>
        </div>


        <div v-if="activeTab === 'privacy'" class="tab-content" role="tabpanel" aria-labelledby="privacy-tab">
          <h2 class="privacy-heading">Configuración de Privacidad</h2>


          <!-- <div class="settings-container">
            <div v-for="config in userConfigs" :key="config.id" class="setting-item">
              <div class="setting-content">
                <span class="setting-label">{{ config.configuration }}</span>
                <span class="setting-description" v-if="config.description">{{ config.description }}</span>
              </div>

              <label class="modern-switch">
                <input type="checkbox" :checked="config.value === 'enabled'" @change="toggleConfig(config)">
                <span class="slider round"></span>
              </label>
            </div>
          </div> -->

          <section class="data-section" aria-labelledby="data-heading">
            <h3 id="data-heading" class="section-title">Gestión de datos</h3>

            <div class="data-option danger-zone">
              <h4>Eliminar cuenta</h4>
              <p>Esta acción no se puede deshacer. Todos tus datos serán eliminados permanentemente.</p>
              <button class="btn btn-danger" @click="showDeleteModal = true">Eliminar Cuenta</button>
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

  <Alert 
    v-if="openModal" 
    :type="alertType" 
    :message="alertMessage" 
    @close="openModal = false" 
  />
</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import Alert from '../Alert.vue';
export default {
  name: 'Ajustes',
  components: {
    Navbar,
    Alert
  },
  data() {

    return {
      alertType: '',
      alertMessage: '',
      openModal: false,

      activeTab: 'account',

      tabs: [
        { id: 'account', label: 'Cuenta' },
        // { id: 'notifications', label: 'Notificaciones' },
        { id: 'privacy', label: 'Privacidad' }
      ],

      user: null,

      security: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
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
      showDeleteModal: false,

      loading: false,
      error: null,

      userConfigs: [],

      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      showSuccessMessage: false

    }
  },

  methods: {

    confirmAction() {
      if (this.currentAction === 'deleteAccount') {
        this.deleteAccount()
          .then(() => {
            this.$router.push('/login');
          })
          .catch(() => {
            this.showModal = false;
          });
      } else if (this.currentAction === 'requestData') {
        this.requestUserData()
          .finally(() => {
            this.showModal = false;
          });
      }
    },

    handleApiError(error, defaultMessage) {
      const message = error.response?.data?.message || defaultMessage;
      const status = error.response?.status;

      if (status === 401) {
        this.$router.push('/login');
      }

      this.showToast(`${message} (Código: ${status || 'N/A'})`, 'error');
      console.error('API Error:', error);
    },

    showToast(message, type = 'info') {
      
      this.alertType = type;
      this.alertMessage = message;
      this.openModal = true;
    },

    fetchUserConfigs() {
      axios.get('/config', {
        params: { user_id: this.user.id }
      })
        .then(response => {
          this.userConfigs = response.data.config;
        })
        .catch(error => {
          this.handleApiError(error, 'Error al cargar configuraciones');
        });
    },

    async changePassword() {
      if (this.security.newPassword !== this.security.confirmPassword) {
        this.showToast('Las nuevas contraseñas no coinciden', 'error');
        return;
      }

      try {
        await axios.post('/change-password', {
          user_id: this.user.id,
          current_password: this.security.currentPassword,
          new_password: this.security.newPassword
        });

        this.showToast('¡Contraseña cambiada con éxito!', 'success');

        this.security = {
          currentPassword: '',
          newPassword: '',
          confirmPassword: ''
        };

        this.showCurrentPassword = false;
        this.showNewPassword = false;
        this.showConfirmPassword = false;

      } catch (error) {
        this.handleApiError(error, 'Error al cambiar la contraseña');
      }
    },

    deleteAccount() {
      this.showDeleteModal = false;

      axios.delete(`/user/${this.user.id}`)
        .then(response => {
          this.showToast('Cuenta eliminada exitosamente', 'success');
          sessionStorage.removeItem('user');
          this.$router.push('/signup');
        })
        .catch(error => {
          this.handleApiError(error, 'Error al eliminar la cuenta');
        });
    },

    toggleConfig(config) {
      config.value = config.value === 'enabled' ? 'disabled' : 'enabled';

      axios.post('/config-update-value', {
        user_id: this.user.id,
        configuration_id: config.id,
        status: config.value
      })
        .then(response => {
          this.showToast('Configuración actualizada correctamente', 'success');
        })
        .catch(error => {
          this.handleApiError(error, 'Error al actualizar configuración');
        });
    },

  },

  mounted() {
    document.title = 'Ajustes';
    this.user = JSON.parse(sessionStorage.getItem('user'));
    this.fetchUserConfigs();

  },
}
</script>




<style scoped>
@import '/resources/scss/Ajustes/ajustes.scss';

@import '/resources/scss/Ajustes/ajustes_navbar.scss';

@import '/resources/scss/Ajustes/ajustes_forom.scss';

@import '/resources/scss/Ajustes/ajustes_modal.scss';

@import '/resources/scss/Ajustes/ajustes_privacidad.scss';

@import '/resources/scss/Ajustes/ajustes_responsive.scss';


.password-input {
  position: relative;
  width: 100%;
}

.password-input input {
  padding-right: 35px;
  /* Espacio para el ícono */
  width: 100%;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #6c757d;
  z-index: 2;
}

.toggle-password:hover {
  color: #495057;
}

/* Asegúrate de tener espacio suficiente en los inputs */
.form-group {
  margin-bottom: 1.5rem;
  position: relative;
}


.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-danger {
  background-color: #e53e3e;
  color: white;
}

.btn-secondary {
  background-color: #cbd5e0;
}
</style>