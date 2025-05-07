<template>
  
    <div class="profile-view">


    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>
      </div>

      <div class="nav-links">

           <!-- Secciones para lo usuarios y no usuarios -->
           <a href="/Noticias" class="nav-link">Noticias</a>
           <a href="/Calendario" class="nav-link">Calendario</a>
           <a href="/Tienda" class="nav-link">Tienda</a>
           <a href="/Entrenadores" class="nav-link">Entrenadores</a>
           <a href="/Foro" class="nav-link">Foro</a>

          <!-- Secciones para entrenadores -->
           <a v-if = "userType == 'entrenador'" href="/SolicitudesUsuarios" class="nav-link">Solicitudes</a>

          <!-- Secciones para entrenadores -->
          <a v-if = "userType == 'admin'" href="/SolicitudesEntrenadores" class="nav-link">Solicitudes</a>


      </div>

      <div class="Imagenes">

        <a href="#" class="Carrito">
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


    
  
      <div class="profile-container">
        <!-- Header del Perfil -->
        <div class="profile-header">
          <div class="avatar-container">
            <img :src="user.avatar" alt="Avatar" class="profile-avatar">
            <button class="edit-avatar" @click="triggerFileInput">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <input 
              type="file" 
              ref="avatarInput" 
              @change="handleAvatarChange" 
              accept="image/*" 
              style="display: none;"
            >
          </div>
          <div class="profile-info">
            <h1 class="profile-name">{{ user.name }}</h1>
            <p class="profile-role">{{ user.role }}</p>
            <div class="profile-stats">
              <div class="stat-item">
                <span class="stat-number">{{ stats.followers }}</span>
                <span class="stat-label">Seguidores</span>
              </div>
              <div class="stat-item">
                <span class="stat-number">{{ stats.following }}</span>
                <span class="stat-label">Siguiendo</span>
              </div>
              <div class="stat-item">
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
            <p v-if="!editMode" class="profile-bio">{{ user.bio || 'Añade una breve biografía sobre ti...' }}</p>
            <textarea 
              v-else 
              v-model="user.bio" 
              placeholder="Cuéntanos sobre ti, tus logros, experiencia..."
              rows="4"
            ></textarea>
          </div>
  
          <!-- Sección de Redes Sociales -->
          <div class="profile-section">
            <h2>Redes Sociales</h2>
            <div class="social-links">
              <div v-for="(social, index) in user.socialLinks" :key="index" class="social-item">
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
                <input 
                  type="text" 
                  v-model="social.url" 
                  :placeholder="'Enlace de ' + social.platform"
                  :readonly="!editMode"
                >
                <button 
                  v-if="editMode" 
                  @click="removeSocialLink(index)"
                  class="remove-social"
                >
                  ×
                </button>
              </div>
              <button 
                v-if="editMode" 
                @click="addSocialLink"
                class="add-social"
              >
                + Añadir Red Social
              </button>
            </div>
          </div>
  
          <!-- Sección de Logros -->
          <div class="profile-section">
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
              <button 
                v-if="editMode" 
                @click="addAchievement"
                class="add-achievement"
              >
                + Añadir Logro
              </button>
            </div>
          </div>
  
          <!-- Botones de acción en modo edición -->
          <div v-if="editMode" class="action-buttons">
            <button @click="saveProfile" class="save-btn">Guardar Cambios</button>
            <button @click="discardChanges" class="discard-btn">Descartar Cambios</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ProfileView',
    data() {
      return {
        editMode: false,
        user: {
          name: 'Carlos Pérez',
          role: 'Entrenador de Fútbol',
          email: 'carlos@ejemplo.com',
          phone: '+1 234 567 890',
          location: 'Santo Domingo, RD',
          birthdate: '1985-05-15',
          bio: 'Entrenador profesional con más de 10 años de experiencia formando jugadores de alto rendimiento. Especializado en táctica y desarrollo de habilidades técnicas.',
          avatar: '/imagenes/avatar-default.jpg',
          socialLinks: [
            { platform: 'facebook', url: 'https://facebook.com/carlosperez' },
            { platform: 'instagram', url: 'https://instagram.com/carlosperez' }
          ],
          achievements: [
            {
              title: 'Campeón Nacional 2020',
              description: 'Entrenador principal del equipo campeón de la liga nacional',
              date: '2020-12-15'
            },
            {
              title: 'Certificación UEFA Pro',
              description: 'Obtuve la certificación más alta para entrenadores de fútbol',
              date: '2018-06-10'
            }
          ]
        },
        stats: {
          followers: 245,
          following: 156,
          rating: 4.8
        },
        originalUserData: null
      }
    },
    created() {
      // Guardar copia original para poder descartar cambios
      this.originalUserData = JSON.parse(JSON.stringify(this.user));
    },
    methods: {
      triggerFileInput() {
        this.$refs.avatarInput.click();
      },
      handleAvatarChange(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.user.avatar = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      },
      getSocialIcon(platform) {
        return `/imagenes/social-${platform}.svg`;
      },
      addSocialLink() {
        this.user.socialLinks.push({ platform: 'facebook', url: '' });
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
      saveProfile() {
        // Aquí iría la lógica para guardar en el backend
        console.log('Perfil guardado:', this.user);
        this.originalUserData = JSON.parse(JSON.stringify(this.user));
        this.editMode = false;
        alert('Tus cambios se han guardado correctamente');
      },
      discardChanges() {
        this.user = JSON.parse(JSON.stringify(this.originalUserData));
        this.editMode = false;
      }
    },
    mounted() {
      document.title = 'Perfil';
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