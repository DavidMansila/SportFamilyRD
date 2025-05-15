<template>
  <transition name="cart-slide">

    <div v-if="isVisible" class="cart-overlay" @click.self="$emit('close')">
      <div class="cart-container">

        <!-- Encabezado con efecto vidrio -->
        <div class="cart-header glassmorphism">
          <div class="header-content">
            
            <h2>🛒 Tu Carrito <span class="items-count">{{ cartItems.length }} items</span></h2>
            <button @click="$emit('close')" class="close-btn">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

        </div>

        <!-- Lista de productos con efecto flotante -->
        <div class="cart-items">
          <div v-if="cartItems.length === 0" class="empty-cart">
            <div class="empty-illustration">
              <div class="basket-emoji">🧺</div>
              <div class="floating-items">
                <span>🛍️</span>
                <span>👟</span>
                <span>🎫</span>
              </div>
            </div>
            <p class="empty-text">¡Vaya! Tu carrito está vacío</p>
            <p class="empty-subtext">Explora nuestra tienda y encuentra algo especial</p>
          </div>

          <div v-for="(item, index) in cartItems" :key="item.id" class="cart-item neumorphism">
            <div class="item-image">
              <img :src="item.image" :alt="item.name" class="item-img">
              <div class="item-type" :class="item.type">{{ item.type === 'product' ? '🛍️' : '🎟️' }}</div>
            </div>

            <div class="item-details">
              <h3>{{ item.name }}</h3>
              <div v-if="item.type === 'ticket'" class="event-details">
                <div class="event-date">🗓️ {{ formatDate(item.eventDate) }}</div>
                <div class="ticket-type">🎫 {{ item.ticketType }}</div>
              </div>
              <div class="price">💲{{ item.price.toFixed(2) }}</div>
            </div>

            <div class="item-controls">
              <div class="quantity-control">
                <button @click="updateQuantity(index, item.quantity - 1)" :disabled="item.quantity <= 1"
                  class="qty-btn minus">
                  ➖
                </button>
                <input type="number" v-model.number="item.quantity" min="1"
                  @change="updateQuantity(index, item.quantity)" class="qty-input">
                <button @click="updateQuantity(index, item.quantity + 1)" class="qty-btn plus">
                  ➕
                </button>
              </div>
              <button @click="removeItem(index)" class="remove-btn">
                🗑️ Eliminar
              </button>
            </div>
          </div>
        </div>

        <!-- Total y acciones con efecto flotante -->
        <div class="cart-footer glassmorphism">
          <div class="total-section">
            <div class="total-line">
              <span>Subtotal:</span>
              <span>${{ cartTotal.toFixed(2) }}</span>
            </div>
            <div class="total-line">
              <span>Envío:</span>
              <span>{{ shippingCost }}</span>
            </div>
            <div class="total-line grand-total">
              <span>Total:</span>
              <span class="total-price">${{ (cartTotal + shippingCostValue).toFixed(2) }}</span>
            </div>
          </div>

          <div class="actions">
            <button @click="$emit('close')" class="continue-shopping">
              ⏪ Seguir comprando
            </button>
            <button class="checkout-btn" @click="handleCheckout">
              🚀 Finalizar compra
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  isVisible: Boolean,
  cartItems: Array
});

const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'checkout']);

