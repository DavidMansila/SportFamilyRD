<template>
  <div class="noticias-page">
    <h1 class="page-title">Noticias Deportivas</h1>
    <div v-for="noticia in noticias" :key="noticia.id" class="noticia-card">
      <div class="noticia-image">
        <img :src="noticia.imagen" alt="Imagen de noticia" class="image" />
      </div>
      <div class="noticia-content">
        <h3 class="noticia-title">{{ noticia.titulo }}</h3>
        <p class="noticia-description">{{ noticia.descripcion }}</p>
        <a :href="'/noticia/' + noticia.id" class="read-more">Leer más</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NoticiasComponent',
  data() {
    return {
      noticias: [
        { 
          id: 1, 
          titulo: 'Nuevo evento deportivo', 
          descripcion: 'Detalles sobre el evento deportivo que se celebrará este fin de semana...', 
          imagen: 'https://via.placeholder.com/600x400' 
        },
        { 
          id: 2, 
          titulo: 'Liga nacional', 
          descripcion: 'Resumen de los últimos partidos de la liga nacional...', 
          imagen: 'https://via.placeholder.com/600x400' 
        }
      ]
    };
  }
};
</script>

<style scoped>
.noticias-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  text-align: center;
}

.page-title {
  font-size: 3rem;
  color: #333;
  margin-bottom: 40px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  font-family: 'Helvetica Neue', sans-serif;
}

.noticia-card {
  display: flex;
  flex-direction: column;
  background: #fff;
  margin: 25px 0;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
  overflow: hidden;
}

.noticia-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 18px rgba(0, 0, 0, 0.2);
}

.noticia-image {
  margin-bottom: 15px;
  border-radius: 8px;
  overflow: hidden;
}

.image {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 8px;
}

.noticia-content {
  text-align: left;
}

.noticia-title {
  font-size: 2rem;
  color: #333;
  margin-bottom: 15px;
  font-weight: 600;
}

.noticia-description {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 20px;
  line-height: 1.6;
}

.read-more {
  font-size: 1.1rem;
  color: #007bff;
  font-weight: bold;
  text-decoration: none;
  transition: color 0.3s;
}

.read-more:hover {
  color: #0056b3;
}

/* Responsive design */
@media (max-width: 768px) {
  .noticia-card {
    flex-direction: column;
    align-items: center;
  }

  .image {
    max-width: 100%;
    height: auto;
  }
}
</style>
