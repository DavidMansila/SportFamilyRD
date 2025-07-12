<template>
  <!-- Navbar -->
  <nav class="navbar">

    <div class="logo-container desktop-only">
      <router-link to="/" class="logo-container">
        <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo" />
      </router-link>
    </div>

    <!-- Botón de menú móvil -->
    <button class="mobile-menu-btn mobile-only" @click="toggleMobileMenu" :class="{ active: isMobileMenuOpen }">
      <div class="hamburger-icon">
        <span class="line top"></span>
        <span class="line middle"></span>
        <span class="line bottom"></span>
      </div>
    </button>

    <div class="nav-links desktop-only">

      <!-- Secciones para usuarios -->
      <span class="nav-link" @click="handleNavClick('/directorio')">Deportes</span>
      <span class="nav-link" @click="handleNavClick('/noticias')">Noticias</span>
      <span class="nav-link" @click="handleNavClick('/calendario')">Calendario</span>
      <span class="nav-link" @click="handleNavClick('/tienda')">Tienda</span>
      <span class="nav-link" @click="handleNavClick('/entrenadores')">Entrenadores</span>
      <span class="nav-link" @click="handleNavClick('/foro')">Foro</span>

      <!-- Secciones condicionales -->
      <span v-if="user?.user_type == 'entrenador'" class="nav-link" @click="handleNavClick('/solicitudes-usuarios')">
        Solicitudes-U
      </span>

      <span v-if="user?.user_type == 'admin'" class="nav-link" @click="handleNavClick('/solicitudes-entrenadores')">
        Solicitudes-E
      </span>
    </div>

    <div class="Imagenes">

      <div class="carrito-container" title="Carrito">
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
        <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" title="Ajustes" />
      </router-link>

      <router-link v-if="user" to="/perfil" class="Perfil" title="Perfil">
        <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
      </router-link>


      <template v-if="user">
        <div class="logout-container" title="Cerrar sesión">
          <button @click="openLogoutModal" class="Logout">
            <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
          </button>
        </div>
        <!-- Diálogo de confirmación para cerrar seccion-->
        <div v-if="showLogoutConfirm" class="confirm-dialog-overlay">
          <div class="confirm-dialog">
            <h3>¿Estás seguro de cerrar sesión?</h3>
            <div class="dialog-buttons">
              <button @click="logout" class="confirm-btn">Confirmar</button>
              <button @click="closeLogoutModal" class="cancel-btn">Cancelar</button>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <router-link :to="{ path: '/signup', query: { panel: 'signup' } }" class="Signup">
          <img src="/imagenes/Signup-icon.png" alt="Registrarse" title="Iniciar sesión" class="signup-icon-thick" />
        </router-link>
      </template>

    </div>

    <CarritoComponent v-if="user" :isVisible="isCartVisible" :cartItems="$store.getters.cartItems" :user="user"
      @close="closeCart" @update-quantity="handleUpdateQuantity" @remove-item="handleRemoveItem"
      @checkout="handleCheckout" />

    <!-- Mensaje de navegación -->
    <transition name="fade">
      <div v-if="navAuthMessage" class="nav-auth-alert">
        <div class="message-content">
          <div class="icon-container">
            <router-link :to="{ path: '/signup', query: { panel: 'signup' } }" class="Signup">
            <img src="/imagenes/Signup-icon.png" alt="Información" class="info-icon" />
            </router-link>
          </div>
          <div class="text-container">
            <h3>Acceso Requerido</h3>
            <p>{{ navAuthMessage }}</p>
          </div>
          <!-- <button @click="navAuthMessage = ''" class="close-btn">
            <img src="/imagenes/Close-Icon.png" alt="Cerrar" class="close-icon" />
          </button> -->
        </div>
      </div>
    </transition>

    <!-- Menú móvil desplegable -->
    <div class="mobile-nav" :class="{ active: isMobileMenuOpen }">
      <span class="mobile-nav-link" @click="handleNavClick('/')">
        <i class="fas fa-home"></i>
        <span>Inicio</span>
      </span>
      <span class="mobile-nav-link" @click="handleNavClick('/directorio')">
        <i class="fas fa-home"></i>
        <span>Deportes</span>
      </span>
      <span class="mobile-nav-link" @click="handleNavClick('/noticias')">
        <i class="fas fa-newspaper"></i>
        <span>Noticias</span>
      </span>
      <span class="mobile-nav-link" @click="handleNavClick('/calendario')">
        <i class="fas fa-calendar-alt"></i>
        <span>Calendario</span>
      </span>
      <span class="mobile-nav-link" @click="handleNavClick('/tienda')">
        <i class="fas fa-shopping-cart"></i>
        <span>Tienda</span>
      </span>
      <span class="mobile-nav-link" @click="handleNavClick('/entrenadores')">
        <i class="fas fa-users"></i>
        <span>Entrenadores</span>
      </span>
      <span class="mobile-nav-link" @click="handleNavClick('/foro')">
        <i class="fas fa-comments"></i>
        <span>Foro</span>
      </span>

      <!-- Enlaces condicionales -->
      <span v-if="user?.user_type == 'entrenador'" class="mobile-nav-link"
        @click="handleNavClick('/solicitudes-usuarios')">
        <i class="fas fa-file-signature"></i>
        <span>Solicitudes-U</span>
      </span>

      <span v-if="user?.user_type == 'admin'" class="mobile-nav-link"
        @click="handleNavClick('/solicitudes-entrenadores')">
        <i class="fas fa-file-contract"></i>
        <span>Solicitudes-E</span>
      </span>
    </div>

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
      user: null,
      user_type: '',
      isCartVisible: false,
      cartItems: [],

      authMessage: '',
      navAuthMessage: '',

      showLogoutConfirm: false,
      isMobileMenuOpen: false
    }
  },
  created() {
    this.checkAuthStatus();
    window.addEventListener('user-authenticated', this.checkAuthStatus);
    window.addEventListener('user-logged-out', this.checkAuthStatus);
    window.addEventListener('resize', this.handleResize);
  },
  beforeDestroy() {
    window.removeEventListener('user-authenticated', this.checkAuthStatus);
    window.removeEventListener('user-logged-out', this.checkAuthStatus);
    window.removeEventListener('resize', this.handleResize);
  },

  watch: {
    showLogoutConfirm(newVal) {
      if (newVal) {
        document.body.classList.add('no-scroll');
      } else {
        document.body.classList.remove('no-scroll');
      }
    }
  },

  methods: {

    handleNavClick(route) {
      this.closeMobileMenu();

      if (!this.user) {
        this.navAuthMessage = 'Debes iniciar sesión o registrarte para acceder a esta sección.';
        setTimeout(() => {
          this.navAuthMessage = '';
        }, 4000);
      } else {
        this.$router.push(route);
      }
    },

    checkAuthStatus() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        try {
          const parsedUser = JSON.parse(userData);
          this.user = parsedUser;
          this.user_type = parsedUser.user_type || '';
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
        sessionStorage.removeItem('user');
        sessionStorage.removeItem('token');
        this.user = null;
        this.user_type = '';
        this.showLogoutConfirm = false;

        window.dispatchEvent(new CustomEvent('user-logged-out'));

        const token = sessionStorage.getItem('token');
        if (token) {
          axios.post('/logout', {}, {
            headers: { Authorization: `Bearer ${token}` }
          }).catch(e => console.warn('Error en logout backend:', e.message));
        }

        this.$router.push('/').then(() => {
          window.location.reload();
        });

        this.closeLogoutModal();

      } catch (error) {
        console.warn('Error en logout frontend:', error.message);
        this.$router.push('/').then(() => {
          window.location.reload();
        });
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
      if (this.isCartVisible && this.$refs.cartComponent) {
        this.$refs.cartComponent.fetchCart();
      }
      this.toggleCart();
    },

    handleUpdateQuantity({ index, quantity }) {
      this.$store.dispatch('updateQuantity', { index, quantity });
    },

    handleCheckout() {
      this.closeCart();
    },

    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    },

    closeMobileMenu() {
      this.isMobileMenuOpen = false;
    },

    handleResize() {
      if (window.innerWidth > 768) {
        this.closeMobileMenu();
      }
    },

    openLogoutModal() {
      this.showLogoutConfirm = true;
    },

    closeLogoutModal() {
      this.showLogoutConfirm = false;
    },
  }
}
</script>


