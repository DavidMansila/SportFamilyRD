// store.js
import { createStore } from 'vuex';

export default createStore({


  // Para los productos 
  state: {
    carrito: JSON.parse(localStorage.getItem('carrito')) || [], // Cargar desde localStorage
  },
  mutations: {
    agregarAlCarrito(state, item) {
      const itemExistente = state.carrito.find((i) => i.id === item.id);
      if (itemExistente) {
        itemExistente.cantidad++;
      } else {
        state.carrito.push({ ...item, cantidad: 1 });
      }
      localStorage.setItem('carrito', JSON.stringify(state.carrito)); // Guardar en localStorage
    },
    eliminarDelCarrito(state, index) {
      const item = state.carrito[index];
      if (item.cantidad > 1) {
        item.cantidad--;
      } else {
        state.carrito.splice(index, 1);
      }
      localStorage.setItem('carrito', JSON.stringify(state.carrito)); // Guardar en localStorage
    },
    vaciarCarrito(state) {
      state.carrito = [];
      localStorage.removeItem('carrito'); // Eliminar de localStorage
    },
  },
  actions: {
    agregarAlCarrito({ commit }, item) {
      commit('agregarAlCarrito', item);
    },
    eliminarDelCarrito({ commit }, index) {
      commit('eliminarDelCarrito', index);
    },
    vaciarCarrito({ commit }) {
      commit('vaciarCarrito');
    },
  },
  getters: {
    carrito: (state) => state.carrito,
    totalCarrito: (state) =>
      state.carrito.reduce((total, item) => total + item.precio * item.cantidad, 0),
  },
});