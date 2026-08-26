<template>
    <div class="entrenadores-page">
        <!-- Navbar -->
        <Navbar />

        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="page-title">Conoce a Nuestros Expertos</h1>
                <p class="hero-subtitle">
                    Entrenadores certificados para llevar tu rendimiento al
                    siguiente nivel
                </p>
            </div>
        </div>

        <!-- Sección CTA -->
        <div class="cta-container" v-if="user?.user_type == 'user'">
            <div class="cta-card-nonuser">
                <div class="cta-content">
                    <div class="cta-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="cta-text">
                        <h2>¿Te apasiona entrenar y motivar a otros?</h2>
                        <p>
                            Da el siguiente paso y convierte tu pasión en una carrera. Aplica para ser parte de nuestra
                            comunidad de entrenadores certificados y contribuye al crecimiento del deporte en República
                            Dominicana.
                        </p>
                    </div>
                </div>
                <div class="cta-actions">
                    <router-link to="/solicitud" class="cta-button register-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        Aplicar ahora
                    </router-link>
                </div>
            </div>
        </div>

        <div class="cta-container" v-if="user?.user_type == 'entrenador'">
            <div class="cta-card-nonuser">
                <div class="cta-content">
                    <div class="cta-text">
                        <h2>Bienvenido denuevo: {{ user.name }}</h2>
                        <p>
                            Puedes actualizar tus datos profesionales actualizando
                            tu perfil
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-container" v-if="user?.user_type == 'admin'">
            <div class="cta-card-nonuser">
                <div class="cta-content">
                    <div class="cta-text">
                        <h2>Bienvenido denuevo: {{ user.name }}</h2>
                    </div>
                </div>
            </div>
        </div>



        <div class="cta-container" v-if="!user">
            <div class="cta-card-nonuser">
                <div class="cta-content">
                    <div class="cta-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="cta-text">
                        <h2>¿Quieres llevar tu entrenamiento al siguiente nivel?</h2>
                        <p>Inicia sesión para contactar con entrenadores certificados o da el primer paso para
                            convertirte en uno de ellos. ¡Conéctate con el futuro del deporte en República Dominicana!
                        </p>
                    </div>
                </div>
                <div class="cta-actions">
                    <router-link :to="{ path: '/signup', query: { panel: 'login' } }" class="cta-button login-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        Iniciar Sesión
                    </router-link>
                    <router-link :to="{ path: '/signup', query: { panel: 'signup' } }" class="cta-button register-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        Registrarse
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="controls-section">
            <div class="search-container">
                <input type="text" placeholder="Buscar entrenadores..." v-model="busqueda" />
            </div>

            <div class="filter-tabs">
                <button v-for="deporte in deportes" :key="deporte" @click="filtrarPorDeporte(deporte)"
                    :class="{ active: deporteActivo === deporte }">
                    {{ deporte }}
                </button>
            </div>
        </div>

        <!-- Lista de Entrenadores -->
        <div class="entrenadores-container">
            <transition-group name="cards" tag="div" class="entrenadores-grid">
                <div v-for="entrenador in paginatedEntrenadores" :key="entrenador.trainer_id" class="entrenador-card"
                    @click="verPerfil(entrenador)">
                    <div class="card-image-container">
                        <img :src="entrenador.foto" :alt="`${entrenador.nombre} - ${entrenador.deporte}`" loading="lazy" />
                        <div class="deporte-tag">{{ entrenador.deporte }}</div>
                    </div>

                    <div class="card-content">
                        <div class="card-header">
                            <h3>{{ entrenador.user ? entrenador.user.name : entrenador.nombre }}</h3>
                            <!-- <div class="rating">
                <span v-for="star in 5" :key="star" :class="{ filled: star <= entrenador.rating }">★</span>
              </div> -->
                        </div>

                        <p class="experiencia">
                            Años de Experiencia: {{ entrenador.experiencia }}
                        </p>
                        <!-- <p class="testimonio">"{{ entrenador.testimonio }}"</p> -->

                        <div v-if="user" class="card-footer">
                            <button class="contact-btn" @click.stop="contactarEntrenador(entrenador)">
                                <svg class="message-icon" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                Contactar
                            </button>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>

        <div v-if="entrenadoresFiltrados.length > itemsPerPage" class="pagination-container">
            <paginatorComponent v-model="currentPage" :total-items="entrenadoresFiltrados.length"
                :items-per-page="itemsPerPage" :max-pages-shown="5" />
        </div>

        <!-- Modal de Perfil -->
        <transition name="modal">
            <div v-if="entrenadorSeleccionado" class="profile-modal" @click.self="cerrarPerfil">
                <div class="modal-content">
                    <button class="close-modal" @click="cerrarPerfil">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div class="modal-header">
                        <div class="profile-image">
                            <img :src="entrenadorSeleccionado.foto" :alt="entrenadorSeleccionado.nombre" />
                        </div>
                        <div class="profile-info">
                            <h2>{{ entrenadorSeleccionado.user ? entrenadorSeleccionado.user.name :
                                entrenadorSeleccionado.nombre }}</h2>
                            <div class="deporte-badge">
                                {{ entrenadorSeleccionado.deporte }}
                            </div>

                            <div class="ubicacion" v-if="entrenadorSeleccionado.ubicacion">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                {{ entrenadorSeleccionado.ubicacion }}
                            </div>

                            <!-- <div class="modal-rating">
                <div class="stars">
                  <span v-for="star in 5" :key="star"
                    :class="{ filled: star <= entrenadorSeleccionado.rating }">★</span>
                </div>
                <span class="rating-text">{{ entrenadorSeleccionado.rating }}.0 ({{ entrenadorSeleccionado.reseñas }}
                  reseñas)</span>
              </div> -->
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="section">
                            <h3>Biografía</h3>
                            <p>{{ entrenadorSeleccionado.biografia }}</p>
                        </div>

                        <div class="section">
                            <h3>Especialidades</h3>
                            <div class="especialidades">
                                <span v-for="(
