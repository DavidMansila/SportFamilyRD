<template>
   
    <div class="paginator-container">
      
        <vue-awesome-paginate
            v-model="localSelectedOption"
            :total-items="totalItems"
            :items-per-page="itemsPerPage"
            :max-pages-shown="maxPagesShown"

            :show-breakpoint-buttons="false"
            :show-jump-buttons="true"
            :show-ending-buttons="true"
            
            paginate-buttons-class="btn"
            active-page-class="btn-active"
        />

    </div>
</template>

<script>
export default {
    props:{
        modelValue:{
            required: true,
        },

        itemsPerPage:{
            type: Number,
            default: 10,
        },

        totalItems:{
            type: Number,
            default: 50,
        },

        maxPagesShown:{
            type: Number,
            default: 5,
        }
    },
    emits:['update:modelValue'],
    
    data(){
        return{
            localSelectedOption: this.modelValue,
            currentPage: 1,
        }
    },
    watch: {
        modelValue: {
            handler(newVal) {
                this.localSelectedOption = newVal;
            },
            immediate: true,
            deep: true
        },

        localSelectedOption: {
            handler(newVal) {
                this.$emit('update:modelValue', newVal);
            },
            deep: true
        }
    }
    
}
</script>

<style lang="scss">
.paginator-container {
  height: 75px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;

  .btn {
    background-color: #f4f4f4;
    color: #333;
    height: 42px;
    width: 42px;
    border-radius: 10px;
    margin-inline: 6px;
    cursor: pointer;
    border: 1px solid #ddd;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);

    &:hover {
      background-color: #e0e0e0;
      transform: translateY(-2px);
    }

    &:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
    }
  }

  .btn-active {
    background-color: #007bff;
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    transform: scale(1.05);

    &:hover {
      background-color: #006ae0;
    }

    &:focus-visible {
      outline: none;
    }
  }
}
</style>