<template>
    <div class="solicitudes-entrenadores-container">


        <!-- Navbar -->
        <Navbar />

        <main class="main-content">
            <div class="header-section">
                <h1 class="page-title">Solicitudes de Entrenadores</h1>

                <div class="filters-section">
                    <select v-model="filtroEstado" class="estado-filter">
                        <option value="todos">Todas las solicitudes</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="aprobado">Aprobadas</option>
                        <option value="rechazado">Rechazadas</option>
                    </select>
                </div>
            </div>

            <div class="solicitudes-list">
                <!-- Solicitud Card -->
                <div v-for="solicitud in solicitudesFiltradas" :key="solicitud.id" class="solicitud-card"
                    :class="solicitud.status">
                    <div class="card-header">
                        <h3 class="entrenador-nombre">{{ solicitud.name }}</h3>
                        <span class="fecha-solicitud">{{ formatFecha(solicitud.created_at) }}</span>
                    </div>

                    <div class="card-body">

                        <div class="info-row">
                            <span class="info-label">Ciudad/Region:</span>
                            <span class="info-value">{{ solicitud.city_country }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Deporte:</span>
                            <span class="info-value">{{ solicitud.sport_category }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Experiencia:</span>
                            <span class="info-value">{{ solicitud.experience }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Logros:</span>
                            <span class="info-value">{{ solicitud.archivements }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Certificaciones:</span>
                            <span class="info-value">{{ solicitud.level_of_certifications }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Costo:</span>
                            <span class="info-value">{{ solicitud.cost }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Contacto:</span>
                            <span class="info-value">{{ solicitud.email }} | {{ solicitud.phone }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Declaración:</span>
                            <p class="declaracion-text">{{ solicitud.certificates_linked }}</p>
                        </div>

                        <div v-if="solicitud.documentos" class="documentos-link">
                            <a :href="solicitud.documentos" target="_blank" class="doc-link">
                                📄 Ver documentos adjuntos
                            </a>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="estado-badge" :class="solicitud.status">
                            {{ formatEstado(solicitud.status) }}
                        </div>

                        <div v-if="solicitud.status === 'pending'">
                            <button @click="aprobarSolicitud(solicitud.id)" class="btn-approve">
                                Aprobar
                            </button>
                            <button @click="rechazarSolicitud(solicitud.id)" class="btn-reject">
                                Rechazar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="solicitudesFiltradas.length === 0" class="empty-state">
                    <img src="/public/imagenes/no-news.png" alt="No hay solicitudes" class="empty-icon">
                    <h3>No hay solicitudes para mostrar</h3>
                    <p v-if="filtroEstado !== 'todos'"> Intenta cambiar los filtros de búsqueda </p>
                    <button v-if="filtroEstado !== 'todos'" @click="filtroEstado = 'todos'" class="btn-clear-filters">
                        Mostrar todas
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
            filtroEstado: 'todos',
            solicitudes: []
        }
    },
    
    computed: {
        solicitudesFiltradas() {
            const estadosMap = {
                'pendiente': 'pending',
                'aprobado': 'approved',
                'rechazado': 'rejected'
            };

            if (this.filtroEstado === 'todos') return this.solicitudes;
            return this.solicitudes.filter(s => s.status === estadosMap[this.filtroEstado]);
        }
    },

    methods: {


        getTrainers() {
            axios.get('/trainer')
                .then(response => {
                    this.solicitudes = response.data.trainers;
                    // this.solicitudes = response.data.map(solicitud => ({
                    //     ...solicitud,
                    //     created_at: solicitud.created_at || new Date().toISOString(),
                    // }));
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

            aprobarSolicitud(id) {
                const solicitud = this.solicitudes.find(s => s.id === id);
                if (solicitud) solicitud.status = 'approved';

                console.log('Aprobando solicitud:', solicitud);
                axios.put(`/update-status/${solicitud.id}`, { status: solicitud.status })
                .then(response => {
                    
                    this.getTrainers(); // Actualizar la lista de solicitudes
                })
                .catch(error => {
                    console.error('Error al aprobar la solicitud:', error);
                    alert('Error al aprobar la solicitud');
                });
            },

        async rechazarSolicitud(id) {
            try {
                await axios.put(`/trainer/${id}/status`, { status: 'rejected' });
                this.actualizarEstadoLocal(id, 'rejected');
            } catch (error) {
                console.error('Error al rechazar:', error);
                alert('Error al rechazar la solicitud');
            }
        },
    },
    mounted() {
        this.getTrainers();
        document.title = 'Solicitudes Entrenadores';
    }
}
</script>

<style scoped>
@import '../../../scss/SolicitudUsuarios/SolicitudU_navbar.scss';


/* Main Content Styles */
.main-content {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 2rem;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 2rem;
    color: #2c3e50;
    margin: 0;
}

.filters-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.estado-filter {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: white;
    font-size: 1rem;
    cursor: pointer;
}

/* Solicitudes List */
.solicitudes-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.solicitud-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.solicitud-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.solicitud-card.pendiente {
    border-left: 4px solid #FFC107;
}

.solicitud-card.aprobado {
    border-left: 4px solid #4CAF50;
}

.solicitud-card.rechazado {
    border-left: 4px solid #F44336;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.entrenador-nombre {
    margin: 0;
    font-size: 1.3rem;
    color: #2c3e50;
}

.fecha-solicitud {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.card-body {
    margin-bottom: 1.5rem;
}

.info-row {
    display: flex;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.info-label {
    font-weight: 600;
    color: #2c3e50;
    min-width: 120px;
}

.info-value {
    color: #34495e;
    flex: 1;
}

.declaracion-text {
    margin: 0.5rem 0 0 0;
    color: #34495e;
    font-style: italic;
    line-height: 1.5;
}

.documentos-link {
    margin-top: 1rem;
}

.doc-link {
    color: #3498db;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.doc-link:hover {
    text-decoration: underline;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.estado-badge {
    padding: 0.35rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.estado-badge.pendiente {
    background-color: #FFF3CD;
    color: #856404;
}

.estado-badge.aprobado {
    background-color: #D4EDDA;
    color: #155724;
}

.estado-badge.rechazado {
    background-color: #F8D7DA;
    color: #721C24;
}

.acciones-buttons {
    display: flex;
    gap: 0.75rem;
}

.btn-approve,
.btn-reject {
    padding: 0.5rem 1.25rem;
    border: none;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-approve {
    background-color: #28a745;
    color: white;
}

.btn-approve:hover {
    background-color: #218838;
}

.btn-reject {
    background-color: #dc3545;
    color: white;
}

.btn-reject:hover {
    background-color: #c82333;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    opacity: 0.7;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #7f8c8d;
    margin-bottom: 1.5rem;
}

.btn-clear-filters {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-clear-filters:hover {
    background-color: #2980b9;
}

/* Responsive Design */
@media (max-width: 768px) {

    .header-section {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-row {
        flex-direction: column;
        gap: 0.25rem;
    }

    .info-label {
        min-width: auto;
    }

    .card-footer {
        flex-direction: column;
        align-items: flex-start;
    }

    .acciones-buttons {
        width: 100%;
    }

    .btn-approve,
    .btn-reject {
        flex: 1;
        text-align: center;
    }
}
</style>