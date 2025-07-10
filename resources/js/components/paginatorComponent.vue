<template>
  <div class="paginator-wrapper">
    <div class="paginator-container">
      <vue-awesome-paginate
        v-model="localSelectedOption"
        :total-items="totalItems"
        :items-per-page="itemsPerPage"
        :max-pages-shown="maxPagesShown"
        :show-breakpoint-buttons="false"
        :show-jump-buttons="showJumpButtons"
        :show-ending-buttons="showEndingButtons"
        paginate-buttons-class="btn"
        active-page-class="btn-active"
      />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
    itemsPerPage: {
      type: Number,
      default: 10,
    },
    totalItems: {
      type: Number,
      default: 50,
    },
    maxPagesShown: {
      type: Number,
      default: 5,
    },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      localSelectedOption: this.modelValue,
      windowWidth: window.innerWidth,
      showJumpButtons: true,
      showEndingButtons: true
    };
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
    this.updateResponsiveSettings();
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize);
  },
  watch: {
    modelValue(newVal) {
      this.localSelectedOption = newVal;
    },
    localSelectedOption(newVal) {
      this.$emit('update:modelValue', newVal);
      this.scrollToTop();
    },
    windowWidth() {
      this.updateResponsiveSettings();
    }
  },
  methods: {
    handleResize() {
      this.windowWidth = window.innerWidth;
    },
    updateResponsiveSettings() {
      if (this.windowWidth < 576) {
        this.showJumpButtons = false;
        this.showEndingButtons = false;
      } else if (this.windowWidth < 768) {
        this.showJumpButtons = false;
        this.showEndingButtons = true;
      } else {
        this.showJumpButtons = true;
        this.showEndingButtons = true;
      }
    },
    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  }
};
</script>

<style lang="scss">
.paginator-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  overflow: hidden;
  margin-bottom: 40px;
}

.paginator-container {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  height: 75px;
  padding: 0 16px;
  max-width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;

  &::-webkit-scrollbar {
    display: none;
  }

  .btn {
    background-color: #f4f4f4;
    color: #333;
    height: 42px;
    min-width: 42px;
    border-radius: 10px;
    margin: 0 4px;
    cursor: pointer;
    border: 1px solid #ddd;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    &:hover {
      background-color: #e0e0e0;
      transform: translateY(-2px);
    }

    &:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
  }

  .btn-active {
    background-color: #007bff;
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);

    &:hover {
      background-color: #006ae0;
    }
  }

  @media (max-width: 992px) {
    height: 65px;
    
    .btn {
      height: 38px;
      min-width: 38px;
      font-size: 14px;
    }
  }

  @media (max-width: 768px) {
    height: 60px;
    padding: 0 12px;
    
    .btn {
      height: 36px;
      min-width: 36px;
      margin: 0 3px;
    }
  }

  @media (max-width: 576px) {
    height: 50px;
    
    .btn {
      height: 32px;
      min-width: 32px;
      font-size: 13px;
      margin: 0 2px;
    }
  }
}

</style>