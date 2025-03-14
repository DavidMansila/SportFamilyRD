<template>
  <div class="entrenadores-page">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>
        <h1>SportFamilyRD</h1>
      </div>
      <div class="nav-links">
        <a href="/Noticias" class="nav-link">Noticias</a>
        <a href="/Calendario" class="nav-link">Calendario</a>
        <a href="/Tienda" class="nav-link">Tienda</a>
        <a href="/Entrenadores" class="nav-link">Entrenadores</a>
        <a href="/Foro" class="nav-link">Foro</a>
      </div>
      <div class="auth-buttons">
        <a href="/Ajustes">
          <button class="auth-btn">Ajustes</button>
        </a>
        <a href="/Login">
          <button class="auth-btn">Login</button>
        </a>
      </div>
    </nav>

    <!-- Título de la página -->
    <h2 class="page-title">Lista de Entrenadores</h2>

    <!-- Sección para enviar solicitud de ser entrenador -->
    <div class="solicitud-entrenador">
      <h2 class="section-title">¿Quieres ser entrenador?</h2>
      <p class="section-description">Únete a nuestro equipo y ayuda a otros a alcanzar sus metas deportivas.</p>
      <a href="/Solicitud" class="btn btn-primary">Enviar Solicitud</a>
    </div>

    <!-- Lista de entrenadores -->
    <div class="entrenador-list">
      <div
        v-for="entrenador in entrenadores"
        :key="entrenador.id"
        class="entrenador-card"
      >
        <div class="card-content">
          <img :src="entrenador.foto" alt="foto de entrenador" class="entrenador-foto" />
          <h3 class="entrenador-nombre">{{ entrenador.nombre }}</h3>
          <p class="entrenador-deporte">{{ entrenador.deporte }}</p>
          <p class="entrenador-experiencia">{{ entrenador.experiencia }}</p>
          <p class="entrenador-testimonio">"{{ entrenador.testimonio }}"</p>
          <div class="entrenador-acciones">
            <button @click="verPerfil(entrenador)" class="btn btn-link">Ver perfil</button>
            <button @click="sendMessage(entrenador)" class="btn btn-message">Enviar Solicitud</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pop-up del perfil del entrenador -->
    <div v-if="entrenadorSeleccionado" class="popup-overlay" @click="cerrarPerfil">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarPerfil">×</button>
        <img :src="entrenadorSeleccionado.foto" alt="foto de entrenador" class="popup-foto" />
        <h3 class="popup-nombre">{{ entrenadorSeleccionado.nombre }}</h3>
        <p class="popup-deporte">{{ entrenadorSeleccionado.deporte }}</p>
        <p class="popup-experiencia">{{ entrenadorSeleccionado.experiencia }}</p>
        <p class="popup-testimonio">"{{ entrenadorSeleccionado.testimonio }}"</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EntrenadoresComponent',
  data() {
    return {
      entrenadores: [
        {
          id: 1,
          nombre: 'Carlos Pérez',
          deporte: 'Fútbol',
          experiencia: '10 años entrenando fútbol a nivel profesional.',
          foto: 'https://via.placeholder.com/300',
          testimonio: '¡Es un entrenador increíble! Me ayudó a mejorar mi técnica.',
        },
        {
          id: 2,
          nombre: 'Ana Gómez',
          deporte: 'Tenis',
          experiencia: '5 años entrenando a jugadores jóvenes y adultos.',
          foto: 'https://via.placeholder.com/300',
          testimonio: '¡Ana tiene una gran visión estratégica del juego!',
        },
        {
          id: 3,
          nombre: 'Juan Díaz',
          deporte: 'Baloncesto',
          experiencia: '15 años entrenando en colegios y academias de baloncesto.',
          foto: 'https://via.placeholder.com/300',
          testimonio: 'Gracias a Juan, mi rendimiento ha mejorado enormemente en los partidos.',
        },
        {
          id: 4,
          nombre: 'Laura Martínez',
          deporte: 'Natación',
          experiencia: '8 años de experiencia entrenando a nadadores de nivel olímpico.',
          foto: 'https://via.placeholder.com/300',
          testimonio: 'Laura sabe cómo motivar a sus nadadores, ¡es la mejor!',
        },
        {
          id: 5,
          nombre: 'Ricardo Gómez',
          deporte: 'Ciclismo',
          experiencia: '6 años entrenando atletas en competiciones nacionales e internacionales.',
          foto: 'https://via.placeholder.com/300',
          testimonio: 'Ricardo me ayudó a mejorar mi resistencia y mi rendimiento en cada carrera.',
        },
        {
          id: 6,
          nombre: 'Sofia Martínez',
          deporte: 'Atletismo',
          experiencia: '7 años de experiencia trabajando con atletas de todas las edades.',
          foto: 'https://via.placeholder.com/300',
          testimonio: 'Sofia me enseñó cómo entrenar mi mente tanto como mi cuerpo.',
        },
        {
          id: 7,
          nombre: 'David López',
          deporte: 'Artes Marciales',
          experiencia: '12 años enseñando artes marciales en academias de prestigio.',
          foto: 'https://via.placeholder.com/300',
          testimonio: 'Gracias a David, mi disciplina y enfoque en la vida han mejorado.',
        },
      ],
      entrenadorSeleccionado: null, // Entrenador seleccionado para el pop-up
    };
  },
  methods: {
    // Simula el envío de una solicitud de mensaje a un entrenador
    sendMessage(entrenador) {
      alert(`Solicitud enviada al entrenador ${entrenador.nombre}. Se le ha notificado.`);
    },

    // Muestra el pop-up con la información del entrenador
    verPerfil(entrenador) {
      this.entrenadorSeleccionado = entrenador;
    },

    // Cierra el pop-up
    cerrarPerfil() {
      this.entrenadorSeleccionado = null;
    },
  },
};
</script>

