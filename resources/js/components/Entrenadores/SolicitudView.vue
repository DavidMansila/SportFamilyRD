<template>
  <div class="solicitud-view">


    <!-- Navbar -->
    <Navbar />



    <!-- Contenido principal -->
    <div class="container">
      <!-- Header con animación -->
      <div class="header-animation">
        <h1 class="page-title">Únete a Nuestro Equipo de Entrenadores</h1>
        <p class="page-subtitle">Comparte tu conocimiento y ayuda a otros a alcanzar sus metas deportivas</p>
        <div class="progress-steps">
          <div v-for="step in 3" :key="step" class="step" :class="{
            'active': pasoActual === step,
            'completed': pasoActual > step
          }">
            {{ step }}
          </div>
        </div>
      </div>



      <!-- Formulario de solicitud con pestañas/multipasos -->
      <form @submit.prevent="enviarSolicitud" class="solicitud-form">
        <!-- Paso 1 - Información básica -->
        <div v-if="pasoActual === 1" class="form-step">
          <h2 class="step-title">Información Personal</h2>

          <div class="form-grid">
            <div class="form-group floating-label">
              <input type="text" id="nombre" v-model="formulario.nombre" placeholder=" " required
                @input="validarNombre" />
              <label for="nombre">Nombre completo</label>
              <span class="error-message" v-if="errores.nombre">{{ errores.nombre }}</span>
            </div>

            <div class="form-group floating-label">
              <input type="email" id="email" v-model="formulario.email" placeholder=" " required
                @input="validarEmail" />
              <label for="email">Correo electrónico</label>
              <span class="error-message" v-if="errores.email">{{ errores.email }}</span>
            </div>

            <div class="form-group floating-label">
              <input type="tel" id="telefono" v-model="formulario.telefono" placeholder=" " @input="formatPhoneInput"
                maxlength="14" />
              <label for="telefono">Teléfono</label>
              <span class="error-message" v-if="errores.telefono">{{ errores.telefono }}</span>
            </div>

            <div class="form-group floating-label">
              <select id="ubicacion" v-model="formulario.ubicacion" required class="location-select">
                <option value="" disabled selected></option>
                <option v-for="provincia in provinciasRD" :key="provincia" :value="provincia">
                  {{ provincia }}
                </option>
              </select>
              <label for="ubicacion">Ciudad/Región</label>
            </div>

          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-next" @click="siguientePaso">Siguiente</button>
          </div>
        </div>



        <!-- Paso 2 - Experiencia y certificaciones -->
        <div v-if="pasoActual === 2" class="form-step">
          <h2 class="step-title">Tu Experiencia</h2>

          <div class="form-group floating-label">
            <select id="deporte" v-model="formulario.deporte" required>
              <option value="" disabled selected></option>
              <option value="Fútbol">Fútbol</option>
              <option value="Baloncesto">Baloncesto</option>
              <option value="Tenis">Tenis</option>
              <option value="Natación">Natación</option>
              <option value="Ciclismo">Ciclismo</option>
              <option value="Atletismo">Atletismo</option>
              <option value="Artes Marciales">Artes Marciales</option>
            </select>
            <label for="deporte">Deporte principal</label>
          </div>

          <div class="form-group floating-label">
            <input type="number" id="experiencia" v-model="formulario.experiencia" placeholder=" " min="0" max="50"
              required />
            <label for="experiencia">Años de experiencia</label>
          </div>

          <div class="form-group">
            <label class="custom-label">Nivel de certificación</label>
            <div class="radio-group">
              <label v-for="nivel in nivelesCertificacion" :key="nivel.value" class="radio-option">
                <input type="radio" v-model="formulario.nivelCertificacion" :value="nivel.value" required />
                <span class="radio-custom"></span>
                {{ nivel.label }}
              </label>
            </div>
          </div>

          <!-- <div class="form-group">
            <label class="custom-label">Sube tus certificados (PDF, JPG, PNG)</label>
            <div class="file-upload-area" @click="triggerFileInput('certificados')" @dragover.prevent
              @drop="handleDrop($event, 'certificados')">
              <input type="file" id="certificados" ref="certificadosInput"
                @change="subirArchivos('certificados', $event)" multiple accept=".pdf,.jpg,.jpeg,.png" hidden />
              <div v-if="!formulario.certificados.length" class="upload-placeholder">
                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                  <path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
                <p>Arrastra tus archivos aquí o haz clic para seleccionar</p>
              </div>
              <div v-else class="uploaded-files">
                <div v-for="(file, index) in formulario.certificados" :key="index" class="file-item">
                  <span>{{ file.name }}</span>
                  <button type="button" @click.stop="eliminarArchivo('certificados', index)" class="delete-file">
                    &times;
                  </button>
                </div>
              </div>
            </div>
          </div> -->

          <div class="form-actions">
            <button type="button" class="btn btn-prev" @click="pasoAnterior">Anterior</button>
            <button type="button" class="btn btn-next" @click="siguientePaso">Siguiente</button>
          </div>
        </div>



        <!-- Paso 3 - Detalles adicionales -->
        <div v-if="pasoActual === 3" class="form-step">
          <h2 class="step-title">Detalles Adicionales</h2>

          <!-- Sección de Enfoque de Entrenamiento -->
          <div class="achievements-section">
            <h2 class="section-title">
              <svg class="title-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17 3.34a10 10 0 1 1-14.995 8.984L2 12l.005-.324A10 10 0 0 1 17 3.34z" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M12 8v4l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
              Tu Filosofía de Entrenamiento
            </h2>

            <div class="approach-card">
              <div class="form-description">
                <svg class="info-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 16v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p>Comparte tu metodología única. ¿Qué te diferencia como entrenador?</p>
              </div>

              <div class="form-group floating-label">
                <textarea id="enfoque" v-model="formulario.enfoque" placeholder=" " rows="5" required
                  class="styled-textarea"></textarea>
                <label for="enfoque">Describe tu enfoque de entrenamiento</label>
                <div class="input-hint">
                  Ejemplo: "Me enfoco en desarrollar habilidades técnicas fundamentales mientras fomento el amor por el
                  deporte y
                  trabajo en el desarrollo personal de cada atleta..."
                </div>
              </div>

              <div class="approach-tips">
                <div class="tip-card">
                  <svg class="tip-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <p>Menciona tu metodología de enseñanza</p>
                </div>
                <div class="tip-card">
                  <svg class="tip-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <p>Destaca tus valores fundamentales</p>
                </div>
                <div class="tip-card">
                  <svg class="tip-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <p>Explica cómo personalizas los entrenamientos</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Logros -->
          <div class="achievements-section">
            <h2 class="section-title">
              <svg class="title-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M16 8V16M8 8V16M12 6V18M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              Mis Logros
            </h2>

            <div class="achievements-form">
              <div class="form-description">
                <svg class="info-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p>Destaca tus logros más relevantes como entrenador (competencias, certificaciones, logros con alumnos)
                </p>
              </div>

              <div class="achievements-list">
                <div v-for="(logro, index) in formulario.logros" :key="index" class="achievement-card">
                  <div class="achievement-form">
                    <div class="form-group">
                      <label class="input-label">Título del logro</label>
                      <input type="text" v-model="logro.title" placeholder="Ej: Campeonato Regional de Tenis 2023"
                        class="achievement-input" required>
                    </div>

                    <div class="form-group">
                      <label class="input-label">Descripción</label>
                      <textarea v-model="logro.description"
                        placeholder="Describe el logro, su importancia y tu contribución" class="achievement-textarea"
                        rows="3" required></textarea>
                    </div>

                    <div class="form-row">
                      <div class="form-group">
                        <label class="input-label">Fecha</label>
                        <input type="date" v-model="logro.date" class="achievement-date" required>
                      </div>

                      <button @click="removeAchievement(index)" class="delete-achievement-btn" type="button">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path
                            d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Eliminar
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <button @click="addAchievement" class="add-achievement-btn" type="button">
                <svg class="add-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
                Añadir nuevo logro
              </button>
            </div>
          </div>

          <!-- Sección de Especialidades -->
          <div class="specialties-section">
            <h2 class="section-title">
              <svg class="title-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M9 12L11 14L15 10M19.5 9.5L18.7906 7.02746C18.5157 6.06826 18.3783 5.58866 18.0978 5.20151C17.818 4.8156 17.4378 4.5183 17 4.34294C16.5614 4.16721 16.0413 4.12571 15 4.04271L12 3.75M4.5 9.5L5.20938 7.02746C5.48426 6.06826 5.6217 5.58866 5.90221 5.20151C6.18199 4.8156 6.56216 4.5183 7 4.34294C7.43862 4.16721 7.95866 4.12571 9 4.04271L12 3.75M12 3.75V2.75M12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12C15 13.6569 13.6569 15 12 15Z"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              Mis Especialidades
            </h2>

            <div class="specialties-form">
              <div class="form-description">
                <svg class="info-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p>Agrega tus áreas de especialización o habilidades únicas como entrenador</p>
              </div>

              <div class="specialties-list">
                <div v-for="(especialidad, index) in formulario.especialidades" :key="index" class="specialty-item">
                  <div class="specialty-input-group">
                    <input type="text" v-model="especialidad.description"
                      placeholder="Ejemplo: Entrenamiento funcional para adultos mayores" class="specialty-input"
                      required>
                    <button @click="eliminarEspecialidad(index)" class="delete-btn" type="button"
                      aria-label="Eliminar especialidad">
                      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                          stroke-linejoin="round" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <button @click="agregarEspecialidad" class="add-specialty-btn" type="button">
                <svg class="add-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
                Añadir otra especialidad
              </button>
            </div>
          </div>


          <div class="availability-section">
            <div class="availability-header">
              <h2 class="section-title">
                <svg class="title-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
                Disponibilidad
              </h2>
            </div>

            <div class="availability-grid">
              <div v-for="dia in diasSemana" :key="dia" class="availability-day"
                :class="{ 'active': formulario.disponibilidad[dia] }">
                <label>
                  <input type="checkbox" v-model="formulario.disponibilidad[dia]" />
                  <span class="custom-checkbox"></span>
                  <span class="day-label">{{ dia }}</span>
                </label>
                <div class="day-highlight"></div>

                <div v-if="formulario.disponibilidad[dia]" class="time-slots">
                  <div class="time-slot">
                    <span>De</span>
                    <input type="time" v-model="formulario.horarios[dia].desde" />
                  </div>
                  <div class="time-slot">
                    <span>A</span>
                    <input type="time" v-model="formulario.horarios[dia].hasta" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- <div class="form-group">
            <label class="custom-label">Tarifa por sesión (opcional)</label>
            <div class="price-input">
              <span class="currency">$</span>
              <input type="number" v-model="formulario.tarifa" placeholder="Ejemplo: 25" min="0" />
              <span class="per-session">/sesión</span>
            </div>
          </div> -->

          <div class="form-actions">
            <button type="button" class="btn btn-prev" @click="pasoAnterior">Anterior</button>
            <button type="submit" class="btn btn-submit" >Enviar Solicitud</button>
          </div>
        </div>
      </form>
    </div>



    <!-- Modal de confirmación -->
    <transition name="modal">
      <div v-if="mostrarConfirmacion" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Solicitud Enviada con Éxito</h3>
            <button @click="cerrarModal" class="close-modal">&times;</button>
          </div>
          <div class="modal-body">
            <svg class="success-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            <p>¡Gracias por tu solicitud! Hemos recibido tu información y la revisaremos cuidadosamente.</p>
            <p>Nos pondremos en contacto contigo en un plazo de 3-5 días hábiles.</p>
          </div>
          <div class="modal-footer">
            <button @click="cerrarModal" class="btn btn-confirm">Entendido</button>
          </div>
        </div>
      </div>
    </transition>

  </div>

  <Alert 
    v-if="openModal" 
    :key="alertKey"
    :type="alertType" 
    :message="alertMessage" 
    @close="openModal = false" 
  />  
