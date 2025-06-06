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

                <button class="edit-profile-btn" @click="toggleEditMode">
                    {{ editMode ? 'Cancelar' : 'Editar Perfil' }}
                </button>



            </div>

            <!-- <button v-if="user.user_type === 'entrenador'" class="upload-info-btn" @click="redirectToTrainersPage">
                Subir Info. Entrenadores
            </button> -->

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


                <!-- Sección de Horario Mejorada -->
                <div class="profile-section" v-if="user.user_type === 'entrenador'">
                    <h2>Horario Disponible</h2>

                    <!-- Mostrar horario - Versión mejorada -->
                    <div v-if="!editMode" class="schedule-display">
                        <div v-if="hasSchedule" class="schedule-grid">
                            <div v-for="dia in diasSemana" :key="dia" class="schedule-day-card"
                                :class="{ 'available': user.schedule[dia] && user.schedule[dia].start }">
                                <div class="day-header">
                                    <span>{{ dia }}</span>
                                    <span v-if="user.schedule[dia] && user.schedule[dia].start"
                                        class="available-indicator"></span>
                                    <span v-else class="unavailable-indicator"></span>
                                </div>
                                <div v-if="user.schedule[dia] && user.schedule[dia].start" class="time-slot">
                                    {{ formatTime(user.schedule[dia].start) }} - {{ formatTime(user.schedule[dia].end)
                                    }}
                                </div>
                                <div v-else class="time-slot unavailable">
                                    No disponible
                                </div>
                            </div>
                        </div>
                        <p v-else class="no-schedule">No has establecido tu horario disponible aún.</p>
                    </div>

                    <!-- Editar horario -->
                    <div v-else class="schedule-edit">
                        <div class="schedule-grid">
                            <div v-for="dia in diasSemana" :key="dia" class="availability-day">
                                <div class="day-header">
                                    <label>
                                        <input type="checkbox" v-model="formulario.disponibilidad[dia]" />
                                        {{ dia }}
                                    </label>
                                </div>

                                <div v-if="formulario.disponibilidad[dia]" class="time-slots">
                                    <div class="time-slot">
                                        <label>De</label>
                                        <input type="time" v-model="formulario.horarios[dia].start" :min="minTime"
                                            :max="maxTime" />
                                    </div>
                                    <div class="time-slot">
                                        <label>A</label>
                                        <input type="time" v-model="formulario.horarios[dia].end" :min="minTime"
                                            :max="maxTime" />
                                    </div>
                                </div>
                                <div v-else class="time-slot unavailable">
                                    No disponible
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <button v-if="editMode" @click="agregarEspecialidad" class="btn-agregar-especialidad">
                            + Añadir Nueva Especialidad
                        </button>
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

                <!-- Sección de Redes Sociales -->
                <!-- <div class="profile-section">
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
                </div> -->

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
            entrenadores: [],
            trainer: null,
            deportes: ['Fútbol', 'Tenis', 'Baloncesto', 'Natación', 'Ciclismo', 'Atletismo', 'Artes Marciales'],
            user: {
                name: '',
                email: '',
                phone: '',
                location: '',
                birthdate: '',
                bio: '',
                image: '',
                user_type: '',
                categoria: '',
                especialidades: [],
                achievements: [],
                schedule: {},
            },
            stats: {
                posts: 0,
                likes: 0,
                SolicitudesUsuarios: 0,
                rating: 0
            },
            originalUserData: null,
            originalTrainerData: null,
            nuevaEspecialidad: '',
            diasSemana: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            minTime: "06:00",
            maxTime: "22:00",
            formulario: {
                disponibilidad: {
                    Lunes: false,
                    Martes: false,
                    Miércoles: false,
                    Jueves: false,
                    Viernes: false,
                    Sábado: false,
                    Domingo: false,
                },
                horarios: {
                    Lunes: { start: '', end: '' },
                    Martes: { start: '', end: '' },
                    Miércoles: { start: '', end: '' },
                    Jueves: { start: '', end: '' },
                    Viernes: { start: '', end: '' },
                    Sábado: { start: '', end: '' },
                    Domingo: { start: '', end: '' },
                },
            }
        }
    },
    computed: {
        hasSchedule() {
            return this.user.schedule && Object.keys(this.user.schedule).length > 0 &&
                Object.values(this.user.schedule).some(day => day && day.start);
        }
    },
    methods: {

        cargarEntrenadores() {
            axios.get('/trainer/approved')
                .then(response => {
                    this.entrenadores = response.data.trainers;
                    this.filtroEntrenadores();
                })
                .catch(error => {
                    console.error('Error al cargar entrenadores:', error);
                });
        },

        filtroEntrenadores() {
            // Buscar entrenador con el mismo ID de usuario
            const entrenador = this.entrenadores.find(e => e.user_id === this.user.id);

            if (entrenador) {
                this.trainer = { ...entrenador };
                this.originalTrainerData = JSON.parse(JSON.stringify(entrenador));

                // Asignar propiedades específicas de entrenador
                this.user.categoria = this.trainer.sport_category;

                // Manejar especialidades
                this.user.especialidades = this.trainer.specialties
                    ? this.trainer.specialties.map(s => s.description)
                    : [];

                // Manejar logros
                this.user.achievements = this.trainer.achievements
                    ? [...this.trainer.achievements]
                    : [];

                // Manejar horario - ¡CORRECCIÓN CLAVE AQUÍ!
                this.user.schedule = this.parseSchedule(this.trainer.schedule);
            }
        },

        // Nuevo método para parsear el horario
        parseSchedule(scheduleData) {
            if (!scheduleData) return {};

            let parsedSchedule;
            if (typeof scheduleData === 'string') {
                try {
                    parsedSchedule = JSON.parse(scheduleData);
                } catch (e) {
                    console.error('Error parsing schedule JSON:', e);
                    return {};
                }
            } else {
                parsedSchedule = scheduleData;
            }

            // Convertir estructura del backend a formato frontend
            const transformedSchedule = {};
            for (const dia in parsedSchedule) {
                if (parsedSchedule[dia].available && parsedSchedule[dia].hours) {
                    transformedSchedule[dia] = {
                        start: parsedSchedule[dia].hours.desde,
                        end: parsedSchedule[dia].hours.hasta
                    };
                }
            }

            return transformedSchedule;
        },


        async saveProfile() {
            try {
                // 1. Actualizar datos básicos del usuario
                const userFormData = new FormData();
                userFormData.append('_method', 'PUT');
                userFormData.append('name', this.user.name);
                userFormData.append('email', this.user.email);
                userFormData.append('phone', this.user.phone);
                userFormData.append('location', this.user.location);
                userFormData.append('birthdate', this.user.birthdate);
                userFormData.append('bio', this.user.bio);

                if (this.$refs.avatarInput.files[0]) {
                    userFormData.append('image', this.$refs.avatarInput.files[0]);
                }

                const userResponse = await axios.post(`/user/${this.user.id}`, userFormData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });

                // Actualizar datos básicos del usuario
                this.user = { ...this.user, ...userResponse.data.user };
                sessionStorage.setItem('user', JSON.stringify(this.user));

                // 2. Si es entrenador, actualizar datos específicos
                if (this.user.user_type === 'entrenador' && this.trainer) {
                    // Obtener horario para guardar
                    const scheduleToSave = this.getScheduleToSave();
                    console.log("Horario a guardar:", scheduleToSave);

                    // Convertir a JSON string
                    const scheduleString = JSON.stringify(scheduleToSave);
                    console.log("Horario como string:", scheduleString);

                    const trainerData = {
                        sport_category: this.user.categoria,
                        specialties: this.user.especialidades.map(e => ({ description: e })),
                        achievements: [...this.user.achievements],
                        schedule: scheduleString  // Guardar como string JSON
                    };

                    console.log("Datos a enviar:", trainerData);

                    const trainerResponse = await axios.put(`/trainer/${this.trainer.id}`, trainerData);
                    console.log("Respuesta del servidor:", trainerResponse.data);

                    this.trainer = trainerResponse.data.trainer;

                    // Actualizar el horario en el objeto de usuario
                    this.user.schedule = this.parseSchedule(trainerResponse.data.trainer.schedule);
                }

                this.editMode = false;
                alert('¡Perfil actualizado correctamente!');

            } catch (error) {
                console.error('Error al guardar perfil:', error);
                alert('Error al guardar los cambios. Por favor intenta nuevamente.');
            }
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


        agregarEspecialidad() {
            if (!this.user.especialidades) {
                this.user.especialidades = [];
            }
            this.user.especialidades.push(this.nuevaEspecialidad.trim());
            this.nuevaEspecialidad = '';
        },

        eliminarEspecialidad(index) {
            this.user.especialidades.splice(index, 1);
        },


        // Cargar horario existente en el formulario
        // Cargar horario existente en el formulario
        loadScheduleIntoForm() {
            this.diasSemana.forEach(dia => {
                const diaSchedule = this.user.schedule[dia];

                // Verificar si el día tiene horario definido (start y end)
                const hasSchedule = diaSchedule && diaSchedule.start && diaSchedule.end;

                // Si tiene horario, cargar los tiempos
                if (hasSchedule) {
                    this.formulario.horarios[dia] = {
                        start: diaSchedule.start,
                        end: diaSchedule.end
                    };
                    this.formulario.disponibilidad[dia] = true;
                } else {
                    // Si no tiene horario, limpiar los campos
                    this.formulario.horarios[dia] = { start: '', end: '' };
                }
            });
        },

        // Construir objeto de horario para guardar
        getScheduleToSave() {
            const schedule = {};
            this.diasSemana.forEach(dia => {
                // Crear estructura que espera el backend
                schedule[dia] = {
                    available: this.formulario.disponibilidad[dia],
                    hours: {
                        desde: '',
                        hasta: ''
                    }
                };

                if (this.formulario.disponibilidad[dia]) {
                    const start = this.formulario.horarios[dia].start;
                    const end = this.formulario.horarios[dia].end;

                    // Formatear horas (quitar segundos si existen)
                    schedule[dia].hours.desde = start ? start.substring(0, 5) : '';
                    schedule[dia].hours.hasta = end ? end.substring(0, 5) : '';
                }
            });
            return schedule;
        },




        // Formatear hora para visualización (HH:MM)
        formatTime(time) {
            if (!time) return '';
            // Si el tiempo tiene segundos, los quitamos
            if (time.includes(':') && time.length > 5) {
                return time.substring(0, 5);
            }
            return time;
        },

        // Formatear hora para guardar (asegurar formato correcto)
        formatTimeForSave(time) {
            return this.formatTime(time);
        },

        // Al activar/desactivar el modo edición
        toggleEditMode() {
            this.editMode = !this.editMode;
            if (this.editMode) {
                this.loadScheduleIntoForm();
            }
        },




    },
    mounted() {
        this.user = JSON.parse(sessionStorage.getItem('user'));
        document.title = 'Perfil de ' + this.user.name;

        if (this.user.user_type === 'entrenador') {
            this.cargarEntrenadores();
        }

        // Guardar copia de seguridad para descartar cambios
        this.originalUserData = JSON.parse(JSON.stringify(this.user));
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

/* Estilos mejorados para el horario */
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.schedule-day-card,
.availability-day {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    background: #ffffff;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.schedule-day-card.available {
    border-left: 4px solid #4CAF50;
}

.day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
}

.available-indicator,
.unavailable-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.available-indicator {
    background-color: #4CAF50;
}

.unavailable-indicator {
    background-color: #f44336;
}

.time-slot {
    font-size: 0.95rem;
    color: #555;
}

.time-slot.unavailable {
    color: #999;
    font-style: italic;
}

.time-slots {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 10px;
}

.time-slots .time-slot {
    display: flex;
    flex-direction: column;
}

.time-slots label {
    font-size: 0.85rem;
    margin-bottom: 5px;
    color: #666;
}

.time-slots input[type="time"] {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.no-schedule {
    color: #777;
    font-style: italic;
    text-align: center;
    padding: 20px 0;
}

/* Estilo para días seleccionados en modo edición */
.availability-day {
    border-left: 4px solid #2196F3;
}

/* Transiciones para una mejor experiencia */
.schedule-day-card:hover,
.availability-day:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>