esp, i
                                    ) in entrenadorSeleccionado.especialidades" :key="i" class="especialidad-tag">
                                    {{ esp }}
                                </span>
                            </div>
                        </div>

                        <div class="section">
                            <h3>Logros</h3>
                            <div class="logros">
                                <ul class="logros">
                                    <li v-for="(
logro, index
                                        ) in entrenadorSeleccionado.logros" :key="index">
                                        {{ logro }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="section" v-if="
                            entrenadorSeleccionado &&
                            entrenadorSeleccionado.horario
                        ">
                            <h3 class="horario-titulo">
                                🗓️ Horario Disponible
                            </h3>
                            <div class="horario-grid">
                                <div v-for="(diaAbrev, index) in [
                                    'Lun',
                                    'Mar',
                                    'Mié',
                                    'Jue',
                                    'Vie',
                                    'Sáb',
                                    'Dom',
                                ]" :key="index" class="horario-dia" :class="{
                                    disponible: isDisponible(diaAbrev),
                                    noDisponible: !isDisponible(diaAbrev),
                                }">
                                    <span class="dia-nombre">{{
                                        diaAbrev
                                    }}</span>
                                    <span class="estado-icono">
                                        <template v-if="isDisponible(diaAbrev)">
                                            ✅ Disponible
                                            <br />
                                            <small>{{
                                                getHorario(diaAbrev).desde
                                            }}
                                                -
                                                {{
                                                    getHorario(diaAbrev).hasta
                                                }}</small>
                                        </template>
                                        <template v-else>
                                            ❌ No Disponible
                                        </template>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="section">
              <h3>Testimonios</h3>
              <div class="testimonios">
                <div v-for="(testimonio, index) in entrenadorSeleccionado.testimonios" :key="index" class="testimonio">
                  <p>"{{ testimonio.texto }}"</p>
                  <span class="autor">- {{ testimonio.autor }}</span>
                </div>
              </div>
            </div> -->
                    </div>

                    <div v-if="user" class="modal-footer">
                        <button class="primary-btn" @click="contactarEntrenador(entrenadorSeleccionado)">
                            Contactar a
                            {{ entrenadorSeleccionado.nombre.split(" ")[0] }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Modal de Contacto -->
        <transition name="modal">
            <div v-if="mostrarFormularioContacto" class="contact-modal" @click.self="cerrarFormularioContacto">
                <div class="modal-content">
                    <button class="close-modal" @click="cerrarFormularioContacto">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <h2 class="modal-title">
                        Contactar a {{ contactoEntrenador.user ? contactoEntrenador.user.name.split(" ")[0] :
                            contactoEntrenador.nombre.split(" ")[0] }}
                    </h2>

                    <div class="profile-info">
                        <div class="user-details">
                            <span class="user-avatar">
                                <img :src="user.image ||
                                    '/imagenes/Perfil-Icon.png'
                                    " alt="Tu foto de perfil" />
                            </span>
                            <div>
                                <p><strong>Nombre:</strong> {{ user.name }}</p>
                                <p v-if="user.email">
                                    <strong>Email:</strong> {{ user.email }}
                                </p>
                                <p v-if="user.location"><strong>Ubicacion:</strong> {{ user.location }}</p>
                            </div>
                        </div>
                        <p class="mensaje">
                            <strong>
                                Tus datos de perfil serán enviados al entrenador
                                para que pueda conocerte mejor
                            </strong>
                        </p>
                    </div>

                    <form @submit.prevent="enviarFormularioContacto" class="contact-form">
                        <div class="form-group">
                            <label for="objetivos">¿Qué buscas aprender o lograr?</label>
                            <textarea id="objetivos" v-model="formularioContacto.objetivos" rows="4"
                                placeholder="Ej: Mejorar mi técnica de tiro, prepararme para una competencia, perder peso..."
                                required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="nivel">Nivel en el deporte</label>
                            <select id="nivel" v-model="formularioContacto.nivel" required>
                                <option value="" disabled selected>
                                    Selecciona tu nivel
                                </option>
                                <option value="Principiante">
                                    Principiante
                                </option>
                                <option value="Intermedio">Intermedio</option>
                                <option value="Avanzado">Avanzado</option>
                                <option value="Profesional">Profesional</option>
                            </select>
                        </div>

                        <p class="data-notice">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <template v-if="user?.user_type == 'entrenador'">
                                Los entrenadores no pueden enviar solicitudes de
                                entrenamiento.
                            </template>
                            <template v-else>
                                Tu solicitud será evaluada en un plazo
                                aproximado de 3 días laborables.
                            </template>
                        </p>

                        <button type="submit" class="submit-btn">
                            Enviar Solicitud
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Burbuja de Mensajes Flotante -->
        <ChatBubbleComponent v-if="user && !entrenadorSeleccionado && !mostrarFormularioContacto" :user="user" />
    </div>

    <Alert v-if="openModal" :key="alertKey" :message="alertMessage" :type="alertType" @closed="openModal = null" />
</template>

<script>
import axios from "axios";
import Navbar from "../navbarComponent.vue";
import ChatBubbleComponent from "../ChatBubbleComponent.vue";
import paginatorComponent from "@/components/paginatorComponent.vue";
import Alert from '../Alert.vue';

export default {
    name: "Entrenadores",
    components: {
        Navbar,
        ChatBubbleComponent,
        paginatorComponent,
        Alert,
    },
    data() {
        return {
            openModal: false,
            alertMessage: "",
            alertType: "", // 'error', 'success', 'alert'.
            alertKey: 0,
            user: null,
            scrollPosition: 0,
            busqueda: "",
            deporteActivo: "Todos",
            dias: [
                "Lunes",
                "Martes",
                "Miércoles",
                "Jueves",
                "Viernes",
                "Sábado",
                "Domingo",
            ],
            deportes: [
                "Todos",
                "Fútbol",
                "Tenis",
                "Baloncesto",
                "Natación",
                "Ciclismo",
                "Atletismo",
                "Artes Marciales",
            ],
            entrenadorSeleccionado: null,
            mostrarFormularioContacto: false,
            contactoEntrenador: null,
            formularioContacto: {
                nivel: "",
                objetivos: "",
            },
            entrenadores: [],
            currentPage: 1,
            itemsPerPage: 9,
        };
    },
    computed: {
        entrenadoresFiltrados() {
            let filtrados = this.entrenadores;

            if (this.deporteActivo !== "Todos") {
                filtrados = filtrados.filter(
                    (e) => e.deporte === this.deporteActivo
                );
            }
            if (this.busqueda) {
                const term = this.busqueda.toLowerCase();
                filtrados = filtrados.filter(
                    (e) =>
                        e.nombre.toLowerCase().includes(term) ||
                        e.deporte.toLowerCase().includes(term) ||
                        e.especialidades.some((esp) =>
                            esp.toLowerCase().includes(term)
                        )
                );
            }
            return filtrados;
        },

        paginatedEntrenadores() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.entrenadoresFiltrados.slice(start, end);
        },
    },

    methods: {
        shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        },

        filtrarPorDeporte(deporte) {
            this.deporteActivo = deporte;
            this.currentPage = 1;
        },

        verPerfil(entrenador) {
            // Guardar posición actual del scroll antes de abrir el modal
            this.scrollPosition =
                window.pageYOffset || document.documentElement.scrollTop;

            // Deshabilitar scroll del body
            document.body.style.overflow = "hidden";
            document.body.style.position = "fixed";
            document.body.style.top = `-${this.scrollPosition}px`;
            document.body.style.width = "100%";

            this.entrenadorSeleccionado = entrenador;
        },

        cerrarPerfil() {
            try {
                // Habilitar scroll del body
                document.body.style.overflow = "auto";
                document.body.style.position = "";
                document.body.style.top = "";
                document.body.style.width = "";

                // Restaurar posición del scroll
                window.scrollTo(0, this.scrollPosition);
            } finally {
                document.body.style.overflow = "auto";
                this.entrenadorSeleccionado = null;
            }
        },

        contactarEntrenador(entrenador) {
            this.contactoEntrenador = {
                trainer_id: entrenador.trainer_id, // Usar trainer_id del entrenador
                user_id: entrenador.user_id, // Usar user_id del entrenador
                nombre: entrenador.nombre,
            };
            this.mostrarFormularioContacto = true;

            // Guardar posición actual del scroll antes de abrir el modal
            this.scrollPosition =
                window.pageYOffset || document.documentElement.scrollTop;

            // Deshabilitar scroll del body
            document.body.style.overflow = "hidden";
            document.body.style.position = "fixed";
            document.body.style.top = `-${this.scrollPosition}px`;
            document.body.style.width = "100%";
        },

        cerrarFormularioContacto() {
            try {
                // Habilitar scroll del body
                document.body.style.overflow = "auto";
                document.body.style.position = "";
                document.body.style.top = "";
                document.body.style.width = "";

                // Restaurar posición del scroll
                window.scrollTo(0, this.scrollPosition);
            } finally {
                this.mostrarFormularioContacto = false;
                this.formularioContacto = {
                    edad: "",
                    nivel: "",
                    objetivos: "",
                };
            }
        },

        async enviarFormularioContacto() {

            if (!this.user?.id) {
                this.alertType = "alert";
                this.alertMessage = "Debes iniciar sesión para contactar a un entrenador";
                this.alertKey++;
                this.openModal = true;

                return;
            }

            // Verificar si el usuario es entrenador
            if (this.user.user_type === "entrenador") {
                this.alertType = "error";
                this.alertMessage = "Los entrenadores no pueden enviar solicitudes a otros entrenadores.";
                this.alertKey++;
                this.openModal = true;

                return;
            }

            try {
                // Verificar si ya existe una solicitud
                const checkResponse = await axios.post(
                    "/training/check-existing",
                    {
                        user_id: this.user.id,
                        trainer_id: this.contactoEntrenador.trainer_id,
                    }
                );

                if (checkResponse.data.exists) {
                    // Si está expirada, eliminar y permitir nueva
                    if (checkResponse.data.status === "expired") {
                        await axios.delete(
                            `/training/${checkResponse.data.id}`
                        );
                    } else {
                        this.alertType = "error";
                        this.alertMessage = `Ya tienes una solicitud pendiente con ${this.contactoEntrenador.nombre}`;
                        this.alertKey++;
                        this.openModal = true;

                        return;
                    }
                }

                // Enviar nueva solicitud
                const formData = {
                    user_id: this.user.id,
                    trainer_id: this.contactoEntrenador.trainer_id,
                    sport_level: this.formularioContacto.nivel,
                    description: this.formularioContacto.objetivos,
                    status: "pending",
                };

                const response = await axios.post("/training", formData);

                if (response.status === 201) {
                    this.alertType = "success";
                    this.alertMessage = `Solicitud enviada a ${this.contactoEntrenador.nombre} con éxito`;
                    this.alertKey++;
                    this.openModal = true;

                    this.cerrarFormularioContacto();
                }
            } catch (error) {
                // Manejo de errores
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    let errorMsg = Object.values(errors).flat().join("\n");

                    this.alertType = "error";
                    this.alertMessage = `Error de validación:\n${errorMsg}`;
                    this.alertKey++;
                    this.openModal = true;
                } else if (error.response?.status === 401) {
                    this.alertType = "error";
                    this.alertMessage = "Tu sesión ha expirado. Por favor inicia sesión nuevamente.";
                    this.alertKey++;
                    this.openModal = true;

                } else if (error.response?.data?.message) {

                    this.alertType = "error";
                    this.alertMessage = `Error: ${error.response.data.message}`;
                    this.alertKey++;
                    this.openModal = true;
                } else {
                    console.error("Error completo:", error);

                    this.alertType = "error";
                    this.alertMessage = `Error al procesar la solicitud: ${error.message}`;
                    this.alertKey++;
                    this.openModal = true;
                }
            }
        },

        cargarEntrenadores() {
            axios
                .get("/trainer/approved")
                .then((response) => {
                    let trainers = response.data.trainer.map((trainer) => ({
                        trainer_id: trainer.id,
                        user_id: trainer.user_id,
                        nombre: trainer.user ? trainer.user.name : trainer.name,
                        deporte: trainer.sport_category,
                        experiencia: trainer.experience,
                        foto: trainer.image,
                        rating: trainer.rating || 5,
                        reseñas: trainer.reviews || 0,
                        biografia: trainer.description || "",
                        horario: this.parseSchedule(trainer.schedule),
                        ubicacion: trainer.city_country || "",
                        especialidades: trainer.specialties
                            ? trainer.specialties.map(
                                (e) => e.description || e.name
                            )
                            : [],
                        logros: trainer.achievements
                            ? trainer.achievements.map(
                                (a) =>
                                    `${a.title}${a.date ? ` (${a.date})` : ""
                                    }`
                            )
                            : [],
                    }));

                    trainers = this.shuffleArray(trainers);

                    if (this.user?.user_type === "entrenador") {
                        const userIndex = trainers.findIndex(
                            (t) => t.user_id === this.user.id
                        );
                        if (userIndex !== -1) {
                            const userTrainer = trainers.splice(
                                userIndex,
                                1
                            )[0];
                            trainers.unshift(userTrainer);
                        }
                    }

                    this.entrenadores = trainers;
                    this.$store.dispatch("cacheSection", { key: "entrenadores", data: trainers });
                })
                .catch((error) => {
                    console.error(
                        "Error al cargar entrenadores aprobados:",
                        error
                    );
                    this.alertType = "error";
                    this.alertMessage = "Error al cargar entrenadores aprobados.";
                    this.alertKey++;
                    this.openModal = true;
                    this.entrenadores = []; // Asegurarse de que la lista esté vacía en caso de error
                });
        },

        diaCompleto(diaAbrev) {
            const mapaDias = {
                Lun: "Lunes",
                Mar: "Martes",
                Mié: "Miércoles",
                Jue: "Jueves",
                Vie: "Viernes",
                Sáb: "Sábado",
                Dom: "Domingo",
            };
            return mapaDias[diaAbrev] || diaAbrev;
        },

        // Devuelve true si el día está disponible
        isDisponible(diaAbrev) {
            if (!this.entrenadorSeleccionado?.horario) return false;
            const dia = this.diaCompleto(diaAbrev);
            return this.entrenadorSeleccionado.horario[dia]?.start !== "";
        },

        // Devuelve objeto {desde, hasta} con horas para ese día o vacíos si no disponible
        getHorario(diaAbrev) {
            if (!this.entrenadorSeleccionado?.horario)
                return { desde: "", hasta: "" };
            const dia = this.diaCompleto(diaAbrev);
            const horarioDia = this.entrenadorSeleccionado.horario[dia];

            return {
                desde: horarioDia?.start || "",
                hasta: horarioDia?.end || "",
            };
        },

        // Función para parsear el horario
        parseSchedule(scheduleData) {
            const dias = [
                "Lunes",
                "Martes",
                "Miércoles",
                "Jueves",
                "Viernes",
                "Sábado",
                "Domingo",
            ];
            const defaultSchedule = {};

            dias.forEach((dia) => {
                defaultSchedule[dia] = { start: "", end: "" };
            });

            if (!scheduleData) return defaultSchedule;

            try {
                const parsed =
                    typeof scheduleData === "object"
                        ? scheduleData
                        : JSON.parse(scheduleData);

                const transformed = { ...defaultSchedule };

                for (const dia in parsed) {
                    if (parsed[dia]?.available && parsed[dia]?.hours) {
                        transformed[dia] = {
                            start: parsed[dia].hours.desde || "",
                            end: parsed[dia].hours.hasta || "",
                        };
                    }
                }
                return transformed;
            } catch (e) {
                console.error("Error parseando horario:", e);
                return defaultSchedule;
            }
        },

        // Función para formatear la hora
        formatScheduleTime(time) {
            if (!time) return "";
            return time.length > 5 ? time.substring(0, 5) : time;
        },
    },
    watch: {
        busqueda() {
            this.currentPage = 1;
        },
    },
    mounted() {

        const userData = sessionStorage.getItem("user");

        if (userData) {
            this.user = JSON.parse(userData);
        } else {
            this.user = null;
        }

        const cachedEntrenadores = this.$store.getters.sectionCache("entrenadores");
        if (cachedEntrenadores) {
            this.entrenadores = cachedEntrenadores;
        } else {
            this.cargarEntrenadores();
        }
    },
};
</script>