const cartTotal = computed(() => {
  return props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const shippingCostValue = computed(() => cartTotal.value > 50 ? 0 : 5.99);
const shippingCost = computed(() => cartTotal.value > 50 ? '¡Gratis! 🎉' : `$${shippingCostValue.value.toFixed(2)}`);
const shippingProgress = computed(() => Math.min((cartTotal.value / 50) * 100, 100));
const shippingMessage = computed(() =>
  cartTotal.value > 50
    ? '¡Felicidades! Envío gratis aplicado'
    : `$${(50 - cartTotal.value).toFixed(2)} más para envío gratis`
);

const formatDate = (dateString) => {
  // Implementar formato de fecha
  return new Date(dateString).toLocaleDateString();
};

// Resto de métodos igual
</script>

<style scoped>
.cart-overlay {
  position: fixed;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: flex-end;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.cart-container {
  width: 100%;
  max-width: 450px;
  background: #f5f5f5;
  height: 100vh;
  display: flex;
  flex-direction: column;
  box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
}

.glassmorphism {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.neumorphism {
  background: #f5f5f5;
  border-radius: 15px;
  box-shadow: 8px 8px 16px #d9d9d9,
    -8px -8px 16px #ffffff;
}

.cart-header {
  padding: 1.5rem;
  position: relative;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

h2 {
  font-family: 'Poppins', sans-serif;
  color: #2c3e50;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.items-count {
  font-size: 0.9em;
  color: #7f8c8d;
  font-weight: normal;
}

.close-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #2c3e50;
  transition: transform 0.3s ease;
}

.close-btn:hover {
  transform: rotate(90deg);
}

.close-btn svg {
  width: 24px;
  height: 24px;
}

.progress-bar {
  height: 8px;
  background: #ecf0f1;
  border-radius: 4px;
  overflow: hidden;
  margin: 1rem 0;
}

.progress {
  height: 100%;
  background: linear-gradient(90deg, #2ecc71, #27ae60);
  transition: width 0.5s ease;
}

.shipping-text {
  font-size: 0.9em;
  color: #27ae60;
  text-align: center;
  font-weight: 500;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: grid;
  gap: 1rem;
}

.empty-cart {
  text-align: center;
  padding: 3rem 0;
}

.empty-illustration {
  position: relative;
  margin: 2rem auto;
  width: 150px;
  height: 150px;
}

.basket-emoji {
  font-size: 4rem;
  animation: bounce 2s infinite;
}

.floating-items {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
}

.floating-items span {
  position: absolute;
  font-size: 1.5rem;
  animation: float 3s infinite;
}

@keyframes float {

  0%,
  100% {
    transform: translateY(0) rotate(0deg);
  }

  50% {
    transform: translateY(-20px) rotate(10deg);
  }
}

.empty-text {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 1rem 0;
}

.empty-subtext {
  color: #7f8c8d;
}

.cart-item {
  padding: 1rem;
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 1rem;
  transition: transform 0.3s ease;
}

.cart-item:hover {
  transform: translateY(-3px);
}

.item-image {
  position: relative;
  border-radius: 10px;
  overflow: hidden;
}

.item-img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 10px;
}

.item-type {
  position: absolute;
  top: 5px;
  right: 5px;
  background: white;
  padding: 3px 8px;
  border-radius: 20px;
  font-size: 0.8em;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.price {
  font-size: 1.2em;
  color: #27ae60;
  font-weight: bold;
  margin-top: 0.5rem;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-btn {
  width: 30px;
  height: 30px;
  border: none;
  border-radius: 50%;
  background: #ecf0f1;
  cursor: pointer;
  transition: all 0.3s ease;
}

.qty-btn:hover {
  background: #dde1e3;
  transform: scale(1.1);
}

.qty-input {
  width: 50px;
  text-align: center;
  border: 2px solid #ecf0f1;
  border-radius: 8px;
  padding: 0.3rem;
  font-weight: bold;
}

.remove-btn {
  background: none;
  border: none;
  color: #e74c3c;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.5rem;
  margin-top: 0.5rem;
  transition: all 0.3s ease;
}

.remove-btn:hover {
  color: #c0392b;
  transform: scale(1.05);
}

.cart-footer {
  padding: 1.5rem;
  margin-top: auto;
}

.total-section {
  padding: 1rem;
  background: white;
  border-radius: 10px;
  margin-bottom: 1rem;
}

.total-line {
  display: flex;
  justify-content: space-between;
  margin: 0.5rem 0;
  color: #7f8c8d;
}

.grand-total {
  font-size: 1.2em;
  font-weight: bold;
  color: #2c3e50;
  margin-top: 1rem;
}

.total-price {
  color: #27ae60;
}

.actions {
  display: grid;
  gap: 1rem;
}

.continue-shopping {
  background: white;
  border: 2px solid #3498db;
  color: #3498db;
  padding: 1rem;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.continue-shopping:hover {
  background: #3498db;
  color: white;
}

.checkout-btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 1.2rem;
  border-radius: 10px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.checkout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

/* Animaciones */
.cart-slide-enter-active,
.cart-slide-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.cart-slide-enter-from,
.cart-slide-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

@keyframes bounce {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-10px);
  }
}

@media (max-width: 480px) {
  .cart-container {
    max-width: 100%;
  }

  .cart-item {
    grid-template-columns: 1fr;
  }

  .item-image {
    width: 100%;
    height: 150px;
  }
}
</style>