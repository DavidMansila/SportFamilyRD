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
                <span>🛍</span>
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
              <div class="item-type" :class="item.type">{{ item.type === 'product' ? '🛍' : '🎟' }}</div>
            </div>

            <div class="item-details">
              <h3>{{ item.name }}</h3>
              <div v-if="item.type === 'event'" class="event-details">
                <div class="event-date">🗓 {{ formatDate(item.eventDate) }}</div>
                <div class="ticket-type"> 🎫 {{ item.quantity }}</div>
              </div>
              <div class="price">💲{{ (item.price || 0).toFixed(2) }}</div>
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
                🗑 Eliminar
              </button>
            </div>
          </div>
        </div>

        <!-- Total y acciones con efecto flotante -->
        <div class="cart-footer glassmorphism">
          <div class="total-section">
            <div class="total-line grand-total">
              <span>Total:</span>
              <span class="total-price">${{ cartTotal }}</span>
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



        <!-- Modal de pago simulado -->
        <div v-if="showPaymentModal" class="payment-modal-overlay">
          <div class="payment-modal">
            <div class="modal-header">
              <h3>🪙 Proceso de Pago Simulado</h3>
              <button @click="resetPayment" class="close-modal">
                ✖
              </button>
            </div>

            <div class="payment-steps">
              <div class="step" :class="{ active: paymentStep === 1, completed: paymentStep > 1 }">
                <div class="step-icon">1</div>
                <div class="step-label">Confirmación</div>
              </div>
              <div class="step" :class="{ active: paymentStep === 2, completed: paymentStep > 2 }">
                <div class="step-icon">2</div>
                <div class="step-label">Procesando</div>
              </div>
              <div class="step" :class="{ active: paymentStep === 3 }">
                <div class="step-icon">3</div>
                <div class="step-label">Completado</div>
              </div>
            </div>

            <div v-if="paymentStep === 1" class="scrollable-content">
              <div class="order-summary">
                <h4>Resumen de tu compra</h4>
                <div class="summary-item" v-for="item in cartItems" :key="item.id">
                  <span>{{ item.name }} x {{ item.quantity }}</span>
                  <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
                </div>
                <div class="summary-total">
                  <span>Total:</span>
                  <span>${{ cartTotal }}</span>
                </div>
              </div>

              <div class="payment-methods">
                <h4>Método de pago simulado</h4>
                <div class="method-cards">
                  <div v-for="method in paymentMethods" :key="method.id" class="method-card"
                    :class="{ selected: selectedMethod === method.id }" @click="selectedMethod = method.id">
                    <div class="method-icon">{{ method.icon }}</div>
                    <div class="method-name">{{ method.name }}</div>
                  </div>
                </div>
              </div>

              <button class="pay-now-btn" @click="startPayment">
                💳 Confirmar y Pagar ${{ cartTotal }}
              </button>
            </div>

            <div v-if="paymentStep === 2" class="processing-payment">
              <div class="loader">
                <div class="spinner"></div>
              </div>
              <h4>Procesando tu pago...</h4>
              <p>Estamos simulando la transacción con el método {{ currentMethod?.name }}</p>
              <div class="fake-progress">
                <div class="progress-bar" :style="{ width: progress + '%' }"></div>
              </div>
            </div>

            <div v-if="paymentStep === 3" class="scrollable-content">
              <div class="result-icon" :class="paymentSuccess ? 'success' : 'error'">
                {{ paymentSuccess ? '✅' : '❌' }}
              </div>
              <h3>{{ paymentSuccess ? '¡Pago Exitoso!' : 'Pago Fallido' }}</h3>
              <p v-if="paymentSuccess">
                Tu compra de ${{ cartTotal }} se ha completado exitosamente con {{ currentMethod?.name }}.
              </p>
              <p v-else>
                La transacción simulada fue rechazada. Por favor intenta con otro método de pago.
              </p>

              <div class="transaction-details" v-if="paymentSuccess">
                <div class="detail">
                  <span>ID de transacción:</span>
                  <span>SIM-{{ transactionId }}</span>
                </div>
                <div class="detail">
                  <span>Fecha:</span>
                  <span>{{ new Date().toLocaleString() }}</span>
                </div>
                <div class="detail">
                  <span>Método:</span>
                  <span>{{ currentMethod?.name }}</span>
                </div>
              </div>

              <button class="finish-btn" @click="completePayment">
                {{ paymentSuccess ? '🛍️ Continuar Comprando' : '🔄 Reintentar Pago' }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </transition>

</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  isVisible: Boolean
});

const emit = defineEmits(['close']);

const cartItems = ref([]);
const isLoading = ref(true);

