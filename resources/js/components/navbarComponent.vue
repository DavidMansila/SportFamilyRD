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
        Solicitudes-U
      </router-link>

      <router-link v-if="user_type == 'admin'" to="/solicitudes-entrenadores" class="nav-link">
        Solicitudes-E
      </router-link>
    </div>

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

      <router-link v-if="user" to="/ajustes" class="Ajustes">
        <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" />
      </router-link>

      <router-link v-if="user" to="/perfil" class="Perfil">
        <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
      </router-link>


      <!-- SI EL USUARIO ESTA REGISTRADO PUES APARECE LOGOUT Y SINO PUES APARECE EL REGISTRARSE -->
      <template v-if="user">
        <div class="logout-container">
          <button @click="showLogoutConfirm = true" class="Logout">
            <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
          </button>
        </div>
        <!-- Diálogo de confirmación para cerrar seccion-->
        <div v-if="showLogoutConfirm" class="confirm-dialog-overlay">
          <div class="confirm-dialog">
            <h3>¿Estás seguro de cerrar sesión?</h3>
            <div class="dialog-buttons">
              <button @click="logout" class="confirm-btn">Confirmar</button>
              <button @click="showLogoutConfirm = false" class="cancel-btn">Cancelar</button>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <router-link :to="{ path: '/signup', query: { panel: 'signup' } }" class="Signup">
          <img src="/imagenes/Signup-Icon.png" alt="Registrarse" class="signup-icon" />
        </router-link>
      </template>

    </div>

    <CarritoComponent :isVisible="isCartVisible" :cartItems="$store.getters.cartItems" :user="user" @close="closeCart"
      @update-quantity="handleUpdateQuantity" @remove-item="handleRemoveItem" @checkout="handleCheckout" />

  </nav>
</template>


<script>
import axios from 'axios';
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

      authMessage: '',

      showLogoutConfirm: false,

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

        sessionStorage.removeItem('user');
        this.user = null;
        this.user_type = '';
        this.showLogoutConfirm = false;

        window.dispatchEvent(new CustomEvent('user-logged-out'));

        this.$router.push('/');
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
        if (error.response?.status === 419) {
          await axios.get('/sanctum/csrf-cookie');
          return this.logout();
        }
      }
    },

    toggleCart() {
      this.isCartVisible = !this.isCartVisible;
    },

    closeCart() {
      this.isCartVisible = false;
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
      // Forzar actualización del carrito
      if (this.isCartVisible && this.$refs.cartComponent) {
        this.$refs.cartComponent.fetchCart();
      }

      this.toggleCart();
    }

  }
}
</script>


<style lang="scss">
@import '/resources/scss/Navbar/Navbar_responsive.scss';

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



// PARA EL LOGOUT

.logout-container {
  display: flex;
  align-items: center;
  justify-content: center;
}

.Logout {
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
  position: relative;
  overflow: hidden;

  .logout-icon {
    width: 24px;
    height: 24px;
    transition: transform 0.3s ease-in-out;
  }

  &:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
    box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);

    .logout-icon {
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
}

/* En la sección de Imagenes del nav bar PARA EL CARRITO */

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
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}


// PARA EL LOGOUT UN MENSAJE DE CONFIRMACION

.confirm-dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(3px);
  animation: fadeIn 0.3s ease;
}

.confirm-dialog {
  background: linear-gradient(145deg, #ffffff, #f8f9fa);
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  text-align: center;
  max-width: 450px;
  width: 90%;
  border: 1px solid rgba(255, 255, 255, 0.3);
  transform: scale(0.95);
  animation: scaleUp 0.3s ease forwards;

  h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;

    &::before {
      content: '🔒';
      font-size: 1.8rem;
    }
  }
}

.dialog-buttons {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  margin-top: 2rem;
}

.confirm-btn,
.cancel-btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  font-size: 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.confirm-btn {
  background: linear-gradient(135deg, #ff6b6b, #e74c3c);
  color: white;

  &::after {
    content: '✓';
    font-size: 1.2rem;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
  }
}

.cancel-btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;

  &::after {
    content: '✕';
    font-size: 1.2rem;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes scaleUp {
  from {
    transform: scale(0.95);
  }

  to {
    transform: scale(1);
  }
}
</style>