<style scoped>
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f8f9fa;
}

/* ------------------- ESTILOS DEL NAVBAR ------------------- */
.navbar {
  background: linear-gradient(to right, #000000, #ffb16c);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logo {
  width: 50px;
  height: 50px;
  border-radius: 50%;
}

.logo-container {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.logo-container h1 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
  transition: color 0.3s ease-in-out;
}

.nav-link:hover {
  color: #ffd700;
}

.auth-buttons {
  display: flex;
  gap: 1rem;
}

.auth-btn {
  background: transparent;
  border: 2px solid white;
  color: white;
  padding: 0.5rem 1.2rem;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.auth-btn:hover {
  background-color: white;
  color: #ff3149;
}

/* Estilos Generales de la Página */
.page-title {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 40px;
  text-align: center;
  padding-top: 30px;
}

/* Sección de solicitud de entrenador */
.solicitud-entrenador {
  background: linear-gradient(to right, #000000, #676767);
  border-radius: 12px;
  padding: 40px 20px;
  margin-bottom: 40px;
  text-align: center;
  color: white;
}

.section-title {
  font-size: 2rem;
  margin-bottom: 10px;
}

.section-description {
  font-size: 1.1rem;
  margin-bottom: 20px;
}

.btn-primary {
  background-color: rgb(0, 0, 0);
  color: #ffffff;
  padding: 12px 30px;
  font-size: 1.1rem;
  border-radius: 25px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.btn-primary:hover {
  background-color: #000000;
  transform: translateY(-2px);
  color: burlywood;
}

/* Lista de entrenadores */
.entrenador-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.entrenador-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
  padding: 20px;
  text-align: center;
  transition: all 0.3s ease-in-out;
}

.entrenador-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.entrenador-foto {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 15px;
}

.entrenador-nombre {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 10px;
}

.entrenador-deporte,
.entrenador-experiencia,
.entrenador-testimonio {
  font-size: 1rem;
  color: #555;
  margin-bottom: 10px;
}

.entrenador-deporte {
  font-weight: bold;
}

.entrenador-testimonio {
  font-style: italic;
  color: #777;
}

.entrenador-acciones {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-top: 15px;
}

.btn {
  padding: 10px 20px;
  font-size: 1rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-link {
  background-color: transparent;
  color: #6a11cb;
  text-decoration: none;
}

.btn-link:hover {
  text-decoration: underline;
}

.btn-message {
  background-color: #28a745;
  color: white;
}

.btn-message:hover {
  background-color: #218838;
}

/* Estilos del pop-up */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.popup-content {
  background-color: white;
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
  position: relative;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.btn-cerrar {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #333;
}

.popup-foto {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 15px;
}

.popup-nombre {
  font-size: 1.8rem;
  color: #333;
  margin-bottom: 10px;
}

.popup-deporte,
.popup-experiencia,
.popup-testimonio {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 10px;
}

.popup-deporte {
  font-weight: bold;
}

.popup-testimonio {
  font-style: italic;
  color: #777;
}

/* Responsive Design */
@media (max-width: 768px) {
  .page-title {
    font-size: 2rem;
  }

  .solicitud-entrenador {
    padding: 20px;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .section-description {
    font-size: 1rem;
  }

  .entrenador-list {
    grid-template-columns: 1fr;
  }

  .popup-content {
    padding: 1rem;
  }

  .popup-nombre {
    font-size: 1.5rem;
  }

  .popup-deporte,
  .popup-experiencia,
  .popup-testimonio {
    font-size: 1rem;
  }
}
</style>