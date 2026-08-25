<template>
    <div class="solicitudes-entrenadores-container">
        <Navbar />

        <main class="main-content">
            <div class="header-section">
                <div class="title-wrapper">
                    <h1 class="page-title">Solicitudes de Entrenadores</h1>
                    <p class="page-subtitle">Administra las solicitudes de los entrenadores</p>
                </div>

                <div class="filters-section">
                    <div class="select-wrapper">
                        <select v-model="filtroEstado" class="estado-filter" @change="getTrainers">
                            <option value="all">Todas las solicitudes</option>
                            <option value="pending">Pendientes</option>
                            <option value="approved">Aprobadas</option>
                            <option value="rejected">Rechazadas</option>
                        </select>
                        <span class="select-arrow">▼</span>
                    </div>
                </div>
            </div>

            <div class="solicitudes-list">
                <div v-for="solicitud in solicitudesFiltradas" :key="solicitud.id" class="solicitud-card"
                    :class="solicitud.status">
                    <div class="card-header">
                        <div class="user-avatar">
                            <span>{{ getInitials(solicitud.name) }}</span>
                        </div>
                        <div class="user-info">
                            <h3 class="entrenador-nombre">{{ solicitud.name }}</h3>
                            <span class="fecha-solicitud">{{ formatFecha(solicitud.created_at) }}</span>
                        </div>
                        <div class="estado-badge" :class="solicitud.status">
                            {{ formatEstado(solicitud.status) }}
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value">{{ solicitud.city_country }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Deporte:</span>
                                <span class="info-value">{{ solicitud.sport_category }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Experiencia:</span>
                                <span class="info-value">{{ solicitud.experience }} años</span>
                            </div>

                            <!-- <div class="info-item">
                                <span class="info-label">Costo:</span>
                                <span class="info-value">{{ formatCurrency(solicitud.cost) }}</span>
                            </div> -->
                        </div>

                        <div class="details-section">

                            <div class="detail-item full-width">
                                <h4 class="detail-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        viewBox="0 0 16 16" class="icon">
                                        <path
                                            d="M2.5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2h-11zm4.5 3h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1 0-1zM4 5.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-1zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-7z" />
                                    </svg>
                                    Logros
                                </h4>

                                <div v-if="solicitud.achievements && solicitud.achievements.length > 0"
                                    class="achievements-grid">
                                    <div v-for="(logro, index) in solicitud.achievements" :key="index"
                                        class="achievement-card">
                                        <div class="achievement-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="currentColor" viewBox="0 0 16 16">
                                                <path
                                                    d="M9.673 5.933v1.938h1.033c.66 0 1.068-.316 1.068-.95 0-.64-.422-.988-1.05-.988h-1.05z" />
                                                <path
                                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm5.937 7 1.99-5.999H6.61L5.277 9.708H5.22L3.875 5.001H2.5L4.508 11h1.429zM8.5 5.001V11h1.173V8.763h1.064L11.787 11h1.327L11.91 8.583C12.455 8.373 13 7.779 13 6.9c0-1.147-.773-1.9-2.105-1.9H8.5z" />
                                            </svg>
                                        </div>
                                        <div class="achievement-content">
                                            <h5 class="achievement-title">{{ logro.title }}</h5>
                                            <p class="achievement-desc">{{ logro.description }}</p>
                                            <div v-if="logro.date" class="achievement-date">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z" />
                                                    <path
                                                        d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4zM8 8a.5.5 0 0 1 .5.5V10H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V11H6a.5.5 0 0 1 0-1h1.5V8.5A.5.5 0 0 1 8 8z" />
                                                </svg>
                                                {{ formatDate(logro.date) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="no-items">No se han registrado logros</p>
                            </div>

                            <div class="detail-item">
                                <h4 class="detail-title">Certificaciones</h4>
                                <p class="detail-content">{{ solicitud.level_of_certification || 'No especificado' }}
                                </p>
                            </div>

                            <div class="detail-item">
                                <h4 class="detail-title">Contacto</h4>
                                <p class="detail-content">
                                    <a :href="`mailto:${solicitud.email}`">{{ solicitud.email }}</a><br>
                                    {{ solicitud.phone || 'Sin teléfono' }}
                                </p>
                            </div>

                            <div class="detail-item full-width">
                                <h4 class="detail-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        viewBox="0 0 16 16" class="icon">
                                        <path
                                            d="M9.5 2.672a.5.5 0 1 0 1 0V.843a.5.5 0 0 0-1 0v1.829Zm4.5.035A.5.5 0 0 0 13.293 2L12 3.293a.5.5 0 1 0 .707.707L14 2.707ZM7.293 4A.5.5 0 1 0 8 3.293L6.707 2A.5.5 0 0 0 6 2.707L7.293 4Zm-.621 2.5a.5.5 0 1 0 0-1H4.843a.5.5 0 1 0 0 1h1.829Zm8.485 0a.5.5 0 1 0 0-1h-1.829a.5.5 0 0 0 0 1h1.829ZM13.293 10A.5.5 0 1 0 14 9.293L12.707 8a.5.5 0 1 0-.707.707L13.293 10ZM9.5 11.157a.5.5 0 0 0 1 0V9.328a.5.5 0 0 0-1 0v1.829Zm-5.172-2a.5.5 0 0 0-.707 0L2 9.293a.5.5 0 1 0 .707.707L4.328 9.12a.5.5 0 0 0 0-.707ZM8 10a.5.5 0 0 0 0 1h1.829a.5.5 0 1 0 0-1H8Z" />
                                        <path
                                            d="M14 6.5v3a3.5 3.5 0 0 1-3.5 3.5H6A4.5 4.5 0 0 1 1.5 9h1A3.5 3.5 0 0 0 6 12.5h4.5a2.5 2.5 0 0 0 2.5-2.5V6.5a2.5 2.5 0 0 0-2.5-2.5H6A3.5 3.5 0 0 0 2.5 7h-1A4.5 4.5 0 0 1 6 2.5h4.5A3.5 3.5 0 0 1 14 6.5Zm-5.5 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                    </svg>
                                    Especialidades
                                </h4>

                                <div v-if="solicitud.specialties && solicitud.specialties.length > 0"
                                    class="specialties-container">
                                    <span v-for="(especialidad, index) in solicitud.specialties" :key="index"
                                        class="specialty-badge">
                                        {{ especialidad.description }}
                                    </span>
                                </div>
                                <p v-else class="no-items">No se han registrado especialidades</p>
                            </div>

                        </div>

                        <div v-if="solicitud.documentos" class="documentos-section">
                            <a :href="solicitud.documentos" target="_blank" rel="noopener noreferrer" class="doc-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z" />
                                    <path
                                        d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                </svg>
                                Ver documentos adjuntos
                            </a>
                        </div>
                    </div>

                    <div v-if="solicitud.status === 'pending'" class="card-actions">
                        <button @click="aprobarSolicitud(solicitud.id)" class="btn-action btn-approve">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z" />
                            </svg>
                            Aprobar
                        </button>
                        <button @click="rechazarSolicitud(solicitud.id)" class="btn-action btn-reject">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                            </svg>
                            Rechazar
                        </button>
                    </div>
                </div>

                <div v-if="solicitudesFiltradas.length === 0" class="empty-state">
                    <div class="empty-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                            <path
                                d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                        </svg>
                    </div>
                    <h3>No hay solicitudes para mostrar</h3>
                    <p v-if="filtroEstado !== 'todos'">No se encontraron solicitudes con el filtro actual</p>
                    <button v-if="filtroEstado !== 'todos'" @click="filtroEstado = 'todos'" class="btn-clear-filters">
                        Mostrar todas las solicitudes
                    </button>
                </div>
            </div>
        </main>
    </div>
    
    <Alert
        v-if="openModal"
        :key="alertKey"
        :message="alertMessage"
        :type="alertType"
        @closed="openModal = null"
    />
</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import Alert from '../Alert.vue';

export default {
    name: 'SolicitudesEntrenadores',
    components: {
        Navbar,
        Alert
    },
    data() {
        return {
            filtroEstado: 'all',
            solicitudes: [],
            openModal: false,
            alertMessage: "",
            alertType: "", // 'error', 'success', 'alert'.
            alertKey: 0,
        }
    },
    computed: {
        solicitudesFiltradas() {
            return this.solicitudes;
        }
    },
    methods: {

        async getTrainers() {
            const status = this.filtroEstado === 'all' ? null : this.filtroEstado;
            try {
                const response = await axios.get('/trainer', {
                    params: { status: status }
                });

                const lista = response.data.trainer || [];

                this.solicitudes = lista.map(trainer => ({
                    ...trainer,
                    status: trainer.status.toLowerCase(),
                    achievements: Array.isArray(trainer.achievements)
                        ? trainer.achievements
                        : [],
                    specialties: Array.isArray(trainer.specialties)
                        ? trainer.specialties
                        : []
                })).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } catch (error) {
                console.error('Error al cargar solicitudes:', error);

                this.alertType = "error";
                this.alertMessage = "Error al cargar las solicitudes";
                this.alertKey++;
                this.openModal = true;
                
            }
        },

        formatFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },

        formatEstado(status) {
            const estados = {
                pending: 'Pendiente',
                approved: 'Aprobado',
                rejected: 'Rechazado'
            };
            return estados[status] || 'Desconocido';
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount || 0);
        },

        getInitials(name) {
            if (!name) return 'NN';
            const parts = name.split(' ');
            return parts.length >= 2
                ? `${parts[0][0]}${parts[1][0]}`.toUpperCase()
                : name.substring(0, 2).toUpperCase();
        },

        aprobarSolicitud(id) {
            const solicitud = this.solicitudes.find(s => s.id === id);
            if (solicitud) {
                axios.put(`/update-status/${solicitud.id}`, {
                    status: 'approved'
                })
                    .then(response => {
                        console.log('Solicitud aprobada:', response.data);
                        solicitud.status = 'approved';
                        this.getTrainers();
                    })
                    .catch(error => {
                        console.error('Error al aprobar la solicitud:', error);
                        this.alertType = "error";
                        this.alertMessage = "Error al aprobar la solicitud";
                        this.alertKey++;
                        this.openModal = true;
                        
                    });
            }
        },

        rechazarSolicitud(id) {
            const solicitud = this.solicitudes.find(s => s.id === id);
            if (solicitud) {
                axios.put(`/update-status/${solicitud.id}`, {
                    status: 'rejected'
                })
                    .then(response => {
                        console.log('Solicitud rechazada:', response.data);
                        solicitud.status = 'rejected';
                        this.getTrainers();
                    })
                    .catch(error => {
                        console.error('Error al rechazar la solicitud:', error);
                        
                        this.alertType = "error";
                        this.alertMessage = "Error al rechazar la solicitud";
                        this.alertKey++;
                        this.openModal = true;
                    });
            }
        },

    },
    mounted() {
        this.getTrainers();
        document.title = 'Solicitudes Entrenadores';
        document.body.style.backgroundColor = '#f8f9fa';
        document.body.style.paddingBottom = '10px';
    },
    beforeUnmount() {
        document.body.style.backgroundColor = '';
        document.body.style.paddingBottom = '';
    }
}
</script>

<style scoped>
@import '../../../scss/SolicitudUsuarios/SolicitudU_navbar.scss';

:root {
    --primary-color: #4361ee;
    --success-color: #4cc9f0;
    --danger-color: #f72585;
    --warning-color: #f8961e;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --gray-color: #6c757d;
    --border-radius: 8px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

.solicitudes-entrenadores-container {
    min-height: 100vh;
    background-color: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.main-content {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.title-wrapper {
    flex: 1;
    min-width: 300px;
}

.page-title {
    font-size: 2rem;
    color: var(--dark-color);
    margin: 0;
    font-weight: 700;
    line-height: 1.2;
}

.page-subtitle {
    color: var(--gray-color);
    margin: 0.5rem 0 0 0;
    font-size: 1rem;
}

.filters-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.select-wrapper {
    position: relative;
    min-width: 220px;
}

.estado-filter {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    border: 1px solid #e0e0e0;
    background-color: white;
    font-size: 0.95rem;
    cursor: pointer;
    appearance: none;
    transition: var(--transition);
    box-shadow: var(--box-shadow);
    color: var(--dark-color);
}

.estado-filter:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

.select-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--gray-color);
    font-size: 0.8rem;
}

.solicitudes-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.solicitud-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    transition: var(--transition);
}

.solicitud-card:hover {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.solicitud-card.pending {
    border-top: 3px solid var(--warning-color);
}

.solicitud-card.approved {
    border-top: 3px solid var(--success-color);
}

.solicitud-card.rejected {
    border-top: 3px solid var(--danger-color);
}

/* Card Header */
.card-header {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    gap: 1rem;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: rgb(0, 0, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.user-info {
    flex: 1;
    min-width: 200px;
}

.entrenador-nombre {
    margin: 0;
    font-size: 1.25rem;
    color: var(--dark-color);
    font-weight: 600;
}

.fecha-solicitud {
    font-size: 0.85rem;
    color: var(--gray-color);
    display: block;
    margin-top: 0.25rem;
}

.estado-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.estado-badge.pending {
    background-color: rgba(248, 150, 30, 0.1);
    color: var(--warning-color);
}

.estado-badge.approved {
    background-color: rgba(76, 201, 240, 0.1);
    color: var(--success-color);
}

.estado-badge.rejected {
    background-color: rgba(247, 37, 133, 0.1);
    color: var(--danger-color);
}

/* Card Body */
.card-body {
    padding: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 7rem;
    margin-bottom: 2rem;
    margin-left: 90px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.85rem;
    color: var(--gray-color);
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.info-value {
    font-size: 1rem;
    color: var(--dark-color);
    font-weight: 500;
}

.details-section {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.detail-item {
    padding: 1rem;
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-title {
    margin: 0 0 0.75rem 0;
    font-size: 0.95rem;
    color: var(--primary-color);
    font-weight: 600;
}

.detail-content {
    margin: 0;
    font-size: 0.95rem;
    color: var(--dark-color);
    line-height: 1.5;
}

.documentos-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f0f0f0;
}

.doc-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.doc-link:hover {
    color: #2a4bd6;
    text-decoration: underline;
}

/* Card Actions */
.card-actions {
    display: flex;
    padding: 1rem 1.5rem;
    background-color: #f9f9f9;
    border-top: 1px solid #f0f0f0;
    gap: 1rem;
}

.btn-action {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-approve {
    background-color: #3ab7d8;
    color: white;
}

.btn-approve:hover {
    background-color: #0a6179;
}

.btn-reject {
    background-color: #e5177a;
    color: white;
}

.btn-reject:hover {
    background-color: #7b0f43;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-top: 2rem;
}

.empty-icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-color);
    opacity: 0.7;
}

.empty-state h3 {
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.empty-state p {
    color: var(--gray-color);
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-clear-filters {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
}

.btn-clear-filters:hover {
    background-color: #2a4bd6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: stretch;
    }

    .select-wrapper {
        width: 100%;
    }

    .info-grid {
        grid-template-columns: 1fr 1fr;
    }

    .details-section {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 0 1rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .card-actions {
        flex-direction: column;
    }
}

.logros-list,
.especialidades-list {
    padding-left: 1.5rem;
    margin: 0;
}

.logros-list li,
.especialidades-list li {
    margin-bottom: 0.5rem;
    line-height: 1.4;
}







.detail-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}

.icon {
    margin-right: 8px;
}

.achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.achievement-card {
    display: flex;
    background: #ffffff;
    border: 1px solid #eaeaea;
    border-radius: 10px;
    padding: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.achievement-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.achievement-icon {
    margin-right: 16px;
    color: #000000;
    display: flex;
    align-items: center;
}

.achievement-icon svg {
    width: 24px;
    height: 24px;
}

.achievement-content {
    flex: 1;
}

.achievement-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: #2c3e50;
}

.achievement-desc {
    font-size: 0.9rem;
    color: #555;
    margin: 0 0 8px 0;
    line-height: 1.4;
}

.achievement-date {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: #7a7a7a;
}

.achievement-date svg {
    width: 14px;
    height: 14px;
    opacity: 0.7;
}

.specialties-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.specialty-badge {
    background: linear-gradient(135deg, #565656, #000000);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(56, 56, 56, 0.2);
    display: inline-flex;
    align-items: center;
}

.no-items {
    color: #7a7a7a;
    font-style: italic;
    padding: 8px 0;
    margin: 0;
}

.detail-item {
    margin-bottom: 24px;
}

.detail-item:last-child {
    margin-bottom: 0;
}
</style>