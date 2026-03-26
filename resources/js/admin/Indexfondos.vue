<template>
  <div class="container mt-5">
    <div class="card shadow">
      <div class="card-header bg-danger text-white">
        <h5 class="mb-0">Crear Prioridad</h5>
      </div>

      <div class="card-body">
        <form @submit.prevent="crearPrioridad">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la prioridad</label>
            <input
              type="text"
              id="nombre"
              v-model="form.nombre"
              class="form-control"
              placeholder="Ej: Alta, Media, Baja"
              required
            />
          </div>

          <div class="mb-3">
            <label for="color" class="form-label">Color</label>
            <input
              type="color"
              id="color"
              v-model="form.color"
              class="form-control form-control-color"
              title="Elige un color"
              required
            />
          </div>

          <button
            type="submit"
            class="btn btn-danger"
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
        color: '#ff0000', // color por defecto rojo
      },
      loading: false,
      mensaje: '',
      error: '',
    };
  },
  methods: {
    async crearPrioridad() {
      this.loading = true;
      this.mensaje = '';
      this.error = '';

      try {
        const response = await axios.post('/nuevaprioridad', this.form);
        this.mensaje = 'Prioridad creada con éxito.';
        this.form.nombre = '';
        this.form.color = '#ff0000'; // reset al color por defecto
      } catch (err) {
        this.error = err.response?.data?.message || 'Error al guardar la prioridad.';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
