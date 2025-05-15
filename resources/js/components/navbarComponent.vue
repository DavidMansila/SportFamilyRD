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
      <router-link v-if="user_type == 'entrenador' || user_type == 'admin'" to="/solicitudes-usuarios" class="nav-link">
        SolicitudesU
      </router-link>

      <router-link v-if="user_type == 'admin'" to="/solicitudes-entrenadores" class="nav-link">
        SolicitudesE
      </router-link>
    </div>

    <!-- HACER CARRITO FUNCIONAL, PUEDE SER CREAR UN COMPONENT Y LLAMAR AL COMPONENTE AQUI O CREAR EL CARRITO AQUI MISMO, LO
    MEJOR FUERA HACER UN COMPONENTE DE CARRITO -->
    <div class="Imagenes">

      <div class="carrito-container">
        <button @click="handleCartClick" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon" />
          <span v-if="user && cartItems.length > 0" class="cart-badge">{{ cartItems.length }}</span>
        </button>

        <!-- Mensaje flotante -->
        <transition name="fade">
          <div v-if="authMessage" class="auth-alert">
            {{ authMessage }}
          </div>
        </transition>
      </div>

      <router-link to="/ajustes" class="Ajustes">
        <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" />
      </router-link>

      <router-link to="/perfil" class="Perfil">
        <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
      </router-link>


      <!-- SI EL USUARIO ESTA REGISTRADO PUES APARECE LOGOUT Y SINO PUES APARECE EL REGISTRARSE -->
      <template v-if="user">
        <router-link to="" class="Logout" @click.native.prevent="logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
        </router-link>
      </template>
      <template v-else>
        <router-link to="/signup" class="Signup">
          <img src="/imagenes/Signup-Icon.png" alt="Registrarse" class="signup-icon" />
        </router-link>
      </template>

    </div>

    <CarritoComponent :isVisible="isCartVisible" :cartItems="$store.getters.cartItems" :user="user" @close="closeCart"
      @update-quantity="handleUpdateQuantity" @remove-item="handleRemoveItem" @checkout="handleCheckout" />

  </nav>
</template>


<script>
import CarritoComponent from './CarritoComponent.vue';

export default {
  components: {
    CarritoComponent
  },
  data() {
    return {
      user: null, // Cambiado a null para mejor manejo
      user_type: '',
      isCartVisible: false,
      cartItems: [], // Debes llenar esto con tu lógica de carrito
      authMessage: ''

    }
  },
  created() {
    this.checkAuthStatus();
    // Escuchar eventos personalizados si otras partes de la app afectan la autenticación
    window.addEventListener('user-authenticated', this.checkAuthStatus);
    window.addEventListener('user-logged-out', this.checkAuthStatus);
  },
  beforeDestroy() {
    // Limpiar event listeners
    window.removeEventListener('user-authenticated', this.checkAuthStatus);
    window.removeEventListener('user-logged-out', this.checkAuthStatus);
  },
  methods: {

    checkAuthStatus() {
      // Verificar si hay usuario en sessionStorage
      const userData = sessionStorage.getItem('user');
      if (userData) {
        try {
          const parsedUser = JSON.parse(userData);
          this.user = parsedUser;
          this.user_type = parsedUser.user_type || ''; // Asume que user_type está en el objeto user
        } catch (e) {
          console.error('Error parsing user data:', e);
          this.clearAuthData();
        }
      } else {
        this.clearAuthData();
      }
    },

    clearAuthData() {
      this.user = null;
      this.user_type = '';
    },

    async logout() {
      try {
        await axios.post('/logout');
        this.clearAuthData();
        sessionStorage.removeItem('user');

        // Disparar evento para notificar a otros componentes
        window.dispatchEvent(new Event('user-logged-out'));

        this.$router.push({
          path: '/signup',
          query: { logoutSuccess: 'true' }
        });

        // Mostrar notificación
        this.$toast.success('Has cerrado sesión correctamente', {
          position: 'top-right',
          duration: 3000
        });
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
        this.$toast.error('Ocurrió un error al cerrar sesión', {
          position: 'top-right',
          duration: 3000
        });
      }
    },

    toggleCart() {
      this.isCartVisible = !this.isCartVisible;
    },

    closeCart() {
      this.isCartVisible = false;
    },

    updateQuantity({ index, quantity }) {
      // Tu lógica para actualizar cantidad
      this.cartItems[index].quantity = quantity;
    },

    removeItem(index) {
      // Tu lógica para eliminar item
      this.cartItems.splice(index, 1);
    },

    handleCheckout() {
      // Tu lógica de checkout
      console.log('Procesar compra');
    },

    handleUpdateQuantity({ index, quantity }) {
      this.$store.dispatch('updateQuantity', { index, quantity });
    },

    handleRemoveItem(index) {
      this.$store.dispatch('removeItem', index);
    },

    handleCartClick() {
      if (!this.user) {
        this.authMessage = '⚠️ Debes iniciar sesión para ver el carrito';
        setTimeout(() => {
          this.authMessage = '';
        }, 3000);
        return;
      }
      this.toggleCart();
    }

  }
}
</script>


