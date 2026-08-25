import { createStore } from "vuex";

// Cache de secciones (entrenadores, noticias, tienda, foro, calendario) para no
// tener que volver a pedirle los datos al backend cada vez que el usuario
// navega de una seccion a otra dentro de la misma sesion. Vive en sessionStorage:
// sobrevive a recargas de pagina mientras la pestaña siga abierta, pero se borra
// explicitamente al cerrar sesion (ver clearSectionCache en navbarComponent.vue).
function loadSectionCache() {
    try {
        const raw = sessionStorage.getItem("sectionCache");
        return raw ? JSON.parse(raw) : {};
    } catch (e) {
        return {};
    }
}

function persistSectionCache(sectionCache) {
    try {
        sessionStorage.setItem("sectionCache", JSON.stringify(sectionCache));
    } catch (e) {
        // sessionStorage llena o no disponible: el cache sigue funcionando en memoria
    }
}

export default createStore({
    state: {
        cartItems: [],
        sectionCache: loadSectionCache(),
    },
    mutations: {
        SAVE_CART(state) {
            localStorage.setItem("cart", JSON.stringify(state.cartItems));
        },
        SET_SECTION_CACHE(state, { key, data }) {
            state.sectionCache[key] = { data, timestamp: Date.now() };
            persistSectionCache(state.sectionCache);
        },
        CLEAR_SECTION_CACHE(state) {
            state.sectionCache = {};
            sessionStorage.removeItem("sectionCache");
        },
        ADD_TO_CART(state, product) {
            const existingItem = state.cartItems.find(
                (i) => i.id === product.id // Corregido a 'product'
            );
            if (existingItem) {
                existingItem.quantity += product.quantity;
            } else {
                state.cartItems.push({
                    ...product,
                    quantity: product.quantity || 1,
                });
            }
        },
        UPDATE_QUANTITY(state, { index, quantity }) {
            state.cartItems[index].quantity = quantity;
        },
        REMOVE_ITEM(state, index) {
            state.cartItems.splice(index, 1);
        },
    },
    actions: {
        addToCart({ commit }, product) {
            commit("ADD_TO_CART", product);
            commit("SAVE_CART");
        },
        updateQuantity({ commit }, payload) {
            commit("UPDATE_QUANTITY", payload);
            commit("SAVE_CART");
        },
        removeItem({ commit }, index) {
            commit("REMOVE_ITEM", index);
            commit("SAVE_CART");
        },
        cacheSection({ commit }, { key, data }) {
            commit("SET_SECTION_CACHE", { key, data });
        },
        clearSectionCache({ commit }) {
            commit("CLEAR_SECTION_CACHE");
        },
    },
    getters: {
        cartItems: (state) => state.cartItems,
        cartTotal: (state) => {
            return state.cartItems.reduce((total, item) => {
                return total + item.price * item.quantity;
            }, 0);
        },
        sectionCache: (state) => (key) => state.sectionCache[key]?.data ?? null,
    },
});
