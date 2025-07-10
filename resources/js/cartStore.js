import { createStore } from "vuex";

export default createStore({
    state: {
        cartItems: [],
    },
    mutations: {
        SAVE_CART(state) {
            localStorage.setItem("cart", JSON.stringify(state.cartItems));
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
    },
    getters: {
        cartItems: (state) => state.cartItems,
        cartTotal: (state) => {
            return state.cartItems.reduce((total, item) => {
                return total + item.price * item.quantity;
            }, 0);
        },
    },
});
