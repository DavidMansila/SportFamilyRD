<template>
   
    <div class="paginator-container">
      
        <vue-awesome-paginate
            v-model="localSelectedOption"
            :total-items="totalItems"
            :items-per-page="itemsPerPage"
            :max-pages-shown="maxPagesShown"

            :show-breakpoint-buttons="false"
            :show-jump-buttons="true"
            
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
    gap: 10px;
   
  
  .btn {
    background-color: white;
    color: black;
    height: 40px;
    width: 40px;
    border-radius: 8px;
    margin-inline: 5px;
    cursor: pointer;
  }

  .btn-active {
    background-color: rgb(85, 85, 239);
    color: white;

    &:focus-visible {
      outline: none;
    }
  }
}

</style>