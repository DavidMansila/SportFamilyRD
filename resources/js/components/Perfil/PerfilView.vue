<template>

    <div class="profile-view">

        <!-- Navbar -->
        <Navbar />

        <div class="profile-container">
            <!-- Header del Perfil -->
            <div class="profile-header">
                <div class="avatar-container">
                    <img :src="user.image" alt="Avatar" class="profile-avatar">
                    <button class="edit-avatar" @click="handleAvatarChange">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
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
                            <span class="stat-label">Solicitudes Usuarios</span>
                        </div>

                        <div class="stat-item" v-if="user.user_type === 'admin'">
                            <span class="stat-number">{{ stats.SolicitudesUsuarios }}</span>
                            <span class="stat-label">Solicitudes Entrenadores</span>
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


                <!-- <button v-if="user.user_type === 'entrenador'" class="upload-info-btn" @click="redirectToTrainersPage">
                    Subir Información
                </button> -->

            </div>

            <!-- Contenido del Perfil -->
            <div class="profile-content">
                <!-- Sección de Información Básica -->
                <div class="profile-section">

                    <template v-if="user.user_type === 'entrenador'">
                        <h2>Información de Entrenador</h2>
                    </template>

                    <template v-else>
                        <h2>Información de Usuario</h2>
                    </template>


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
                            <span v-if="!editMode" class="info-value">
                                {{ formatPhone(user.phone) || 'No especificado' }}
                            </span>
                            <input v-else type="tel" v-model="user.phone" @input="formatPhoneInput"
                                placeholder="Añade tu teléfono" maxlength="12">
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
                        <div class="info-item" v-if="user.user_type === 'entrenador'">
                            <span class="info-label">Deporte:</span>

                            <span v-if="!editMode" class="info-value">
                                {{ user.categoria || 'No especificada' }} <!-- Cambiado a mostrar string simple -->
                            </span>

                            <multiselect v-else v-model="user.categoria" :options="deportes" :multiple="false"
                                placeholder="Selecciona un deporte" :allow-empty="false" required></multiselect>
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



                <!-- Sección de Especialidades -->
                <div class="profile-section" v-if="user.user_type === 'entrenador'">
                    <h2>Mis Especialidades</h2>
                    <div v-if="!editMode" class="especialidades-list">
                        <div v-if="user.especialidades && user.especialidades.length > 0"
                            class="especialidades-container">
                            <span v-for="(especialidad, index) in user.especialidades" :key="index"
                                class="especialidad-tag">
                                {{ especialidad }}
                            </span>
                        </div>
                        <p v-else class="no-especialidades">No has agregado especialidades aún</p>
                    </div>

                    <div v-else class="especialidades-edit">
                        <div v-for="(especialidad, index) in user.especialidades" :key="index"
                            class="especialidad-edit-item">
                            <div class="especialidad-input-container">
                                <input type="text" v-model="user.especialidades[index]"
                                    placeholder="Escribe una especialidad">
                                <button @click="eliminarEspecialidad(index)" class="btn-eliminar-especialidad">
                                    ×
                                </button>
                            </div>
                        </div>
                        <button @click="agregarNuevaEspecialidad" class="btn-agregar-especialidad">
                            + Añadir Nueva Especialidad
                        </button>
                    </div>
                </div>


                <!-- Sección de Redes Sociales -->
                <div class="profile-section">
                    <!-- <h2>Redes Sociales</h2>
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
                    </div> -->

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

            <!-- Botones de acción en modo edición -->
            <div v-if="editMode" class="action-buttons">
                <button @click="saveProfile" class="save-btn">Guardar Cambios</button>
                <button @click="discardChanges" class="discard-btn">Descartar Cambios</button>
            </div>


        </div>

    </div>
</template>


<script>
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import Navbar from '../navbarComponent.vue';
import axios from 'axios';

