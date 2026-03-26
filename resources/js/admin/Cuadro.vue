<template>
  <div class="container mt-5">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Crear Categoría</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="crearCategoria">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la categoría</label>
            <input
              type="text"
              id="nombre"
              v-model="form.nombre"
              class="form-control"
              placeholder="Ej: Documentos, Correspondencia"
              required
            />
          </div>

          <button
            type="submit"
            class="btn btn-primary"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ loading ? 'Guardando...' : 'Guardar' }}
          </button>

          <div v-if="mensaje" class="alert alert-success mt-3" role="alert">
            {{ mensaje }}
          </div>
          <div v-if="error" class="alert alert-danger mt-3" role="alert">
            {{ error }}
          </div>
        </form>

        <hr />

        <h6>Categorías existentes</h6>
        <ul class="list-group">
          <li v-for="categoria in categorias" :key="categoria.id" class="list-group-item d-flex justify-content-between align-items-center">
            {{ categoria.nombre }}
            <button class="btn btn-sm btn-danger" @click="eliminarCategoria(categoria.id)">Eliminar</button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      form: {
        nombre: '',
      },
      categorias: [],
      loading: false,
      mensaje: '',
      error: '',
    };
  },
  methods: {
    async cargarCategorias() {
      try {
        const response = await axios.get('/categorias');
        this.categorias = response.data;
      } catch (err) {
        console.error('Error cargando categorías', err);
      }
    },

    async crearCategoria() {
      this.loading = true;
      this.mensaje = '';
      this.error = '';

      try {
        await axios.post('/nuevacategoria', this.form);
        this.mensaje = 'Categoría creada con éxito.';
        this.form.nombre = '';
        this.cargarCategorias();
      } catch (err) {
        this.error = err.response?.data?.message || 'Error al crear la categoría.';
      } finally {
        this.loading = false;
      }
    },

    async eliminarCategoria(id) {
      if(!confirm('¿Seguro que quieres eliminar esta categoría?')) return;

      try {
        await axios.delete(`/categorias/${id}`);
        this.cargarCategorias();
      } catch (err) {
        alert('Error al eliminar categoría');
      }
    },
  },
  mounted() {
    this.cargarCategorias();
  },
};
</script>
