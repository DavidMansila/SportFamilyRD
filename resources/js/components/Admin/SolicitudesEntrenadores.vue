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
                        <label class="visually-hidden" for="filtro-estado">Filtrar por estado</label>
                        <select id="filtro-estado" v-model="filtroEstado" class="estado-filter" @change="getTrainers">
                            <option value="all">Todas las solicitudes</option>
                            <option value="pending">Pendientes</option>
                            <option value="approved">Aprobadas</option>
                            <option value="rejected">Rechazadas</option>
                        </select>
                        <span class="select-arrow" aria-hidden="true">▼</span>
                    </div>
                </div>
            </div>

            <!-- Resumen por estado. Sirve de filtro rápido y, sobre todo, deja
                 ver de un vistazo cuántas solicitudes quedan por resolver:
                 antes había que ir cambiando el desplegable para saberlo. -->
            <div class="admin-stats">
                <button type="button" class="admin-stat" :class="{ 'is-active': filtroEstado === 'all' }"
                    @click="aplicarFiltro('all')">
                    <span class="admin-stat__dot admin-stat__dot--all"></span>
                    <span>
                        <span class="admin-stat__value">{{ conteos.all }}</span>
                        <span class="admin-stat__label">En total</span>
                    </span>
                </button>
                <button type="button" class="admin-stat" :class="{ 'is-active': filtroEstado === 'pending' }"
                    @click="aplicarFiltro('pending')">
                    <span class="admin-stat__dot admin-stat__dot--pending"></span>
                    <span>
                        <span class="admin-stat__value">{{ conteos.pending }}</span>
                        <span class="admin-stat__label">Pendientes</span>
                    </span>
                </button>
                <button type="button" class="admin-stat" :class="{ 'is-active': filtroEstado === 'approved' }"
                    @click="aplicarFiltro('approved')">
                    <span class="admin-stat__dot admin-stat__dot--approved"></span>
                    <span>
                        <span class="admin-stat__value">{{ conteos.approved }}</span>
                        <span class="admin-stat__label">Aprobadas</span>
                    </span>
                </button>
                <button type="button" class="admin-stat" :class="{ 'is-active': filtroEstado === 'rejected' }"
                    @click="aplicarFiltro('rejected')">
                    <span class="admin-stat__dot admin-stat__dot--rejected"></span>
                    <span>
                        <span class="admin-stat__value">{{ conteos.rejected }}</span>
                        <span class="admin-stat__label">Rechazadas</span>
                    </span>
                </button>
            </div>

            <!-- Esqueleto de carga: antes la lista aparecía vacía mientras
                 llegaba la respuesta, y parecía que no había solicitudes. -->
            <div v-if="cargando" class="admin-skeleton" aria-hidden="true">
                <div class="admin-skeleton__card" v-for="n in 3" :key="n"></div>
            </div>

            <div class="solicitudes-list" v-else>
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
                        <button @click="pedirConfirmacionRechazo(solicitud)" class="btn-action btn-reject"
                            :disabled="procesando === solicitud.id">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16" aria-hidden="true">
                                <path
                                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                            </svg>
                            Rechazar
                        </button>
                        <button @click="aprobarSolicitud(solicitud.id)" class="btn-action btn-approve"
                            :disabled="procesando === solicitud.id">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z" />
                            </svg>
                            Aprobar
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
                    <p v-if="filtroEstado !== 'all'">No se encontraron solicitudes con el filtro actual</p>
                    <p v-else>Cuando alguien solicite ser entrenador, aparecerá aquí.</p>
                    <button v-if="filtroEstado !== 'all'" @click="aplicarFiltro('all')" class="btn-clear-filters">
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

    <!-- Rechazar le cierra la puerta a alguien y manda un correo: merece una
         confirmacion, igual que los borrados del resto de la aplicacion. -->
    <ConfirmDialog :open="!!solicitudARechazar" :busy="procesando === solicitudARechazar?.id"
        title="Rechazar solicitud" confirm-label="Sí, rechazar" @cancel="solicitudARechazar = null"
        @confirm="rechazarSolicitud">
        Se va a rechazar la solicitud de <strong>{{ solicitudARechazar?.name }}</strong>.
        Se le enviará un correo avisándole.
    </ConfirmDialog>
