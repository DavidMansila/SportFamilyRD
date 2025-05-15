<template>

    <div class="profile-view">

        <!-- Navbar -->
        <Navbar />

        <div class="profile-container">
            <!-- Header del Perfil -->
            <div class="profile-header">
                <div class="avatar-container">
                    <img :src=" user.image" alt="Avatar" class="profile-avatar">
                    <button class="edit-avatar" @click="handleAvatarChange">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                            />
                            <path
                              d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                            />
                        </svg>
                    </button>
                    <input type="file" ref="avatarInput" @change="handleAvatarChange" accept="image/*"
                      style="display: none;">
                </div>
                <div class="profile-info">

                    <h1 class="profile-name">{{ user.name }}</h1>
                    <p class="profile-role">{{ user.user_type }}</p>

                    <div class="profile-stats">

                        <div class="stat-item">
                            <span class="stat-number">{{ stats.posts }}</span>
                            <span class="stat-label">Publicaciones</span>
                        </div>

                        <div class="stat-item">
                            <span class="stat-number">{{ stats.likes }}</span>
                            <span class="stat-label">Likes</span>
                        </div>

                        <div class="stat-item" v-if="user.user_type === 'entrenador'">
                            <span class="stat-number">{{ stats.SolicitudesUsuarios }}</span>
                            <span class="stat-label">Siguiendo</span>
                        </div>

                        <div class="stat-item" v-if="user.user_type === 'entrenador'">
                            <span class="stat-number">{{ stats.rating }}</span>
                            <span class="stat-label">Valoración</span>
                        </div>

                    </div>
                </div>

                <button class="edit-profile-btn" @click="editMode = !editMode">
                    {{ editMode ? 'Cancelar' : 'Editar Perfil' }}
                </button>
            </div>

            <!-- Contenido del Perfil -->
            <div class="profile-content">
                <!-- Sección de Información Básica -->
                <div class="profile-section">
                    <h2>Información Básica</h2>

                    <div class="info-grid">

                        <div class="info-item">
                            <span class="info-label">Nombre:</span>
                            <span v-if="!editMode" class="info-value">{{ user.name }}</span>
                            <input v-else type="text" v-model="user.name">
                        </div>

                        <div class="info-item">
                            <span class="info-label">Correo:</span>
                            <span v-if="!editMode" class="info-value">{{ user.email }}</span>
                            <input v-else type="email" v-model="user.email">
                        </div>

                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span v-if="!editMode" class="info-value">{{ user.phone || 'No especificado' }}</span>
                            <input v-else type="tel" v-model="user.phone" placeholder="Añade tu teléfono">
                        </div>

                        <div class="info-item">
                            <span class="info-label">Ubicación:</span>
                            <span v-if="!editMode" class="info-value">{{ user.location || 'No especificada' }}</span>
                            <input v-else type="text" v-model="user.location" placeholder="Añade tu ubicación">
                        </div>

                        <div class="info-item">
                            <span class="info-label">Fecha Nacimiento:</span>
                            <span v-if="!editMode" class="info-value">{{ user.birthdate || 'No especificada' }}</span>
                            <input v-else type="date" v-model="user.birthdate">
                        </div>

                    </div>

                </div>


                <!-- Sección de Biografía -->
                <div class="profile-section">
                    <h2>Biografía</h2>
                    <p v-if="!editMode" class="profile-bio">{{ user.bio || 'Añade una breve biografía sobre ti...' }}
                    </p>
                    <textarea v-else v-model="user.bio" placeholder="Cuéntanos sobre ti, tus logros, experiencia..."
                        rows="4"></textarea>
                </div>


                <!-- Sección de Redes Sociales -->
                <div class="profile-section"> 
                    <h2>Redes Sociales</h2>
                    <div class="social-links">
                        <div v-for="(social, index) in user.social_links" :key="index" class="social-item">
                            <select v-model="social.platform" v-if="editMode">
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter</option>
                                <option value="instagram">Instagram</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="youtube">YouTube</option>
                            </select>
                            <span v-else class="social-icon">
                                <img :src="getSocialIcon(social.platform)" :alt="social.platform">
                            </span>
                            <input type="text" v-model="social.url" :placeholder="'Enlace de ' + social.platform"
                                :readonly="!editMode">
                            <button v-if="editMode" @click="removeSocialLink(index)" class="remove-social">
                                ×
                            </button>
                        </div>
                        <button v-if="editMode" @click="" class="add-social">
                            + Añadir Red Social
                        </button>
                    </div>
                    <!-- Botones de acción en modo edición -->
                    <div v-if="editMode" class="action-buttons">
                        <button @click="saveProfile" class="save-btn">Guardar Cambios</button>
                        <button @click="discardChanges" class="discard-btn">Descartar Cambios</button>
                    </div>
                </div>


                <!-- Sección de Logros -->
                <div class="profile-section" v-if="user.user_type === 'entrenador'">
                    <h2>Mis Logros</h2>
                    <div class="achievements">
                        <div v-for="(achievement, index) in user.achievements" :key="index" class="achievement-item">
                            <div v-if="!editMode" class="achievement-display">
                                <h3>{{ achievement.title }}</h3>
                                <p>{{ achievement.description }}</p>
                                <span class="achievement-date">{{ achievement.date }}</span>
                            </div>
                            <div v-else class="achievement-edit">
                                <input type="text" v-model="achievement.title" placeholder="Título del logro">
                                <textarea v-model="achievement.description" placeholder="Descripción"></textarea>
                                <input type="date" v-model="achievement.date">
                                <button @click="removeAchievement(index)" class="remove-achievement">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                        <button v-if="editMode" @click="addAchievement" class="add-achievement">
                            + Añadir Logro
                        </button>
                    </div>
                </div>


            </div>

        </div>

    </div>
