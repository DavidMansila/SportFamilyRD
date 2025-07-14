<template>
  <div
    v-if="visible"
    class="custom-alert"
    :class="type"
  >
    <p>
      <span class="icon">{{ icon }}</span>
      {{ message }}
    </p>
  </div>
</template>

<script>
export default {
  props: {
    message: String,
    type: {
      type: String,
      default: 'error', // 'error', 'alert', 'success'
    },
    autoClose: {
      type: Boolean,
      default: true, // ⚡ Por defecto, se cierra solo
    },
    duration: {
      type: Number,
      default: 3000, // ⚡ 3 segundos
    },
  },
  data() {
    return {
      visible: true,
    };
  },
  computed: {
    icon() {
      switch (this.type) {
        case 'success':
          return '✅';
        case 'alert':
          return '⚠️';
        case 'error':
        default:
          return '❌';
      }
    },
  },
  watch: {
    message(newVal) {
      if (newVal) {
        this.visible = true;
        if (this.autoClose) {
          setTimeout(() => {
            this.closeAlert();
          }, this.duration);
        }
      }
    },
  },
  mounted() {
    if (this.autoClose && this.message) {
      setTimeout(() => {
        this.closeAlert();
      }, this.duration);
    }
  },
  methods: {
    closeAlert() {
      this.visible = false;
      this.$emit('closed');
    },
  },
};
</script>

<style scoped>
.custom-alert {
  position: fixed;
  top: 20px;
  right: 20px;
  background: linear-gradient(to right, #000000, #a10013);
  color: white;
  padding: 16px 24px;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.5);
  z-index: 9999;
  transition: all 0.3s ease;
}

.custom-alert p {
  margin: 0;
  display: flex;
  align-items: center;
}

.custom-alert .icon {
  margin-right: 8px;
  font-size: 1.4em;
}
</style>