</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import Alert from '../Alert.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';

export default {
    name: 'SolicitudesEntrenadores',
    components: {
        Navbar,
        Alert,
        ConfirmDialog
    },
    data() {
        return {
            filtroEstado: 'all',
            solicitudes: [],
            // Conteo por estado para el resumen de arriba. Se pide aparte del
            // listado filtrado, porque si no, al filtrar por "Pendientes" los
            // contadores del resto se quedarian en cero.
            conteos: { all: 0, pending: 0, approved: 0, rejected: 0 },
            cargando: true,
            // id de la solicitud que se esta aprobando o rechazando, para
            // desactivar sus botones y que no se pulse dos veces.
            procesando: null,
            solicitudARechazar: null,
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

        aplicarFiltro(estado) {
            if (this.filtroEstado === estado) return;
            this.filtroEstado = estado;
            this.getTrainers();
        },

        // Los contadores se calculan sobre TODAS las solicitudes, no sobre las
        // que se estan mostrando: si no, al filtrar por un estado los demas
        // contadores caerian a cero.
        async actualizarConteos() {
            try {
                const { data } = await axios.get('/trainer');
                const lista = data.trainer || [];

                const conteos = { all: lista.length, pending: 0, approved: 0, rejected: 0 };
                lista.forEach(t => {
                    const estado = String(t.status || '').toLowerCase();
                    if (estado in conteos) conteos[estado]++;
                });
                this.conteos = conteos;
            } catch (error) {
                console.error('Error al contar solicitudes:', error);
            }
        },

        async getTrainers() {
            const status = this.filtroEstado === 'all' ? null : this.filtroEstado;
            this.cargando = true;
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

                this.actualizarConteos();
            } catch (error) {
                console.error('Error al cargar solicitudes:', error);

                this.alertType = "error";
                this.alertMessage = "Error al cargar las solicitudes";
                this.alertKey++;
                this.openModal = true;
            } finally {
                this.cargando = false;
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

        pedirConfirmacionRechazo(solicitud) {
            this.solicitudARechazar = solicitud;
        },

        // Aprobar y rechazar hacian exactamente lo mismo salvo por el estado y
        // el texto, duplicado linea por linea. Ahora comparten este metodo.
        async cambiarEstado(solicitud, estado) {
            if (!solicitud || this.procesando) return;

            this.procesando = solicitud.id;
            const aprobada = estado === 'approved';

            try {
                await axios.put(`/update-status/${solicitud.id}`, { status: estado });

                solicitud.status = estado;
                this.solicitudARechazar = null;
                await this.getTrainers();

                this.alertType = 'success';
                this.alertMessage = aprobada
                    ? `Solicitud de ${solicitud.name} aprobada`
                    : `Solicitud de ${solicitud.name} rechazada`;
                this.alertKey++;
                this.openModal = true;
            } catch (error) {
                console.error(`Error al ${aprobada ? 'aprobar' : 'rechazar'} la solicitud:`, error);

                this.alertType = 'error';
                this.alertMessage = aprobada
                    ? 'No se pudo aprobar la solicitud. Inténtalo de nuevo.'
                    : 'No se pudo rechazar la solicitud. Inténtalo de nuevo.';
                this.alertKey++;
                this.openModal = true;
            } finally {
                this.procesando = null;
            }
        },

        aprobarSolicitud(id) {
            return this.cambiarEstado(this.solicitudes.find(s => s.id === id), 'approved');
        },

        rechazarSolicitud() {
            return this.cambiarEstado(this.solicitudARechazar, 'rejected');
        },

    },
    mounted() {
        this.getTrainers();
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

/* Toda la hoja de estilos de esta pagina vive ahora en
   scss/Admin/Admin_Entrenadores.scss. Antes estaban aqui, encabezados por un
   bloque :root con una paleta propia que Vue convertia en
   "[data-v-...]:root" al aplicarle el ambito, por lo que nunca llegaba a
   aplicarse: la pagina se veia sin sombras, sin bordes redondeados y con los
   botones de aprobar/rechazar transparentes. */
@import '../../../scss/Admin/Admin_Entrenadores.scss';
</style>