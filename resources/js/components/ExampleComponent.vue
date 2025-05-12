<template>
  <div>
    <nav class="main-nav">
      <div class="nav-container">
      
      </div>

    </nav>

    <router-view></router-view>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isMenuOpen: false,
      isAuthenticated: false, // Debes conectar esto con tu sistema de autenticación
      user: {}, // Datos del usuario desde tu store
      cartCount: 0 // Conectar con tu carrito
    }
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen
    },
    async logout() {
      try {
        await this.$axios.post('/logout')
        // Limpiar estado del usuario
        this.$router.push('/')
      } catch (error) {
        console.error('Error al cerrar sesión:', error)
      }
    }
  }
}
</script>

<style lang="scss">

</style>