<style lang="scss">
@import '../../scss/Navbar/Navbar_responsive.scss';

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
  justify-content: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
  transition: color 0.3s ease-in-out;
  cursor: pointer;
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


.Imagenes a img {
  width: 24px;
  height: 24px;
  transition: transform 0.3s ease-in-out;
}



/* Encabezado móvil */
.mobile-header.mobile-only {
  display: none;
  flex-direction: column;
  align-items: center;
  gap: 5px;

  .home-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: white;

    i {
      font-size: 1.8rem;
      color: #ff3149;
      margin-bottom: 5px;
    }

    span {
      font-size: 0.9rem;
      font-weight: 600;
    }
  }
}

/* Botón de menú móvil */
.mobile-menu-btn.mobile-only {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 1.8rem;
  cursor: pointer;
  z-index: 1001;
}

/* Menú móvil */
.mobile-nav {
  position: fixed;
  top: 80px;
  left: 0;
  width: 100%;
  background: rgba(10, 10, 30, 0.98);
  backdrop-filter: blur(10px);
  padding: 20px;
  display: none;
  flex-direction: column;
  gap: 15px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
  z-index: 999;

  &.active {
    display: flex;
  }

  .mobile-nav-link {
    color: white;
    text-decoration: none;
    padding: 12px 0px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    text-align: center;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(255, 49, 73, 0.3);
      transform: translateX(5px);
    }

    i {
      color: #ffcc00;
      font-size: 1.2rem;
      width: 25px;
    }

    span {
      font-size: 1.1rem;
    }
  }
}