export default {
    name: 'ProfileView',
    components: {
        Navbar,
        Multiselect
    },
    data() {
        return {
            editMode: false,
            // nuevaEspecialidad: '',
            deportes: ['Fútbol', 'Tenis', 'Baloncesto', 'Natación', 'Ciclismo', 'Atletismo', 'Artes Marciales'],
            user: {
                categoria: '',
                // especialidades: []
            },
            stats: {
                posts: 0,
                likes: 0,
                SolicitudesUsuarios: 0,
                rating: 0
            },
            originalUserData: null,
            nuevaEspecialidad: '',
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

            // formData.append('categoria', this.user.categoria);

            // Agregar imagen si existe
            if (this.$refs.avatarInput.files[0]) {
                formData.append('image', this.$refs.avatarInput.files[0]);
            }

            // if (this.user.especialidades) {
            //     formData.append('especialidades', this.user.especialidades.join(','));
            // }

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



        formatPhone(number) {
            if (!number) return '';
            // Eliminar todos los caracteres que no sean dígitos
            const cleaned = number.replace(/\D/g, '');
            // Aplicar formato xxx-xxx-xxxx
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                return `${match[1]}-${match[2]}-${match[3]}`;
            }
            return number; // Retornar el valor original si no coincide
        },

        formatPhoneInput(event) {
            const input = event.target.value;
            // Eliminar todos los caracteres que no sean dígitos
            const cleaned = input.replace(/\D/g, '');
            // Aplicar formato mientras se escribe
            let formatted = cleaned.replace(/^(\d{3})(\d{0,3})(\d{0,4}).*/, (_, p1, p2, p3) => {
                let result = p1;
                if (p2) result += `-${p2}`;
                if (p3) result += `-${p3}`;
                return result;
            });
            // Actualizar el valor en el modelo
            this.user.phone = formatted;
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
            this.user.social_links.push({ platform: '', url: '' });
        },

        removeSocialLink(index) {
            this.user.social_links.splice(index, 1);
        },

        addAchievement() {
            if (!this.user.achievements) {
                this.user.achievements = []; // Inicializar si no existe
            }
            this.user.achievements.push({
                title: '',
                description: '',
                date: new Date().toISOString().split('T')[0]
            });
        },

        removeAchievement(index) {
            this.user.achievements.splice(index, 1);
        },


        formatAchievementDate(date) {
            if (!date) return 'Fecha no especificada';
            try {
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(date).toLocaleDateString('es-ES', options);
            } catch (e) {
                return date;
            }
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
        },


        agregarNuevaEspecialidad() {
            if (!this.user.especialidades) {
                this.$set(this.user, 'especialidades', []);
            }
            this.user.especialidades.push('');
        },

        eliminarEspecialidad(index) {
            this.user.especialidades.splice(index, 1);
        },


        // redirectToTrainersPage() {
        //     // Guardar los datos del perfil para usarlos en la página de entrenadores
        //     sessionStorage.setItem('DatosEntrenador', JSON.stringify(this.user));
        //     // Redirigir a la página de entrenadores
        //     this.$router.push('/entrenadores');
        // },


        // agregarEspecialidad() {
        //     if (this.nuevaEspecialidad.trim() && !this.user.especialidades.includes(this.nuevaEspecialidad.trim())) {
        //         if (!this.user.especialidades) {
        //             this.user.especialidades = [];
        //         }
        //         this.user.especialidades.push(this.nuevaEspecialidad.trim());
        //         this.nuevaEspecialidad = '';
        //     }
        // },

        // eliminarEspecialidad(index) {
        //     this.user.especialidades.splice(index, 1);
        // },


    },
    mounted() {
        // Cargar datos iniciales
        this.user = JSON.parse(sessionStorage.getItem('user'));
        document.title = 'Perfil de ' + this.user.name;
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


.profile-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.upload-info-btn {
    background-color: #2196F3;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
}

.upload-info-btn:hover {
    background-color: #0b7dda;
}

/* Estilos para logros */
.achievement-item {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.achievement-display h3 {
    margin: 0 0 5px 0;
    color: #333;
}

.achievement-display p {
    margin: 5px 0;
    color: #666;
}

.achievement-date {
    font-size: 0.9em;
    color: #888;
}

/* Estilos para redes sociales */
.social-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.social-icon img {
    width: 24px;
    height: 24px;
}

.add-social,
.add-achievement {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
}

.remove-social,
.remove-achievement {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-actions {
        flex-direction: column;
    }
}

/* Estilo para el botón Subir Información */
.upload-info-btn {
    background-color: #4CAF50;
    /* Verde */
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-left: 11px;
    /* Espacio del botón Editar Perfil */
}

.upload-info-btn:hover {
    background-color: #45a049;
    /* Verde más oscuro al pasar el mouse */
}

/* Estilos para móviles */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .upload-info-btn {
        margin-left: 0;
        margin-top: 10px;
        width: 100%;
    }
}





/* MULTISELECT */
.multiselect {
    width: 100%;
    max-width: 300px;
    margin-top: 8px;
}

.multiselect__tags {
    min-height: 40px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.multiselect__option--highlight {
    background: #4CAF50;
    color: white;
}








/* Estilos para la sección de especialidades */
.especialidades-list {
    margin-top: 15px;
}

.especialidades-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.especialidad-tag {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 0.9rem;
    display: inline-block;
}

.no-especialidades {
    color: #757575;
    font-style: italic;
}

.especialidades-edit {
    margin-top: 15px;
}

.especialidades-input-container {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}

.especialidad-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-agregar-especialidad {
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.especialidades-edit-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.especialidad-edit-item {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 6px 12px 6px 12px;
    border-radius: 16px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-eliminar-especialidad {
    background: none;
    border: none;
    color: #f44336;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
}

/* Responsive */
@media (max-width: 768px) {
    .especialidades-input-container {
        flex-direction: column;
    }

    .btn-agregar-especialidad {
        padding: 8px;
    }
}





.especialidades-edit {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.especialidad-edit-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.especialidad-input-container {
    flex: 1;
    display: flex;
    gap: 8px;
}

.especialidad-input-container input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-eliminar-especialidad {
    background: #ff4444;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 12px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-eliminar-especialidad:hover {
    background: #cc0000;
}

.btn-agregar-especialidad {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    align-self: flex-start;
    transition: background 0.3s;
}

.btn-agregar-especialidad:hover {
    background: #45a049;
}
</style>