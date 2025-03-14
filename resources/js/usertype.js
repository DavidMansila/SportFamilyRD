// store.js
import { createStore } from 'vuex';

export default createStore({
// Para los usuarios
  state: {
    user: null, // Aquí se almacenará la información del usuario
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      localStorage.setItem('user', JSON.stringify(user)); // Guardar en localStorage
    },
    logout(state) {
      state.user = null;
      localStorage.removeItem('user'); // Eliminar de localStorage
    },
  },
  actions: {
    login({ commit }, user) {
      commit('setUser', user);
    },
    logout({ commit }) {
      commit('logout');
    },
  },
  getters: {
    isAdmin: (state) => state.user && state.user.role === 'admin', // Verifica si el usuario es admin
    currentUser: (state) => state.user, // Obtiene el usuario actual
  },
});