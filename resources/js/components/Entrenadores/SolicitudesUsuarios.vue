<template>
    <div class="solicitudes-container">
        <!-- Navbar -->
        <Navbar />

        <main class="solicitudes-main">
            <header class="page-header">
                <h1 class="title">Solicitudes de Entrenamiento</h1>

                <div class="filters-container">
                    <select v-model="filtroEstado" class="filter-select" @change="actualizarFiltros">
                        <option value="todos">Todas las solicitudes</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="aprobado">Aprobadas</option>
                        <option value="rechazado">Rechazadas</option>
                    </select>

                    <button class="btn-refresh" @click="cargarSolicitudes" aria-label="Recargar solicitudes">
                        🔄
                    </button>
                </div>
            </header>

            <section class="solicitudes-grid">
                <transition-group name="list" tag="div" class="solicitudes-list">
                    <article v-for="solicitud in solicitudesFiltradas" :key="solicitud.id" class="solicitud-card"
                        :class="solicitud.estado">
                        <div class="card-header">
                            <div class="user-info">
                                <div class="avatar" :style="{ backgroundColor: getAvatarColor(solicitud.userName) }">
                                    {{ getInitials(solicitud.userName) }}
                                </div>
                                <div>
                                    <h3 class="user-name">{{ solicitud.userName }}</h3>
                                    <p class="user-age">Edad: {{ solicitud.edad }}</p>
                                </div>
                            </div>
                            <time class="request-date">
                                {{ formatFecha(solicitud.fechaSolicitud) }}
                            </time>
                        </div>

                        <div class="card-body">
                            <div class="sport-info">
                                <span class="sport-tag">{{ solicitud.My_level }}</span>
                                <div class="contact-info">
                                    <a :href="`mailto:${solicitud.email}`" class="email-link">
                                        📧 {{ solicitud.email }}
                                    </a>
                                    <a :href="`tel:${solicitud.telefono}`" class="phone-link">
                                        📱 {{ solicitud.telefono }}
                                    </a>
                                </div>
                            </div>

                            <div class="message-container">
                                <p class="user-message">"{{ solicitud.mensaje }}"</p>
                            </div>
                        </div>

                        <div class="card-actions">
                            <span class="status-badge" :class="solicitud.estado">
                                {{ getEstadoTexto(solicitud.estado) }}
                            </span>

                            <div v-if="solicitud.estado === 'pendiente'" class="action-buttons">
                                <button @click="manejarAccion(solicitud.id, 'aprobado')" class="btn-success"
                                    title="Aprobar solicitud">
                                    ✅ Aceptar
                                </button>
                                <button @click="manejarAccion(solicitud.id, 'rechazado')" class="btn-danger"
                                    title="Rechazar solicitud">
                                    ❌ Rechazar
                                </button>
                            </div>
                        </div>
                    </article>
                </transition-group>

                <div v-if="isLoading" class="loading-state">
                    <div class="spinner"></div>
                    <p>Cargando solicitudes...</p>
                </div>

                <div v-else-if="!solicitudes.length" class="empty-state">
                    <img src="/imagenes/no-news.png" alt="No hay solicitudes" class="empty-image">
                    <h3>No hay solicitudes disponibles</h3>
                    <p>Actualmente no hay solicitudes de entrenamiento para mostrar.</p>
                </div>

                <div v-else-if="!solicitudesFiltradas.length" class="empty-filter-state">
                    <img src="/imagenes/no-news.png" alt="No hay resultados" class="empty-image">
                    <h3>No hay coincidencias</h3>
                    <p>No se encontraron solicitudes con el filtro aplicado.</p>
                    <button @click="filtroEstado = 'todos'" class="btn-clear-filter">Limpiar filtros</button>
                </div>
            </section>
        </main>

        <transition name="fade">
            <div v-if="mostrarToast" class="toast-message">
                {{ toastMessage }}
            </div>
        </transition>
    </div>
</template>

<script>
import Navbar from '../navbarComponent.vue';
import axios from 'axios';