// Obtener carrito
const fetchCart = async () => {
  try {
    const user = JSON.parse(sessionStorage.getItem('user') || '{}');
    const response = await axios.get('/cart', {
      params: { user_id: user.id }
    });

    // Verificar si la respuesta tiene items
    if (!response.data || !Array.isArray(response.data.items)) {
      throw new Error('Formato de respuesta inválido');
    }

    cartItems.value = response.data.items.map(item => {
      if (!item) return null;

      const itemData = item.item;
      const itemType = item.type;

      if (itemData && itemData.id) {
        return {
          id: itemData.id,
          name: itemType === 'product'
            ? itemData.name
            : itemData.title || itemData.place || 'Evento sin nombre',
          price: parseFloat(itemData.price) || 0,
          image: itemData.image || '',
          quantity: item.quantity,
          cart_item_id: item.id,
          type: itemType,
          ...(itemType === 'event' && {
            eventDate: itemData.date
          })
        };
      }
      else {
        console.warn('Item inválido en el carrito:', item);
        return {
          id: 'invalid-' + Math.random().toString(36).substr(2, 9),
          name: 'Item no disponible',
          price: 0,
          image: '',
          quantity: item.quantity,
          cart_item_id: item.id,
          type: 'invalid'
        };
      }
    }).filter(Boolean);

  } catch (error) {
    console.error('Error fetching cart:', error);
    // Mostrar mensaje de error al usuario
    cartItems.value = [];
  } finally {
    isLoading.value = false;
  }
};

// watcher para actualizar cuando se abre el carrito
watch(() => props.isVisible, (newVal) => {
  if (newVal) {
    fetchCart();
  }
});

// Actualizar cantidad
const updateQuantity = async (index, newQuantity) => {
  if (newQuantity < 1) return;

  const item = cartItems.value[index];
  try {
    await axios.put(`/cart/items/${item.cart_item_id}`, {
      quantity: newQuantity
    });
    cartItems.value[index].quantity = newQuantity;
  } catch (error) {
    console.error('Error updating quantity:', error);
  }
};

// Eliminar item
const removeItem = async (index) => {
  const item = cartItems.value[index];
  try {
    await axios.delete(`/cart/items/${item.cart_item_id}`);
    cartItems.value.splice(index, 1);
  } catch (error) {
    console.error('Error removing item:', error);
  }
};

// Finalizar compra
const handleCheckout = () => {
  if (cartItems.value.length === 0) return;
  showPaymentModal.value = true;
  paymentStep.value = 1;
};

onMounted(fetchCart);

// Computed: Totales
const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.price * item.quantity);
  }, 0);
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};


const cleanId = (id) => {
  return id.replace('PROD-', '').replace('EVT-', '');
};



// SIMULACION DE PAGO

const showPaymentModal = ref(false);
const paymentStep = ref(1);
const selectedMethod = ref(1);
const progress = ref(0);
const paymentSuccess = ref(true);
const transactionId = ref('');

// Función para vaciar el carrito en la base de datos
const clearCartFromDatabase = async () => {
  try {
    const user = JSON.parse(sessionStorage.getItem('user') || '{}');
    await axios.delete('/cart/clear', {
      params: { user_id: user.id }
    });
    console.log('Carrito vaciado en la base de datos');
  } catch (error) {
    console.error('Error al vaciar el carrito:', error);
  }
};

watch(showPaymentModal, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

onUnmounted(() => {
  document.body.style.overflow = '';
});

const paymentMethods = ref([
  { id: 1, name: 'Tarjeta de Crédito', icon: '💳' },
  { id: 2, name: 'PayPal Simulado', icon: '📱' },
  { id: 3, name: 'Transferencia Bancaria', icon: '🏦' },
  // { id: 4, name: 'Criptomonedas', icon: '₿' }
]);

// Computed para obtener el método actual
const currentMethod = computed(() => {
  return paymentMethods.value.find(m => m.id === selectedMethod.value);
});

// Iniciar el proceso de pago
const startPayment = () => {
  paymentStep.value = 2;

  // Simular progreso de pago
  const interval = setInterval(() => {
    progress.value += Math.floor(Math.random() * 10) + 5;
    if (progress.value >= 100) {
      clearInterval(interval);
      setTimeout(() => {
        // 80% de éxito, 20% de fallo para simular
        paymentSuccess.value = Math.random() > 0.2;
        paymentStep.value = 3;
        transactionId.value = Math.random().toString(36).substring(2, 10).toUpperCase();
      }, 500);
    }
  }, 300);
};

// Resetear el proceso de pago
const resetPayment = () => {
  showPaymentModal.value = false;
  paymentStep.value = 1;
  progress.value = 0;
  document.body.style.overflow = '';
};

// Completar el pago
const completePayment = async () => {
  if (paymentSuccess.value) {
    try {
      await clearCartFromDatabase();
      cartItems.value = [];
    } catch (error) {
      console.error('Error al completar el pago:', error);
    }
  }
  resetPayment();
  emit('close');
};

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
  z-index: 2000;
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









.payment-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 3000;
  backdrop-filter: blur(5px);
}

