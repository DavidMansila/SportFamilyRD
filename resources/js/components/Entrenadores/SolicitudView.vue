<template>
  <div class="solicitud-view">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo"/>
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
           <a v-if = "userType == 'entrenador'" href="/SolicitudesUsuarios" class="nav-link">Solicitudes</a>

          <!-- Secciones para entrenadores -->
          <a v-if = "userType == 'admin'" href="/SolicitudesEntrenadores" class="nav-link">Solicitudes</a>


      </div>

      <div class="Imagenes">

        <a href="#" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
        </a>

        <a href= "/Ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
        </a>

        <a href= "/Perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
        </a>

        <a :href=" login ? '/Login' : '/Logout' " class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
        </a>

      </div>
    </nav>

    

    <!-- Contenido principal -->
    <div class="container">
      <!-- Header con animación -->
      <div class="header-animation">
        <h1 class="page-title">Únete a Nuestro Equipo de Entrenadores</h1>
        <p class="page-subtitle">Comparte tu conocimiento y ayuda a otros a alcanzar sus metas deportivas</p>
        <div class="progress-steps">
    <div 
      v-for="step in 3" 
      :key="step" 
      class="step"
      :class="{
        'active': pasoActual === step,
        'completed': pasoActual > step
      }"
    >
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
              <input
                type="text"
                id="nombre"
                v-model="formulario.nombre"
                placeholder=" "
                required
                @input="validarNombre"
              />
              <label for="nombre">Nombre completo</label>
              <span class="error-message" v-if="errores.nombre">{{ errores.nombre }}</span>
            </div>

            <div class="form-group floating-label">
              <input
                type="email"
                id="email"
                v-model="formulario.email"
                placeholder=" "
                required
                @input="validarEmail"
              />
              <label for="email">Correo electrónico</label>
              <span class="error-message" v-if="errores.email">{{ errores.email }}</span>
            </div>

            <div class="form-group floating-label">
              <input
                type="tel"
                id="telefono"
                v-model="formulario.telefono"
                placeholder=" "
                @input="validarTelefono"
              />
              <label for="telefono">Teléfono (opcional)</label>
              <span class="error-message" v-if="errores.telefono">{{ errores.telefono }}</span>
            </div>

            <div class="form-group floating-label">
              <input
                type="text"
                id="ubicacion"
                v-model="formulario.ubicacion"
                placeholder=" "
                required
              />
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
            <input
              type="number"
              id="experiencia"
              v-model="formulario.experiencia"
              placeholder=" "
              min="0"
              max="50"
              required
            />
            <label for="experiencia">Años de experiencia</label>
          </div>

          <div class="form-group">
            <label class="custom-label">Nivel de certificación</label>
            <div class="radio-group">
              <label v-for="nivel in nivelesCertificacion" :key="nivel.value" class="radio-option">
                <input
                  type="radio"
                  v-model="formulario.nivelCertificacion"
                  :value="nivel.value"
                  required
                />
                <span class="radio-custom"></span>
                {{ nivel.label }}
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="custom-label">Sube tus certificados (PDF, JPG, PNG)</label>
            <div class="file-upload-area" @click="triggerFileInput('certificados')" @dragover.prevent @drop="handleDrop($event, 'certificados')">
              <input
                type="file"
                id="certificados"
                ref="certificadosInput"
                @change="subirArchivos('certificados', $event)"
                multiple
                accept=".pdf,.jpg,.jpeg,.png"
                hidden
              />
              <div v-if="!formulario.certificados.length" class="upload-placeholder">
                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-prev" @click="pasoAnterior">Anterior</button>
            <button type="button" class="btn btn-next" @click="siguientePaso">Siguiente</button>
          </div>
        </div>



        <!-- Paso 3 - Detalles adicionales -->
        <div v-if="pasoActual === 3" class="form-step">
          <h2 class="step-title">Detalles Adicionales</h2>
          
          <div class="form-group">
            <label class="custom-label">Describe tu enfoque de entrenamiento</label>
            <textarea
              v-model="formulario.enfoque"
              placeholder="Ejemplo: 'Me enfoco en desarrollar la técnica fundamental mientras fomento el amor por el deporte...'"
              rows="4"
              required
            ></textarea>
          </div>

          <div class="form-group">
            <label class="custom-label">Logros destacados</label>
            <div class="multi-input">
              <div v-for="(logro, index) in formulario.logros" :key="index" class="input-with-action">
                <input
                  type="text"
                  v-model="formulario.logros[index]"
                  placeholder="Ejemplo: 'Campeón regional 2020'"
                />
                <button type="button" @click="eliminarLogro(index)" class="remove-item">
                  &times;
                </button>
              </div>
              <button type="button" @click="agregarLogro" class="add-item">
                + Añadir otro logro
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="custom-label">Disponibilidad</label>
            <div class="availability-grid">
              <div v-for="dia in diasSemana" :key="dia" class="availability-day">
                <label>
                  <input type="checkbox" v-model="formulario.disponibilidad[dia]" />
                  {{ dia }}
                </label>
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

          <div class="form-group">
            <label class="custom-label">Tarifa por sesión (opcional)</label>
            <div class="price-input">
              <span class="currency">$</span>
              <input
                type="number"
                v-model="formulario.tarifa"
                placeholder="Ejemplo: 25"
                min="0"
              />
              <span class="per-session">/sesión</span>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-prev" @click="pasoAnterior">Anterior</button>
            <button type="submit" class="btn btn-submit">Enviar Solicitud</button>
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
              <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
</template>



<script>
export default {
  name: 'SolicitudView',
  data() {
    return {
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
        nombre: '',
        email: '',
        telefono: '',
        ubicacion: '',
        deporte: '',
        experiencia: '',
        nivelCertificacion: '',
        certificados: [],
        enfoque: '',
        logros: [''],
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
      }
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
      // Validaciones específicas para cada paso
      if (this.pasoActual === 1) {
        if (!this.formulario.nombre.trim()) {
          this.errores.nombre = 'Por favor ingresa tu nombre completo'
          return false
        }
        if (!this.formulario.email.trim()) {
          this.errores.email = 'Por favor ingresa un correo electrónico válido'
          return false
        }
      }
      return true
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
        // Aquí iría la lógica para enviar al backend
        console.log('Formulario enviado:', this.formulario)
        
        // Simular envío exitoso
        this.mostrarConfirmacion = true
        this.resetFormulario()
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
        logros: [''],
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
    }
  }
}
</script>




<style scoped>
@import '../../../scss/Entrenadores/solicitud.scss';
</style>