</template>



<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import Alert from '../Alert.vue';

export default {
  name: 'SolicitudView',
  components: {
    Navbar,
    Alert
  },
  data() {
    return {
      openModal: false,
      alertType: 'success', // 'success', 'error', 'alert'
      alertMessage: '',
      alertKey: 0, // Para forzar la re-renderización del componente Alert
      user: null,
      pasoActual: 1,
      mostrarConfirmacion: false,
      diasSemana: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
      nivelesCertificacion: [
        { value: 'ninguna', label: 'Sin certificación formal' },
        { value: 'basica', label: 'Certificación básica' },
        { value: 'intermedia', label: 'Certificación intermedia' },
        { value: 'avanzada', label: 'Certificación avanzada' },
        { value: 'nacional', label: 'Certificación nacional' },
        { value: 'internacional', label: 'Certificación internacional' }
      ],
      formulario: {
        user_id: '',
        status: 'pending',
        nombre: '',
        email: '',
        telefono: '',
        ubicacion: '',
        deporte: '',
        experiencia: '',
        nivelCertificacion: '',
        certificados: [],
        enfoque: '',
        logros: [
          { title: '', description: '', date: '' }
        ],
        especialidades: [
          { description: '' }
        ],
        disponibilidad: {
          Lunes: false,
          Martes: false,
          Miércoles: false,
          Jueves: false,
          Viernes: false,
          Sábado: false,
          Domingo: false
        },
        horarios: {
          Lunes: { desde: '09:00', hasta: '17:00' },
          Martes: { desde: '09:00', hasta: '17:00' },
          Miércoles: { desde: '09:00', hasta: '17:00' },
          Jueves: { desde: '09:00', hasta: '17:00' },
          Viernes: { desde: '09:00', hasta: '17:00' },
          Sábado: { desde: '09:00', hasta: '17:00' },
          Domingo: { desde: '09:00', hasta: '17:00' }
        },
        tarifa: null
      },
      errores: {
        nombre: '',
        email: '',
        telefono: ''
      },
      provinciasRD: [
        'Distrito Nacional',
        'Azua',
        'Bahoruco',
        'Barahona',
        'Dajabón',
        'Duarte',
        'Elías Piña',
        'El Seibo',
        'Espaillat',
        'Hato Mayor',
        'Hermanas Mirabal',
        'Independencia',
        'La Altagracia',
        'La Romana',
        'La Vega',
        'María Trinidad Sánchez',
        'Monseñor Nouel',
        'Monte Cristi',
        'Monte Plata',
        'Pedernales',
        'Peravia',
        'Puerto Plata',
        'Samaná',
        'Sánchez Ramírez',
        'San Cristóbal',
        'San José de Ocoa',
        'San Juan',
        'San Pedro de Macorís',
        'Santiago',
        'Santiago Rodríguez',
        'Santo Domingo',
        'Valverde'
      ],
    }
  },

  methods: {
    siguientePaso() {
      if (this.validarPasoActual()) {
        this.pasoActual++
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    },
    pasoAnterior() {
      this.pasoActual--
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },

    validarPasoActual() {
      let valido = true;

      if (this.pasoActual === 1) {
        if (!this.formulario.nombre.trim()) {
          this.errores.nombre = 'Nombre completo requerido';
          valido = false;
        }
        if (!this.formulario.email.trim()) {
          this.errores.email = 'Email requerido';
          valido = false;
        }
        if (this.formulario.telefono) {
          const soloDigitos = this.formulario.telefono.replace(/\D/g, '');
          if (soloDigitos.length !== 10) {
            this.errores.telefono = 'Teléfono debe tener 10 dígitos';
            valido = false;
          }
        }
      }

      if (this.pasoActual === 2) {
        if (!this.formulario.deporte) {
          
          alertType = 'alert';
          alertMessage = 'Selecciona un deporte';
          openModal = true;

          valido = false;
        }
        if (this.formulario.experiencia < 0 || this.formulario.experiencia > 50) {

          alertType = 'alert';
          alertMessage = 'Experiencia debe ser entre 0-50 años';
          openModal = true;

          valido = false;
        }
      }

      return valido;
    },

    validarNombre() {
      if (this.formulario.nombre.length < 3) {
        this.errores.nombre = 'El nombre debe tener al menos 3 caracteres'
      } else {
        this.errores.nombre = ''
      }
    },

    validarEmail() {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!re.test(this.formulario.email)) {
        this.errores.email = 'Por favor ingresa un correo electrónico válido'
      } else {
        this.errores.email = ''
      }
    },

    validarTelefono() {
      if (this.formulario.telefono && !/^[0-9+\- ]+$/.test(this.formulario.telefono)) {
        this.errores.telefono = 'Solo números, + y - permitidos'
      } else {
        this.errores.telefono = ''
      }
    },

    triggerFileInput(field) {
      this.$refs[`${field}Input`].click()
    },

    subirArchivos(field, event) {
      const files = Array.from(event.target.files)
      this.formulario[field] = [...this.formulario[field], ...files]
    },

    handleDrop(event, field) {
      event.preventDefault()
      const files = Array.from(event.dataTransfer.files)
      this.formulario[field] = [...this.formulario[field], ...files]
    },

    eliminarArchivo(field, index) {
      this.formulario[field].splice(index, 1)
    },

    agregarLogro() {
      this.formulario.logros.push('')
    },

    eliminarLogro(index) {
      if (this.formulario.logros.length > 1) {
        this.formulario.logros.splice(index, 1)
      } else {
        this.formulario.logros[index] = ''
      }
    },

    enviarSolicitud() {

      if (this.validarPasoActual()) {
        const formData = new FormData();

        // Datos básicos
        formData.append('user_id', this.formulario.user_id);
        formData.append('name', this.formulario.nombre);
        formData.append('email', this.formulario.email);
        formData.append('phone', this.formulario.telefono);
        formData.append('city_country', this.formulario.ubicacion);
        formData.append('sport_category', this.formulario.deporte);
        formData.append('experience', this.formulario.experiencia);
        formData.append('level_of_certification', this.formulario.nivelCertificacion);
        formData.append('description', this.formulario.enfoque);
        formData.append('cost', this.formulario.tarifa);
        formData.append('status', this.formulario.status);

        // Certificados
        this.formulario.certificados.forEach((file, index) => {
          formData.append(`certificates[${index}]`, file);
        });

        const logrosValidos = this.formulario.logros.filter(logro =>
          logro.title.trim() !== '' && logro.description.trim() !== '' && logro.date
        );
        formData.append('achievements', JSON.stringify(logrosValidos));

        // Especialidades
        const especialidadesValidas = this.formulario.especialidades
          .filter(e => e.description.trim() !== '')

        formData.append('specialties', JSON.stringify(especialidadesValidas));

        // Horarios
        const schedule = {};
        this.diasSemana.forEach(dia => {
          if (this.formulario.disponibilidad[dia]) {
            schedule[dia] = {
              available: true,
              hours: this.formulario.horarios[dia]
            };
          }
        });

        formData.append('schedule', JSON.stringify(schedule));

        // Enviar a Laravel
        axios.post('/solicitud-entrenador', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
          .then(response => {
            this.mostrarConfirmacion = true;
            this.resetFormulario();
          })
          .catch(error => {
            console.error('Error:', error.response.data);
            
            this.openModal = true;
            this.alertType = 'error';
            this.alertMessage = 'Error al enviar la solicitud. Por favor, inténtalo de nuevo más tarde.';
            this.alertKey++;
          });
      }
    },

    resetFormulario() {
      this.formulario = {
        nombre: '',
        email: '',
        telefono: '',
        ubicacion: '',
        deporte: '',
        experiencia: '',
        nivelCertificacion: '',
        certificados: [],
        enfoque: '',
        logros: [
          { title: '', description: '', date: '' }
        ],
        especialidades: [
          { name: '' }
        ],
        disponibilidad: {
          Lunes: false,
          Martes: false,
          Miércoles: false,
          Jueves: false,
          Viernes: false,
          Sábado: false,
          Domingo: false
        },
        horarios: {
          Lunes: { desde: '09:00', hasta: '17:00' },
          Martes: { desde: '09:00', hasta: '17:00' },
          Miércoles: { desde: '09:00', hasta: '17:00' },
          Jueves: { desde: '09:00', hasta: '17:00' },
          Viernes: { desde: '09:00', hasta: '17:00' },
          Sábado: { desde: '09:00', hasta: '17:00' },
          Domingo: { desde: '09:00', hasta: '17:00' }
        },
        tarifa: null
      }
      this.pasoActual = 1
    },

    cerrarModal() {
      this.mostrarConfirmacion = false
      this.$router.push('/Entrenadores')
    },

    addAchievement() {
      this.formulario.logros.push({
        title: '',
        description: '',
        date: new Date().toISOString().split('T')[0]
      });
    },

    removeAchievement(index) {
      this.formulario.logros.splice(index, 1);
    },


    agregarEspecialidad() {
      this.formulario.especialidades.push({ description: '' });
    },

    eliminarEspecialidad(index) {
      this.formulario.especialidades.splice(index, 1);
    },



    validarTelefono() {
      // Eliminar caracteres no numéricos para validar
      const soloDigitos = this.formulario.telefono.replace(/\D/g, '');

      if (soloDigitos && soloDigitos.length < 10) {
        this.errores.telefono = 'Teléfono debe tener 10 dígitos';
      } else {
        this.errores.telefono = '';
      }
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

    // Función para formatear el teléfono mientras se escribe
    formatPhoneInput(event) {
      const input = event.target.value;
      // Eliminar todos los caracteres que no sean dígitos
      let cleaned = input.replace(/\D/g, '');

      // Limitar a 10 dígitos
      if (cleaned.length > 10) cleaned = cleaned.substring(0, 10);

      // Aplicar formato mientras se escribe
      let formatted = '';
      if (cleaned.length > 0) {
        formatted = '(' + cleaned.substring(0, 3);
        if (cleaned.length > 3) {
          formatted += ') ' + cleaned.substring(3, 6);
          if (cleaned.length > 6) {
            formatted += '-' + cleaned.substring(6, 10);
          }
        }
      }

      // Actualizar el valor en el modelo
      this.formulario.telefono = formatted;
    },

    // Función para formatear el teléfono para mostrar
    formatPhoneForDisplay(phone) {
      if (!phone) return '';
      // Eliminar todo excepto dígitos
      const cleaned = phone.replace(/\D/g, '');

      // Aplicar formato (xxx) xxx-xxxx
      const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
      if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
      }
      return phone;
    },

    // Cargar datos del usuario
    cargarDatosUsuario() {
      if (this.user) {
        if (!this.formulario.nombre && this.user.name) {
          this.formulario.nombre = this.user.name;
        }

        if (!this.formulario.email && this.user.email) {
          this.formulario.email = this.user.email;
        }

        if (!this.formulario.telefono && this.user.phone) {
          this.formulario.telefono = this.formatPhoneForDisplay(this.user.phone);
        }

        if (!this.formulario.ubicacion && this.user.location) {
          this.formulario.ubicacion = this.user.location;
        }
      }
    },


  },
  mounted() {
    this.user = JSON.parse(sessionStorage.getItem('user') || null),
      this.formulario.user_id = this.user.id;

    if (this.user) {
      this.formulario.user_id = this.user.id;
      this.cargarDatosUsuario();
    }
  }
}
</script>




<style scoped>
@import '../../../scss/Entrenadores/entrenadores_navbar.scss';
@import '../../../scss/Entrenadores/solicitud.scss';
@import '/resources/scss/Entrenadores/solicitud_logrosyespecialidades.scss';


.location-select {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  background-color: white;
  color: #333;
  transition: all 0.3s ease;
}

.location-select:focus {
  border-color: #4a90e2;
  outline: none;
  box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}

/* Ajustar el selector en formularios responsivos */
@media (max-width: 767px) {
  .location-select {
    padding: 10px 12px;
    font-size: 0.95rem;
  }
}


.training-approach-section {
  margin-bottom: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.approach-card {
  padding: 1.5rem;
}

.form-group.floating-label {
  position: relative;
  margin-bottom: 1.5rem;

  label {
    position: absolute;
    top: 18px;
    left: 15px;
    font-size: 1rem;
    color: #6c757d;
    pointer-events: none;
    transition: all 0.3s ease;
    background: white;
    padding: 0 5px;
  }

  .styled-textarea {
    width: 100%;
    padding: 1.5rem;
    border: 1px solid #e1e5eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    min-height: 150px;
    resize: vertical;
    background: #f8fafc;

    &:focus {
      border-color: #4a90e2;
      outline: none;
      box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
      background: white;
    }

    &:focus~label,
    &:not(:placeholder-shown)~label {
      top: -10px;
      left: 12px;
      font-size: 0.85rem;
      color: #4a90e2;
      font-weight: 500;
    }
  }
}

.input-hint {
  font-size: 0.85rem;
  color: #6c757d;
  margin-top: 8px;
  padding: 0 5px;
  font-style: italic;
  line-height: 1.4;
}

.approach-tips {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-top: 1.5rem;
}

.tip-card {
  background: #f0f7ff;
  border-radius: 8px;
  padding: 15px;
  display: flex;
  align-items: flex-start;
  border-left: 3px solid #4a90e2;

  .tip-icon {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    flex-shrink: 0;
    color: #4a90e2;
  }

  p {
    margin: 0;
    font-size: 0.9rem;
    color: #495057;
    line-height: 1.4;
  }
}

/* Ajustes responsivos */
@media (max-width: 768px) {
  .approach-tips {
    grid-template-columns: 1fr;
  }

  .training-approach-section {
    border-radius: 10px;
  }
}




/* sección de disponibilidad */
.availability-section {
  margin-top: 2rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  padding: 5rem;
  transition: all 0.3s ease;

  &:hover {
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
  }
}

.availability-header {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;

  .section-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;

    svg.title-icon {
      width: 24px;
      height: 24px;
      margin-right: 10px;
      color: #4a90e2;
    }
  }
}

.availability-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
  grid-auto-rows: min-content;
  align-items: start;
}