<style lang="scss">
/* Navbar */
.navbar {
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  /* Distribuye el espacio entre los elementos */
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Logo a la izquierda */
.logo-container {
  display: flex;
  align-items: center;
}

.logo {
  width: 200px;
  /* Tamaño del logo */
  height: 70px;
}


/* Enlaces en el centro */
.nav-links {
  display: flex;
  gap: 2rem;
  flex-grow: 1;
  /* Ocupa el espacio disponible */
  justify-content: center;
  /* Centra los enlaces */
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
  gap: 15px;
  /* Espaciado entre los iconos */
}

.Imagenes a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  /* Tamaño uniforme */
  height: 40px;
  border-radius: 50%;
  /* Forma circular */
  background: rgba(255, 255, 255, 0.1);
  /* Fondo semitransparente */
  transition: all 0.3s ease-in-out;
  position: relative;
  overflow: hidden;
}

.Imagenes a img {
  width: 24px;
  /* Tamaño del ícono */
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

/* En la sección de Imagenes del nav bar */
.Imagenes {
  display: flex;
  align-items: center;
  gap: 15px;

  .Carrito {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease-in-out;
    border: none;
    padding: 0;
    cursor: pointer;

    .carrito-icon {
      width: 24px;
      height: 24px;
      transition: transform 0.3s ease-in-out;
    }

    &:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: scale(1.1);
      box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);

      .carrito-icon {
        transform: rotate(10deg) scale(1.2);
      }
    }

    &::before {
      content: "";
      position: absolute;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.05);
      transform: scale(0);
      border-radius: 50%;
      transition: transform 0.3s ease-in-out;
    }

    &:hover::before {
      transform: scale(1.3);
      opacity: 0;
    }

    .cart-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #e74c3c;
      color: white;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      font-size: 0.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      pointer-events: none;
    }
  }
}

.carrito-container {
  position: relative;
}

.auth-alert {
  position: absolute;
  top: 50px;
  right: 0;
  background: #fff3cd;
  color: #856404;
  padding: 0.8rem 1.2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  font-size: 0.9rem;
  white-space: nowrap;
  z-index: 100;
  border: 1px solid #ffeeba;
}

.auth-alert::before {
  content: "";
  position: absolute;
  top: -10px;
  right: 15px;
  border-width: 5px;
  border-style: solid;
  border-color: transparent transparent #fff3cd transparent;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* ------------------- RESPONSIVE DE HOME PARA TODOS LOS DISPOSITIVOS ------------------ */

/* Pantallas grandes (TVs, monitores 4K) - 1920px+ */
@media (min-width: 1920px) {
  .navbar {
    padding: 1em 1rem;
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