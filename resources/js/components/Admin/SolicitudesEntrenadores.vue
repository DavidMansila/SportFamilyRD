<template>
    <div class="solicitudes-container">


        <!-- Navbar -->
        <nav class="navbar">
            <div class="logo-container">
                <a href="/" class="logo-container">
                    <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo" />
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
                <a v-if="userType == 'entrenador'" href="/SolicitudesUsuarios" class="nav-link">Solicitudes</a>

                <!-- Secciones para entrenadores -->
                <a v-if="userType == 'admin'" href="/SolicitudesEntrenadores" class="nav-link">Solicitudes</a>


            </div>

            <div class="Imagenes">

                <a href="#" class="Carrito">
                    <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon" />
                </a>

                <a href="/Ajustes" class="Ajustes">
                    <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" />
                </a>

                <a href="/Perfil" class="Perfil">
                    <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
                </a>

                <a :href="login ? '/Login' : '/Logout'" class="Logout">
                    <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
                </a>

            </div>
        </nav>


        <h1>Solicitudes de Entrenadores</h1>

        <!-- Filtros -->
        <div class="filtros-container">
            <select v-model="filtroEstado" class="filtro-select">
                <option value="todos">Todas las solicitudes</option>
                <option value="pendiente">Pendientes</option>
                <option value="aprobado">Aprobadas</option>
                <option value="rechazado">Rechazadas</option>
            </select>
        </div>

        <!-- Listado de Solicitudes -->
        <div class="solicitudes-list">
            <div v-for="solicitud in solicitudesFiltradas" :key="solicitud.id" class="solicitud-card" :class="{
                'aprobada': solicitud.estado === 'aprobado',
                'rechazada': solicitud.estado === 'rechazado'
            }">
                <div class="solicitud-header">
                    <h3>{{ solicitud.nombre }}</h3>
                    <span class="fecha">{{ formatFecha(solicitud.fechaSolicitud) }}</span>
                </div>

                <div class="solicitud-info">
                    <p><strong>Certificaciones:</strong> {{ solicitud.certificaciones }}</p>
                    <p><strong>Años de experiencia:</strong> {{ solicitud.experiencia }}</p>
                    <p><strong>Especialidades:</strong> {{ solicitud.especialidades }}</p>
                    <p><strong>Contacto:</strong> {{ solicitud.email }} | {{ solicitud.telefono }}</p>
                    <p><strong>Declaración personal:</strong> {{ solicitud.mensaje }}</p>
                    <p v-if="solicitud.documentos" class="documentos">
                        <a :href="solicitud.documentos" target="_blank">Ver documentos adjuntos</a>
                    </p>
                </div>

                <div class="solicitud-acciones">
                    <p class="estado">Estado actual: {{ solicitud.estado }}</p>
                    <div v-if="solicitud.estado === 'pendiente'">
                        <button @click="aprobarSolicitud(solicitud.id)" class="btn-aprobar">Aprobar</button>
                        <button @click="rechazarSolicitud(solicitud.id)" class="btn-rechazar">Rechazar</button>
                    </div>
                </div>
            </div>

            <div v-if="solicitudesFiltradas.length === 0" class="sin-solicitudes">
                No hay solicitudes para mostrar
            </div>
        </div>
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
                    nombre: "Carlos Martínez",
                    experiencia: "5 años",
                    certificaciones: "NSCA-CPT, ACE Fitness",
                    especialidades: "Entrenamiento funcional, Pérdida de peso",
                    email: "carlos.fit@example.com",
                    telefono: "809-555-9876",
                    mensaje: "Especializado en transformaciones corporales y nutrición deportiva",
                    documentos: "/docs/certificaciones_carlos.pdf",
                    fechaSolicitud: new Date(),
                    estado: "pendiente"
                },
                {
                    id: 2,
                    nombre: "Ana Rodríguez",
                    experiencia: "8 años",
                    certificaciones: "ISSA, CrossFit L2",
                    especialidades: "CrossFit, Halterofilia",
                    email: "ana.crossfit@example.com",
                    telefono: "829-555-6543",
                    mensaje: "Entrenadora certificada con experiencia en competidores de CrossFit Games",
                    documentos: "/docs/certificaciones_ana.pdf",
                    fechaSolicitud: new Date('2024-03-01'),
                    estado: "pendiente"
                }
            ]
        }
    },
    computed: {
        solicitudesFiltradas() {
            if (this.filtroEstado === 'todos') return this.solicitudes;
            return this.solicitudes.filter(s => s.estado === this.filtroEstado);
        }
    },
    methods: {
        formatFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        aprobarSolicitud(id) {
            const solicitud = this.solicitudes.find(s => s.id === id);
            if (solicitud) solicitud.estado = 'aprobado';
        },
        rechazarSolicitud(id) {
            const solicitud = this.solicitudes.find(s => s.id === id);
            if (solicitud) solicitud.estado = 'rechazado';
        }
    }
}
</script>



<style scoped>

@import '../../../scss/SolicitudEntrenadores/SolicitudE_navbar.scss';

@import '../../../scss/SolicitudEntrenadores/SolicitudE.scss';

</style>