.availability-day {
  position: relative;
  background: #f8fafc;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
  overflow: hidden;
  min-height: 60px;
  display: flex;
  flex-direction: column;

  label {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    font-weight: 500;
    color: #4a5568;
    cursor: pointer;
    position: relative;
    z-index: 2;
    min-height: 60px;
    box-sizing: border-box;

    input[type="checkbox"] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;

      &:checked ~ .day-highlight {
        opacity: 1;
        transform: scale(1);
      }
      
    }

    .custom-checkbox {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 2px solid #cbd5e0;
      border-radius: 6px;
      margin-right: 10px;
      position: relative;
      transition: all 0.2s ease;

      &::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 6px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        opacity: 0;
        transition: opacity 0.2s ease;
      }
    }

    input:checked + .custom-checkbox {
      background: #4a90e2;
      border-color: #4a90e2;

      &::after {
        opacity: 1;
      }
    }
  }

  .day-highlight {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(74, 144, 226, 0.15);
    opacity: 0;
    transform: scale(0.9);
    transition: all 0.3s ease;
    z-index: 1;
  }

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  }
}

.time-slots {
  padding: 0 15px 15px;
  background: rgba(255, 255, 255, 0.95);
  position: relative;
  z-index: 2;
  border-top: 1px solid rgba(226, 232, 240, 0.5);
  flex-grow: 1;

  .time-slot {
    display: flex;
    align-items: center;
    margin-top: 12px;
    width: 100%;

    span {
      font-size: 0.8rem;
      color: #4a5568;
      min-width: 25px;
    }

    input[type="time"] {
      flex: 1;
      padding: 6px 8px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: white;
      font-size: 0.8rem;
      transition: all 0.2s ease;
      box-sizing: border-box;
      width: 50%;
      max-width: 100%;

      &:focus {
        border-color: #4a90e2;
        outline: none;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.15);
      }
    }
  }
}

/* Diseño responsivo */
@media (max-width: 992px) {
  .availability-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  }
}

@media (max-width: 768px) {
  .availability-grid {
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
  }

  .availability-day label {
    padding: 10px 12px;
    font-size: 0.9rem;
    min-height: 55px;
  }

  .time-slots {
    padding: 0 10px 10px;

    .time-slot {
      flex-direction: row;
      align-items: center;
      margin-top: 8px;

      span {
        margin-bottom: 0;
        font-size: 0.75rem;
        min-width: 20px;
      }

      input[type="time"] {
        padding: 5px 8px;
        font-size: 0.75rem;
        max-width: 100px;
      }
    }
  }
}

@media (max-width: 576px) {
  .availability-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .availability-section {
    padding: 1rem;
  }

  .availability-day {
    min-height: 55px;

    label {
      min-height: 55px;
      padding: 8px 10px;
      font-size: 0.85rem;
    }
  }

  .time-slots {
    .time-slot {
      input[type="time"] {
        font-size: 0.7rem;
        max-width: 90px;
      }
    }
  }
}

@media (max-width: 380px) {
  .availability-grid {
    grid-template-columns: 1fr;
  }
  
  .time-slots .time-slot input[type="time"] {
    max-width: 100%;
  }
}
</style>