<template>
    <div class="solicitudes-entrenadores-container">
        <!-- Navbar -->
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
                <!-- Solicitud Card -->
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
                                <span class="info-value">{{ solicitud.experience }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Costo:</span>
                                <span class="info-value">{{ formatCurrency(solicitud.cost) }}</span>
                            </div>
                        </div>

                        <div class="details-section">
                            <div class="detail-item">
                                <h4 class="detail-title">Logros</h4>
                                <p class="detail-content">{{ solicitud.achievements || 'No especificado' }}</p>
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
                                <h4 class="detail-title">Declaración Personal</h4>
                                <p class="detail-content">{{ solicitud.certificates_linked || 'No proporcionada' }}</p>
                            </div>
                        </div>

                        <div v-if="solicitud.documentos" class="documentos-section">
                            <a :href="solicitud.documentos" target="_blank" class="doc-link">
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

                <!-- Empty State -->
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
</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';

export default {
    name: 'SolicitudesEntrenadores',
    components: {
        Navbar
    },
    data() {
        return {
            filtroEstado: 'all',
            solicitudes: []
        }
    },
    computed: {
        solicitudesFiltradas() {
            return this.solicitudes;
        }
    },
    methods: {

        getTrainers() {
            const status = this.filtroEstado === 'all' ? null : this.filtroEstado;
            axios.get('/trainer', {
                params: {
                    status: status
                }
            })
                .then(response => {
                    this.solicitudes = response.data.trainers
                        .map(trainer => ({
                            ...trainer,
                            status: trainer.status.toLowerCase()
                        }))
                        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                })
                .catch(error => {
                    console.error('Error al cargar solicitudes:', error);
                    alert('Error al cargar las solicitudes');
                });
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
                axios.put(`/update-status/${solicitud.id}`, { // Cambiar a PUT y usar trainer.id
                    status: 'approved'
                })
                    .then(response => {
                        console.log('Solicitud aprobada:', response.data);
                        // Actualizar estado localmente
                        solicitud.status = 'approved';
                        // Actualizar la lista completa
                        this.getTrainers();
                    })
                    .catch(error => {
                        console.error('Error al aprobar la solicitud:', error);
                        alert('Error al aprobar la solicitud');
                    });
            }
        },

        rechazarSolicitud(id) {
            const solicitud = this.solicitudes.find(s => s.id === id);
            if (solicitud) {
                axios.put(`/update-status/${solicitud.id}`, { // Cambiar a PUT y usar trainer.id
                    status: 'rejected'
                })
                    .then(response => {
                        console.log('Solicitud rechazada:', response.data);
                        // Actualizar estado localmente
                        solicitud.status = 'rejected';
                        // Actualizar la lista completa
                        this.getTrainers();
                    })
                    .catch(error => {
                        console.error('Error al rechazar la solicitud:', error);
                        alert('Error al rechazar la solicitud');
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

/* Base Styles */
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

/* Header Section */
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

/* Filters Section */
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

/* Solicitudes List */
.solicitudes-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 3rem;
}

/* Solicitud Card */
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
    gap: 1.5rem;
    margin-bottom: 1.5rem;
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
</style>