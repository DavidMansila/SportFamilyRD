<template>
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

            <router-link class="Logout" @click="logout()">
                <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
            </router-link>

        </div>
    </nav>
</template>


<script>
export default {
    data() {
        return {
            user: [],
        }
    },
    async created() {
        await this.loadUser();
    },
    methods: {
        logout() {
            axios.post('/logout')
                .then(response => {
                    console.log('Logout successful:', response.data);
                    this.user = null;
                    sessionStorage.removeItem('user');
                    this.$router.push('/');

                }).catch((error) => {
                    console.log(error);
                    console.error('Error al cerrar sesión:', error);
                });
        }
    }
}
</script>


<style lang="scss">


/* Navbar */
.navbar {
    background: linear-gradient(to right, #000000, #0051a8);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between; /* Distribuye el espacio entre los elementos */
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  /* Logo a la izquierda */
  .logo-container {
    display: flex;
    align-items: center;
  }
  
  .logo {
    width: 200px; /* Tamaño del logo */
    height: 70px;
  }
  
  
  /* Enlaces en el centro */
  .nav-links {
    display: flex;
    gap: 2rem;
    flex-grow: 1; /* Ocupa el espacio disponible */
    justify-content: center; /* Centra los enlaces */
  }
  
  .nav-link {
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 500;
    transition: color 0.3s ease-in-out;
  }
  
  .nav-link:hover {
    color: #fbbf24;
  }
  
  
  /* Imagenes del nav bar */
  
  .Imagenes {
    display: flex;
    align-items: center;
    gap: 15px; /* Espaciado entre los iconos */
  }
  
  .Imagenes a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px; /* Tamaño uniforme */
    height: 40px;
    border-radius: 50%; /* Forma circular */
    background: rgba(255, 255, 255, 0.1); /* Fondo semitransparente */
    transition: all 0.3s ease-in-out;
    position: relative;
    overflow: hidden;
  }
  
  .Imagenes a img {
    width: 24px; /* Tamaño del ícono */
    height: 24px;
    transition: transform 0.3s ease-in-out;
  }
  
  
  /* Efecto hover */
  .Imagenes a:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
    box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
  }
  
  .Imagenes a:hover img {
    transform: rotate(10deg) scale(1.2);
  }
  
  
  /* Animación sutil de entrada */
  .Imagenes a::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.05);
    transform: scale(0);
    border-radius: 50%;
    transition: transform 0.3s ease-in-out;
  }
  
  .Imagenes a:hover::before {
    transform: scale(1.3);
    opacity: 0;
  }

  
/* ------------------- RESPONSIVE DE HOME PARA TODOS LOS DISPOSITIVOS ------------------ */

/* Pantallas grandes (TVs, monitores 4K) - 1920px+ */
@media (min-width: 1920px) {
  .navbar {
    padding: 1.5rem 2rem;
  }
  }
  
  /* Laptops y pantallas medianas - 1440px-1599px */
  @media (max-width: 1599px) and (min-width: 1440px) {
  .navbar {
    padding: 1.2rem 1.5rem;
  }
  
  .nav-links {
    gap: 1.8rem;
  }
  
  .logo {
    width: 190px;
    height: 65px;
  }
  }
  
  /* Tablets en horizontal y laptops pequeñas - 1200px-1439px */
  @media (max-width: 1439px) and (min-width: 1200px) {
  .navbar {
    padding: 1rem 1.2rem;
  }
  
  .nav-links {
    gap: 1.5rem;
  }
  
  .logo {
    width: 180px;
    height: 60px;
  }
  }
  
  /* Tablets grandes - 1024px-1199px */
  @media (max-width: 1199px) and (min-width: 1024px) {
  .navbar {
    padding: 1rem;
  }
  
  .nav-links {
    gap: 1.2rem;
  }
  
  .logo {
    width: 170px;
    height: 55px;
  }
  }
  
  /* Tablets en vertical - 768px-1023px */
  @media (max-width: 1023px) and (min-width: 768px) {
  .navbar {
    flex-direction: column;
    padding: 1rem;
  }
  
  .logo-container {
    margin-bottom: 1rem;
  }
  
  .nav-links {
    flex-wrap: wrap;
    gap: 1rem;
    margin: 1rem 0;
  }
  
  .logo {
    width: 180px;
    height: 60px;
  }
  }
  
  /* Teléfonos grandes - 576px-767px */
  @media (max-width: 767px) and (min-width: 576px) {
  .navbar {
    padding: 0.8rem 1rem;
  }
  
  .nav-links {
    gap: 0.8rem;
  }
  
  .logo {
    width: 170px;
    height: 55px;
  }
  }
  
  /* Teléfonos medianos - 481px-575px */
  @media (max-width: 575px) and (min-width: 481px) {
  .navbar {
    padding: 0.8rem;
  }
  
  .logo {
    width: 160px;
    height: 50px;
  }
  
  .nav-links {
    gap: 0.7rem;
  }
  
  .nav-link {
    font-size: 0.9rem;
  }
  
  .Imagenes a {
    width: 35px;
    height: 35px;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  }
  
  /* Teléfonos pequeños - 320px-480px */
  @media (max-width: 480px) {
  .navbar {
    padding: 0.8rem;
  }
  
  .logo {
    width: 160px;
    height: 60px;
  }
  
  .nav-links {
    gap: 1rem;
  }
  
  .nav-link {
    font-size: 0.9rem;
  }
  
  .Imagenes a {
    width: 35px;
    height: 35px;
  }
  }
  
  /* Teléfonos muy pequeños - hasta 320px */
  @media (max-width: 320px) {
  .navbar {
    padding: 0.6rem;
  }
  
  .logo {
    width: 140px;
    height: 40px;
  }
  
  .nav-links {
    gap: 0.5rem;
  }
  
  .nav-link {
    font-size: 0.8rem;
  }
  
  .Imagenes a {
    width: 30px;
    height: 30px;
  }
  }

</style>