/* Estilos responsivos */
@media (max-width: 768px) {
  .desktop-only {
    display: none !important;
  }

  .mobile-only {
    display: flex !important;
  }

  .navbar {
    padding: 12px 15px;
  }

  .mobile-header.mobile-only {
    flex-grow: 1;
    justify-content: center;
    margin: 0 10px;
  }

  .mobile-menu-btn.mobile-only {
    display: block;
  }

  .Imagenes {
    gap: 8px;

    a,
    button {
      width: 36px;
      height: 36px;

      img {
        width: 20px;
        height: 20px;
      }
    }
  }

  .mobile-nav {
    top: 70px;
  }
}

/* Estilos para pantallas muy pequeñas */
@media (max-width: 480px) {
  .mobile-header.mobile-only {
    span {
      font-size: 0.8rem;
    }

    i {
      font-size: 1.5rem;
    }
  }

  .mobile-menu-btn.mobile-only {
    font-size: 1.5rem;
  }

  .Imagenes {
    gap: 6px;

    a,
    button {
      width: 34px;
      height: 34px;

      img {
        width: 18px;
        height: 18px;
      }
    }
  }
}


.mobile-menu-btn {
  background: transparent;
  border: none;
  padding: 10px;
  cursor: pointer;
  z-index: 1001;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;

  &:hover {
    .hamburger-icon .line {
      background: #ffffff;
    }
  }
}

.hamburger-icon {
  width: 30px;
  height: 21px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.line {
  display: block;
  height: 3px;
  width: 100%;
  background: white;
  border-radius: 3px;
  transition: all 0.3s ease;
  transform-origin: center;
}

/* Animación cuando el menú está abierto */
.mobile-menu-btn.active {
  .top {
    transform: translateY(9px) rotate(45deg);
  }

  .middle {
    opacity: 0;
    transform: scaleX(0);
  }

  .bottom {
    transform: translateY(-9px) rotate(-45deg);
  }
}


.no-scroll {
  overflow: hidden;
}


@media (max-width: 768px) {
  .dialog-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
  }

  .confirm-btn,
  .cancel-btn {
    padding: 25px 20px;
    min-width: 300px;
    white-space: nowrap;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
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
}



.nav-auth-alert {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  z-index: 99999;
  width: 90%;
  max-width: 500px;
  color: white;
  text-align: center;
  animation: pulse 2s infinite;

  .message-content {
    display: flex;
    align-items: center;
    gap: 20px;

    .icon-container {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      padding: 15px;

      .info-icon {
        width: 40px;
        height: 40px;
        filter: invert(1);
      }
    }

    .text-container {
      flex: 1;

      h3 {
        font-size: 1.8rem;
        margin-bottom: 10px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      }

      p {
        font-size: 1.2rem;
        line-height: 1.5;
      }
    }

    .close-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;

      &:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
      }

      .close-icon {
        width: 20px;
        height: 20px;
        filter: invert(1);
      }
    }
  }
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(26, 42, 108, 0.5);
  }

  70% {
    box-shadow: 0 0 0 15px rgba(26, 42, 108, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(26, 42, 108, 0);
  }
}
</style>
