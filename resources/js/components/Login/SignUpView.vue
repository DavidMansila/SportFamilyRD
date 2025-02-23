<template>
    <div class="sign-up-container">
        <div class="left-side">
            <form @submit.prevent="submitForm">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" v-model="name">   
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" v-model="email">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" v-model="password">
                </div>
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
        </div>
        <div class="right-side"></div>
    </div>
</template>
<script>
import axios from 'axios';
    export default {
        data() {
            return {
                name: '',
                email: '',
                password: ''
            }
        },

        methods: {
            submitForm() {
                console.log(this.name, this.email, this.password);
                axios.post('/register', {//crear
                    name: this.name,
                    email: this.email,
                    password: this.password
                }).then(response => {
                    console.log(response);
                    //alert que diga registrado
                //swal,
                }).catch(error => {
                    console.log(error);
                    //alert que diga, algo salio mal, lo podemos personalizar 
                });


                axios.put('/api/register', {//actualizar
                    name: this.name,
                    email: this.email,
                    password: this.password
                })
                axios.get('/api/register', );
                axios.delete('/api/register', {//eliminar
                    name: this.name,
                    email: this.email,
                    password: this.password
                })
            }
        }
    }
</script>

<style lang="scss">

    .sign-up-container {
        height: 100vh; 
        width: 100%;  
        display: flex;
        background-color:var(--bg-view);
        .left-side {
            margin-top: 5px;
            width: 45%;
            height: 100vh;

            
            .form-group {
                margin-bottom: 10px;
            }
        }
        .right-side {
            width: 45%;
            height: 100vh;
        }
    }
</style>