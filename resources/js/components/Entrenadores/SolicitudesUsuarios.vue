<template>
    <div class="solicitudes-container">



    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <router-link to="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo" />
        </router-link>
      </div>

      <div class="nav-links">
        <!-- Secciones para usuarios -->
        <router-link to="/noticias" class="nav-link">Noticias</router-link>
        <router-link to="/calendario" class="nav-link">Calendario</router-link>
        <router-link to="/tienda" class="nav-link">Tienda</router-link>
        <router-link to="/entrenadores" class="nav-link">Entrenadores</router-link>
        <router-link to="/foro" class="nav-link">Foro</router-link>

        <!-- Secciones condicionales -->
        <router-link v-if="user_type == 'entrenador'" to="/solicitudes-usuarios" class="nav-link">
          Solicitudes
        </router-link>

        <router-link v-if="user_type == 'admin'" to="/solicitudes-entrenadores" class="nav-link">
          Solicitudes
        </router-link>
      </div>

      <div class="Imagenes">
        <router-link to="/carrito" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon" />
        </router-link>

        <router-link to="/ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" />
        </router-link>

        <router-link to="/perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
        </router-link>

        <router-link :to="login ? '/login' : '/logout'" class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
        </router-link>
      </div>
    </nav>



        <main class="solicitudes-container">
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
                <transition-group name="list" tag="div">
                    <article v-for="solicitud in solicitudesFiltradas" :key="solicitud.id" class="solicitud-card"
                        :class="solicitud.estado">
                        <div class="card-header">
                            <div class="user-info">
                                <avatar :initials="getInitials(solicitud.userName)" />
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
                                <span class="sport-tag">{{ solicitud.deporte }}</span>
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
                            <status-badge :estado="solicitud.estado" />

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

                <empty-state v-if="!solicitudes.length" />
                <empty-filter-state v-else-if="!solicitudesFiltradas.length" />
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
export default {
    data() {
        return {
            filtroEstado: 'todos',
            solicitudes: [
                {
                    id: 1,
                    userName: "Juan Pérez",
                    edad: 25,
                    deporte: "Baloncesto",
                    email: "juan@example.com",
                    telefono: "809-555-1234",
                    mensaje: "Busco entrenamiento para mejorar mi tiro de 3 puntos",
                    fechaSolicitud: new Date(),
                    estado: "pendiente"
                },
                {
                    id: 2,
                    userName: "María García",
                    edad: 18,
                    deporte: "Voleibol",
                    email: "maria@example.com",
                    telefono: "829-555-5678",
                    mensaje: "Quiero prepararme para pruebas universitarias",
                    fechaSolicitud: new Date('2024-02-15'),
                    estado: "aprobado"
                }
            ],

            user_type: 'entrenador',
            
            filtroEstado: 'todos',

            mostrarToast: false,

            toastMessage: '',
        }
    },

    computed: {
        solicitudesFiltradas() {
            return this.solicitudes
                .filter(s => this.filtroEstado === 'todos' || s.estado === this.filtroEstado)
                .sort((a, b) => new Date(b.fechaSolicitud) - new Date(a.fechaSolicitud))
        }
    },

    async created() {
        await this.cargarSolicitudes()
    },

    methods: {
        async cargarSolicitudes() {
            try {
                // Simular llamada API
                this.solicitudes = await this.$api.get('/solicitudes')
            } catch (error) {
                this.mostrarNotificacion('Error cargando solicitudes')
            }
        },

        async manejarAccion(id, accion) {
            try {
                await this.$api.patch(`/solicitudes/${id}`, { estado: accion })
                this.actualizarEstadoLocal(id, accion)
                this.mostrarNotificacion(`Solicitud ${accion} correctamente`)
            } catch (error) {
                this.mostrarNotificacion('Error actualizando estado')
            }
        },

        actualizarEstadoLocal(id, nuevoEstado) {
            const index = this.solicitudes.findIndex(s => s.id === id)
            if (index > -1) {
                this.$set(this.solicitudes, index, {
                    ...this.solicitudes[index],
                    estado: nuevoEstado
                })
            }
        },

        mostrarNotificacion(mensaje) {
            this.toastMessage = mensaje
            this.mostrarToast = true
            setTimeout(() => this.mostrarToast = false, 3000)
        },

        getInitials(nombre) {
            return nombre.split(' ').map(p => p[0]).join('').toUpperCase()
        },

        formatFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        }
    }
}
</script>



<style scoped>

@import '../../../scss/SolicitudUsuarios/SolicitudU_navbar.scss';

@import '../../../scss/SolicitudUsuarios/SolicitudU.scss';

</style>