export default {
    name: 'SolicitudesEntrenamientos',
    components: {
        Navbar
    },
    data() {
        return {
            filtroEstado: 'todos',
            solicitudes: [],
            isLoading: true,
            mostrarToast: false,
            toastMessage: '',
            colors: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8'],
            user: null
        }
    },
    computed: {
        solicitudesFiltradas() {
            return this.solicitudes
                .filter(s => this.filtroEstado === 'todos' || s.estado === this.filtroEstado)
                .sort((a, b) => new Date(b.fechaSolicitud) - new Date(a.fechaSolicitud))
        }
    },
    methods: {

        async cargarSolicitudes() {
            this.isLoading = true;
            try {
                if (!this.user) {
                    throw new Error("Usuario no autenticado");
                }

                const trainerId = this.user.id;
                console.log("Buscando solicitudes para trainer_id:", trainerId);

                const response = await axios.get('/training-request', {
                    params: { trainer_id: trainerId }
                });

                console.log("Respuesta completa de la API:", response);

                // Verifica la estructura de los datos
                if (Array.isArray(response.data)) {
                    console.log("Número de solicitudes recibidas:", response.data.length);
                } else {
                    console.error("La respuesta no es un array:", typeof response.data);
                }

                // ... resto del código ...
            } catch (error) {
                console.error('Error completo:', error);
                console.error('Respuesta de error:', error.response);
                this.mostrarNotificacion('Error: ' + (error.response?.data?.message || error.message));
            } finally {
                this.isLoading = false;
            }
        },

        mapStatus(status) {
            const statusMap = {
                'pending': 'pendiente',
                'accepted': 'aprobado',
                'rejected': 'rechazado'
            };
            return statusMap[status] || 'pendiente';
        },

        async manejarAccion(id, accion) {
            try {
                const statusMap = {
                    'aprobado': 'accepted',
                    'rechazado': 'rejected'
                };
                const apiStatus = statusMap[accion];

                const response = await axios.put(`/training/${id}`, {
                    status: apiStatus
                });

                // Actualizar el estado localmente
                const index = this.solicitudes.findIndex(s => s.id === id);
                if (index !== -1) {
                    this.solicitudes[index].estado = accion;
                }

                this.mostrarNotificacion(`Solicitud ${accion} correctamente`);
            } catch (error) {
                console.error('Error actualizando estado:', error);
                this.mostrarNotificacion('Error: ' + error.response?.data?.message || error.message);
            }
        },

        // async crearChat(training) {
        //     try {
        //         const chatData = {
        //             user_id: training.user_id,
        //             trainer_id: training.trainer_id,
        //             training_id: training.id
        //         };

        //         const response = await axios.post('/chats', chatData);
        //         console.log('Chat creado:', response.data);
        //         this.mostrarNotificacion('Chat creado con éxito');
        //     } catch (error) {
        //         console.error('Error creando chat:', error);

        //         if (error.response?.status === 409) {
        //             this.mostrarNotificacion('Ya existe un chat para esta solicitud');
        //         } else {
        //             this.mostrarNotificacion('Error creando chat: ' + error.message);
        //         }
        //     }
        // },

        mostrarNotificacion(mensaje) {
            this.toastMessage = mensaje;
            this.mostrarToast = true;
            setTimeout(() => this.mostrarToast = false, 3000);
        },

        getInitials(nombre) {
            return nombre.split(' ').map(p => p[0]).join('').toUpperCase().substring(0, 2);
        },

        getAvatarColor(nombre) {
            const hash = nombre.split('').reduce((acc, char) => char.charCodeAt(0) + acc, 0);
            return this.colors[hash % this.colors.length];
        },

        formatFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getEstadoTexto(estado) {
            const estados = {
                pendiente: 'pendiente',
                aprobado: 'aprobado',
                rechazado: 'rechazado'
            };
            return estados[estado];
        },

        actualizarFiltros() {
            // Puedes añadir lógica adicional aquí si necesitas
        }
    },
    async mounted() {
        const userData = sessionStorage.getItem('user');
        if (userData) {
            this.user = JSON.parse(userData);
            console.log("Usuario cargado:", this.user);

            // Verificar si es entrenador
            if (this.user.user_type !== 'entrenador') {
                this.mostrarNotificacion("Solo los entrenadores pueden ver solicitudes");
                return;
            }
        } else {
            console.error("No se encontró usuario en sessionStorage");
            this.mostrarNotificacion("Debes iniciar sesión");
        }

        await this.cargarSolicitudes();
        document.title = 'Solicitudes Usuarios';
    }
}
</script>

<style scoped>
@import '../../../scss/SolicitudUsuarios/SolicitudU.scss';
@import '../../../scss/SolicitudUsuarios/SolicitudU_navbar.scss';

/* Estilos adicionales */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    text-align: center;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(0, 0, 0, 0.1);
    border-left-color: #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.toast-message {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 15px 25px;
    border-radius: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    animation: fadeInOut 3s ease;
}

@keyframes fadeInOut {
    0% {
        opacity: 0;
        bottom: 0;
    }

    10% {
        opacity: 1;
        bottom: 20px;
    }

    90% {
        opacity: 1;
        bottom: 20px;
    }

    100% {
        opacity: 0;
        bottom: 0;
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>