</template>


<script>
import Navbar from '../navbarComponent.vue';
import axios from 'axios';
export default {
    name: 'ProfileView',
    components: {
        Navbar
    },
    data() {
        return {
            editMode: false,
            user: {
                id: null,
                name: '',
                email: '',
                phone: '',
                location: '',
                birthdate: '',
                bio: '',
                image: '/default-avatar.png',
                socialLinks: [],
                achievements: []
            },
            stats: {
                posts: 0,
                likes: 0,
                SolicitudesUsuarios: 0,
                rating: 0
            },
            originalUserData: null
        }
    },

    methods: {

      async saveProfile() {
       
        const formData = new FormData();

        // Agregar campos básicos
        formData.append('_method', 'PUT');
        formData.append('name', this.user.name);
        formData.append('email', this.user.email);
        formData.append('phone', this.user.phone);
        formData.append('location', this.user.location);
        formData.append('birthdate', this.user.birthdate);
        formData.append('bio', this.user.bio);

        // Agregar imagen si existe
        if (this.$refs.avatarInput.files[0]) {
            formData.append('image', this.$refs.avatarInput.files[0]);
        }

        // Enviar solicitud PUT
        axios.post(`/user/${this.user.id}`, formData, {
         headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then(response => {
          // Actualizar datos locales
          this.user = response.data.user;
          sessionStorage.setItem('user', JSON.stringify(response.data.user));
          this.editMode = false;
          alert('¡Perfil actualizado correctamente!');

        })
        .catch(error => {
            this.handleError(error, 'Error al guardar perfil');
        });

        
        
      },

      handleAvatarChange(event) {
        // Si es el clic en el botón
        if (!event.target.files) {
          this.$refs.avatarInput.click()
          return
        }

        // Si es la selección de archivo
        const file = event.target.files[0]
        if (file) {
          this.validarYActualizarAvatar(file)
        }
      },

      validarYActualizarAvatar(file) {
        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
          alert('Por favor selecciona un archivo de imagen válido')
          return
        }

        // Validar tamaño (ejemplo: 2MB máximo)
        const maxSize = 2 * 1024 * 1024
        if (file.size > maxSize) {
          alert('El tamaño máximo permitido es 2MB')
          return
        }

        // Crear previsualización
        const reader = new FileReader()
        reader.onload = (e) => {
          this.user.image = e.target.result

         
          this.subirAvatarAlServidor(file)
        }
        reader.readAsDataURL(file)
      },

      async subirAvatarAlServidor(file) {
        const formData = new FormData()
        formData.append('_method', 'PUT');
        formData.append('image', file)

        axios.post(`/user/${this.user.id}`, formData, {
         headers: { 'Content-Type': 'multipart/form-data' },
        })
        .then(response => {
          this.user = response.data.user;
          sessionStorage.setItem('user', JSON.stringify(response.data.user));
          alert('imagen actualizada correctamente!');

        })
        .catch(error => {
            this.handleError(error, 'Error al guardar perfil');
        });
        
      },

      discardChanges() {
          this.user = JSON.parse(JSON.stringify(this.originalUserData));
          this.editMode = false;
      },

      getChangedFields() {
          const changes = {};
          Object.keys(this.user).forEach(key => {
              if (JSON.stringify(this.user[key]) !== JSON.stringify(this.originalUserData[key])) {
                  changes[key] = this.user[key];
              }
          });
          return changes;
      },

      getSocialIcon(platform) {
          const icons = {
              facebook: 'facebook-icon.svg',
              twitter: 'twitter-icon.svg',
              instagram: 'instagram-icon.svg',
              linkedin: 'linkedin-icon.svg',
              youtube: 'youtube-icon.svg'
          };
          return `/imagenes/social/${icons[platform] || 'default-social-icon.svg'}`;
      },

      addSocialLink() {
        this.user.social_links.push({ platform: 'facebook', url: '' });
      },

      removeSocialLink(index) {
          this.user.socialLinks.splice(index, 1);
      },

      addAchievement() {
          this.user.achievements.push({
              title: '',
              description: '',
              date: new Date().toISOString().split('T')[0]
          });
      },

      removeAchievement(index) {
          this.user.achievements.splice(index, 1);
      },

      handleError(error, defaultMsg) {
          const errorMsg = error.response?.data?.message || defaultMsg;
          this.error = errorMsg;
          this.showToast(errorMsg, 'error');
          console.error(error);
      },

      showToast(message, type = 'info') {
          // Implementar lógica de tu sistema de notificaciones
          alert(`${type.toUpperCase()}: ${message}`);
      }
    },
    mounted() {
      // Cargar datos iniciales
      this.user = JSON.parse(sessionStorage.getItem('user'));
      
      
    }
  }
</script>



<style scoped>
@import '/resources/scss/Perfil/perfil.scss';

@import '/resources/scss/Perfil/perfil_navbar.scss';

@import '/resources/scss/Perfil/perfil_contenido.scss';

@import '/resources/scss/Perfil/perfil_social_links.scss';

@import '/resources/scss/Perfil/perfil_logros.scss';

@import '/resources/scss/Perfil/perfil_responsive.scss';
</style>