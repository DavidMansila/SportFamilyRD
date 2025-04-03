<template>
  <div class="entrenadores-page">

      <!-- Navbar -->
      <nav class="navbar">
        <div class="logo-container">
          <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>
      </div>
        <div class="nav-links">
            <a href="/Noticias" class="nav-link">Noticias</a>
            <a href="/Calendario" class="nav-link">Calendario</a>
             <a href="/Tienda" class="nav-link">Tienda</a>
             <a href="/Entrenadores" class="nav-link">Entrenadores</a>
             <a href="/Foro" class="nav-link">Foro</a>
        </div>

        <div class="Imagenes">

            <a class="Carrito">
                <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
            </a>

            <a href= "/Ajustes" class="Ajustes">
                <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
            </a>

            <a class="Perfil">
                <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
            </a>

            <a :href=" login ? '/Login' : '/Logout' " class="Logout">
                <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
            </a>

        </div>
      </nav>


    <!-- Título de la página -->
    <h1 class="page-title">Lista de Entrenadores</h1>

    <!-- Sección para enviar solicitud de ser entrenador -->
    <div class="contenedor">
  <div class="solicitud-entrenador">
    <h2 class="section-title">¿Quieres ser entrenador?</h2>
    <p class="section-description">Únete a nuestro equipo y ayuda a otros a alcanzar sus metas deportivas.</p>
    <a href="/Solicitud" class="btn btn-primary">Enviar Solicitud</a>
  </div>
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
            <!-- <button @click="verPerfil(entrenador)" class="btn btn-link">Ver</button> -->
            <button @click="sendMessage(entrenador)" class="btn btn-message">Solicitud de mensaje</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Pop-up del perfil del entrenador
    <div v-if="entrenadorSeleccionado" class="popup-overlay" @click="cerrarPerfil">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarPerfil">×</button>
        <img :src="entrenadorSeleccionado.foto" alt="foto de entrenador" class="popup-foto" />
        <h3 class="popup-nombre">{{ entrenadorSeleccionado.nombre }}</h3>
        <p class="popup-deporte">{{ entrenadorSeleccionado.deporte }}</p>
        <p class="popup-experiencia">{{ entrenadorSeleccionado.experiencia }}</p>
        <p class="popup-testimonio">"{{ entrenadorSeleccionado.testimonio }}"</p>
      </div>
    </div> -->

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
          foto: '/imagenes/Entrenador1.png',
          testimonio: '¡Es un entrenador increíble! Me ayudó a mejorar mi técnica.',
        },
        {
          id: 2,
          nombre: 'Ana Gómez',
          deporte: 'Tenis',
          experiencia: '5 años entrenando a jugadores jóvenes y adultos.',
          foto: '/imagenes/Entrenador2.png',
          testimonio: '¡Ana tiene una gran visión estratégica del juego!',
        },
        {
          id: 3,
          nombre: 'Juan Díaz',
          deporte: 'Baloncesto',
          experiencia: '15 años entrenando en colegios y academias de baloncesto.',
          foto: '/imagenes/Entrenador3.png',
          testimonio: 'Gracias a Juan, mi rendimiento ha mejorado enormemente en los partidos.',
        },
        {
          id: 4,
          nombre: 'Laura Martínez',
          deporte: 'Natación',
          experiencia: '8 años de experiencia entrenando a nadadores de nivel olímpico.',
          foto: '/imagenes/Entrenador4.png',
          testimonio: 'Laura sabe cómo motivar a sus nadadores, ¡es la mejor!',
        },
        {
          id: 5,
          nombre: 'Ricardo Gómez',
          deporte: 'Ciclismo',
          experiencia: '6 años entrenando atletas en competiciones nacionales e internacionales.',
          foto: '/imagenes/Entrenador5.png',
          testimonio: 'Ricardo me ayudó a mejorar mi resistencia y mi rendimiento en cada carrera.',
        },
        {
          id: 6,
          nombre: 'Sofia Martínez',
          deporte: 'Atletismo',
          experiencia: '7 años de experiencia trabajando con atletas de todas las edades.',
          foto: '/imagenes/Entrenador6.png',
          testimonio: 'Sofia me enseñó cómo entrenar mi mente tanto como mi cuerpo.',
        },
        {
          id: 7,
          nombre: 'David López',
          deporte: 'Artes Marciales',
          experiencia: '12 años enseñando artes marciales en academias de prestigio.',
          foto: '/imagenes/Entrenador7.png',
          testimonio: 'Gracias a David, mi disciplina y enfoque en la vida han mejorado.',
        },
      ],
    };
  },
  methods: {
    
    // Simula el envío de una solicitud de mensaje a un entrenador
    sendMessage(entrenador) {
      if (this.solicitudesEnviadas[entrenador.id]) {
        alert(`Ya has enviado una solicitud de mensaje al entrenador ${entrenador.nombre}.`);
      } else {
        this.solicitudesEnviadas[entrenador.id] = true;
        alert(`Solicitud enviada al entrenador ${entrenador.nombre}. Se le ha notificado.`);
      }
    },


  },
};
</script>


<style scoped>
  @import '../../../scss/Entrenadores/entrenadores.scss';
</style>