.payment-modal {
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  animation: modal-appear 0.4s ease-out;
  display: flex;
  flex-direction: column;
}

.scrollable-content {
  overflow-y: auto;
  flex: 1;
  max-height: 60vh;
  padding: 0 20px;
  text-align: center;
}

.payment-result {
  padding: 20px;
}

@media (max-height: 700px) {
  .scrollable-content {
    max-height: 50vh;
  }
}

.payment-content,
.payment-result {
  min-height: 300px;
}


.transaction-details {
  background: #f9f9f9;
  border-radius: 10px;
  padding: 1.5rem;
  margin: 2rem 0;
  text-align: left;
  max-height: 200px;
  overflow-y: auto;
}


.payment-content,
.processing-payment,
.payment-result {
  padding: 2rem;
  min-height: min-content;
}

@media (max-height: 700px) {
  .payment-modal {
    max-height: 85vh;
  }

  .payment-content,
  .processing-payment,
  .payment-result {
    padding: 1.5rem;
  }
}

@keyframes modal-appear {
  from {
    transform: translateY(50px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-modal {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  transition: transform 0.3s;
}

.close-modal:hover {
  transform: scale(1.2);
}

.payment-steps {
  display: flex;
  justify-content: space-between;
  padding: 1.5rem;
  background: #f8f9fa;
  border-bottom: 1px solid #eee;
}

.step {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.step:not(:last-child):after {
  content: '';
  position: absolute;
  top: 20px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: #ddd;
  z-index: 1;
}

.step.completed:after {
  background: #2ecc71;
}

.step-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #ddd;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  margin-bottom: 0.5rem;
  position: relative;
  z-index: 2;
}

.step.active .step-icon {
  background: #3498db;
  color: white;
  box-shadow: 0 0 0 5px rgba(52, 152, 219, 0.2);
}

.step.completed .step-icon {
  background: #2ecc71;
  color: white;
}

.payment-content,
.processing-payment,
.payment-result {
  padding: 2rem;
}

.order-summary,
.payment-methods {
  background: #f9f9f9;
  border-radius: 10px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px dashed #eee;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-weight: bold;
  font-size: 1.2rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #eee;
}

.method-cards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.method-card {
  border: 2px solid #eee;
  border-radius: 10px;
  padding: 1rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.method-card:hover {
  border-color: #3498db;
  transform: translateY(-3px);
}

.method-card.selected {
  border-color: #3498db;
  background: rgba(52, 152, 219, 0.1);
}

.method-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.pay-now-btn {
  width: 100%;
  padding: 1.2rem;
  margin-bottom: 20px;
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.3s;
}

.pay-now-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
}

.processing-payment {
  text-align: center;
}

.loader {
  margin: 2rem auto;
}

.spinner {
  width: 60px;
  height: 60px;
  border: 5px solid rgba(52, 152, 219, 0.2);
  border-top: 5px solid #3498db;
  border-radius: 50%;
  margin: 0 auto;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.fake-progress {
  height: 10px;
  background: #eee;
  border-radius: 5px;
  margin-top: 2rem;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #3498db, #2ecc71);
  border-radius: 5px;
  transition: width 0.5s ease;
}

.payment-result {
  text-align: center;
}

.result-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

.result-icon.success {
  color: #2ecc71;
  animation: bounce 0.5s ease;
}

.result-icon.error {
  color: #e74c3c;
  animation: shake 0.5s ease;
}

@keyframes bounce {

  0%,
  20%,
  50%,
  80%,
  100% {
    transform: translateY(0);
  }

  40% {
    transform: translateY(-20px);
  }

  60% {
    transform: translateY(-10px);
  }
}

@keyframes shake {

  0%,
  100% {
    transform: translateX(0);
  }

  20%,
  60% {
    transform: translateX(-10px);
  }

  40%,
  80% {
    transform: translateX(10px);
  }
}

.detail {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #eee;
}

.detail:last-child {
  border-bottom: none;
}

.finish-btn {
  padding: 1rem 2rem;
  margin: 20px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.finish-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}


.payment-result {
  max-height: 60vh;
  overflow-y: auto;
  padding: 20px;
}

.transaction-details {
  max-height: 200px;
  overflow-y: auto;
}

@media (max-height: 700px) {
  .payment-result {
    max-height: 50vh;
  }
}
</style>