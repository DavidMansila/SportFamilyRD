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
            <a href="/Settings">
                  <button class="auth-btn">Ajustes</button>
            </a>
            <a href="/Login">
                 <button class="auth-btn">Login</button>
            </a>
        </div>
      </nav>


    <h1 class="page-title">Lista de Entrenadores</h1>

    <!-- Sección para enviar solicitud de ser entrenador -->
    <div class="solicitud-entrenador">
      <h2 class="section-title">¿Quieres ser entrenador?</h2>
      <button @click="redirectToSolicitud" class="btn btn-primary">Enviar Solicitud</button>
    </div>

    <!-- Lista de entrenadores -->
    <div class="entrenador-list">
      <div v-for="entrenador in entrenadores" :key="entrenador.id" class="entrenador-card">
        <div class="card-content">
          <img :src="entrenador.foto" alt="foto de entrenador" class="entrenador-foto" />
          <h3 class="entrenador-nombre">{{ entrenador.nombre }}</h3>
          <p class="entrenador-deporte">{{ entrenador.deporte }}</p>
          <p class="entrenador-experiencia">{{ entrenador.experiencia }}</p>
          <p class="entrenador-testimonio">"{{ entrenador.testimonio }}"</p>
          <a :href="'/entrenador/' + entrenador.id" class="btn btn-link">Ver perfil</a>
          <button @click="sendMessage(entrenador)" class="btn btn-message">Enviar Solicitud</button>
        </div>
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
        }
      ]
    };
  },
  methods: {
    // Redirige al formulario para enviar solicitud de ser entrenador
    redirectToSolicitud() {
      this.$router.push('/solicitar-entrenador');
    },

    // Simula el envío de una solicitud de mensaje a un entrenador
    sendMessage(entrenador) {
      alert(`Solicitud enviada al entrenador ${entrenador.nombre}. Se le ha notificado.`);
      // Aquí podrías integrar una API o lógica para gestionar las notificaciones
    }
  }
};
</script>

<style scoped>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }


  .logo-container {
    display: flex;
    gap: 1rem;
    flex-direction: row;

    h1 {
    font-size: 2rem;
    font-weight: bold;
    color: rgb(255, 255, 255);
  }
  }
  
  .container {
    margin: 0 auto;
  }

  
  /* Navbar */
  .navbar {
    background: linear-gradient(to right, #000000, #ffb16c);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo {
    width: 50px;
    height: 50px;
}

.nav-links {
    display: flex;
    gap: 2rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: bold;
    transition: color 0.3s ease-in-out;
}

.nav-link:hover {
    color: #fbbf24;
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


.page-title {
  font-size: 3rem;
  color: #333;
  margin-bottom: 40px;
  text-align: center;
  padding-top: 30px;
}

.solicitud-entrenador {
  margin-bottom: 40px;
  text-align: center;
}

.solicitud-entrenador h2 {
  font-size: 1.8rem;
  margin-bottom: 20px;
  color: #333;
}

.solicitud-entrenador .btn-primary {
  background-color: #007bff;
  color: white;
  padding: 15px 30px;
  font-size: 1.1rem;
  border-radius: 5px;
  border: none;
  cursor: pointer;
}

.solicitud-entrenador .btn-primary:hover {
  background-color: #0056b3;
}

.entrenador-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
}

.entrenador-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
  width: 280px;
  padding: 20px;
  text-align: center;
  transition: all 0.3s ease-in-out;
}

.entrenador-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.card-content {
  display: flex;
  flex-direction: column;
  align-items: center;
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

.btn {
  padding: 10px 20px;
  font-size: 1rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 15px;
}

.btn-link {
  color: #007bff;
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
</style>