<style scoped>
@import "../../../scss/Entrenadores/entrenadores.scss";

@import "../../../scss/Entrenadores/entrenadores_grid.scss";

@import "../../../scss/Entrenadores/entrenadores_mensajes.scss";

@import "../../../scss/Entrenadores/entrenadores_modal.scss";

@import "../../../scss/Entrenadores/entrenadores_navbar.scss";

@import "../../../scss/Entrenadores/entrenadores_responsive.scss";

/* Nuevo estilo para la paginación */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    padding-bottom: 30px;
}

/* Nuevos estilos para el modal de contacto */
.contact-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.contact-modal .modal-content {
    background-color: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    padding: 30px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-title {
    text-align: center;
    margin-bottom: 25px;
    color: #2c3e50;
    font-size: 1.5rem;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.submit-btn {
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 14px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.submit-btn:hover {
    background-color: #2980b9;
}

.horario-titulo {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.horario-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
}

.horario-dia {
    padding: 0.75rem;
    border-radius: 10px;
    background-color: #f1f1f1;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    text-align: center;
    transition: 0.3s ease;
}

.horario-dia.disponible {
    background-color: #e6ffec;
    border: 1px solid #8de4a3;
}

.horario-dia:not(.disponible) {
    background-color: #ffeaea;
    border: 1px solid #f5a8a8;
}

.dia-nombre {
    font-weight: bold;
    font-size: 1rem;
    display: block;
    margin-bottom: 0.25rem;
}

.estado-icono {
    font-size: 0.9rem;
}

.profile-info {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;

    p {
        margin-bottom: 10px;
        color: #495057;
    }
}

.user-details {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e9ecef;

    p {
        margin: 5px 0;
        font-size: 0.95rem;
    }
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;

    img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
}

.data-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 10px;
    padding: 10px;
    background-color: #e8f4fd;
    border-radius: 8px;

    svg {
        flex-shrink: 0;
    }
}

.mensaje {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 10px;
}


/* Estilos para la sección CTA */
.cta-container {
    max-width: 1200px;
}

.cta-card-nonuser {
    background: linear-gradient(135deg, #b87800, rgb(37, 23, 0));
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.cta-card-nonuser:hover {
    transform: translateY(-5px);
}

.cta-card-nonuser::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=2070') no-repeat center center/cover;
    opacity: 0.1;
    z-index: -1;
}

.cta-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem 2rem;
    text-align: center;
}

.cta-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.cta-icon svg {
    width: 40px;
    height: 40px;
    stroke: white;
}

.cta-text h2 {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
    line-height: 1.3;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.cta-text p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

.cta-actions {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    padding: 0 2rem 2.5rem;
    flex-wrap: wrap;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.9rem 2rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.cta-button svg {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    stroke-width: 2.5px;
}

.login-btn {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    color: #1a2980;
}

.register-btn {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.login-btn:hover {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.register-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Efecto de onda al pasar el ratón */
.cta-button::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: -1;
}

.cta-button:hover::after {
    transform: translateX(0);
}

/* Responsive */
@media (max-width: 768px) {
    .cta-content {
        padding: 2rem 1.5rem;
    }

    .cta-text h2 {
        font-size: 1.7rem;
    }

    .cta-text p {
        font-size: 1rem;
    }

    .cta-actions {
        flex-direction: column;
        align-items: center;
        padding: 0 1.5rem 2rem;
    }

    .cta-button {
        width: 100%;
        max-width: 280px;
    }
}

@media (max-width: 480px) {
    .cta-text h2 {
        font-size: 1.5rem;
    }

    .cta-icon {
        width: 70px;
        height: 70px;
    }

    .cta-icon svg {
        width: 35px;
        height: 35px;
    }
}


.ubicacion {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
}

.ubicacion svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>
